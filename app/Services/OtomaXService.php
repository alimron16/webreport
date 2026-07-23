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

                    // 1. Get reseller's kode_level
                    $reseller = $db->table('reseller')->where('kode', $kode_rs)->select('kode_level')->first();
                    $kode_level = $reseller->kode_level ?? null;

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

                    return [
                        'ok' => true, 
                        'result' => [
                            'nominal' => $finalNominal,
                            'bank' => $bank
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

                    // Check for duplicate
                    $existing = $db->table('reseller')->where('kode', $kode)->first();
                    if ($existing) {
                        return ['ok' => false, 'desc' => "Kode '{$kode}' sudah terdaftar di OtomaX."];
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
                        'kode_level'       => 'O',
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

                default:
                    return [
                        'ok' => false,
                        'desc' => "Query database untuk endpoint '{$endpoint}' belum diimplementasikan."
                    ];
            }
        } catch (\Exception $e) {
            return [
                'ok' => false,
                'desc' => 'Database Query Error: ' . $e->getMessage()
            ];
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
                    'alamat_ip' => '192.168.1.1',
                    'url_report' => 'http://yusufapi.com/callback',
                    'wallets' => [
                        'A' => 1500000,
                        'B' => 2000000
                    ]
                ],
                'arief' => [
                    'kode' => 'arief',
                    'nama' => 'Arief Wijaya',
                    'email' => 'arief@gmail.com',
                    'pengirim' => '081211221334',
                    'tipe_pengirim' => 'W',
                    'saldo' => 100000,
                    'poin' => 10,
                    'komisi' => 500,
                    'wallets' => [
                        'A' => 50000,
                        'B' => 50000
                    ]
                ]
            ]);
        }

        if (!Session::has('otomax_mock_transactions')) {
            Session::put('otomax_mock_transactions', [
                [
                    'trxid' => 4522967,
                    'kode_reseller' => 'yusuf',
                    'tgl_entri' => '2026-07-09 10:15:30',
                    'kode_produk' => 'BYROMNI',
                    'tujuan' => '0812996000',
                    'jumlah' => 1,
                    'harga' => 12500,
                    'status' => 'Sukses',
                    'keterangan' => 'Transaksi Sukses SN: 0912384918239'
                ],
                [
                    'trxid' => 4522968,
                    'kode_reseller' => 'yusuf',
                    'tgl_entri' => '2026-07-09 12:44:12',
                    'kode_produk' => 'T10',
                    'tujuan' => '08571234567',
                    'jumlah' => 1,
                    'harga' => 10450,
                    'status' => 'Sukses',
                    'keterangan' => 'Transaksi Sukses SN: 99401824912'
                ],
                [
                    'trxid' => 4522969,
                    'kode_reseller' => 'arief',
                    'tgl_entri' => '2026-07-09 14:02:00',
                    'kode_produk' => 'PLN20',
                    'tujuan' => '14029481928',
                    'jumlah' => 1,
                    'harga' => 20100,
                    'status' => 'Proses',
                    'keterangan' => 'Sedang diproses'
                ]
            ]);
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
                $limit = (int)($data['limit'] ?? 10);

                $filtered = array_filter($transactions, function ($t) use ($kode_rs, $tgl_awal, $tgl_akhir) {
                    $matchRs = empty($kode_rs) || strtoupper($t['kode_reseller']) === $kode_rs;
                    $date = substr($t['tgl_entri'], 0, 10);
                    $matchStart = empty($tgl_awal) || $date >= $tgl_awal;
                    $matchEnd = empty($tgl_akhir) || $date <= $tgl_akhir;
                    return $matchRs && $matchStart && $matchEnd;
                });

                return ['ok' => true, 'result' => array_slice(array_values($filtered), 0, $limit)];

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
                return ['ok' => true, 'result' => $products];
                
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
                    'bank' => $data['bank'] ?? 'BCA',
                    'status' => 0
                ];
                $pending[] = $newTiket;
                \Illuminate\Support\Facades\Session::put('mock_pending_tiket', $pending);

                return [
                    'ok' => true,
                    'result' => [
                        'nominal' => $newTiket['jumlah'],
                        'bank' => $newTiket['bank']
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
