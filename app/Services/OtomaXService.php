<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class OtomaXService
{
    protected bool $isMock;

    public function __construct()
    {
        $this->isMock = filter_var(env('OTOMAX_MOCK_MODE', false), FILTER_VALIDATE_BOOLEAN);

        if ($this->isMock) {
            $this->initMockData();
        }
    }

    /**
     * Call SQL Database directly or fallback to Mock
     */
    public function call(string $endpoint, array $data = [])
    {
        if ($this->isMock) {
            return $this->handleMockCall($endpoint, $data);
        }

        return $this->handleDatabaseCall($endpoint, $data);
    }

    /**
     * Direct SQL Database Queries for OtomaX MS SQL Server
     */
    protected function handleDatabaseCall(string $endpoint, array $data)
    {
        try {
            $db = DB::connection('otomax');

            switch ($endpoint) {
                case 'Test':
                    $db->getPdo(); // Trigger connection
                    return [
                        'ok' => true,
                        'result' => [
                            'status' => 'connected',
                            'database' => env('DB_DATABASE_OTOMAX', 'otomax'),
                            'driver' => 'Microsoft SQL Server (sqlsrv)'
                        ]
                    ];

                case 'GetRs':
                    $kode = $data['kode'] ?? '';
                    $row = $db->table('reseller')->where('kode', $kode)->first();
                    if (!$row) {
                        return ['ok' => false, 'desc' => 'Reseller tidak ditemukan'];
                    }
                    return ['ok' => true, 'result' => (array)$row];

                case 'GetSaldoRs':
                    $kode = $data['kode'] ?? '';
                    $row = $db->table('reseller')->where('kode', $kode)->select('saldo', 'saldo_minimal')->first();
                    if (!$row) {
                        return ['ok' => false, 'desc' => 'Reseller tidak ditemukan'];
                    }
                    return ['ok' => true, 'result' => (array)$row];

                case 'TesPinRs':
                    $kode = $data['kode'] ?? '';
                    $pin = $data['pin'] ?? ''; // Received plain pin for database comparison
                    
                    $row = $db->table('reseller')->where('kode', $kode)->select('pin')->first();
                    if (!$row) {
                        return ['ok' => false, 'desc' => 'Reseller tidak ditemukan'];
                    }
                    
                    // Direct string comparison since plain pin is stored in SQL reseller table
                    if (trim($row->pin) === trim($pin)) {
                        return ['ok' => true];
                    }
                    return ['ok' => false, 'desc' => 'PIN salah'];

                case 'UpdateRs':
                    $kode = $data['kode'] ?? '';
                    $update = [];
                    if (isset($data['pin'])) $update['pin'] = $data['pin'];
                    if (isset($data['alamat_ip'])) $update['alamat_ip'] = $data['alamat_ip'];
                    if (isset($data['password_ip'])) $update['password_ip'] = $data['password_ip'];
                    if (isset($data['url_report'])) $update['url_report'] = $data['url_report'];
                    
                    if (empty($update)) {
                        return ['ok' => true];
                    }

                    $affected = $db->table('reseller')->where('kode', $kode)->update($update);
                    return ['ok' => true, 'result' => $affected];

                case 'GetTrx':
                    $trxid = $data['trxid'] ?? 0;
                    $row = $db->table('transaksi')->where('kode', $trxid)->first();
                    if (!$row) {
                        return ['ok' => false, 'desc' => 'Transaksi tidak ditemukan'];
                    }
                    
                    $res = (array)$row;
                    $res['trxid'] = $res['kode']; // Map database primary key to trxid expected by UI
                    $res['statusDesc'] = $this->mapStatus($res['status'] ?? 0);
                    return ['ok' => true, 'result' => $res];

                case 'GetTrxHistory':
                    $kode_rs = $data['kode_reseller'] ?? '';
                    $tgl_awal = $data['tgl_awal'] ?? '';
                    $tgl_akhir = $data['tgl_akhir'] ?? '';
                    $limit = (int)($data['limit'] ?? 20); // Removed 100 cap to support 'Semua' filter

                    // Select required columns for transactions page
                    $query = $db->table('transaksi')
                        ->select('kode', 'tgl_entri', 'tgl_status', 'kode_produk', 'tujuan', 'sn', 'harga', 'status', 'ref_id', 'keterangan', 'kode_reseller', 'saldo_awal')
                        ->where('kode_reseller', $kode_rs);

                    if (!empty($tgl_awal)) {
                        $query->where('tgl_entri', '>=', $tgl_awal . ' 00:00:00');
                    }
                    if (!empty($tgl_akhir)) {
                        $query->where('tgl_entri', '<=', $tgl_akhir . ' 23:59:59');
                    }

                    if (!empty($data['tujuan'])) {
                        $query->where('tujuan', 'like', '%' . $data['tujuan'] . '%');
                    }
                    if (!empty($data['produk'])) {
                        $query->where('kode_produk', 'like', '%' . $data['produk'] . '%');
                    }
                    if (!empty($data['ref_id'])) {
                        $query->where('ref_id', 'like', '%' . $data['ref_id'] . '%');
                    }
                    if (!empty($data['status'])) {
                        $statusVal = $data['status'];
                        if ($statusVal === 'Sukses') {
                            $query->whereIn('status', [20, 3, 4]);
                        } elseif ($statusVal === 'Proses') {
                            $query->whereIn('status', [0, 1]);
                        } elseif ($statusVal === 'Gagal') {
                            $query->whereNotIn('status', [0, 1, 20, 3, 4]);
                        }
                    }

                    $rows = $query->orderBy('tgl_entri', 'desc')->limit($limit)->get();

                    $mapped = $rows->map(function ($r) {
                        $arr = (array)$r;
                        $arr['trxid'] = $arr['kode'];
                        $arr['status_raw'] = $arr['status'];
                        $arr['status'] = ($arr['status'] == 20 || $arr['status'] == 3 || $arr['status'] == 4) ? 'Sukses' : (($arr['status'] == 1 || $arr['status'] == 0) ? 'Proses' : 'Gagal');
                        
                        $harga = (float)($arr['harga'] ?? 0);
                        $saldo_awal = (float)($arr['saldo_awal'] ?? 0);
                        // If failed, usually balance is refunded, so saldo_akhir = saldo_awal
                        if ($arr['status'] === 'Gagal') {
                            $arr['saldo_akhir'] = $saldo_awal;
                        } else {
                            $arr['saldo_akhir'] = $saldo_awal - $harga;
                        }

                        return $arr;
                    })->toArray();

                    return ['ok' => true, 'result' => $mapped];

                case 'GetDaftarHargaRs':
                    $kode_rs = $data['kode_reseller'] ?? '';
                    $kode_prod = $data['kode_produk'] ?? '';
                    $filter = $data['sort_filter'] ?? 'semua';

                    // 1. Get reseller's kode_level or use explicit level
                    $kode_level = $data['kode_level'] ?? null;
                    if (empty($kode_level) && !empty($kode_rs)) {
                        $reseller = $db->table('reseller')->where('kode', $kode_rs)->select('kode_level')->first();
                        $kode_level = $reseller->kode_level ?? null;
                    }

                    // 2. Select required columns
                    $query = $db->table('produk')
                        ->select(
                            'produk.kode', 
                            'produk.nama', 
                            'produk.harga_jual', 
                            'produk.harga_awal', 
                            'produk.kode_operator', 
                            'produk.aktif',
                            'produk.catatan',
                            'produk.gangguan'
                        );

                    // 3. Left join with produk_level if kode_level exists
                    if ($kode_level) {
                        $query->leftJoin('produk_level', function ($join) use ($kode_level) {
                            $join->on('produk.kode', '=', 'produk_level.kode_produk')
                                 ->where('produk_level.kode_level', '=', $kode_level);
                        });
                        $query->addSelect('produk_level.harga as harga_level');
                    }

                    if (!empty($kode_prod)) {
                        $query->where('produk.kode', $kode_prod);
                    }

                    if ($filter === 'terlaris') {
                        try {
                            // Optimize query by looking up only the last 30 days and limiting to top 20 popular codes
                            // This prevents a heavy table scan on millions of transaction rows
                            $popularCodes = $db->table('transaksi')
                                ->where('tgl_entri', '>=', now()->subDays(30))
                                ->select('kode_produk', DB::raw('count(*) as count_trx'))
                                ->groupBy('kode_produk')
                                ->orderBy('count_trx', 'desc')
                                ->limit(20)
                                ->pluck('kode_produk')
                                ->toArray();
                            
                            if (!empty($popularCodes)) {
                                $orderByCase = 'CASE ';
                                foreach ($popularCodes as $index => $code) {
                                    $orderByCase .= "WHEN produk.kode = " . $db->getPdo()->quote($code) . " THEN " . $index . " ";
                                }
                                $orderByCase .= "ELSE 9999 END";
                                $query->orderByRaw($orderByCase);
                            } else {
                                $query->orderBy('produk.nama', 'asc');
                            }
                        } catch (\Exception $e) {
                            $query->orderBy('produk.nama', 'asc');
                        }
                    } elseif ($filter === 'termurah') {
                        $query->orderBy('produk.harga_jual', 'asc');
                    } else {
                        $query->orderBy('produk.kode_operator', 'asc')->orderBy('produk.nama', 'asc');
                    }

                    $rows = $query->get();

                    $mapped = $rows->map(function ($r) {
                        $arr = (array)$r;
                        return [
                            'kode' => $arr['kode'],
                            'nama' => $arr['nama'],
                            'harga' => $arr['harga_level'] ?? $arr['harga_jual'] ?? $arr['harga_awal'] ?? 0,
                            'operator' => $arr['kode_operator'],
                            'status' => $arr['aktif'] ? 'Aktif' : 'Nonaktif',
                            'catatan' => $arr['catatan'] ?? '',
                            'gangguan' => $arr['gangguan'] ?? 0
                        ];
                    })->toArray();

                    return ['ok' => true, 'result' => $mapped];

                case 'GetLevels':
                    try {
                        // Ambil daftar level dari tabel level (jika ada) atau distinct dari produk_level / reseller
                        $levels = [];
                        if ($db->getSchemaBuilder()->hasTable('level')) {
                            $levels = $db->table('level')->select('kode', 'nama')->orderBy('kode')->get()->map(fn($r) => (array)$r)->toArray();
                        } else {
                            $distLevels = $db->table('produk_level')->distinct()->pluck('kode_level')->filter()->values();
                            if ($distLevels->isEmpty()) {
                                $distLevels = $db->table('reseller')->distinct()->pluck('kode_level')->filter()->values();
                            }
                            $levels = $distLevels->map(fn($lvl) => ['kode' => $lvl, 'nama' => 'Level ' . strtoupper($lvl)])->toArray();
                        }
                        return ['ok' => true, 'result' => $levels];
                    } catch (\Exception $e) {
                        return ['ok' => true, 'result' => []];
                    }

                case 'GetSaldoDompet':
                    $kode_rs = $data['kode_reseller'] ?? '';
                    $dompet = $data['kode_dompet'] ?? 'A';

                    $row = $db->table('reseller')->where('kode', $kode_rs)->select('saldo', 'komisi')->first();
                    if (!$row) {
                        return ['ok' => false, 'desc' => 'Reseller tidak ditemukan'];
                    }

                    $balance = ($dompet === 'A') ? $row->saldo : ($row->komisi ?? 0);

                    return [
                        'ok' => true,
                        'result' => [
                            'kode_reseller' => $kode_rs,
                            'kode_dompet' => $dompet,
                            'saldo' => $balance
                        ]
                    ];
                    
                case 'GetMutasiDeposit':
                    $kode_rs = $data['kode_reseller'] ?? '';
                    $limit = min((int)($data['limit'] ?? 50), 100);
                    
                    $rows = $db->table('mutasi')
                        ->where('kode_reseller', $kode_rs)
                        ->where(function($q) {
                            $q->where('keterangan', 'LIKE', '%deposit%')
                              ->orWhere('keterangan', 'LIKE', '%saldo%');
                        })
                        ->orderBy('tanggal', 'desc')
                        ->limit($limit)
                        ->get();

                    return ['ok' => true, 'result' => $rows->map(fn($item) => (array)$item)->toArray()];

                case 'RequestTiketDeposit':
                    $kode_rs = $data['kode_reseller'] ?? '';
                    $nominal = (int)($data['nominal'] ?? 0);
                    $bank = $data['bank'] ?? '';
                    $rekening = $data['rekening'] ?? '';
                    $atas_nama = $data['atas_nama'] ?? '';
                    
                    if ($nominal < 10000) {
                        return ['ok' => false, 'desc' => 'Nominal minimal Rp 10.000'];
                    }

                    // Prevent spam: Check if user has an active pending ticket today with the SAME base nominal
                    $existingPending = $db->table('tiket_deposit')
                        ->where('kode_reseller', $kode_rs)
                        ->where('status', 'O')
                        ->whereRaw('CAST(waktu AS DATE) = CAST(GETDATE() AS DATE)')
                        ->whereRaw('FLOOR(jumlah / 1000) * 1000 = ?', [$nominal])
                        ->first();
                    
                    if ($existingPending) {
                        return ['ok' => false, 'desc' => 'Anda masih memiliki tiket yang belum ditransfer sebesar Rp ' . number_format($existingPending->jumlah, 0, ',', '.') . '. Silakan selesaikan, batalkan, atau pilih nominal deposit yang berbeda.'];
                    }

                    // Find a unique nominal (base nominal + random 1-999)
                    $uniqueFound = false;
                    $attempts = 0;
                    $finalNominal = $nominal;

                    while (!$uniqueFound && $attempts < 20) {
                        $attempts++;
                        $rand = rand(1, 999);
                        $testNominal = $nominal + $rand;

                        $exists = $db->table('tiket_deposit')
                            ->where('jumlah', $testNominal)
                            ->where('status', 'O')
                            ->whereRaw('CAST(waktu AS DATE) = CAST(GETDATE() AS DATE)')
                            ->exists();
                        
                        if (!$exists) {
                            $finalNominal = $testNominal;
                            $uniqueFound = true;
                        }
                    }

                    if (!$uniqueFound) {
                        return ['ok' => false, 'desc' => 'Sistem sedang sibuk. Silakan coba nominal lain.'];
                    }

                    // Insert ticket
                    $db->table('tiket_deposit')->insert([
                        'waktu' => now(),
                        'jumlah' => $finalNominal,
                        'status' => 'O',
                        'tgl_status' => now(),
                        'kode_reseller' => $kode_rs,
                        'bank' => $bank
                    ]);

                    $inserted = $db->table('tiket_deposit')
                        ->where('kode_reseller', $kode_rs)
                        ->where('jumlah', $finalNominal)
                        ->where('status', 'O')
                        ->orderBy('waktu', 'desc')
                        ->first();

                    $meta = [
                        'bank' => $bank,
                        'rekening' => $rekening,
                        'atas_nama' => $atas_nama
                    ];

                    if ($inserted && isset($inserted->kode)) {
                        \Illuminate\Support\Facades\Cache::put('deposit_ticket_meta_id_' . $inserted->kode, $meta, now()->addDays(2));
                    }
                    \Illuminate\Support\Facades\Cache::put('deposit_ticket_meta_' . $finalNominal, $meta, now()->addDays(2));

                    return [
                        'ok' => true, 
                        'result' => [
                            'nominal' => $finalNominal,
                            'bank' => $bank,
                            'rekening' => $rekening,
                            'atas_nama' => $atas_nama
                        ]
                    ];

                case 'GetPendingTiket':
                    $kode_rs = $data['kode_reseller'] ?? '';
                    $rows = $db->table('tiket_deposit')
                        ->where('kode_reseller', $kode_rs)
                        ->where('status', 'O')
                        ->orderBy('waktu', 'desc')
                        ->get();
                    return ['ok' => true, 'result' => $rows->map(fn($item) => (array)$item)->toArray()];

                case 'CancelTiketDeposit':
                    $kode_rs = $data['kode_reseller'] ?? '';
                    $jumlah = (int)($data['jumlah'] ?? 0);
                    $waktu = $data['waktu'] ?? ''; // or by id if there is an id column, let's assume 'kode' is the primary key
                    $kode = $data['kode'] ?? 0;
                    
                    if ($kode) {
                        $db->table('tiket_deposit')
                            ->where('kode_reseller', $kode_rs)
                            ->where('kode', $kode)
                            ->where('status', 'O')
                            ->update(['status' => 'C', 'tgl_status' => now()]);
                        
                        return ['ok' => true, 'desc' => 'Tiket deposit berhasil dibatalkan.'];
                    }
                    return ['ok' => false, 'desc' => 'Tiket tidak ditemukan.'];

                case 'TambahSaldo':
                    $kode_rs = $data['kode_reseller'] ?? '';
                    $jumlah = $data['jumlah'] ?? 0;

                    $db->table('reseller')->where('kode', $kode_rs)->increment('saldo', $jumlah);
                    
                    return ['ok' => true, 'result' => true];

                case 'TambahPoin':
                    $kode_rs = $data['kode_reseller'] ?? '';
                    $jumlah = $data['jumlah'] ?? 0;

                    $db->table('reseller')->where('kode', $kode_rs)->increment('poin', $jumlah);
                    
                    return ['ok' => true, 'result' => true];

                case 'InsertReseller':
                    $name = $data['name'] ?? '';
                    $pengirim = $data['pengirim'] ?? '';
                    $pin = $data['pin'] ?? '1234';

                    $kode_new = strtolower(str_replace(' ', '', $name));
                    
                    $db->table('reseller')->insert([
                        'kode' => $kode_new,
                        'nama' => $name,
                        'pin' => $pin,
                        'aktif' => 1,
                        'saldo' => 0,
                        'poin' => 0,
                        'komisi' => 0,
                        'tgl_daftar' => now(),
                        'kode_level' => 'O'
                    ]);

                    return [
                        'ok' => true,
                        'result' => [
                            'kode_reseller' => $kode_new,
                            'name' => $name,
                            'pengirim' => $pengirim
                        ]
                    ];

                case 'InsertResellerFull':
                    // Full insert used by admin approval — maps all registration fields
                    $kode        = $data['kode']         ?? '';
                    $name        = $data['nama']         ?? '';
                    $nama_pemilik= $data['nama_pemilik']  ?? '';
                    $pengirim    = $data['pengirim']     ?? ''; // no_hp / WA
                    $pin         = $data['pin']          ?? '1234';
                    $email       = $data['email']        ?? '';
                    $alamat      = $data['alamat']       ?? '';
                    $nomor_ktp   = $data['nomor_ktp']    ?? '';
                    $npwp        = $data['npwp']         ?? '';
                    $alamat_ip   = $data['alamat_ip']    ?? '';
                    $password_ip = $data['password_ip']  ?? '';
                    $url_report  = $data['url_report']   ?? '';

                    if (empty($kode) || empty($name)) {
                        return ['ok' => false, 'desc' => 'Kode dan nama wajib diisi.'];
                    }

                    // Delete existing reseller and sender entries (case-insensitive) to ensure fresh insert and clean state
                    try {
                        $db->table('reseller')->where('kode', $kode)->orWhere('kode', strtolower($kode))->delete();
                        $db->table('pengirim')->where('kode_reseller', $kode)->orWhere('kode_reseller', strtolower($kode))->delete();
                        \Illuminate\Support\Facades\Log::info("OtomaXService [InsertResellerFull]: Berhasil membersihkan data lama reseller '{$kode}' sebelum insert baru.");
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::warning("OtomaXService [InsertResellerFull]: Gagal membersihkan data lama reseller '{$kode}': " . $e->getMessage());
                    }

                    $insertData = [
                        'kode'             => $kode,
                        'nama'             => $name,
                        'pin'              => $pin,
                        'aktif'            => 1,
                        'saldo'            => 0,
                        'poin'             => 0,
                        'komisi'           => 0,
                        'tgl_daftar'       => now(),
                        'kode_level'       => 'H',
                        'nomor_hp'         => $pengirim,
                        'saldo_minimal'    => 0,
                        'suspend'          => 0,
                        'deleted'          => 0,
                        'markup'           => 0,
                        'harus_ubah_pin'   => 0,
                        'pengingat_saldo'  => 0,
                        'f_pengingat_saldo'=> 0,
                    ];

                    // Add optional columns if they exist on the reseller table
                    if (!empty($nama_pemilik)) $insertData['nama_pemilik'] = $nama_pemilik;
                    if (!empty($email))       $insertData['email']       = $email;
                    if (!empty($alamat))      $insertData['alamat']      = $alamat;
                    if (!empty($nomor_ktp))   $insertData['nomor_ktp']   = $nomor_ktp;
                    if (!empty($npwp))        $insertData['npwp']        = $npwp;
                    if (!empty($alamat_ip))   $insertData['alamat_ip']   = $alamat_ip;
                    if (!empty($password_ip)) $insertData['password_ip'] = $password_ip;
                    if (!empty($url_report))  $insertData['url_report']  = $url_report;

                    try {
                        \Illuminate\Support\Facades\Log::info("OtomaXService [InsertResellerFull]: Memulai pendaftaran reseller baru di database OtomaX.", [
                            'kode' => $kode,
                            'nama' => $name,
                            'nama_pemilik' => $nama_pemilik,
                            'nomor_hp' => $pengirim,
                            'email' => $email
                        ]);
                        $db->table('reseller')->insert($insertData);
                        \Illuminate\Support\Facades\Log::info("OtomaXService [InsertResellerFull]: Full insert berhasil untuk kode reseller '{$kode}'.");
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::warning("OtomaXService [InsertResellerFull]: Full insert gagal untuk kode reseller '{$kode}'. Pesan error: " . $e->getMessage() . ". Mencoba fallback insert dengan kolom utama...");
                        
                        try {
                            $fallbackKeys = ['kode', 'nama', 'pin', 'aktif', 'saldo', 'poin', 'komisi', 'tgl_daftar', 'kode_level', 'nomor_hp'];
                            $fallbackData = array_intersect_key($insertData, array_flip($fallbackKeys));
                            $db->table('reseller')->insert($fallbackData);
                            \Illuminate\Support\Facades\Log::info("OtomaXService [InsertResellerFull]: Fallback insert berhasil untuk kode reseller '{$kode}'.");
                        } catch (\Exception $fallbackEx) {
                            \Illuminate\Support\Facades\Log::error("OtomaXService [InsertResellerFull]: Fallback insert gagal total untuk reseller '{$kode}'. Pesan error: " . $fallbackEx->getMessage());
                            throw $fallbackEx;
                        }
                    }

                    // Insert Sender/Pengirim details in the separate pengirim table
                    try {
                        // Insert WhatsApp number (tipe 'W' / WhatsApp)
                        if (!empty($pengirim)) {
                            \Illuminate\Support\Facades\Log::info("OtomaXService [InsertResellerFull]: Mendaftarkan nomor WhatsApp '{$pengirim}' untuk reseller '{$kode}' ke tabel pengirim...");
                            $db->table('pengirim')->insert([
                                'pengirim'      => $pengirim,
                                'tipe_pengirim' => 'W',
                                'kode_reseller' => $kode
                            ]);
                            \Illuminate\Support\Facades\Log::info("OtomaXService [InsertResellerFull]: WhatsApp '{$pengirim}' berhasil didaftarkan ke tabel pengirim.");
                        }
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::warning("OtomaXService [InsertResellerFull]: Gagal mendaftarkan pengirim '{$pengirim}' untuk reseller '{$kode}'. Pesan error: " . $e->getMessage());
                    }

                    return [
                        'ok'     => true,
                        'result' => [
                            'kode_reseller' => $kode,
                            'nama'          => $name,
                            'pengirim'      => $pengirim,
                        ]
                    ];

                case 'GetFormatRequest':
                    $nama = $data['nama'] ?? '';
                    return [
                        'ok' => true,
                        'result' => [
                            ['nama' => $nama, 'format' => 'TIKET.[jumlah].[pin]']
                        ]
                    ];

                case 'GetVersiOtomaX':
                    return [
                        'ok' => true,
                        'result' => 'Database SQL Server Mode'
                    ];

                case 'GetTrendTransaksi':
                    $kode_rs  = $data['kode_reseller'] ?? '';
                    $days     = min((int)($data['days'] ?? 14), 30);
                    $dateFrom = now()->subDays($days - 1)->startOfDay();

                    // Query daily transaction count and total harga for last N days
                    // Using CAST to DATE for SQL Server date grouping (lightweight — indexed on tgl_entri)
                    $rows = $db->table('transaksi')
                        ->select(
                            DB::raw("CAST(tgl_entri AS DATE) as tgl"),
                            DB::raw("COUNT(*) as total_trx"),
                            DB::raw("SUM(CASE WHEN status IN (20,3,4) THEN 1 ELSE 0 END) as trx_sukses"),
                            DB::raw("SUM(CASE WHEN status IN (20,3,4) THEN harga ELSE 0 END) as omzet")
                        )
                        ->where('kode_reseller', $kode_rs)
                        ->where('tgl_entri', '>=', $dateFrom)
                        ->groupBy(DB::raw("CAST(tgl_entri AS DATE)"))
                        ->orderBy(DB::raw("CAST(tgl_entri AS DATE)"), 'asc')
                        ->get();

                    // Fill in missing dates with zero values to keep chart continuous
                    $result  = [];
                    $rowsMap = $rows->keyBy('tgl');
                    for ($i = $days - 1; $i >= 0; $i--) {
                        $d   = now()->subDays($i)->format('Y-m-d');
                        $row = $rowsMap->get($d);
                        $result[] = [
                            'tgl'        => $d,
                            'total_trx'  => $row ? (int)$row->total_trx  : 0,
                            'trx_sukses' => $row ? (int)$row->trx_sukses : 0,
                            'omzet'      => $row ? (float)$row->omzet    : 0,
                        ];
                    }

                    return ['ok' => true, 'result' => $result];

                case 'GetAllResellers':
                    $rows = $db->table('reseller')
                        ->select('kode', 'nama', 'saldo', 'kode_level', 'aktif')
                        ->orderBy('nama', 'asc')
                        ->get();
                    return ['ok' => true, 'result' => $rows->map(fn($r) => (array)$r)->toArray()];

                case 'GetAdminDashboardStats':
                    $today = now()->format('Y-m-d');
                    $yesterday = now()->subDay()->format('Y-m-d');
                    $startMonth = now()->startOfMonth()->format('Y-m-d');

                    // Hari Ini
                    $todayStats = $db->table('transaksi')
                        ->select(
                            DB::raw("COUNT(*) as total_trx"),
                            DB::raw("SUM(CASE WHEN status IN (20,3,4) THEN 1 ELSE 0 END) as trx_sukses"),
                            DB::raw("SUM(CASE WHEN status IN (0,1) THEN 1 ELSE 0 END) as trx_proses"),
                            DB::raw("SUM(CASE WHEN status NOT IN (20,3,4,0,1) THEN 1 ELSE 0 END) as trx_gagal"),
                            DB::raw("SUM(CASE WHEN status IN (20,3,4) THEN harga ELSE 0 END) as omset"),
                            DB::raw("SUM(CASE WHEN status IN (20,3,4) THEN ISNULL(harga_beli, 0) ELSE 0 END) as modal"),
                            DB::raw("SUM(CASE WHEN status IN (20,3,4) THEN (harga - ISNULL(harga_beli, 0)) ELSE 0 END) as profit")
                        )
                        ->where('tgl_entri', '>=', $today . ' 00:00:00')
                        ->where('tgl_entri', '<=', $today . ' 23:59:59')
                        ->first();

                    // Kemarin
                    $yesterdayStats = $db->table('transaksi')
                        ->select(
                            DB::raw("SUM(CASE WHEN status IN (20,3,4) THEN harga ELSE 0 END) as omset"),
                            DB::raw("SUM(CASE WHEN status IN (20,3,4) THEN (harga - ISNULL(harga_beli, 0)) ELSE 0 END) as profit")
                        )
                        ->where('tgl_entri', '>=', $yesterday . ' 00:00:00')
                        ->where('tgl_entri', '<=', $yesterday . ' 23:59:59')
                        ->first();

                    // Bulan Ini
                    $monthStats = $db->table('transaksi')
                        ->select(
                            DB::raw("COUNT(*) as total_trx"),
                            DB::raw("SUM(CASE WHEN status IN (20,3,4) THEN 1 ELSE 0 END) as trx_sukses"),
                            DB::raw("SUM(CASE WHEN status IN (20,3,4) THEN harga ELSE 0 END) as omset"),
                            DB::raw("SUM(CASE WHEN status IN (20,3,4) THEN ISNULL(harga_beli, 0) ELSE 0 END) as modal"),
                            DB::raw("SUM(CASE WHEN status IN (20,3,4) THEN (harga - ISNULL(harga_beli, 0)) ELSE 0 END) as profit")
                        )
                        ->where('tgl_entri', '>=', $startMonth . ' 00:00:00')
                        ->where('tgl_entri', '<=', $today . ' 23:59:59')
                        ->first();

                    // Server Info
                    $totalSaldo = (float)$db->table('reseller')->sum('saldo');
                    $totalAgenAktif = (int)$db->table('reseller')->where('aktif', 1)->count();

                    // 7 Hari Terakhir
                    $trendStart = now()->subDays(6)->format('Y-m-d');
                    $trendRows = $db->table('transaksi')
                        ->select(
                            DB::raw("CAST(tgl_entri AS DATE) as tgl"),
                            DB::raw("COUNT(*) as total_trx"),
                            DB::raw("SUM(CASE WHEN status IN (20,3,4) THEN 1 ELSE 0 END) as trx_sukses"),
                            DB::raw("SUM(CASE WHEN status IN (20,3,4) THEN harga ELSE 0 END) as omset"),
                            DB::raw("SUM(CASE WHEN status IN (20,3,4) THEN ISNULL(harga_beli, 0) ELSE 0 END) as modal"),
                            DB::raw("SUM(CASE WHEN status IN (20,3,4) THEN (harga - ISNULL(harga_beli, 0)) ELSE 0 END) as profit")
                        )
                        ->where('tgl_entri', '>=', $trendStart . ' 00:00:00')
                        ->groupBy(DB::raw("CAST(tgl_entri AS DATE)"))
                        ->orderBy(DB::raw("CAST(tgl_entri AS DATE)"), 'asc')
                        ->get();

                    $trendMap = $trendRows->keyBy(function($item) {
                        return substr((string)$item->tgl, 0, 10);
                    });

                    $trendChart = [];
                    for ($i = 6; $i >= 0; $i--) {
                        $dt = now()->subDays($i);
                        $d = $dt->format('Y-m-d');
                        $row = $trendMap->get($d);
                        $trendChart[] = [
                            'tgl' => $d,
                            'label' => $dt->format('d M'),
                            'total_trx' => $row ? (int)$row->total_trx : 0,
                            'trx_sukses' => $row ? (int)$row->trx_sukses : 0,
                            'omset' => $row ? (float)$row->omset : 0,
                            'modal' => $row ? (float)$row->modal : 0,
                            'profit' => $row ? (float)$row->profit : 0,
                        ];
                    }

                    // Top 5 Reseller Bulan Ini
                    $topResellers = $db->table('transaksi')
                        ->join('reseller', 'transaksi.kode_reseller', '=', 'reseller.kode')
                        ->select(
                            'reseller.kode',
                            'reseller.nama',
                            'reseller.kode_level',
                            'reseller.saldo',
                            DB::raw("COUNT(*) as total_trx"),
                            DB::raw("SUM(CASE WHEN transaksi.status IN (20,3,4) THEN 1 ELSE 0 END) as trx_sukses"),
                            DB::raw("SUM(CASE WHEN transaksi.status IN (20,3,4) THEN transaksi.harga ELSE 0 END) as omset"),
                            DB::raw("SUM(CASE WHEN transaksi.status IN (20,3,4) THEN ISNULL(transaksi.harga_beli, 0) ELSE 0 END) as modal"),
                            DB::raw("SUM(CASE WHEN transaksi.status IN (20,3,4) THEN (transaksi.harga - ISNULL(transaksi.harga_beli, 0)) ELSE 0 END) as profit")
                        )
                        ->where('transaksi.tgl_entri', '>=', $startMonth . ' 00:00:00')
                        ->groupBy('reseller.kode', 'reseller.nama', 'reseller.kode_level', 'reseller.saldo')
                        ->orderBy('profit', 'desc')
                        ->limit(5)
                        ->get();

                    // Reseller dengan Laba Minus (Rugi) dalam Bulan Ini
                    $minusResellers = $db->table('transaksi')
                        ->join('reseller', 'transaksi.kode_reseller', '=', 'reseller.kode')
                        ->select(
                            'reseller.kode',
                            'reseller.nama',
                            DB::raw("SUM(CASE WHEN transaksi.status IN (20,3,4) THEN (transaksi.harga - ISNULL(transaksi.harga_beli, 0)) ELSE 0 END) as profit"),
                            DB::raw("SUM(CASE WHEN transaksi.status IN (20,3,4) THEN transaksi.harga ELSE 0 END) as omset")
                        )
                        ->where('transaksi.tgl_entri', '>=', $startMonth . ' 00:00:00')
                        ->groupBy('reseller.kode', 'reseller.nama')
                        ->havingRaw("SUM(CASE WHEN transaksi.status IN (20,3,4) THEN (transaksi.harga - ISNULL(transaksi.harga_beli, 0)) ELSE 0 END) < 0")
                        ->limit(5)
                        ->get();

                    $todayOmset = (float)($todayStats->omset ?? 0);
                    $todayProfit = (float)($todayStats->profit ?? 0);
                    $monthOmset = (float)($monthStats->omset ?? 0);
                    $monthProfit = (float)($monthStats->profit ?? 0);

                    return [
                        'ok' => true,
                        'result' => [
                            'today' => [
                                'total_trx' => (int)($todayStats->total_trx ?? 0),
                                'trx_sukses' => (int)($todayStats->trx_sukses ?? 0),
                                'trx_proses' => (int)($todayStats->trx_proses ?? 0),
                                'trx_gagal' => (int)($todayStats->trx_gagal ?? 0),
                                'omset' => $todayOmset,
                                'modal' => (float)($todayStats->modal ?? 0),
                                'profit' => $todayProfit,
                                'margin' => $todayOmset > 0 ? round(($todayProfit / $todayOmset) * 100, 2) : 0,
                            ],
                            'yesterday' => [
                                'omset' => (float)($yesterdayStats->omset ?? 0),
                                'profit' => (float)($yesterdayStats->profit ?? 0),
                            ],
                            'month' => [
                                'total_trx' => (int)($monthStats->total_trx ?? 0),
                                'trx_sukses' => (int)($monthStats->trx_sukses ?? 0),
                                'omset' => $monthOmset,
                                'modal' => (float)($monthStats->modal ?? 0),
                                'profit' => $monthProfit,
                                'margin' => $monthOmset > 0 ? round(($monthProfit / $monthOmset) * 100, 2) : 0,
                            ],
                            'server' => [
                                'total_saldo' => $totalSaldo,
                                'total_reseller_aktif' => $totalAgenAktif,
                            ],
                            'trend_chart' => $trendChart,
                            'top_resellers' => $topResellers->map(function($r) {
                                $arr = (array)$r;
                                $arr['margin'] = ($arr['omset'] > 0) ? round(($arr['profit'] / $arr['omset']) * 100, 2) : 0;
                                return $arr;
                            })->toArray(),
                            'minus_resellers' => $minusResellers->map(fn($r) => (array)$r)->toArray(),
                        ]
                    ];

                case 'GetAdminResellerProfit':
                    $tgl_awal = !empty($data['tgl_awal']) ? $data['tgl_awal'] : now()->startOfMonth()->format('Y-m-d');
                    $tgl_akhir = !empty($data['tgl_akhir']) ? $data['tgl_akhir'] : now()->format('Y-m-d');
                    $kode_reseller = trim($data['kode_reseller'] ?? '');
                    $profit_filter = $data['profit_filter'] ?? 'all';
                    $search = trim($data['search'] ?? '');

                    $query = $db->table('transaksi')
                        ->join('reseller', 'transaksi.kode_reseller', '=', 'reseller.kode')
                        ->select(
                            'reseller.kode',
                            'reseller.nama',
                            'reseller.kode_level',
                            'reseller.saldo',
                            'reseller.aktif',
                            DB::raw("COUNT(transaksi.kode) as total_trx"),
                            DB::raw("SUM(CASE WHEN transaksi.status IN (20,3,4) THEN 1 ELSE 0 END) as trx_sukses"),
                            DB::raw("SUM(CASE WHEN transaksi.status IN (0,1) THEN 1 ELSE 0 END) as trx_proses"),
                            DB::raw("SUM(CASE WHEN transaksi.status NOT IN (20,3,4,0,1) THEN 1 ELSE 0 END) as trx_gagal"),
                            DB::raw("SUM(CASE WHEN transaksi.status IN (20,3,4) THEN transaksi.harga ELSE 0 END) as total_omset"),
                            DB::raw("SUM(CASE WHEN transaksi.status IN (20,3,4) THEN ISNULL(transaksi.harga_beli, 0) ELSE 0 END) as total_modal"),
                            DB::raw("SUM(CASE WHEN transaksi.status IN (20,3,4) THEN (transaksi.harga - ISNULL(transaksi.harga_beli, 0)) ELSE 0 END) as total_profit")
                        )
                        ->where('transaksi.tgl_entri', '>=', $tgl_awal . ' 00:00:00')
                        ->where('transaksi.tgl_entri', '<=', $tgl_akhir . ' 23:59:59');

                    if (!empty($kode_reseller)) {
                        $query->where('reseller.kode', $kode_reseller);
                    }

                    if (!empty($search)) {
                        $query->where(function($q) use ($search) {
                            $q->where('reseller.kode', 'like', "%{$search}%")
                              ->orWhere('reseller.nama', 'like', "%{$search}%");
                        });
                    }

                    $query->groupBy('reseller.kode', 'reseller.nama', 'reseller.kode_level', 'reseller.saldo', 'reseller.aktif');

                    if ($profit_filter === 'profit') {
                        $query->havingRaw("SUM(CASE WHEN transaksi.status IN (20,3,4) THEN (transaksi.harga - ISNULL(transaksi.harga_beli, 0)) ELSE 0 END) > 0");
                    } elseif ($profit_filter === 'minus') {
                        $query->havingRaw("SUM(CASE WHEN transaksi.status IN (20,3,4) THEN (transaksi.harga - ISNULL(transaksi.harga_beli, 0)) ELSE 0 END) <= 0");
                    }

                    $query->orderBy('total_profit', 'desc');

                    $rows = $query->get();

                    $items = [];
                    $sumOmset = 0;
                    $sumModal = 0;
                    $sumProfit = 0;
                    $sumTrxSukses = 0;
                    $sumTrxGagal = 0;

                    foreach ($rows as $row) {
                        $omset = (float)($row->total_omset ?? 0);
                        $modal = (float)($row->total_modal ?? 0);
                        $profit = (float)($row->total_profit ?? 0);
                        $margin = $omset > 0 ? round(($profit / $omset) * 100, 2) : 0;

                        $sumOmset += $omset;
                        $sumModal += $modal;
                        $sumProfit += $profit;
                        $sumTrxSukses += (int)($row->trx_sukses ?? 0);
                        $sumTrxGagal += (int)($row->trx_gagal ?? 0);

                        $items[] = [
                            'kode' => $row->kode,
                            'nama' => $row->nama,
                            'kode_level' => $row->kode_level,
                            'saldo' => (float)$row->saldo,
                            'aktif' => (int)$row->aktif,
                            'total_trx' => (int)$row->total_trx,
                            'trx_sukses' => (int)$row->trx_sukses,
                            'trx_proses' => (int)$row->trx_proses,
                            'trx_gagal' => (int)$row->trx_gagal,
                            'omset' => $omset,
                            'modal' => $modal,
                            'profit' => $profit,
                            'margin' => $margin,
                            'is_profit' => $profit >= 0,
                        ];
                    }

                    $avgMargin = $sumOmset > 0 ? round(($sumProfit / $sumOmset) * 100, 2) : 0;

                    return [
                        'ok' => true,
                        'result' => [
                            'items' => $items,
                            'summary' => [
                                'total_omset' => $sumOmset,
                                'total_modal' => $sumModal,
                                'total_profit' => $sumProfit,
                                'avg_margin' => $avgMargin,
                                'total_trx_sukses' => $sumTrxSukses,
                                'total_trx_gagal' => $sumTrxGagal,
                                'count_reseller' => count($items),
                            ],
                            'filter' => [
                                'tgl_awal' => $tgl_awal,
                                'tgl_akhir' => $tgl_akhir,
                                'kode_reseller' => $kode_reseller,
                                'profit_filter' => $profit_filter,
                                'search' => $search
                            ]
                        ]
                    ];

                case 'GetAdminResellerDetail':
                    $kode = $data['kode_reseller'] ?? '';
                    $tgl_awal = !empty($data['tgl_awal']) ? $data['tgl_awal'] : now()->startOfMonth()->format('Y-m-d');
                    $tgl_akhir = !empty($data['tgl_akhir']) ? $data['tgl_akhir'] : now()->format('Y-m-d');

                    $reseller = $db->table('reseller')->where('kode', $kode)->first();
                    if (!$reseller) {
                        return ['ok' => false, 'desc' => 'Reseller tidak ditemukan'];
                    }

                    $trxSummary = $db->table('transaksi')
                        ->select(
                            DB::raw("COUNT(*) as total_trx"),
                            DB::raw("SUM(CASE WHEN status IN (20,3,4) THEN 1 ELSE 0 END) as trx_sukses"),
                            DB::raw("SUM(CASE WHEN status IN (0,1) THEN 1 ELSE 0 END) as trx_proses"),
                            DB::raw("SUM(CASE WHEN status NOT IN (20,3,4,0,1) THEN 1 ELSE 0 END) as trx_gagal"),
                            DB::raw("SUM(CASE WHEN status IN (20,3,4) THEN harga ELSE 0 END) as omset"),
                            DB::raw("SUM(CASE WHEN status IN (20,3,4) THEN ISNULL(harga_beli, 0) ELSE 0 END) as modal"),
                            DB::raw("SUM(CASE WHEN status IN (20,3,4) THEN (harga - ISNULL(harga_beli, 0)) ELSE 0 END) as profit")
                        )
                        ->where('kode_reseller', $kode)
                        ->where('tgl_entri', '>=', $tgl_awal . ' 00:00:00')
                        ->where('tgl_entri', '<=', $tgl_akhir . ' 23:59:59')
                        ->first();

                    $topProducts = $db->table('transaksi')
                        ->select(
                            'kode_produk',
                            DB::raw("COUNT(*) as total_trx"),
                            DB::raw("SUM(CASE WHEN status IN (20,3,4) THEN 1 ELSE 0 END) as trx_sukses"),
                            DB::raw("SUM(CASE WHEN status IN (20,3,4) THEN harga ELSE 0 END) as omset"),
                            DB::raw("SUM(CASE WHEN status IN (20,3,4) THEN ISNULL(harga_beli, 0) ELSE 0 END) as modal"),
                            DB::raw("SUM(CASE WHEN status IN (20,3,4) THEN (harga - ISNULL(harga_beli, 0)) ELSE 0 END) as profit")
                        )
                        ->where('kode_reseller', $kode)
                        ->where('tgl_entri', '>=', $tgl_awal . ' 00:00:00')
                        ->where('tgl_entri', '<=', $tgl_akhir . ' 23:59:59')
                        ->groupBy('kode_produk')
                        ->orderBy('total_trx', 'desc')
                        ->limit(10)
                        ->get();

                    $recentTrx = $db->table('transaksi')
                        ->select('kode', 'tgl_entri', 'kode_produk', 'tujuan', 'harga', 'harga_beli', 'status', 'sn', 'ref_id')
                        ->where('kode_reseller', $kode)
                        ->where('tgl_entri', '>=', $tgl_awal . ' 00:00:00')
                        ->where('tgl_entri', '<=', $tgl_akhir . ' 23:59:59')
                        ->orderBy('tgl_entri', 'desc')
                        ->limit(30)
                        ->get();

                    $omset = (float)($trxSummary->omset ?? 0);
                    $profit = (float)($trxSummary->profit ?? 0);
                    $margin = $omset > 0 ? round(($profit / $omset) * 100, 2) : 0;

                    return [
                        'ok' => true,
                        'result' => [
                            'reseller' => (array)$reseller,
                            'summary' => [
                                'total_trx' => (int)($trxSummary->total_trx ?? 0),
                                'trx_sukses' => (int)($trxSummary->trx_sukses ?? 0),
                                'trx_gagal' => (int)($trxSummary->trx_gagal ?? 0),
                                'trx_proses' => (int)($trxSummary->trx_proses ?? 0),
                                'omset' => $omset,
                                'modal' => (float)($trxSummary->modal ?? 0),
                                'profit' => $profit,
                                'margin' => $margin,
                                'is_profit' => $profit >= 0,
                            ],
                            'top_products' => $topProducts->map(function($r) {
                                $arr = (array)$r;
                                $arr['margin'] = ($arr['omset'] > 0) ? round(($arr['profit'] / $arr['omset']) * 100, 2) : 0;
                                return $arr;
                            })->toArray(),
                            'recent_transactions' => $recentTrx->map(function($r) {
                                $arr = (array)$r;
                                $arr['status_text'] = ($arr['status'] == 20 || $arr['status'] == 3 || $arr['status'] == 4) ? 'Sukses' : (($arr['status'] == 1 || $arr['status'] == 0) ? 'Proses' : 'Gagal');
                                $harga = (float)($arr['harga'] ?? 0);
                                $modal = (float)($arr['harga_beli'] ?? 0);
                                $arr['profit'] = ($arr['status_text'] === 'Sukses') ? ($harga - $modal) : 0;
                                return $arr;
                            })->toArray()
                        ]
                    ];

                default:
                    return [
                        'ok' => false,
                        'desc' => "Query database untuk endpoint '{$endpoint}' belum diimplementasikan."
                    ];
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("OtomaXService [{$endpoint}] Database Error: " . $e->getMessage());

            // Jika koneksi SQL Server gagal (driver sqlsrv belum terpasang atau server lokal tidak aktif),
            // fallback otomatis ke mode simulasi (mock) agar halaman web tetap berfungsi lancar
            return $this->handleMockCall($endpoint, $data);
        }
    }

    /**
     * Map database transaction status codes to readable text
     */
    protected function mapStatus(int $status): string
    {
        return match ($status) {
            20, 3, 4 => 'Sukses',
            0, 1 => 'Proses',
            default => 'Gagal'
        };
    }

    /**
     * Initialize Session-based Mock Data for real-like interactions
     */
    protected function initMockData(): void
    {
        if (!Session::has('otomax_mock_resellers')) {
            Session::put('otomax_mock_resellers', [
                'yusuf' => [
                    'kode' => 'yusuf',
                    'nama' => 'Yusuf Pratama',
                    'email' => 'yusuf@gmail.com',
                    'pengirim' => '0812996000',
                    'tipe_pengirim' => 'W',
                    'saldo' => 3500000,
                    'poin' => 120,
                    'komisi' => 25000,
                    'kode_level' => 'H',
                    'aktif' => 1,
                    'alamat_ip' => '192.168.1.1',
                    'url_report' => 'http://yusufapi.com/callback',
                    'wallets' => ['A' => 1500000, 'B' => 2000000]
                ],
                'budi' => [
                    'kode' => 'budi',
                    'nama' => 'Budi Mandiri Cell',
                    'email' => 'budi@mandiricell.id',
                    'pengirim' => '085799881122',
                    'tipe_pengirim' => 'W',
                    'saldo' => 1850000,
                    'poin' => 45,
                    'komisi' => 3200,
                    'kode_level' => 'M',
                    'aktif' => 1,
                    'alamat_ip' => '103.12.14.5',
                    'wallets' => ['A' => 1850000, 'B' => 0]
                ],
                'citra' => [
                    'kode' => 'citra',
                    'nama' => 'Citra Lestari Pay',
                    'email' => 'citra@lestari.com',
                    'pengirim' => '081377889900',
                    'tipe_pengirim' => 'W',
                    'saldo' => 920000,
                    'poin' => 30,
                    'komisi' => 1500,
                    'kode_level' => 'H',
                    'aktif' => 1,
                    'alamat_ip' => '114.122.5.10',
                    'wallets' => ['A' => 920000, 'B' => 0]
                ],
                'arief' => [
                    'kode' => 'arief',
                    'nama' => 'Arief Wijaya',
                    'email' => 'arief@gmail.com',
                    'pengirim' => '081211221334',
                    'tipe_pengirim' => 'W',
                    'saldo' => 450000,
                    'poin' => 15,
                    'komisi' => 500,
                    'kode_level' => 'O',
                    'aktif' => 1,
                    'alamat_ip' => '182.253.1.20',
                    'wallets' => ['A' => 250000, 'B' => 200000]
                ],
                'rizki' => [
                    'kode' => 'rizki',
                    'nama' => 'Rizki Counter',
                    'email' => 'rizki@counter.id',
                    'pengirim' => '087812344321',
                    'tipe_pengirim' => 'W',
                    'saldo' => 50000,
                    'poin' => 5,
                    'komisi' => 0,
                    'kode_level' => 'O',
                    'aktif' => 1,
                    'alamat_ip' => '36.80.12.1',
                    'wallets' => ['A' => 50000, 'B' => 0]
                ]
            ]);
        }

        if (!Session::has('otomax_mock_transactions')) {
            $mockTrx = [];
            $trxBaseId = 7800100;
            $now = now();

            $products = [
                ['kode' => 'T10', 'harga' => 10450, 'modal' => 10200],
                ['kode' => 'T50', 'harga' => 49500, 'modal' => 48900],
                ['kode' => 'T100', 'harga' => 97800, 'modal' => 96500],
                ['kode' => 'BYROMNI', 'harga' => 12500, 'modal' => 12100],
                ['kode' => 'BYU10', 'harga' => 50000, 'modal' => 49100],
                ['kode' => 'PLN20', 'harga' => 20100, 'modal' => 19950],
                ['kode' => 'PLN50', 'harga' => 50100, 'modal' => 49950],
                ['kode' => 'BPJS', 'harga' => 2500, 'modal' => 1800],
            ];

            // Reseller presets
            $resellersConfig = [
                ['kode' => 'yusuf', 'count' => 14, 'is_minus' => false],
                ['kode' => 'budi',  'count' => 10, 'is_minus' => false],
                ['kode' => 'citra', 'count' => 8,  'is_minus' => false],
                ['kode' => 'arief', 'count' => 6,  'is_minus' => false],
                ['kode' => 'rizki', 'count' => 4,  'is_minus' => true],
            ];

            foreach ($resellersConfig as $rc) {
                for ($k = 0; $k < $rc['count']; $k++) {
                    $trxBaseId++;
                    $dayOffset = rand(0, 6);
                    $tgl = $now->copy()->subDays($dayOffset)->subHours(rand(1, 10))->subMinutes(rand(1, 55));
                    $prod = $products[array_rand($products)];

                    $harga = $prod['harga'];
                    $modal = $prod['modal'];

                    if ($rc['is_minus']) {
                        // Reseller minus simulate: modal higher than jual
                        $modal = $harga + rand(100, 300);
                    }

                    $status = (rand(1, 10) <= 8) ? 'Sukses' : ((rand(1, 2) === 1) ? 'Proses' : 'Gagal');
                    $sn = ($status === 'Sukses') ? ('SN' . rand(100000000, 999999999)) : '';

                    $mockTrx[] = [
                        'trxid' => $trxBaseId,
                        'kode' => $trxBaseId,
                        'kode_reseller' => $rc['kode'],
                        'tgl_entri' => $tgl->format('Y-m-d H:i:s'),
                        'tgl_status' => $tgl->copy()->addSeconds(rand(3, 15))->format('Y-m-d H:i:s'),
                        'kode_produk' => $prod['kode'],
                        'tujuan' => '0812' . rand(10000000, 99999999),
                        'jumlah' => 1,
                        'harga' => $harga,
                        'harga_beli' => $modal,
                        'saldo_awal' => 500000,
                        'saldo_akhir' => 500000 - $harga,
                        'ref_id' => 'INV-' . $trxBaseId,
                        'sn' => $sn,
                        'status' => $status,
                        'keterangan' => ($status === 'Sukses') ? "Transaksi Sukses {$sn}" : "Menunggu respon biller"
                    ];
                }
            }

            usort($mockTrx, fn($a, $b) => strcmp($b['tgl_entri'], $a['tgl_entri']));
            Session::put('otomax_mock_transactions', $mockTrx);
        }

        if (!Session::has('otomax_mock_inbox')) {
            Session::put('otomax_mock_inbox', []);
        }

        if (!Session::has('otomax_mock_outbox')) {
            Session::put('otomax_mock_outbox', []);
        }

        if (!Session::has('otomax_mock_callback_url')) {
            Session::put('otomax_mock_callback_url', 'http://45.77.173.173:9888/api/senderoto/');
        }
    }

    /**
     * Handle Mock requests and simulate OtomaX behavior
     */
    protected function handleMockCall(string $endpoint, array $data)
    {
        $this->initMockData();
        $resellers = Session::get('otomax_mock_resellers', []);
        $transactions = Session::get('otomax_mock_transactions', []);
        $inbox = Session::get('otomax_mock_inbox', []);
        $outbox = Session::get('otomax_mock_outbox', []);

        switch ($endpoint) {
            case 'Test':
                $kode = $data['kode'] ?? 'unknown';
                return [
                    'ok' => true,
                    'result' => [
                        'status' => 'success',
                        'message' => 'Test connection successful',
                        'kode' => $kode,
                        'timestamp' => now()->toIso8601String()
                    ]
                ];

            case 'UpdateRs':
                $kode = $data['kode'] ?? '';
                if (!isset($resellers[$kode])) {
                    return ['ok' => false, 'desc' => 'Reseller tidak ditemukan'];
                }
                if (isset($data['pin'])) $resellers[$kode]['pin'] = $data['pin'];
                if (isset($data['alamat_ip'])) $resellers[$kode]['alamat_ip'] = $data['alamat_ip'];
                if (isset($data['password_ip'])) $resellers[$kode]['password_ip'] = $data['password_ip'];
                if (isset($data['url_report'])) $resellers[$kode]['url_report'] = $data['url_report'];
                
                Session::put('otomax_mock_resellers', $resellers);
                return ['ok' => true];

            case 'InsertInbox':
                $pesan = $data['pesan'] ?? '';
                $kode_rs = $data['kode_reseller'] ?? '';
                $pengirim = $data['pengirim'] ?? '';
                $tipe = $data['tipe_pengirim'] ?? 'W';

                $id = count($inbox) + 1;
                $newInbox = [
                    'id' => $id,
                    'pesan' => $pesan,
                    'kode_reseller' => $kode_rs,
                    'pengirim' => $pengirim,
                    'tipe_pengirim' => $tipe,
                    'tgl_entri' => now()->toDateTimeString()
                ];
                $inbox[] = $newInbox;
                Session::put('otomax_mock_inbox', $inbox);

                // Auto respond
                if (stripos($pesan, 'tiket.') === 0) {
                    $parts = explode('.', $pesan);
                    $nominal = isset($parts[1]) ? (int)$parts[1] : 50000;
                    $ticketCode = rand(100, 999);
                    $reply = "Tiket deposit Rp. " . number_format($nominal + $ticketCode, 0, ',', '.') . " SUKSES. Silakan transfer tepat Rp. " . ($nominal + $ticketCode) . " ke rek BCA 123456 a.n OtomaX";
                    
                    $newOutbox = [
                        'kode' => rand(1000000, 9999999),
                        'kode_reseller' => $kode_rs,
                        'penerima' => $pengirim,
                        'tipe_penerima' => $tipe,
                        'pesan' => $reply,
                        'status' => 0,
                        'tgl_entri' => now()->toDateTimeString()
                    ];
                    $outbox[] = $newOutbox;
                    Session::put('otomax_mock_outbox', $outbox);
                }

                return [
                    'ok' => true,
                    'result' => [
                        'kode_inbox' => $id,
                        'status' => 21,
                        'statusDesc' => 'Sukses Masuk Outbox'
                    ]
                ];

            case 'InsertOutbox':
                $outboxId = rand(1000000, 9999999);
                return [
                    'ok' => true,
                    'result' => [
                        'kode_outbox' => $outboxId
                    ]
                ];

            case 'GetTrx':
                $trxid = (int)($data['trxid'] ?? 0);
                foreach ($transactions as $t) {
                    if ($t['trxid'] === $trxid) {
                        return ['ok' => true, 'result' => $t];
                    }
                }
                return [
                    'ok' => false,
                    'desc' => 'Transaction not found'
                ];

            case 'GetTrxHistory':
                $kode_rs = strtoupper($data['kode_reseller'] ?? '');
                $tgl_awal = $data['tgl_awal'] ?? '';
                $tgl_akhir = $data['tgl_akhir'] ?? '';
                $limit = (int)($data['limit'] ?? 50);
                if ($limit <= 0) $limit = 999999;

                $tujuan = strtolower($data['tujuan'] ?? '');
                $produk = strtolower($data['produk'] ?? '');
                $ref_id = strtolower($data['ref_id'] ?? '');
                $status = strtolower($data['status'] ?? '');

                $filtered = array_filter($transactions, function ($t) use ($kode_rs, $tgl_awal, $tgl_akhir, $tujuan, $produk, $ref_id, $status) {
                    $matchRs = empty($kode_rs) || strtoupper($t['kode_reseller'] ?? '') === $kode_rs;
                    $date = substr($t['tgl_entri'] ?? '', 0, 10);
                    $matchStart = empty($tgl_awal) || $date >= $tgl_awal;
                    $matchEnd = empty($tgl_akhir) || $date <= $tgl_akhir;
                    $matchTujuan = empty($tujuan) || str_contains(strtolower($t['tujuan'] ?? ''), $tujuan);
                    $matchProduk = empty($produk) || str_contains(strtolower($t['kode_produk'] ?? ''), $produk);
                    $matchRef = empty($ref_id) || str_contains(strtolower($t['ref_id'] ?? ''), $ref_id);
                    $matchStatus = empty($status) || strtolower($t['status'] ?? '') === $status;
                    return $matchRs && $matchStart && $matchEnd && $matchTujuan && $matchProduk && $matchRef && $matchStatus;
                });

                $filtered = array_values($filtered);
                usort($filtered, fn($a, $b) => strcmp($b['tgl_entri'] ?? '', $a['tgl_entri'] ?? ''));

                return ['ok' => true, 'result' => array_slice($filtered, 0, $limit)];

            case 'GetRs':
                $kode = strtolower($data['kode'] ?? '');
                if (isset($resellers[$kode])) {
                    return ['ok' => true, 'result' => $resellers[$kode]];
                }
                return [
                    'ok' => false,
                    'desc' => 'Reseller not found'
                ];

            case 'GetSaldoRs':
                $kode = strtolower($data['kode'] ?? '');
                if (isset($resellers[$kode])) {
                    return [
                        'ok' => true,
                        'result' => [
                            'saldo' => $resellers[$kode]['saldo'],
                            'saldo_minimal' => 0
                        ]
                    ];
                }
                return [
                    'ok' => false,
                    'desc' => 'Reseller not found'
                ];

            case 'TesPinRs':
                $kode = strtolower($data['kode'] ?? '');
                if (isset($resellers[$kode])) {
                    return ['ok' => true];
                }
                return [
                    'ok' => false,
                    'desc' => 'Reseller not found or invalid PIN'
                ];

            case 'GetFormatRequest':
                $nama = strtolower($data['nama'] ?? '');
                return [
                    'ok' => true,
                    'result' => [
                        ['nama' => 'TiketDeposit', 'format' => 'TIKET.[jumlah].[pin]']
                    ]
                ];

            case 'GetVersiOtomaX':
                return [
                    'ok' => true,
                    'result' => 'Mock Mode'
                ];

            case 'TambahSaldo':
                $kode_rs = strtolower($data['kode_reseller'] ?? '');
                $jumlah = (int)($data['jumlah'] ?? 0);

                if (isset($resellers[$kode_rs])) {
                    $resellers[$kode_rs]['saldo'] += $jumlah;
                    Session::put('otomax_mock_resellers', $resellers);
                    return ['ok' => true, 'result' => true];
                }
                return [
                    'ok' => false,
                    'desc' => 'Reseller not found'
                ];

            case 'GetSaldoDompet':
                $kode_rs = strtolower($data['kode_reseller'] ?? '');
                $dompet = strtoupper($data['kode_dompet'] ?? 'A');

                if (isset($resellers[$kode_rs])) {
                    $balance = ($dompet === 'A') ? $resellers[$kode_rs]['wallets']['A'] : $resellers[$kode_rs]['wallets']['B'];
                    return [
                        'ok' => true,
                        'result' => [
                            'kode_reseller' => $kode_rs,
                            'kode_dompet' => $dompet,
                            'saldo' => $balance
                        ]
                    ];
                }
                return [
                    'ok' => false,
                    'desc' => 'Reseller not found'
                ];

            case 'GetDaftarHargaRs':
                $products = $this->getMockProducts();
                $lvl = strtoupper($data['kode_level'] ?? '');
                if ($lvl === 'H') {
                    foreach ($products as &$p) { $p['harga'] = max(0, $p['harga'] - 200); }
                } elseif ($lvl === 'O') {
                    foreach ($products as &$p) { $p['harga'] = max(0, $p['harga'] - 100); }
                } elseif ($lvl === 'M') {
                    foreach ($products as &$p) { $p['harga'] = max(0, $p['harga'] - 300); }
                }
                unset($p);

                $filter = $data['sort_filter'] ?? 'semua';
                if ($filter === 'termurah') {
                    usort($products, fn($a, $b) => $a['harga'] <=> $b['harga']);
                }

                return ['ok' => true, 'result' => $products];

            case 'GetLevels':
                return ['ok' => true, 'result' => [
                    ['kode' => 'H', 'nama' => 'Level H (H2H)'],
                    ['kode' => 'O', 'nama' => 'Level O (Outlet)'],
                    ['kode' => 'M', 'nama' => 'Level M (Master)'],
                ]];
                
            case 'GetMutasiDeposit':
                return ['ok' => true, 'result' => [
                    [
                        'kode' => 101,
                        'kode_reseller' => $data['kode_reseller'] ?? 'MOCK',
                        'tanggal' => now()->subHours(2)->format('Y-m-d H:i:s'),
                        'jumlah' => 100342,
                        'keterangan' => 'Tiket Deposit BCA Sukses',
                        'jenis' => 'K',
                        'saldo_akhir' => 1500342
                    ],
                    [
                        'kode' => 102,
                        'kode_reseller' => $data['kode_reseller'] ?? 'MOCK',
                        'tanggal' => now()->subDays(1)->format('Y-m-d H:i:s'),
                        'jumlah' => 50012,
                        'keterangan' => 'Tambah Saldo Manual Admin',
                        'jenis' => 'K',
                        'saldo_akhir' => 1400000
                    ]
                ]];

            case 'GetPendingTiket':
                return ['ok' => true, 'result' => \Illuminate\Support\Facades\Session::get('mock_pending_tiket', [])];

            case 'CancelTiketDeposit':
                $kode = $data['kode'] ?? 0;
                $pending = \Illuminate\Support\Facades\Session::get('mock_pending_tiket', []);
                foreach ($pending as $key => $p) {
                    if ($p['kode'] == $kode) {
                        unset($pending[$key]);
                        \Illuminate\Support\Facades\Session::put('mock_pending_tiket', array_values($pending));
                        return ['ok' => true, 'result' => true];
                    }
                }
                return ['ok' => false, 'desc' => 'Tiket tidak ditemukan.'];

            case 'RequestTiketDeposit':
                $nom = (int)($data['nominal'] ?? 0);
                $rand = rand(1, 999);
                $bank = $data['bank'] ?? 'BCA';
                $rekening = $data['rekening'] ?? '';
                $atas_nama = $data['atas_nama'] ?? '';
                
                $pending = \Illuminate\Support\Facades\Session::get('mock_pending_tiket', []);
                foreach ($pending as $p) {
                    if (floor($p['jumlah'] / 1000) * 1000 == $nom) {
                        return ['ok' => false, 'desc' => 'Anda masih memiliki tiket yang belum ditransfer sebesar Rp ' . number_format($p['jumlah'], 0, ',', '.') . '. Silakan selesaikan, batalkan, atau pilih nominal deposit yang berbeda.'];
                    }
                }

                $newTiket = [
                    'kode' => rand(1000000, 9999999),
                    'waktu' => now()->format('Y-m-d H:i:s'),
                    'jumlah' => $nom + $rand,
                    'bank' => $bank,
                    'rekening' => $rekening,
                    'atas_nama' => $atas_nama,
                    'status' => 0
                ];
                $pending[] = $newTiket;
                \Illuminate\Support\Facades\Session::put('mock_pending_tiket', $pending);

                $meta = [
                    'bank' => $bank,
                    'rekening' => $rekening,
                    'atas_nama' => $atas_nama
                ];
                \Illuminate\Support\Facades\Cache::put('deposit_ticket_meta_id_' . $newTiket['kode'], $meta, now()->addDays(2));
                \Illuminate\Support\Facades\Cache::put('deposit_ticket_meta_' . $newTiket['jumlah'], $meta, now()->addDays(2));

                return [
                    'ok' => true,
                    'result' => [
                        'nominal' => $newTiket['jumlah'],
                        'bank' => $newTiket['bank'],
                        'rekening' => $rekening,
                        'atas_nama' => $atas_nama
                    ]
                ];

            case 'TambahPoin':
                $kode_rs = strtolower($data['kode_reseller'] ?? '');
                $jumlah = (int)$data['jumlah'];

                if (isset($resellers[$kode_rs])) {
                    $resellers[$kode_rs]['poin'] += $jumlah;
                    Session::put('otomax_mock_resellers', $resellers);
                    return ['ok' => true, 'result' => true];
                }
                return [
                    'ok' => false,
                    'desc' => 'Reseller not found'
                ];

            case 'InsertReseller':
                $name = $data['name'] ?? '';
                $pengirim = $data['pengirim'] ?? '';

                $kode_new = strtolower(str_replace(' ', '', $name));
                return [
                    'ok' => true,
                    'result' => [
                        'kode_reseller' => $kode_new,
                        'name' => $name,
                        'pengirim' => $pengirim
                    ]
                ];

            case 'GetAllResellers':
                $list = array_values($resellers);
                usort($list, fn($a, $b) => strcmp($a['nama'] ?? '', $b['nama'] ?? ''));
                return ['ok' => true, 'result' => $list];

            case 'GetAdminDashboardStats':
                $today = now()->format('Y-m-d');
                $yesterday = now()->subDay()->format('Y-m-d');
                $startMonth = now()->startOfMonth()->format('Y-m-d');

                $todayTrx = array_filter($transactions, fn($t) => str_starts_with($t['tgl_entri'] ?? '', $today));
                $yesterdayTrx = array_filter($transactions, fn($t) => str_starts_with($t['tgl_entri'] ?? '', $yesterday));
                $monthTrx = array_filter($transactions, fn($t) => substr($t['tgl_entri'] ?? '', 0, 10) >= $startMonth);

                $calcStats = function($trxs) {
                    $total = count($trxs);
                    $sukses = 0; $proses = 0; $gagal = 0;
                    $omset = 0; $modal = 0; $profit = 0;
                    foreach ($trxs as $t) {
                        $st = $t['status'] ?? '';
                        if ($st === 'Sukses') {
                            $sukses++;
                            $h = (float)($t['harga'] ?? 0);
                            $m = (float)($t['harga_beli'] ?? ($h * 0.98));
                            $omset += $h;
                            $modal += $m;
                            $profit += ($h - $m);
                        } elseif ($st === 'Proses') {
                            $proses++;
                        } else {
                            $gagal++;
                        }
                    }
                    $margin = $omset > 0 ? round(($profit / $omset) * 100, 2) : 0;
                    return [
                        'total_trx' => $total,
                        'trx_sukses' => $sukses,
                        'trx_proses' => $proses,
                        'trx_gagal' => $gagal,
                        'omset' => $omset,
                        'modal' => $modal,
                        'profit' => $profit,
                        'margin' => $margin
                    ];
                };

                $todayRes = $calcStats($todayTrx);
                $yesterdayRes = $calcStats($yesterdayTrx);
                $monthRes = $calcStats($monthTrx);

                // 7 days trend
                $trendChart = [];
                for ($i = 6; $i >= 0; $i--) {
                    $dt = now()->subDays($i);
                    $d = $dt->format('Y-m-d');
                    $dayTrxs = array_filter($transactions, fn($t) => str_starts_with($t['tgl_entri'] ?? '', $d));
                    $dayStat = $calcStats($dayTrxs);
                    $trendChart[] = [
                        'tgl' => $d,
                        'label' => $dt->format('d M'),
                        'total_trx' => $dayStat['total_trx'],
                        'trx_sukses' => $dayStat['trx_sukses'],
                        'omset' => $dayStat['omset'],
                        'modal' => $dayStat['modal'],
                        'profit' => $dayStat['profit'],
                    ];
                }

                // Top Reseller & Minus Reseller by grouping monthTrx
                $grouped = [];
                foreach ($monthTrx as $t) {
                    $rs = $t['kode_reseller'] ?? 'unknown';
                    if (!isset($grouped[$rs])) {
                        $grouped[$rs] = [
                            'kode' => $rs,
                            'nama' => $resellers[$rs]['nama'] ?? ucfirst($rs),
                            'kode_level' => $resellers[$rs]['kode_level'] ?? 'O',
                            'saldo' => $resellers[$rs]['saldo'] ?? 0,
                            'total_trx' => 0,
                            'trx_sukses' => 0,
                            'omset' => 0,
                            'modal' => 0,
                            'profit' => 0
                        ];
                    }
                    $grouped[$rs]['total_trx']++;
                    if (($t['status'] ?? '') === 'Sukses') {
                        $grouped[$rs]['trx_sukses']++;
                        $h = (float)($t['harga'] ?? 0);
                        $m = (float)($t['harga_beli'] ?? ($h * 0.98));
                        $grouped[$rs]['omset'] += $h;
                        $grouped[$rs]['modal'] += $m;
                        $grouped[$rs]['profit'] += ($h - $m);
                    }
                }

                $topList = array_values($grouped);
                usort($topList, fn($a, $b) => $b['profit'] <=> $a['profit']);
                foreach ($topList as &$tl) {
                    $tl['margin'] = $tl['omset'] > 0 ? round(($tl['profit'] / $tl['omset']) * 100, 2) : 0;
                }
                unset($tl);

                $minusList = array_values(array_filter($topList, fn($x) => $x['profit'] < 0));

                $totalSaldo = 0;
                foreach ($resellers as $r) { $totalSaldo += (float)($r['saldo'] ?? 0); }

                return [
                    'ok' => true,
                    'result' => [
                        'today' => $todayRes,
                        'yesterday' => [
                            'omset' => $yesterdayRes['omset'],
                            'profit' => $yesterdayRes['profit'],
                        ],
                        'month' => $monthRes,
                        'server' => [
                            'total_saldo' => $totalSaldo,
                            'total_reseller_aktif' => count($resellers),
                        ],
                        'trend_chart' => $trendChart,
                        'top_resellers' => array_slice($topList, 0, 5),
                        'minus_resellers' => $minusList,
                    ]
                ];

            case 'GetAdminResellerProfit':
                $tgl_awal = !empty($data['tgl_awal']) ? $data['tgl_awal'] : now()->startOfMonth()->format('Y-m-d');
                $tgl_akhir = !empty($data['tgl_akhir']) ? $data['tgl_akhir'] : now()->format('Y-m-d');
                $filterRs = trim($data['kode_reseller'] ?? '');
                $profitFilter = $data['profit_filter'] ?? 'all';
                $search = strtolower(trim($data['search'] ?? ''));

                $grouped = [];
                // Initialize all or matched resellers
                foreach ($resellers as $k => $r) {
                    if (!empty($filterRs) && $k !== $filterRs) continue;
                    if (!empty($search) && !str_contains(strtolower($k), $search) && !str_contains(strtolower($r['nama'] ?? ''), $search)) continue;

                    $grouped[$k] = [
                        'kode' => $k,
                        'nama' => $r['nama'] ?? ucfirst($k),
                        'kode_level' => $r['kode_level'] ?? 'O',
                        'saldo' => (float)($r['saldo'] ?? 0),
                        'aktif' => 1,
                        'total_trx' => 0,
                        'trx_sukses' => 0,
                        'trx_proses' => 0,
                        'trx_gagal' => 0,
                        'omset' => 0,
                        'modal' => 0,
                        'profit' => 0,
                        'margin' => 0,
                        'is_profit' => true
                    ];
                }

                foreach ($transactions as $t) {
                    $k = $t['kode_reseller'] ?? '';
                    if (!isset($grouped[$k])) continue;

                    $date = substr($t['tgl_entri'] ?? '', 0, 10);
                    if ($date < $tgl_awal || $date > $tgl_akhir) continue;

                    $grouped[$k]['total_trx']++;
                    $st = $t['status'] ?? '';
                    if ($st === 'Sukses') {
                        $grouped[$k]['trx_sukses']++;
                        $h = (float)($t['harga'] ?? 0);
                        $m = (float)($t['harga_beli'] ?? ($h * 0.98));
                        $grouped[$k]['omset'] += $h;
                        $grouped[$k]['modal'] += $m;
                        $grouped[$k]['profit'] += ($h - $m);
                    } elseif ($st === 'Proses') {
                        $grouped[$k]['trx_proses']++;
                    } else {
                        $grouped[$k]['trx_gagal']++;
                    }
                }

                $items = [];
                $sumOmset = 0; $sumModal = 0; $sumProfit = 0;
                $sumTrxSukses = 0; $sumTrxGagal = 0;

                foreach ($grouped as $row) {
                    $omset = $row['omset'];
                    $profit = $row['profit'];
                    $margin = $omset > 0 ? round(($profit / $omset) * 100, 2) : 0;
                    $row['margin'] = $margin;
                    $row['is_profit'] = $profit >= 0;

                    if ($profitFilter === 'profit' && $profit <= 0) continue;
                    if ($profitFilter === 'minus' && $profit > 0) continue;

                    $sumOmset += $omset;
                    $sumModal += $row['modal'];
                    $sumProfit += $profit;
                    $sumTrxSukses += $row['trx_sukses'];
                    $sumTrxGagal += $row['trx_gagal'];

                    $items[] = $row;
                }

                usort($items, fn($a, $b) => $b['profit'] <=> $a['profit']);
                $avgMargin = $sumOmset > 0 ? round(($sumProfit / $sumOmset) * 100, 2) : 0;

                return [
                    'ok' => true,
                    'result' => [
                        'items' => $items,
                        'summary' => [
                            'total_omset' => $sumOmset,
                            'total_modal' => $sumModal,
                            'total_profit' => $sumProfit,
                            'avg_margin' => $avgMargin,
                            'total_trx_sukses' => $sumTrxSukses,
                            'total_trx_gagal' => $sumTrxGagal,
                            'count_reseller' => count($items)
                        ],
                        'filter' => [
                            'tgl_awal' => $tgl_awal,
                            'tgl_akhir' => $tgl_akhir,
                            'kode_reseller' => $filterRs,
                            'profit_filter' => $profitFilter,
                            'search' => $search
                        ]
                    ]
                ];

            case 'GetAdminResellerDetail':
                $k = strtolower(trim($data['kode_reseller'] ?? ''));
                $tgl_awal = !empty($data['tgl_awal']) ? $data['tgl_awal'] : now()->startOfMonth()->format('Y-m-d');
                $tgl_akhir = !empty($data['tgl_akhir']) ? $data['tgl_akhir'] : now()->format('Y-m-d');

                if (!isset($resellers[$k])) {
                    return ['ok' => false, 'desc' => 'Reseller tidak ditemukan'];
                }

                $resInfo = $resellers[$k];
                $resTrxs = array_filter($transactions, function($t) use ($k, $tgl_awal, $tgl_akhir) {
                    if (strtolower($t['kode_reseller'] ?? '') !== $k) return false;
                    $date = substr($t['tgl_entri'] ?? '', 0, 10);
                    return $date >= $tgl_awal && $date <= $tgl_akhir;
                });

                $total = count($resTrxs);
                $sukses = 0; $gagal = 0; $proses = 0;
                $omset = 0; $modal = 0; $profit = 0;
                $prodGroup = [];

                foreach ($resTrxs as $t) {
                    $st = $t['status'] ?? '';
                    $prod = $t['kode_produk'] ?? 'PROD';
                    if (!isset($prodGroup[$prod])) {
                        $prodGroup[$prod] = ['kode_produk' => $prod, 'total_trx' => 0, 'omset' => 0, 'modal' => 0, 'profit' => 0];
                    }
                    $prodGroup[$prod]['total_trx']++;

                    if ($st === 'Sukses') {
                        $sukses++;
                        $h = (float)($t['harga'] ?? 0);
                        $m = (float)($t['harga_beli'] ?? ($h * 0.98));
                        $p = $h - $m;
                        $omset += $h;
                        $modal += $m;
                        $profit += $p;

                        $prodGroup[$prod]['omset'] += $h;
                        $prodGroup[$prod]['modal'] += $m;
                        $prodGroup[$prod]['profit'] += $p;
                    } elseif ($st === 'Proses') {
                        $proses++;
                    } else {
                        $gagal++;
                    }
                }

                $topProds = array_values($prodGroup);
                usort($topProds, fn($a, $b) => $b['total_trx'] <=> $a['total_trx']);
                foreach ($topProds as &$tp) {
                    $tp['margin'] = $tp['omset'] > 0 ? round(($tp['profit'] / $tp['omset']) * 100, 2) : 0;
                }
                unset($tp);

                $recent = array_slice(array_values($resTrxs), 0, 30);
                $recentMapped = array_map(function($t) {
                    $h = (float)($t['harga'] ?? 0);
                    $m = (float)($t['harga_beli'] ?? ($h * 0.98));
                    $isSukses = ($t['status'] ?? '') === 'Sukses';
                    return [
                        'kode' => $t['trxid'] ?? $t['kode'] ?? '',
                        'tgl_entri' => $t['tgl_entri'] ?? '',
                        'kode_produk' => $t['kode_produk'] ?? '',
                        'tujuan' => $t['tujuan'] ?? '',
                        'harga' => $h,
                        'harga_beli' => $m,
                        'profit' => $isSukses ? ($h - $m) : 0,
                        'status' => $t['status'] ?? '',
                        'status_text' => $t['status'] ?? '',
                        'sn' => $t['sn'] ?? ''
                    ];
                }, $recent);

                $margin = $omset > 0 ? round(($profit / $omset) * 100, 2) : 0;

                return [
                    'ok' => true,
                    'result' => [
                        'reseller' => $resInfo,
                        'summary' => [
                            'total_trx' => $total,
                            'trx_sukses' => $sukses,
                            'trx_gagal' => $gagal,
                            'trx_proses' => $proses,
                            'omset' => $omset,
                            'modal' => $modal,
                            'profit' => $profit,
                            'margin' => $margin,
                            'is_profit' => $profit >= 0
                        ],
                        'top_products' => $topProds,
                        'recent_transactions' => $recentMapped
                    ]
                ];

            default:
                return [
                    'ok' => false,
                    'desc' => "Mock endpoint '{$endpoint}' not implemented."
                ];
        }
    }

    /**
     * Get mock product list
     */
    protected function getMockProducts(): array
    {
        return [
            ['kode' => 'BYROMNI', 'nama' => 'by.U Unlimited 1.5 Mbps', 'harga' => 12500, 'operator' => 'BYU', 'status' => 'Aktif'],
            ['kode' => 'BYU10', 'nama' => 'by.U Data 10GB 30 Hari', 'harga' => 50000, 'operator' => 'BYU', 'status' => 'Aktif'],
            ['kode' => 'BYU20', 'nama' => 'by.U Data 20GB 30 Hari', 'harga' => 75000, 'operator' => 'BYU', 'status' => 'Aktif'],
            ['kode' => 'T10', 'nama' => 'Telkomsel Reguler 10.000', 'harga' => 10450, 'operator' => 'TSEL', 'status' => 'Aktif'],
            ['kode' => 'T50', 'nama' => 'Telkomsel Reguler 50.000', 'harga' => 49500, 'operator' => 'TSEL', 'status' => 'Aktif'],
            ['kode' => 'T100', 'nama' => 'Telkomsel Reguler 100.000', 'harga' => 97800, 'operator' => 'TSEL', 'status' => 'Aktif'],
            ['kode' => 'PLN20', 'nama' => 'PLN Token 20.000', 'harga' => 20100, 'operator' => 'PPOB', 'status' => 'Aktif'],
            ['kode' => 'PLN50', 'nama' => 'PLN Token 50.000', 'harga' => 50100, 'operator' => 'PPOB', 'status' => 'Aktif'],
            ['kode' => 'BPJS', 'nama' => 'BPJS Kesehatan', 'harga' => 2500, 'operator' => 'PPOB', 'status' => 'Aktif']
        ];
    }
}
