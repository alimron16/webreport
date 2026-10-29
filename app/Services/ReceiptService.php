<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ReceiptService
{
    /**
     * Get predefined built-in receipt templates and rules
     */
    public function getDefaultRules(): array
    {
        return [
            [
                'id'            => 'rule_pln_pra',
                'name'          => 'PLN Prabayar (Token Listrik)',
                'category'      => 'PRABAYAR',
                'provider'      => 'PLN',
                'keywords'      => 'PLN, TPLN, DPLN, TOKEN, STROOM, PLNTOKEN',
                'logo_url'      => '/images/receipt/pln.svg',
                'bank_name'     => 'PLN PAYMENT POINT',
                'default_admin' => 3000,
                'footer_info'   => 'Informasi Hubungi: Call Center 123 • Mobile PLN • PLN Terdekat',
                'parser_mode'   => 'template',
                'sn_template'   => '[token]/[namapelanggan]/[golongan]/[daya]/[kwh] PLNREF=[plnreff]. RP TOKEN=[rptoken]. PPN=[ppn]. PPJ=[ppj]/MATERAI:[materai]',
                'is_default'    => true,
            ],
            [
                'id'            => 'rule_pln_pasca',
                'name'          => 'PLN Pascabayar (Tagihan Listrik)',
                'category'      => 'PASCA BAYAR',
                'provider'      => 'PLNPASCA',
                'keywords'      => 'PLNPASCA, TAGPLN, BYRPLN, PASCAPLN, PLNPOST',
                'logo_url'      => '/images/receipt/pln.svg',
                'bank_name'     => 'PLN PAYMENT POINT',
                'default_admin' => 3750,
                'footer_info'   => 'Informasi Hubungi: Call Center 123 • Mobile PLN • PLN Terdekat',
                'parser_mode'   => 'template',
                'sn_template'   => '[idpel]/[namapelanggan]/[golongan]/[daya]/[blth]/[stand]/[tagihan]/[admin]/[totalbayar]/[reff]',
                'is_default'    => true,
            ],
            [
                'id'            => 'rule_bank_bca',
                'name'          => 'Transfer Bank BCA',
                'category'      => 'BANK',
                'bank_name'     => 'BANK CENTRAL ASIA',
                'provider'      => 'BCA',
                'keywords'      => 'BCA, TFBCA, IBCA, BCATF, TRANSFER_BCA',
                'logo_url'      => '/images/gateways/bca.png',
                'default_admin' => 2500,
                'remark'        => 'Transfer Uang',
                'parser_mode'   => 'template',
                'sn_template'   => '[norek]/[namapelanggan]/[nominal]/[reff]',
                'is_default'    => true,
            ],
            [
                'id'            => 'rule_bank_bri',
                'name'          => 'Transfer Bank BRI',
                'category'      => 'BANK',
                'bank_name'     => 'BANK RAKYAT INDONESIA',
                'provider'      => 'BRI',
                'keywords'      => 'BRI, TFBRI, IBRI, BRITF, TRANSFER_BRI',
                'logo_url'      => '/images/gateways/bri.png',
                'default_admin' => 2500,
                'remark'        => 'Transfer Uang',
                'parser_mode'   => 'template',
                'sn_template'   => '[norek]/[namapelanggan]/[nominal]/[reff]',
                'is_default'    => true,
            ],
            [
                'id'            => 'rule_bank_bni',
                'name'          => 'Transfer Bank BNI',
                'category'      => 'BANK',
                'bank_name'     => 'BANK NEGARA INDONESIA',
                'provider'      => 'BNI',
                'keywords'      => 'BNI, TFBNI, IBNI, BNITF, TRANSFER_BNI',
                'logo_url'      => '/images/gateways/bni.png',
                'default_admin' => 2500,
                'remark'        => 'Transfer Uang',
                'parser_mode'   => 'template',
                'sn_template'   => '[norek]/[namapelanggan]/[nominal]/[reff]',
                'is_default'    => true,
            ],
            [
                'id'            => 'rule_bank_mandiri',
                'name'          => 'Transfer Bank Mandiri',
                'category'      => 'BANK',
                'bank_name'     => 'BANK MANDIRI',
                'provider'      => 'MANDIRI',
                'keywords'      => 'MANDIRI, TFMANDIRI, IMANDIRI, TRANSFER_MANDIRI',
                'logo_url'      => '/images/gateways/mandiri.png',
                'default_admin' => 2500,
                'remark'        => 'Transfer Uang',
                'parser_mode'   => 'template',
                'sn_template'   => '[norek]/[namapelanggan]/[nominal]/[reff]',
                'is_default'    => true,
            ],
            [
                'id'            => 'rule_bank_bsi',
                'name'          => 'Transfer Bank Syariah Indonesia (BSI)',
                'category'      => 'BANK',
                'bank_name'     => 'BANK SYARIAH INDONESIA',
                'provider'      => 'BSI',
                'keywords'      => 'BSI, TFBSI, IBSI, TRANSFER_BSI',
                'logo_url'      => '/images/receipt/bsi.svg',
                'default_admin' => 2500,
                'remark'        => 'Transfer Uang',
                'parser_mode'   => 'template',
                'sn_template'   => '[norek]/[namapelanggan]/[nominal]/[reff]',
                'is_default'    => true,
            ],
        ];
    }

    /**
     * Enrich stored rules with default templates if missing
     */
    public function enrichRulesWithDefaults(array $rules): array
    {
        $defaults = $this->getDefaultRules();
        $defaultMap = [];
        foreach ($defaults as $d) {
            $defaultMap[$d['id']] = $d;
        }

        foreach ($rules as &$rule) {
            $id = $rule['id'] ?? '';
            if (isset($defaultMap[$id])) {
                $d = $defaultMap[$id];
                if (empty($rule['sn_template']) && !empty($d['sn_template'])) {
                    $rule['sn_template'] = $d['sn_template'];
                }
                if (empty($rule['parser_mode']) && !empty($d['parser_mode'])) {
                    $rule['parser_mode'] = $d['parser_mode'];
                }
                if (empty($rule['provider']) && !empty($d['provider'])) {
                    $rule['provider'] = $d['provider'];
                }
                if (empty($rule['keywords']) && !empty($d['keywords'])) {
                    $rule['keywords'] = $d['keywords'];
                }
            }
        }
        unset($rule);

        return $rules;
    }

    /**
     * Safely parse currency string with Indonesian formats (e.g., "1.133,00" -> 1133)
     */
    public function parseCurrencyValue(string $val): float
    {
        $val = trim($val);
        if (empty($val)) return 0.0;

        if (str_contains($val, ',') && str_contains($val, '.')) {
            $val = str_replace('.', '', $val);
            $val = str_replace(',', '.', $val);
        } elseif (str_contains($val, ',')) {
            $parts = explode(',', $val);
            if (count($parts) === 2 && strlen($parts[1]) <= 2) {
                $val = $parts[0] . '.' . $parts[1];
            } else {
                $val = str_replace(',', '', $val);
            }
        } else {
            if (substr_count($val, '.') === 1 && strlen(substr($val, strrpos($val, '.') + 1)) !== 3) {
                // decimal dot
            } else {
                $val = str_replace('.', '', $val);
            }
        }
        return (float)$val;
    }

    /**
     * Get active rules (persistent from database / cache)
     */
    public function getRules(): array
    {
        $cached = Cache::get('receipt_rules');
        if (is_array($cached) && !empty($cached)) {
            return $this->enrichRulesWithDefaults($cached);
        }

        $dbJson = Setting::get('receipt_rules', null);
        if ($dbJson) {
            $decoded = json_decode($dbJson, true);
            if (is_array($decoded) && !empty($decoded)) {
                $enriched = $this->enrichRulesWithDefaults($decoded);
                Cache::forever('receipt_rules', $enriched);
                return $enriched;
            }
        }

        $defaults = $this->getDefaultRules();
        $this->saveRules($defaults);
        return $defaults;
    }

    /**
     * Save rules to database & persistent cache
     */
    public function saveRules(array $rules): void
    {
        Cache::forever('receipt_rules', $rules);
        try {
            Setting::set('receipt_rules', json_encode($rules));
        } catch (\Throwable $e) {}
    }

    /**
     * Reset rules to original defaults
     */
    public function resetRules(): array
    {
        $defaults = $this->getDefaultRules();
        $this->saveRules($defaults);
        return $defaults;
    }

    /**
     * Find matching rule for a transaction (hanya mendukung 3 kategori: PRABAYAR, PASCA BAYAR, BANK)
     */
    public function findMatchingRule(array $trx): ?array
    {
        $rules = $this->getRules();
        $code = strtoupper(trim($trx['kode_produk'] ?? ''));
        $name = strtoupper(trim($trx['nama_produk'] ?? ($trx['nama'] ?? '')));
        $op   = strtoupper(trim($trx['kode_operator'] ?? ''));
        $sn   = strtoupper(trim($trx['sn'] ?? ''));
        $ket  = strtoupper(trim($trx['keterangan'] ?? ''));

        $supportedCategories = ['PRABAYAR', 'PASCA BAYAR', 'BANK'];

        // PASS 1: Prioritas Tertinggi — Pencocokan Kode Produk Spesifik (Keywords)
        $isBankInquiry = (
            str_starts_with($code, 'CEK') || str_contains($code, 'INQ') ||
            str_contains($name, 'CEK') || str_contains($name, 'INQUIRY') || str_contains($name, 'TAGIHAN') ||
            str_contains($ket, 'CEK REKENING') || str_contains($ket, 'INQUIRY')
        );

        foreach ($rules as $r) {
            $cat = strtoupper(trim($r['category'] ?? ''));
            if (!in_array($cat, $supportedCategories, true)) continue;
            if ($cat === 'BANK' && $isBankInquiry) continue; // Cek/Inquiry Bank bukan transaksi transfer

            $rawKeywords = $r['keywords'] ?? '';
            if (empty(trim($rawKeywords))) continue;

            $keywords = array_filter(array_map('trim', explode(',', strtoupper($rawKeywords))));
            foreach ($keywords as $kw) {
                if (empty($kw)) continue;

                if ($kw === '*' || $kw === 'ALL') {
                    return $r;
                }

                // Cocokkan persis atau awalan kode produk
                if ($code === $kw || str_starts_with($code, $kw) || str_contains($code, $kw)) {
                    return $r;
                }
            }
        }

        // PASS 2: Pencocokan Berdasarkan Provider / Operator
        foreach ($rules as $r) {
            $cat = strtoupper(trim($r['category'] ?? ''));
            if (!in_array($cat, $supportedCategories, true)) continue;
            if ($cat === 'BANK' && $isBankInquiry) continue;

            $rawProvider = trim($r['provider'] ?? '');
            if (empty($rawProvider)) continue;

            $providers = array_filter(array_map('trim', explode(',', strtoupper($rawProvider))));
            foreach ($providers as $prov) {
                if (empty($prov)) continue;

                // Cocokkan dengan kode_operator dari tabel produk
                if (!empty($op) && ($op === $prov || str_contains($op, $prov))) {
                    return $r;
                }

                // Cocokkan dengan kode_produk
                if (str_contains($code, $prov) || str_starts_with($code, $prov)) {
                    return $r;
                }

                // Cocokkan dengan nama produk
                if (str_contains($name, $prov)) {
                    return $r;
                }
            }
        }

        // PASS 3: Fallback ke kecocokan nama produk / keterangan dari keywords aturan
        foreach ($rules as $r) {
            $cat = strtoupper(trim($r['category'] ?? ''));
            if (!in_array($cat, $supportedCategories, true)) continue;
            if ($cat === 'BANK' && $isBankInquiry) continue;

            $rawKeywords = $r['keywords'] ?? '';
            if (empty(trim($rawKeywords))) continue;

            $keywords = array_filter(array_map('trim', explode(',', strtoupper($rawKeywords))));
            foreach ($keywords as $kw) {
                if (empty($kw)) continue;
                if (str_contains($name, $kw) || str_contains($ket, $kw)) {
                    return $r;
                }
            }
        }

        // PASS 4: Fallback heuristic detection if not explicitly matched
        // Token PLN detector (20 digits in SN)
        if (preg_match('/\b\d{4}[-\s]?\d{4}[-\s]?\d{4}[-\s]?\d{4}[-\s]?\d{4}\b/', $sn)) {
            return [
                'id'            => 'rule_auto_pln_pra',
                'name'          => 'PLN Prabayar',
                'category'      => 'PRABAYAR',
                'logo_url'      => '/images/receipt/pln.svg',
                'bank_name'     => 'PLN PAYMENT POINT',
                'default_admin' => 3000,
                'footer_info'   => 'Informasi Hubungi: Call Center 123 • Mobile PLN • PLN Terdekat',
            ];
        }

        // PLN Post detector
        if (str_contains($code, 'PLN') && (str_contains($code, 'PASCA') || str_contains($code, 'TAG') || str_contains($name, 'PASCA'))) {
            return [
                'id'            => 'rule_auto_pln_post',
                'name'          => 'PLN Pascabayar',
                'category'      => 'PASCA BAYAR',
                'logo_url'      => '/images/receipt/pln.svg',
                'bank_name'     => 'PLN PAYMENT POINT',
                'default_admin' => 3750,
                'footer_info'   => 'Informasi Hubungi: Call Center 123 • Mobile PLN • PLN Terdekat',
            ];
        }

        // Bank detector (Hanya untuk transfer riil, bukan cek rekening)
        if (!$isBankInquiry && (str_contains($code, 'TF') || str_contains($name, 'TRANSFER') || str_contains($code, 'BANK'))) {
            return [
                'id'            => 'rule_auto_bank',
                'name'          => 'Transfer Bank',
                'category'      => 'BANK',
                'bank_name'     => 'BANK TRANSFER POINT',
                'logo_url'      => '/images/gateways/bca.png',
                'default_admin' => 2500,
                'remark'        => 'Transfer Uang',
            ];
        }

        // Produk di luar 3 kategori resmi (PRABAYAR, PASCA BAYAR, BANK) tidak memiliki struk
        return null;
    }

    /**
     * Cek apakah transaksi ini mendukung cetak struk.
     * Hanya berlaku untuk transaksi berstatus SUKSES pada 3 kategori resmi: PRABAYAR, PASCA BAYAR, dan TRANSFER BANK.
     */
    public function isPrintable(array $trx): bool
    {
        // 1. Validasi Status Transaksi: HANYA transaksi Sukses yang boleh cetak struk!
        $statusStr = strtolower(trim((string)($trx['status'] ?? '')));
        $statusRaw = (int)($trx['status_raw'] ?? (is_numeric($trx['status'] ?? null) ? $trx['status'] : 0));
        
        $isSukses = in_array($statusStr, ['sukses', 's', 'berhasil', 'success'], true) 
                    || in_array($statusRaw, [20, 3, 4], true);
                    
        if (!$isSukses) {
            return false;
        }

        $rule = $this->findMatchingRule($trx);
        if (!$rule) {
            return false;
        }
        $cat = strtoupper(trim($rule['category'] ?? ''));
        
        if (!in_array($cat, ['PRABAYAR', 'PASCA BAYAR', 'BANK'], true)) {
            return false;
        }

        // 2. Transaksi Bank: Jangan cetak struk untuk Cek Rekening / Inquiry Bank
        if ($cat === 'BANK') {
            $code = strtoupper(trim($trx['kode_produk'] ?? ''));
            $name = strtoupper(trim($trx['nama_produk'] ?? ($trx['nama'] ?? '')));
            $ket  = strtoupper(trim($trx['keterangan'] ?? ''));

            if (
                str_starts_with($code, 'CEK') || str_contains($code, 'INQ') ||
                str_contains($name, 'CEK') || str_contains($name, 'INQUIRY') || str_contains($name, 'TAGIHAN') ||
                str_contains($ket, 'CEK REKENING') || str_contains($ket, 'INQUIRY')
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Parse raw transaction and SN string into structured receipt data
     */
    public function parseReceipt(array $trx, ?array $rule = null): array
    {
        if (!$rule) {
            $rule = $this->findMatchingRule($trx);
        }

        if (!$rule) {
            throw new \InvalidArgumentException('Fitur cetak struk belum tersedia untuk jenis transaksi ini.');
        }

        $category = strtoupper($rule['category'] ?? 'UMUM');
        $sn = !empty($trx['sn']) 
            ? (string)$trx['sn'] 
            : (!empty($trx['keterangan']) 
                ? (string)$trx['keterangan'] 
                : (!empty($trx['ket_modul']) 
                    ? (string)$trx['ket_modul'] 
                    : (string)($trx['bill'] ?? '')));
        $tujuan = (string)($trx['tujuan'] ?? '');
        $harga = (float)($trx['harga'] ?? 0);
        $adminFee = (float)($rule['default_admin'] ?? 2500);

        // Date formatting
        $tglEntri = !empty($trx['tgl_entri']) ? Carbon::parse($trx['tgl_entri']) : Carbon::now();
        $tglBayar = $tglEntri->format('d / m / Y   H : i : s');
        $tglBankFormat = $tglEntri->format('d-m-Y H:i:s');
        $requestTime = $tglEntri->copy()->subMinutes(3)->format('d-m-Y H:i:s');

        // Defaults
        $custName = '';
        $tarifDaya = '';
        $noMeter = $tujuan;
        $idPel = $tujuan;
        $standMeter = '7447-6541';
        $blTh = strtoupper(Carbon::now()->translatedFormat('M/Y'));
        $jumlahKwh = '';
        $token20 = '';
        $reff = !empty($trx['ref_id']) ? $trx['ref_id'] : (!empty($trx['kode']) ? '2TKT' . strtoupper(substr(md5($trx['kode']), 0, 22)) : '');
        $accountNumber = $tujuan ?: '614501012345';
        $rpToken = 0;
        $ppj = 0;
        $ppn = 0;
        $materai = 0;
        $angsuran = 0;
        $jmlBulan = '1 BULAN';
        $denda = 0;

        // Ekstraksi denominasi resmi PLN dari kode / nama produk (misal PLN20 -> 20.000, PLN50 -> 50.000)
        $extractPlnNominal = function(string $code, string $name) {
            $str = strtoupper($code . ' ' . $name);
            if (preg_match('/\b(20|50|100|200|500|1000|5000)\s*(?:K|RB|RIBU)?\b/i', $str, $m)) {
                $num = (int)$m[1];
                if ($num < 1000) return (float)($num * 1000);
                if ($num === 1000) return 1000000.0;
                if ($num === 5000) return 5000000.0;
            }
            if (preg_match('/(?:PLN|TOKEN|DPLN|TPLN)(\d+)/i', $code, $m)) {
                $num = (int)$m[1];
                if ($num <= 500) return (float)($num * 1000);
                if ($num >= 1000 && $num < 10000) return (float)($num * 1000);
                if ($num >= 10000) return (float)$num;
            }
            if (preg_match('/\b(\d{1,3}(?:\.\d{3})+)\b/', $str, $m)) {
                return (float)str_replace('.', '', $m[1]);
            }
            return 0.0;
        };
        $plnNominal = $extractPlnNominal($trx['kode_produk'] ?? '', $trx['nama_produk'] ?? ($trx['nama'] ?? ''));

        $parserMode = $rule['parser_mode'] ?? 'auto';

        // 0. Field-by-Field Regex Extraction (Mapping regex spesifik per kolom struk)
        $fieldRegexMap = [
            'regex_meter'    => function($val) use (&$noMeter) { $noMeter = $val; },
            'regex_idpel'    => function($val) use (&$idPel) { $idPel = $val; },
            'regex_nama'     => function($val) use (&$custName) { $custName = $val; },
            'regex_tarif'    => function($val) use (&$tarifDaya) { $tarifDaya = $val; },
            'regex_token'    => function($val) use (&$token20) { $token20 = $val; },
            'regex_kwh'      => function($val) use (&$jumlahKwh) { $jumlahKwh = $val; },
            'regex_stand'    => function($val) use (&$standMeter) { $standMeter = $val; },
            'regex_blth'     => function($val) use (&$blTh) { $blTh = $val; },
            'regex_jmlbulan' => function($val) use (&$jmlBulan) { $jmlBulan = $val; },
            'regex_denda'    => function($val) use (&$denda) { $d = $this->parseCurrencyValue($val); if ($d >= 0) $denda = $d; },
            'regex_tagihan'  => function($val) use (&$harga) { 
                $n = $this->parseCurrencyValue($val); 
                if ($n > 0) $harga = $n; 
            },
            'regex_reff'     => function($val) use (&$reff) { $reff = $val; },
            'regex_norek'    => function($val) use (&$accountNumber) { $accountNumber = $val; },
        ];

        foreach ($fieldRegexMap as $prop => $setter) {
            $fPattern = trim($rule[$prop] ?? '');
            if (!empty($fPattern)) {
                $pat = $fPattern;
                if (!str_starts_with($pat, '/')) {
                    $pat = '/' . $pat . '/i';
                }
                try {
                    if (@preg_match($pat, $sn, $fMatches)) {
                        $matchedVal = trim($fMatches[1] ?? $fMatches[0] ?? '');
                        if ($matchedVal !== '') {
                            $setter($matchedVal);
                        }
                    }
                } catch (\Throwable $e) {}
            }
        }

        // 1. Template Pattern Parser Mode (Penggantian Parameter [token], [namapelanggan], dll)
        $templateStr = trim($rule['sn_template'] ?? '');
        if (($parserMode === 'template' || !empty($templateStr)) && !empty($templateStr)) {
            try {
                $compiledRegex = $this->compileTemplateToRegex($templateStr);
                if (!empty($compiledRegex) && @preg_match($compiledRegex, $sn, $mTemplate)) {
                    if (!empty($mTemplate['token']))          $token20 = trim($mTemplate['token']);
                    if (!empty($mTemplate['namapelanggan']))  $custName = trim($mTemplate['namapelanggan']);
                    elseif (!empty($mTemplate['nama']))       $custName = trim($mTemplate['nama']);
                    
                    if (!empty($mTemplate['golongan']) && !empty($mTemplate['daya'])) {
                        $tarifDaya = trim($mTemplate['golongan']) . '/' . trim($mTemplate['daya']);
                    } elseif (!empty($mTemplate['daya'])) {
                        $tarifDaya = trim($mTemplate['daya']);
                    } elseif (!empty($mTemplate['golongan'])) {
                        $tarifDaya = trim($mTemplate['golongan']);
                    } elseif (!empty($mTemplate['tarif'])) {
                        $tarifDaya = trim($mTemplate['tarif']);
                    }
                    
                    if (!empty($mTemplate['kwh']))            $jumlahKwh = trim($mTemplate['kwh']);
                    if (!empty($mTemplate['nometer']))        $noMeter = trim($mTemplate['nometer']);
                    elseif (!empty($mTemplate['meter']))     $noMeter = trim($mTemplate['meter']);
                    
                    if (!empty($mTemplate['idpel']))          $idPel = trim($mTemplate['idpel']);
                    if (!empty($mTemplate['reff']))           $reff = trim($mTemplate['reff']);
                    elseif (!empty($mTemplate['plnreff']))    $reff = trim($mTemplate['plnreff']);
                    
                    if (!empty($mTemplate['norek']))          $accountNumber = trim($mTemplate['norek']);
                    elseif (!empty($mTemplate['rekening']))   $accountNumber = trim($mTemplate['rekening']);
                    
                    if (!empty($mTemplate['stand']))          $standMeter = trim($mTemplate['stand']);
                    if (!empty($mTemplate['blth']))           $blTh = trim($mTemplate['blth']);

                    if (!empty($mTemplate['jmlbulan']))       $jmlBulan = trim($mTemplate['jmlbulan']);
                    elseif (!empty($mTemplate['lembar']))     $jmlBulan = trim($mTemplate['lembar']);
                    elseif (!empty($mTemplate['bulan']))      $jmlBulan = trim($mTemplate['bulan']);

                    if (!empty($mTemplate['denda']))          $denda = $this->parseCurrencyValue($mTemplate['denda']);
                    
                    if (!empty($mTemplate['rptoken']))        $rpToken = $this->parseCurrencyValue($mTemplate['rptoken']);
                    if (!empty($mTemplate['ppj']))            $ppj = $this->parseCurrencyValue($mTemplate['ppj']);
                    if (!empty($mTemplate['ppn']))            $ppn = $this->parseCurrencyValue($mTemplate['ppn']);
                    if (!empty($mTemplate['materai']))        $materai = $this->parseCurrencyValue($mTemplate['materai']);
                    if (!empty($mTemplate['angsuran']))       $angsuran = $this->parseCurrencyValue($mTemplate['angsuran']);

                    $rawNominal = $mTemplate['nominal'] ?? $mTemplate['rptagihan'] ?? $mTemplate['totalbayar'] ?? null;
                    if ($rawNominal !== null) {
                        $cleanNum = $this->parseCurrencyValue($rawNominal);
                        if ($cleanNum > 0) $harga = $cleanNum;
                    }
                    if (!empty($mTemplate['admin'])) {
                        $cleanAdm = $this->parseCurrencyValue($mTemplate['admin']);
                        if ($cleanAdm > 0) $adminFee = $cleanAdm;
                    } elseif (!empty($mTemplate['adminbank'])) {
                        $cleanAdm = $this->parseCurrencyValue($mTemplate['adminbank']);
                        if ($cleanAdm > 0) $adminFee = $cleanAdm;
                    }
                }
            } catch (\Throwable $e) {}
        }

        // 2. Custom Regex Parser Mode (Single line named capture groups)
        if ($parserMode === 'regex' && !empty($rule['sn_regex'])) {
            try {
                if (@preg_match($rule['sn_regex'], $sn, $mCustom)) {
                    if (!empty($mCustom['token']))     $token20 = trim($mCustom['token']);
                    if (!empty($mCustom['nama']))      $custName = trim($mCustom['nama']);
                    if (!empty($mCustom['tarif']))     $tarifDaya = trim($mCustom['tarif']);
                    if (!empty($mCustom['kwh']))       $jumlahKwh = trim($mCustom['kwh']);
                    if (!empty($mCustom['stand']))     $standMeter = trim($mCustom['stand']);
                    if (!empty($mCustom['blth']))      $blTh = trim($mCustom['blth']);
                    if (!empty($mCustom['idpel']))     $idPel = trim($mCustom['idpel']);
                    if (!empty($mCustom['meter']))     $noMeter = trim($mCustom['meter']);
                    if (!empty($mCustom['norek']))     $accountNumber = trim($mCustom['norek']);
                    if (!empty($mCustom['reff']))      $reff = trim($mCustom['reff']);
                    elseif (!empty($mCustom['plnreff'])) $reff = trim($mCustom['plnreff']);
                    if (!empty($mCustom['tagihan']))   $harga = $this->parseCurrencyValue($mCustom['tagihan']);
                    elseif (!empty($mCustom['nominal'])) $harga = $this->parseCurrencyValue($mCustom['nominal']);
                    if (!empty($mCustom['denda']))     $denda = $this->parseCurrencyValue($mCustom['denda']);
                    if (!empty($mCustom['jmlbulan']))  $jmlBulan = trim($mCustom['jmlbulan']);
                }
            } catch (\Throwable $e) {}
        }
        // 2. Custom Delimiter / Column Template Parser Mode
        elseif ($parserMode === 'delimiter' && !empty($rule['sn_pattern'])) {
            $delim = !empty($rule['sn_delimiter']) ? $rule['sn_delimiter'] : '/';
            $snParts = explode($delim, $sn);
            $patternParts = explode($delim, $rule['sn_pattern']);

            foreach ($patternParts as $idx => $tag) {
                if (!isset($snParts[$idx])) break;
                $val = trim($snParts[$idx]);
                if (empty($val)) continue;

                $cleanTag = strtoupper(trim($tag, " \t\n\r\0\x0B[]{}()"));
                switch ($cleanTag) {
                    case 'TOKEN':
                        $token20 = $val;
                        break;
                    case 'NAMA':
                    case 'NAMA_PELANGGAN':
                    case 'NAMA_REK':
                    case 'AN':
                        $custName = $val;
                        break;
                    case 'TARIF':
                    case 'DAYA':
                    case 'TARIF_DAYA':
                    case 'TRF':
                        $tarifDaya = $val;
                        break;
                    case 'KWH':
                    case 'JUMLAH_KWH':
                        $jumlahKwh = $val;
                        break;
                    case 'STAND':
                    case 'STAND_METER':
                        $standMeter = $val;
                        break;
                    case 'BLTH':
                    case 'PERIODE':
                    case 'BULAN':
                        $blTh = $val;
                        break;
                    case 'JMLBULAN':
                    case 'LEMBAR':
                        $jmlBulan = $val;
                        break;
                    case 'DENDA':
                        $denda = $this->parseCurrencyValue($val);
                        break;
                    case 'IDPEL':
                        $idPel = $val;
                        break;
                    case 'METER':
                    case 'NOMETER':
                        $noMeter = $val;
                        break;
                    case 'NOREK':
                    case 'REKENING':
                        $accountNumber = $val;
                        break;
                    case 'REFF':
                    case 'PLNREFF':
                    case 'REF':
                    case 'SN':
                        $reff = $val;
                        break;
                    case 'TAGIHAN':
                    case 'RPTAGIHAN':
                    case 'NOMINAL':
                    case 'TOTAL':
                    case 'TAG':
                        $cleanNum = $this->parseCurrencyValue($val);
                        if ($cleanNum > 0) $harga = $cleanNum;
                        break;
                }
            }
        }

        // Smart Auto-Detect Fallback: Token (supports digits and masked X)
        if (empty($token20)) {
            if (preg_match('/([0-9A-Za-z]{4})[-\s]?([0-9A-Za-z]{4})[-\s]?([0-9A-Za-z]{4})[-\s]?([0-9A-Za-z]{4})[-\s]?([0-9A-Za-z]{4})/', $sn, $tokenMatch)) {
                $token20 = "{$tokenMatch[1]} - {$tokenMatch[2]} - {$tokenMatch[3]} - {$tokenMatch[4]} - {$tokenMatch[5]}";
            } elseif (preg_match('/\b[0-9A-Za-z]{20}\b/', $sn, $tokenMatch)) {
                $raw = $tokenMatch[0];
                $token20 = substr($raw, 0, 4) . ' - ' . substr($raw, 4, 4) . ' - ' . substr($raw, 8, 4) . ' - ' . substr($raw, 12, 4) . ' - ' . substr($raw, 16, 4);
            }
        } elseif (preg_match('/^[0-9A-Za-z]{20}$/', preg_replace('/[-\s]/', '', $token20))) {
            $raw = preg_replace('/[-\s]/', '', $token20);
            $token20 = substr($raw, 0, 4) . ' - ' . substr($raw, 4, 4) . ' - ' . substr($raw, 8, 4) . ' - ' . substr($raw, 12, 4) . ' - ' . substr($raw, 16, 4);
        }

        // Smart Auto-Detect Fallback: Customer Name (case-insensitive)
        if (empty($custName)) {
            if (preg_match('/(?:NAMA|NAME|NAMA\s*PELANGGAN|NAMA\s*REK|AN|A\/N)\s*[:=]\s*([^\/,;]+)/i', $sn, $mName)) {
                $custName = trim($mName[1]);
            } elseif (preg_match('/\/([a-zA-Z\s\.\']{3,40})\//i', $sn, $mName)) {
                $custName = trim($mName[1]);
            }
        }

        // Smart Auto-Detect Fallback: Tarif / Daya
        if (empty($tarifDaya)) {
            if (preg_match('/(?:TARIF|DAYA|TARIF\/DAYA|TRF)\s*[:=]\s*([^\/,;]+)/i', $sn, $mTarif)) {
                $tarifDaya = trim($mTarif[1]);
            } elseif (preg_match('/\b(R\d[A-Z]?\/\d+[A-Z]*|B\d\/\d+[A-Z]*|I\d\/\d+[A-Z]*)\b/i', $sn, $mTarif)) {
                $tarifDaya = strtoupper(trim($mTarif[1]));
            }
        }

        // Smart Auto-Detect Fallback: Jumlah kWh
        if (empty($jumlahKwh)) {
            if (preg_match('/(?:KWH|JML\s*KWH|JUMLAH\s*KWH)\s*[:=]\s*([\d\.,]+)/i', $sn, $mKwh)) {
                $jumlahKwh = trim($mKwh[1]);
            } elseif (preg_match('/\/(\d+(?:[\.,]\d+)?)\s*(?:PLNREF|KWH|\/)/i', $sn, $mKwh)) {
                $jumlahKwh = trim($mKwh[1]);
            }
        }

        // Smart Auto-Detect Fallback: Stand Meter
        if (empty($standMeter) || $standMeter === '7447-6541') {
            if (preg_match('/(?:STAND|STAND\s*METER)\s*[:=]\s*([^\/,;]+)/i', $sn, $mStand)) {
                $standMeter = trim($mStand[1]);
            }
        }

        // Smart Auto-Detect Fallback: Reff
        if (empty($reff) || str_starts_with($reff, '2TKT')) {
            if (preg_match('/(?:PLNREF|PLNREFF|REFF|REF|SN)\s*[:=]\s*([A-Za-z0-9]+)/i', $sn, $mReff)) {
                $reff = trim($mReff[1]);
            }
        }

        // Smart Auto-Detect Fallback: BL/TH
        if (preg_match('/(?:BLN?\/THN?|BL\/TH|PERIODE)\s*[:=]\s*([^\/,;]+)/i', $sn, $mBlth)) {
            $blTh = trim($mBlth[1]);
        }

        // Smart Auto-Detect Fallback: IDPEL & No Meter
        if (preg_match('/(?:IDPEL|ID\s*PEL|ID\s*PELANGGAN)\s*[:=]\s*(\d{10,13})/i', $sn, $mIdpel)) {
            $idPel = trim($mIdpel[1]);
        }
        if (preg_match('/(?:NOMETER|NO\s*METER|NO\.?\s*MTR|MTR|METER)\s*[:=]\s*(\d{10,13})/i', $sn, $mMeter)) {
            $noMeter = trim($mMeter[1]);
        }

        // Smart Auto-Detect Fallback: Jumlah Bulan & Denda
        if ($jmlBulan === '1 BULAN') {
            if (preg_match('/(?:JML\s*BULAN|JUMLAH\s*BULAN|LEMBAR|LBR)\s*[:=]\s*(\d+\s*(?:BULAN|LBR)?)/i', $sn, $mJml)) {
                $jmlBulan = trim($mJml[1]);
            }
        }
        if (is_numeric($jmlBulan)) {
            $jmlBulan = $jmlBulan . ' BULAN';
        }

        if ($denda <= 0) {
            if (preg_match('/(?:DENDA)\s*[:=]\s*([\d\.,]+)/i', $sn, $mDenda)) {
                $denda = $this->parseCurrencyValue($mDenda[1]);
            }
        }

        // Smart Auto-Detect Fallback: PPJ, PPN, Materai, Rp Token
        if ($ppj <= 0 && preg_match('/(?:PPJ)\s*[:=]\s*([\d\.,]+)/i', $sn, $mPpj)) {
            $ppj = $this->parseCurrencyValue($mPpj[1]);
        }
        if ($ppn <= 0 && preg_match('/(?:PPN)\s*[:=]\s*([\d\.,]+)/i', $sn, $mPpn)) {
            $ppn = $this->parseCurrencyValue($mPpn[1]);
        }
        if ($materai <= 0 && preg_match('/(?:MATERAI)\s*[:=]\s*([\d\.,]+)/i', $sn, $mMaterai)) {
            $materai = $this->parseCurrencyValue($mMaterai[1]);
        }
        if ($rpToken <= 0 && preg_match('/(?:RP\s*TOKEN|RPTOKEN|STROOM)\s*[:=]\s*([\d\.,]+)/i', $sn, $mRpTok)) {
            $rpToken = $this->parseCurrencyValue($mRpTok[1]);
        }

        // Smart Auto-Detect Fallback: No Rekening (Khusus Bank)
        if (preg_match('/(?:NOREK|NO\.?\s*REK|REK|REKENING)\s*[:=]?\s*(\d{8,25})/i', $sn, $mRek)) {
            $accountNumber = trim($mRek[1]);
        }
        if (empty($accountNumber)) {
            $accountNumber = $tujuan ?: '614501012345';
        }

        // Smart Auto-Detect Fallback: Tagihan Pasca / Nominal Transfer from SN
        if (preg_match('/(?:TAG|TAGIHAN|RP|NOMINAL|JML|JUMLAH)\s*[:=]?\s*Rp?\s*([\d\.,]+)/i', $sn, $mTag)) {
            $parsedNum = $this->parseCurrencyValue($mTag[1]);
            if ($parsedNum > 100 && $harga <= 0) {
                $harga = $parsedNum;
            }
        }

        // Fallbacks for display if still empty
        if (empty($custName)) $custName = ($category === 'UMUM') ? '' : 'PRABOWO SUBIANTO WARDJOYO';
        if (empty($tarifDaya)) $tarifDaya = ($category === 'UMUM') ? '' : 'R1/900VA';
        if (empty($jumlahKwh)) $jumlahKwh = ($category === 'UMUM') ? '' : '503';
        if (empty($token20)) $token20 = ($category === 'UMUM') ? '' : '2920 - 3211 - 4567 - 8910 - 0987';
        if (empty($reff)) $reff = !empty($trx['sn']) ? $trx['sn'] : (!empty($trx['ref_id']) ? $trx['ref_id'] : ($trx['kode'] ?? '2TKT23R3D5994E77D4FA6B977F3A1F3'));

        // Math for PLN Prabayar & Umum: Jangan pernah pakai harga modal/jual wholesale server!
        if ($category === 'PRABAYAR') {
            if ($rpToken > 0) {
                $stroomToken = $rpToken;
            } elseif ($plnNominal > 0) {
                $stroomToken = $plnNominal;
                $rpToken = $plnNominal;
            } else {
                $stroomToken = 20000;
                $rpToken = 20000;
            }
            $totalBayarPra = $stroomToken + $adminFee;
        } else {
            $stroomToken = $rpToken > 0 ? $rpToken : ($harga > 0 ? $harga : 50000);
            $totalBayarPra = $stroomToken + $adminFee;
        }

        // Math for PLN Pasca: Total Bayar = Pokok Tagihan + Biaya Admin + Denda
        $tagihanPasca = $harga > 0 ? $harga : 210345;
        $totalBayarPasca = $tagihanPasca + $adminFee + $denda;

        // Bank Transfer specific
        $bankName = $rule['bank_name'] ?? 'BANK CENTRAL ASIA';
        $transferAmount = $harga > 0 ? $harga : 4000000;
        $trxIdLong = !empty($trx['kode']) ? str_pad($trx['kode'], 18, '0', STR_PAD_LEFT) : '032312023948239990';
        $remark = $rule['remark'] ?? 'Transfer Uang';
        $remitterRef = !empty($trx['ref_id']) ? $trx['ref_id'] : '261021VD3UDD';

        return [
            'category'          => $category,
            'rule'              => $rule,
            'trxid'             => $trx['kode'] ?? 'TRX' . time(),
            'tujuan'            => $tujuan,
            'kode_produk'       => $trx['kode_produk'] ?? 'PROD',
            'nama_produk'       => $trx['nama_produk'] ?? ($trx['kode_produk'] ?? 'PRODUK'),
            'status'            => $trx['status'] ?? 'Sukses',
            'sn'                => $sn,
            
            // Dates
            'tgl_bayar'         => $tglBayar,
            'tgl_bank_format'   => $tglBankFormat,
            'request_time'      => $requestTime,

            // Customer
            'nama'              => $custName,
            'id_pel'            => $idPel,
            'no_meter'          => $noMeter,
            'tarif_daya'        => $tarifDaya,
            'bl_th'             => $blTh,
            'stand_meter'       => $standMeter,
            'jml_bulan'         => $jmlBulan,
            'denda'             => $denda,

            // PLN Numbers
            'admin_fee'         => $adminFee,
            'tagihan_pasca'     => $tagihanPasca,
            'total_bayar_pasca' => $totalBayarPasca,

            'stroom_token'      => $stroomToken,
            'total_bayar_pra'   => $totalBayarPra,
            'materai'           => $materai,
            'ppn'               => $ppn,
            'ppj'               => $ppj,
            'angsuran'          => $angsuran,
            'jumlah_kwh'        => $jumlahKwh,
            'token_stroom'      => $token20,

            // Bank Transfer
            'bank_name'         => $bankName,
            'amount'            => $transferAmount,
            'remark'            => $remark,
            'remitter_ref'      => $remitterRef,
            'beneficiary_ref'   => '-',
            'trx_id_long'       => $trxIdLong,
            'account_number'    => $accountNumber,
            'account_name'      => $custName,

            // General / Meta
            'reff'              => $reff,
            'footer_info'       => $rule['footer_info'] ?? '• Call Center 123  • Mobile PLN  • PLN Terdekat',
            'logo_url'          => $rule['logo_url'] ?? '/logo',
            'store_title'       => Setting::get('site_title', Cache::get('site_title', 'PAYMENT POINT ONLINE BANKING')),
        ];
    }

    /**
     * Convert supplier SN template pattern like "[token]/[namapelanggan]/[golongan]/[daya]"
     * into regular expression with named capture groups.
     */
    public function compileTemplateToRegex(string $template): string
    {
        $template = trim($template);
        if (empty($template)) return '';

        $parts = preg_split('/(\[[a-zA-Z0-9_]+\])/', $template, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        if (empty($parts)) return '';

        $regex = '';
        $count = count($parts);
        foreach ($parts as $idx => $part) {
            $isLast = ($idx === $count - 1);
            if (str_starts_with($part, '[') && str_ends_with($part, ']')) {
                $rawTag = strtolower(substr($part, 1, -1));

                // Normalisasi sinonim tag
                if ($rawTag === 'plnreff') $rawTag = 'reff';
                elseif ($rawTag === 'adminbank') $rawTag = 'admin';
                elseif ($rawTag === 'rptagihan') $rawTag = 'nominal';
                elseif ($rawTag === 'lembar' || $rawTag === 'bulan') $rawTag = 'jmlbulan';

                if (str_starts_with($rawTag, 'var')) {
                    $regex .= $isLast ? '(?:.+?)' : '(?:.+?)';
                } else {
                    // Toleran terhadap karakter trailing setelah token terakhir (misal sisa saldo, spasi, dsb)
                    $regex .= $isLast ? '(?P<' . $rawTag . '>[^\/\r\n,;]+)' : '(?P<' . $rawTag . '>.+?)';
                }
            } else {
                $quoted = preg_quote($part, '/');
                $quoted = preg_replace('/\s+/', '\s*', $quoted);
                $regex .= $quoted;
            }
        }

        return '/' . $regex . '/is';
    }
}
