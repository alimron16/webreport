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
                'is_default'    => true,
            ],
            [
                'id'            => 'rule_dana',
                'name'          => 'Top Up Saldo DANA',
                'category'      => 'UMUM',
                'provider'      => 'DANA',
                'keywords'      => 'DANA, TFDANA',
                'logo_url'      => '/images/gateways/dana.png',
                'default_admin' => 2000,
                'footer_info'   => 'Terima kasih atas pembayaran Anda. Simpan struk ini sebagai bukti transaksi sah.',
                'is_default'    => true,
            ],
        ];
    }

    /**
     * Get active rules (persistent from database / cache)
     */
    public function getRules(): array
    {
        $cached = Cache::get('receipt_rules');
        if (is_array($cached) && !empty($cached)) {
            return $cached;
        }

        $dbJson = Setting::get('receipt_rules', null);
        if ($dbJson) {
            $decoded = json_decode($dbJson, true);
            if (is_array($decoded) && !empty($decoded)) {
                Cache::forever('receipt_rules', $decoded);
                return $decoded;
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
     * Find matching rule for a transaction
     */
    public function findMatchingRule(array $trx): array
    {
        $rules = $this->getRules();
        $code = strtoupper(trim($trx['kode_produk'] ?? ''));
        $name = strtoupper(trim($trx['nama_produk'] ?? ($trx['nama'] ?? '')));
        $op   = strtoupper(trim($trx['kode_operator'] ?? ''));
        $sn   = strtoupper(trim($trx['sn'] ?? ''));
        $ket  = strtoupper(trim($trx['keterangan'] ?? ''));

        // PASS 1: Prioritas Tertinggi — Pencocokan Kode Produk Spesifik (Keywords)
        // Jika ada aturan yang mencantumkan kode produk spesifik (misal 'PLNPASCA' atau 'TFBCA'), dahulukan!
        foreach ($rules as $r) {
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
        // Jika aturan disetel untuk Provider (misal: 'BCA', 'PLN', 'TELKOMSEL'), 
        // otomatis SEMUA produk yang berada di bawah provider/operator tersebut akan cocok!
        foreach ($rules as $r) {
            $rawProvider = trim($r['provider'] ?? '');
            if (empty($rawProvider)) continue;

            $providers = array_filter(array_map('trim', explode(',', strtoupper($rawProvider))));
            foreach ($providers as $prov) {
                if (empty($prov)) continue;

                // Cocokkan dengan kode_operator dari tabel produk
                if (!empty($op) && ($op === $prov || str_contains($op, $prov))) {
                    return $r;
                }

                // Cocokkan dengan kode_produk (misal: TFBCA mengandung BCA, PLN20 mengandung PLN)
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

        // Bank detector
        if (str_contains($code, 'TF') || str_contains($name, 'TRANSFER') || str_contains($code, 'BANK')) {
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

        // General default
        return [
            'id'            => 'rule_default_general',
            'name'          => 'Struk Transaksi',
            'category'      => 'UMUM',
            'logo_url'      => '/logo',
            'default_admin' => 2000,
            'footer_info'   => 'Terima kasih atas pembayaran Anda. Simpan struk ini sebagai bukti transaksi yang sah.',
        ];
    }

    /**
     * Parse raw transaction and SN string into structured receipt data
     */
    public function parseReceipt(array $trx, ?array $rule = null): array
    {
        if (!$rule) {
            $rule = $this->findMatchingRule($trx);
        }

        $category = strtoupper($rule['category'] ?? 'UMUM');
        $sn = (string)($trx['sn'] ?? '');
        $tujuan = (string)($trx['tujuan'] ?? '');
        $harga = (float)($trx['harga'] ?? 0);
        $adminFee = (float)($rule['default_admin'] ?? 2500);

        // Date formatting
        $tglEntri = !empty($trx['tgl_entri']) ? Carbon::parse($trx['tgl_entri']) : Carbon::now();
        $tglBayar = $tglEntri->format('d / m / Y   H : i : s');
        $tglBankFormat = $tglEntri->format('d-m-Y H:i:s');
        $requestTime = $tglEntri->copy()->subMinutes(3)->format('d-m-Y H:i:s');

        // Defaults
        $custName = 'PRABOWO SUBIANTO WARDJOYO';
        $tarifDaya = 'R1/900VA';
        $noMeter = $tujuan;
        $idPel = $tujuan;
        $standMeter = '7447-6541';
        $blTh = strtoupper(Carbon::now()->translatedFormat('M/Y'));
        $jumlahKwh = '503';
        $token20 = '';
        $reff = !empty($trx['ref_id']) ? $trx['ref_id'] : (!empty($trx['kode']) ? '2TKT' . strtoupper(substr(md5($trx['kode']), 0, 22)) : '2TKT23R3D5994E77D4FA6B977F3A1F3');
        $accountNumber = $tujuan ?: '614501012345';

        $parserMode = $rule['parser_mode'] ?? 'auto';

        // 0. Field-by-Field Regex Extraction (Mapping regex spesifik per kolom struk)
        $fieldRegexMap = [
            'regex_meter'   => function($val) use (&$noMeter) { $noMeter = $val; },
            'regex_idpel'   => function($val) use (&$idPel) { $idPel = $val; },
            'regex_nama'    => function($val) use (&$custName) { $custName = $val; },
            'regex_tarif'   => function($val) use (&$tarifDaya) { $tarifDaya = $val; },
            'regex_token'   => function($val) use (&$token20) { $token20 = $val; },
            'regex_kwh'     => function($val) use (&$jumlahKwh) { $jumlahKwh = $val; },
            'regex_stand'   => function($val) use (&$standMeter) { $standMeter = $val; },
            'regex_blth'    => function($val) use (&$blTh) { $blTh = $val; },
            'regex_tagihan' => function($val) use (&$harga) { 
                $n = (float)str_replace(['.', ','], '', $val); 
                if ($n > 0) $harga = $n; 
            },
            'regex_reff'    => function($val) use (&$reff) { $reff = $val; },
            'regex_norek'   => function($val) use (&$accountNumber) { $accountNumber = $val; },
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
                    if (!empty($mTemplate['plnreff']))        $reff = trim($mTemplate['plnreff']);
                    elseif (!empty($mTemplate['reff']))       $reff = trim($mTemplate['reff']);
                    
                    if (!empty($mTemplate['norek']))          $accountNumber = trim($mTemplate['norek']);
                    elseif (!empty($mTemplate['rekening']))   $accountNumber = trim($mTemplate['rekening']);
                    
                    if (!empty($mTemplate['stand']))          $standMeter = trim($mTemplate['stand']);
                    if (!empty($mTemplate['blth']))           $blTh = trim($mTemplate['blth']);
                    
                    $rawNominal = $mTemplate['nominal'] ?? $mTemplate['rptoken'] ?? $mTemplate['totalbayar'] ?? null;
                    if ($rawNominal !== null) {
                        $cleanNum = (float)str_replace(['.', ','], '', $rawNominal);
                        if ($cleanNum > 0) $harga = $cleanNum;
                    }
                    if (!empty($mTemplate['admin'])) {
                        $cleanAdm = (float)str_replace(['.', ','], '', $mTemplate['admin']);
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
                    if (!empty($mCustom['tagihan']))   $harga = (float)str_replace(['.', ','], '', $mCustom['tagihan']);
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
                    case 'REF':
                    case 'SN':
                        $reff = $val;
                        break;
                    case 'TAGIHAN':
                    case 'NOMINAL':
                    case 'TOTAL':
                    case 'TAG':
                        $cleanNum = (float)str_replace(['.', ','], '', $val);
                        if ($cleanNum > 0) $harga = $cleanNum;
                        break;
                }
            }
        }

        // Smart Auto-Detect Fallback: Extract 20-digit token from SN if not already extracted
        if (empty($token20)) {
            if (preg_match('/(\d{4})[-\s]?(\d{4})[-\s]?(\d{4})[-\s]?(\d{4})[-\s]?(\d{4})/', $sn, $tokenMatch)) {
                $token20 = "{$tokenMatch[1]} - {$tokenMatch[2]} - {$tokenMatch[3]} - {$tokenMatch[4]} - {$tokenMatch[5]}";
            } elseif (preg_match('/\b\d{20}\b/', $sn, $tokenMatch)) {
                $raw = $tokenMatch[0];
                $token20 = substr($raw, 0, 4) . ' - ' . substr($raw, 4, 4) . ' - ' . substr($raw, 8, 4) . ' - ' . substr($raw, 12, 4) . ' - ' . substr($raw, 16, 4);
            } else {
                $token20 = '2920 - 3211 - 4567 - 8910 - 0987';
            }
        } elseif (preg_match('/^\d{20}$/', preg_replace('/[-\s]/', '', $token20))) {
            $raw = preg_replace('/[-\s]/', '', $token20);
            $token20 = substr($raw, 0, 4) . ' - ' . substr($raw, 4, 4) . ' - ' . substr($raw, 8, 4) . ' - ' . substr($raw, 12, 4) . ' - ' . substr($raw, 16, 4);
        }

        // Extract Customer Name from SN
        if (preg_match('/(?:NAMA|NAME|NAMA\sPELANGGAN|NAMA\sREK|AN|A\/N)\s*[:=]\s*([^\/,;]+)/i', $sn, $mName)) {
            $custName = trim($mName[1]);
        } elseif (preg_match('/\/([A-Z\s]{4,35})\//', $sn, $mName)) {
            $custName = trim($mName[1]);
        }

        // Extract Tarif / Daya
        if (preg_match('/(?:TARIF|DAYA|TARIF\/DAYA|TRF)\s*[:=]\s*([^\/,;]+)/i', $sn, $mTarif)) {
            $tarifDaya = trim($mTarif[1]);
        } elseif (preg_match('/\b(R\d[A-Z]?\/\d+[A-Z]*|B\d\/\d+[A-Z]*|I\d\/\d+[A-Z]*)\b/i', $sn, $mTarif)) {
            $tarifDaya = strtoupper(trim($mTarif[1]));
        }

        // Extract KWH
        if (preg_match('/(?:KWH|JML\sKWH|JUMLAH\sKWH)\s*[:=]\s*([\d\.,]+)/i', $sn, $mKwh)) {
            $jumlahKwh = trim($mKwh[1]);
        } elseif (preg_match('/\/(\d{2,4}(?:\.\d)?)\s*(?:KWH|\/)/i', $sn, $mKwh)) {
            $jumlahKwh = trim($mKwh[1]);
        }

        // Extract Stand Meter
        if (preg_match('/(?:STAND|STAND\sMETER)\s*[:=]\s*([^\/,;]+)/i', $sn, $mStand)) {
            $standMeter = trim($mStand[1]);
        }

        // Extract Reff
        if (preg_match('/(?:REFF|REF|SN)\s*[:=]\s*([A-Za-z0-9]+)/i', $sn, $mReff)) {
            $reff = trim($mReff[1]);
        }

        // Extract BL/TH
        if (preg_match('/(?:BLN?\/THN?|BL\/TH|PERIODE)\s*[:=]\s*([^\/,;]+)/i', $sn, $mBlth)) {
            $blTh = trim($mBlth[1]);
        }

        // Extract IDPEL & No Meter from SN if available
        if (preg_match('/(?:IDPEL|ID\s*PEL|ID\s*PELANGGAN)\s*[:=]\s*(\d{10,13})/i', $sn, $mIdpel)) {
            $idPel = trim($mIdpel[1]);
        }
        if (preg_match('/(?:NOMETER|NO\s*METER|NO\.?\s*MTR|MTR|METER)\s*[:=]\s*(\d{10,13})/i', $sn, $mMeter)) {
            $noMeter = trim($mMeter[1]);
        }

        // Extract No Rekening (Khusus Bank) from SN if available
        $accountNumber = $tujuan;
        if (preg_match('/(?:NOREK|NO\.?\s*REK|REK|REKENING)\s*[:=]?\s*(\d{8,25})/i', $sn, $mRek)) {
            $accountNumber = trim($mRek[1]);
        }
        if (empty($accountNumber)) {
            $accountNumber = '614501012345';
        }

        // Extract Tagihan Pasca / Nominal Transfer from SN if available
        if (preg_match('/(?:TAG|TAGIHAN|RP|NOMINAL|JML|JUMLAH)\s*[:=]?\s*Rp?\s*([\d\.,]+)/i', $sn, $mTag)) {
            $parsedNum = (float)str_replace(['.', ','], '', $mTag[1]);
            if ($parsedNum > 100) {
                if ($harga <= 0) $harga = $parsedNum;
            }
        }

        // Math for PLN Prabayar
        $stroomToken = $harga > 0 ? $harga : 876543;
        $totalBayarPra = $stroomToken + $adminFee;
        $ppj = $harga > 0 ? round($harga * 0.05) : 43210;

        // Math for PLN Pasca
        $tagihanPasca = $harga > 0 ? $harga : 210345;
        $totalBayarPasca = $tagihanPasca + $adminFee;

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

            // PLN Numbers
            'admin_fee'         => $adminFee,
            'tagihan_pasca'     => $tagihanPasca,
            'total_bayar_pasca' => $totalBayarPasca,

            'stroom_token'      => $stroomToken,
            'total_bayar_pra'   => $totalBayarPra,
            'materai'           => 0,
            'ppn'               => 0,
            'ppj'               => $ppj,
            'angsuran'          => 0,
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
        foreach ($parts as $part) {
            if (str_starts_with($part, '[') && str_ends_with($part, ']')) {
                $rawTag = strtolower(substr($part, 1, -1));
                if (str_starts_with($rawTag, 'var')) {
                    $regex .= '(?:.+?)';
                } else {
                    $regex .= '(?P<' . $rawTag . '>.+?)';
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
