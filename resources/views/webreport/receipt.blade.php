<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pembayaran - {{ $receipt['trxid'] ?? 'Transaksi' }}</title>
    
    <!-- Google Fonts & Boxicons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Courier+Prime:wght@400;700&family=Inter:wght@400;500;600;700;800&family=Roboto+Slab:wght@700;800&family=Roboto+Mono:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- html2canvas for Image Download -->
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>

    <style>
        /* ═══════════════════════════════════════════════
           RECEIPT PAGE STYLES — Exact Template Matching
           ═══════════════════════════════════════════════ */
        * {
            box-sizing: border-box;
        }

        :root {
            --bs-primary: #0B6FB6;
            --bs-primary-rgb: 11, 111, 182;
        }

        .btn-primary {
            background: linear-gradient(135deg, #0B6FB6 0%, #1C8CFB 100%) !important;
            border: none !important;
            box-shadow: 0 4px 10px rgba(11, 111, 182, 0.25);
        }

        .btn-primary:hover {
            opacity: 0.95;
            box-shadow: 0 6px 14px rgba(11, 111, 182, 0.35);
        }

        .btn-outline-primary {
            color: #0B6FB6 !important;
            border-color: #0B6FB6 !important;
        }

        .btn-outline-primary:hover {
            background-color: #0B6FB6 !important;
            color: #ffffff !important;
        }

        body {
            background-color: #f1f5f9;
            color: #0f172a;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            margin: 0;
            padding: 24px 16px 60px;
        }

        /* Top Action Bar (Screen Only) */
        .receipt-action-bar {
            max-width: 1040px;
            margin: 0 auto 20px;
            background: #ffffff;
            border-radius: 12px;
            padding: 14px 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        /* Receipt Canvas Outer Wrapper */
        .receipt-wrapper {
            max-width: 1040px;
            margin: 0 auto;
            overflow-x: auto;
            padding-bottom: 16px;
        }

        /* Base Card for Receipt (Matching User Samples: 1280x569, ratio ~ 2.25:1) */
        .receipt-card {
            background: #ffffff;
            border: 1px solid #71717a;
            border-radius: 0;
            padding: 24px 48px;
            position: relative;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            color: #1e293b;
            min-width: 900px; /* Menjaga ukuran struk selalu persegi panjang horizontal memanjang */
        }

        /* ── TEMPLATE 1 & 2: PLN STYLES (Matching STRUK PRABAYAR & PASCABAYAR) ─── */
        .pln-receipt {
            font-family: 'Courier Prime', 'Roboto Slab', 'Courier New', Courier, monospace;
            font-size: 13.5px;
            line-height: 1.6;
            color: #111827;
            font-weight: 500;
        }

        .pln-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 6px;
        }

        .pln-header-info {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .pln-header-row {
            display: flex;
            align-items: baseline;
            font-size: 14px;
            font-weight: 700;
            line-height: 1.5;
        }

        .pln-header-lbl {
            width: 110px;
            flex-shrink: 0;
            color: #111827;
            font-weight: 700;
        }

        .pln-header-sep {
            margin-right: 8px;
            font-weight: 700;
            color: #111827;
        }

        .pln-header-val {
            font-weight: 700;
            color: #111827;
        }

        .pln-tgl {
            font-size: 14px;
            font-weight: 700;
            line-height: 1.5;
        }

        .pln-logo-box img, .pln-logo-box svg {
            height: 52px;
            max-width: 170px;
            object-fit: contain;
        }

        .pln-title {
            text-align: center;
            font-weight: 700;
            font-size: 15px;
            letter-spacing: 0.5px;
            margin: 10px 0 16px;
            text-transform: uppercase;
        }

        .pln-grid {
            display: flex;
            justify-content: space-between;
            gap: 40px;
        }

        .pln-col {
            flex: 1;
        }

        .pln-row {
            display: flex;
            margin-bottom: 3.5px;
            align-items: baseline;
        }

        .pln-lbl {
            width: 125px;
            flex-shrink: 0;
            font-weight: 700;
            color: #111827;
        }

        .pln-lbl-right {
            width: 135px;
            flex-shrink: 0;
            font-weight: 700;
            color: #111827;
        }

        .pln-sep {
            margin-right: 8px;
            font-weight: 700;
            color: #111827;
        }

        .pln-val {
            flex-grow: 1;
            font-weight: 700;
            color: #111827;
        }

        .pln-money {
            display: inline-flex;
            justify-content: space-between;
            width: 175px;
            font-weight: 700;
        }

        .pln-money-curr {
            flex-shrink: 0;
        }

        .pln-money-val {
            text-align: right;
            flex-grow: 1;
        }

        /* Blue Highlight Box for REFF / TOKEN */
        .pln-highlight-box {
            background-color: #D8EEFB;
            border: none;
            border-radius: 0;
            text-align: center;
            padding: 8px 16px;
            margin: 16px 0 14px;
        }

        .pln-highlight-lbl {
            font-family: 'Courier Prime', 'Roboto Slab', monospace;
            font-size: 13.5px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 2px;
            letter-spacing: 0.5px;
        }

        .pln-highlight-code {
            font-family: 'Roboto Slab', 'Courier Prime', Courier, monospace;
            font-size: 24px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: 1.5px;
            word-break: break-all;
        }

        .token-large {
            font-family: 'Roboto Slab', 'Courier Prime', Courier, monospace;
            font-size: 26px;
            font-weight: 800;
            letter-spacing: 2px;
            color: #0f172a;
        }

        .pln-footer {
            font-family: 'Courier Prime', 'Roboto Slab', monospace;
            font-size: 12.5px;
            color: #1e293b;
            margin-top: 14px;
        }

        .pln-footer-title {
            font-style: italic;
            font-weight: 600;
            margin-bottom: 3px;
        }

        /* ── TEMPLATE 3: BANK TRANSFER (BCA / BANK - Matching STRUK BANK.jpeg) ─── */
        .bank-receipt {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: 13.5px;
            color: #0f172a;
            padding: 4px;
        }

        .bank-top-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }

        .bank-main-title {
            font-size: 20px;
            font-weight: 700;
            color: #1e293b;
        }

        .bank-logo img, .bank-logo svg {
            height: 44px;
            max-width: 150px;
            object-fit: contain;
        }

        /* Blue Section Bar */
        .bank-section-bar {
            background-color: #79B5FB;
            color: #0f172a;
            font-weight: 700;
            font-size: 13.5px;
            padding: 6px 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 10px;
        }

        .bank-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }

        .bank-table td {
            padding: 5px 16px;
            font-size: 13.5px;
            color: #1e293b;
        }

        .bank-table tr:nth-child(odd) {
            background-color: #EDF5FF;
        }

        .bank-table tr:nth-child(even) {
            background-color: #ffffff;
        }

        .bank-table td.b-label {
            width: 250px;
            color: #334155;
            font-weight: 500;
        }

        .bank-table td.b-sep {
            width: 15px;
            text-align: center;
            color: #334155;
            font-weight: 600;
        }

        .bank-table td.b-value {
            font-weight: 600;
            color: #0f172a;
        }

        /* Print Media Styles */
        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            .receipt-action-bar, .non-printable {
                display: none !important;
            }

            .receipt-wrapper {
                max-width: 100% !important;
                margin: 0 !important;
            }

            .receipt-card {
                box-shadow: none !important;
                border: 1.5px solid #000000 !important;
                padding: 20px 28px !important;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>

    <!-- ── TOP CONTROLS (NON-PRINTABLE) ── -->
    <div class="receipt-action-bar non-printable">
        <div class="d-flex align-items-center gap-3">
            <a href="javascript:history.back()" class="btn btn-outline-secondary btn-sm" title="Kembali">
                <i class="bx bx-arrow-back me-1"></i>Kembali
            </a>
            <div class="d-flex align-items-center gap-2">
                <label class="small fw-semibold text-muted text-nowrap">Biaya Admin (Rp):</label>
                <input type="number" id="inputAdminFee" class="form-control form-control-sm" style="width: 110px;" value="{{ (int)$receipt['admin_fee'] }}" oninput="updateAdminFee(this.value)">
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-primary btn-sm" onclick="window.print()">
                <i class="bx bx-printer me-1"></i>Cetak Struk
            </button>
            <button type="button" class="btn btn-success btn-sm" onclick="downloadReceiptImage()">
                <i class="bx bx-download me-1"></i>Simpan Gambar
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="copyWhatsappText()" title="Salin Teks Struk">
                <i class="bx bx-copy me-1"></i>Salin Teks WA
            </button>
            <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#editDrawer" title="Edit Data Struk">
                <i class="bx bx-edit me-1"></i>Ubah Teks
            </button>
        </div>
    </div>

    <!-- ── EDIT DRAWER (COLLAPSIBLE) ── -->
    <div class="collapse mb-4 non-printable" id="editDrawer">
        <div class="card card-body max-w-receipt mx-auto shadow-sm" style="max-width: 820px;">
            <h6 class="fw-bold mb-3"><i class="bx bx-edit me-1"></i>Koreksi Data Teks Struk (Hanya untuk Tampilan Cetak Ini)</h6>
            <div class="row g-2">
                <div class="col-6 col-md-4">
                    <label class="small text-muted mb-1">Nama Pelanggan:</label>
                    <input type="text" class="form-control form-control-sm" id="edit_nama" value="{{ $receipt['nama'] }}" oninput="document.querySelectorAll('.val-nama').forEach(el => el.innerText = this.value)">
                </div>
                <div class="col-6 col-md-4">
                    <label class="small text-muted mb-1">ID Pel / No Meter / No Rek:</label>
                    <input type="text" class="form-control form-control-sm" id="edit_tujuan" value="{{ $receipt['tujuan'] }}" oninput="document.querySelectorAll('.val-tujuan').forEach(el => el.innerText = this.value)">
                </div>
                <div class="col-6 col-md-4">
                    <label class="small text-muted mb-1">Tarif / Daya:</label>
                    <input type="text" class="form-control form-control-sm" id="edit_tarif" value="{{ $receipt['tarif_daya'] }}" oninput="document.querySelectorAll('.val-tarif').forEach(el => el.innerText = this.value)">
                </div>
                @if($receipt['category'] === 'PRABAYAR')
                <div class="col-6 col-md-4">
                    <label class="small text-muted mb-1">Jumlah KWH:</label>
                    <input type="text" class="form-control form-control-sm" id="edit_kwh" value="{{ $receipt['jumlah_kwh'] }}" oninput="document.querySelectorAll('.val-kwh').forEach(el => el.innerText = this.value)">
                </div>
                <div class="col-12 col-md-8">
                    <label class="small text-muted mb-1">Nomor Token (20 Digit):</label>
                    <input type="text" class="form-control form-control-sm font-monospace fw-bold" id="edit_token" value="{{ $receipt['token_stroom'] }}" oninput="document.querySelectorAll('.val-token').forEach(el => el.innerText = this.value)">
                </div>
                @endif
                @if($receipt['category'] === 'PASCA BAYAR')
                <div class="col-6 col-md-4">
                    <label class="small text-muted mb-1">Stand Meter:</label>
                    <input type="text" class="form-control form-control-sm" id="edit_stand" value="{{ $receipt['stand_meter'] }}" oninput="document.querySelectorAll('.val-stand').forEach(el => el.innerText = this.value)">
                </div>
                <div class="col-6 col-md-4">
                    <label class="small text-muted mb-1">Bulan / Tahun:</label>
                    <input type="text" class="form-control form-control-sm" id="edit_blth" value="{{ $receipt['bl_th'] }}" oninput="document.querySelectorAll('.val-blth').forEach(el => el.innerText = this.value)">
                </div>
                <div class="col-12 col-md-4">
                    <label class="small text-muted mb-1">Kode REFF:</label>
                    <input type="text" class="form-control form-control-sm" id="edit_reff" value="{{ $receipt['reff'] }}" oninput="document.querySelectorAll('.val-reff').forEach(el => el.innerText = this.value)">
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- ── RECEIPT CANVAS TO PRINT / CAPTURE ── -->
    <div class="receipt-wrapper">
        <div class="receipt-card" id="receiptCaptureArea">

            {{-- ══════════════════════════════════════════════════════════
                 KATEGORI 1: PASCA BAYAR (CONTOH: STRUK PASCABAYAR.jpeg)
                 ══════════════════════════════════════════════════════════ --}}
            @if($receipt['category'] === 'PASCA BAYAR')
                <div class="pln-receipt">
                    <!-- Header -->
                    <div class="pln-header">
                        <div class="pln-header-info">
                            <div class="pln-header-row">
                                <span class="pln-header-lbl">TGL Bayar</span>
                                <span class="pln-header-sep">:</span>
                                <span class="pln-header-val">{{ $receipt['tgl_bayar'] }}</span>
                            </div>
                        </div>
                        <div class="pln-logo-box">
                            <img src="{{ asset($receipt['logo_url']) }}" alt="PLN Logo">
                        </div>
                    </div>

                    <!-- Title -->
                    <div class="pln-title">
                        STRUK PEMBAYARAN LISTRIK PASCABAYAR
                    </div>

                    <!-- 2 Columns Grid -->
                    <div class="pln-grid">
                        <!-- Left Column -->
                        <div class="pln-col">
                            <div class="pln-row">
                                <span class="pln-lbl">ID PEL</span>
                                <span class="pln-sep">:</span>
                                <span class="pln-val val-tujuan">{{ $receipt['id_pel'] }}</span>
                            </div>
                            <div class="pln-row">
                                <span class="pln-lbl">NAMA</span>
                                <span class="pln-sep">:</span>
                                <span class="pln-val val-nama">{{ $receipt['nama'] }}</span>
                            </div>
                            <div class="pln-row">
                                <span class="pln-lbl">TARIF/DAYA</span>
                                <span class="pln-sep">:</span>
                                <span class="pln-val val-tarif">{{ $receipt['tarif_daya'] }}</span>
                            </div>
                            <div class="pln-row">
                                <span class="pln-lbl">BL/TH</span>
                                <span class="pln-sep">:</span>
                                <span class="pln-val val-blth">{{ $receipt['bl_th'] }}</span>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="pln-col">
                            <div class="pln-row">
                                <span class="pln-lbl-right">STAND METER</span>
                                <span class="pln-sep">:</span>
                                <span class="pln-val val-stand">{{ $receipt['stand_meter'] }}</span>
                            </div>
                            <div class="pln-row">
                                <span class="pln-lbl-right">TAGIHAN</span>
                                <span class="pln-sep">:</span>
                                <span class="pln-val">
                                    <span class="pln-money">
                                        <span class="pln-money-curr">Rp</span>
                                        <span class="pln-money-val val-tagihan-fmt">{{ number_format($receipt['tagihan_pasca'], 0, ',', '.') }}, 00</span>
                                    </span>
                                </span>
                            </div>
                            <div class="pln-row">
                                <span class="pln-lbl-right">ADMIN BANK</span>
                                <span class="pln-sep">:</span>
                                <span class="pln-val">
                                    <span class="pln-money">
                                        <span class="pln-money-curr">Rp</span>
                                        <span class="pln-money-val val-admin-fmt">{{ number_format($receipt['admin_fee'], 0, ',', '.') }}, 00</span>
                                    </span>
                                </span>
                            </div>
                            <div class="pln-row">
                                <span class="pln-lbl-right">TOTAL BAYAR</span>
                                <span class="pln-sep">:</span>
                                <span class="pln-val">
                                    <span class="pln-money">
                                        <span class="pln-money-curr">Rp</span>
                                        <span class="pln-money-val val-total-fmt">{{ number_format($receipt['total_bayar_pasca'], 0, ',', '.') }}, 00</span>
                                    </span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Highlight Box REFF -->
                    <div class="pln-highlight-box">
                        <div class="pln-highlight-lbl">REFF:</div>
                        <div class="pln-highlight-code val-reff">{{ $receipt['reff'] }}</div>
                    </div>

                    <!-- Footer -->
                    <div class="pln-footer">
                        <div class="pln-footer-title">Informasi Hubungi</div>
                        <div>• Call Center 123 &nbsp;&nbsp;&nbsp;• Mobile PLN &nbsp;&nbsp;&nbsp;• PLN Terdekat</div>
                    </div>
                </div>

            {{-- ══════════════════════════════════════════════════════════
                 KATEGORI 2: PRABAYAR (CONTOH: STRUK PRABAYAR.jpeg)
                 ══════════════════════════════════════════════════════════ --}}
            @elseif($receipt['category'] === 'PRABAYAR')
                <div class="pln-receipt">
                    <!-- Header -->
                    <div class="pln-header">
                        <div class="pln-header-info">
                            <div class="pln-header-row">
                                <span class="pln-header-lbl">TGL BELI</span>
                                <span class="pln-header-sep">:</span>
                                <span class="pln-header-val">{{ $receipt['tgl_bayar'] }}</span>
                            </div>
                            <div class="pln-header-row">
                                <span class="pln-header-lbl">BANK</span>
                                <span class="pln-header-sep">:</span>
                                <span class="pln-header-val">{{ $receipt['rule']['bank_name'] ?? 'PLN PAYMENT POINT' }}</span>
                            </div>
                        </div>
                        <div class="pln-logo-box">
                            <img src="{{ asset($receipt['logo_url']) }}" alt="PLN Logo">
                        </div>
                    </div>

                    <!-- Title -->
                    <div class="pln-title">
                        STRUK PEMBELIAN LISTRIK PRABAYAR
                    </div>

                    <!-- 2 Columns Grid -->
                    <div class="pln-grid">
                        <!-- Left Column -->
                        <div class="pln-col">
                            <div class="pln-row">
                                <span class="pln-lbl">NO METER</span>
                                <span class="pln-sep">:</span>
                                <span class="pln-val val-tujuan">{{ $receipt['no_meter'] }}</span>
                            </div>
                            <div class="pln-row">
                                <span class="pln-lbl">ID PEL</span>
                                <span class="pln-sep">:</span>
                                <span class="pln-val val-tujuan">{{ $receipt['id_pel'] }}</span>
                            </div>
                            <div class="pln-row">
                                <span class="pln-lbl">NAMA</span>
                                <span class="pln-sep">:</span>
                                <span class="pln-val val-nama">{{ $receipt['nama'] }}</span>
                            </div>
                            <div class="pln-row">
                                <span class="pln-lbl">TARIF/DAYA</span>
                                <span class="pln-sep">:</span>
                                <span class="pln-val val-tarif">{{ $receipt['tarif_daya'] }}</span>
                            </div>
                            <div class="pln-row" style="margin-top: 14px;">
                                <span class="pln-lbl">RP STROOM/TOKEN</span>
                                <span class="pln-sep">:</span>
                                <span class="pln-val">RP {{ number_format($receipt['stroom_token'], 0, ',', '.') }}, 00</span>
                            </div>
                            <div class="pln-row">
                                <span class="pln-lbl">RP BAYAR</span>
                                <span class="pln-sep">:</span>
                                <span class="pln-val">RP {{ number_format($receipt['total_bayar_pra'], 0, ',', '.') }}, 00</span>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="pln-col">
                            <div class="pln-row">
                                <span class="pln-lbl-right">ADMIN BANK</span>
                                <span class="pln-sep">:</span>
                                <span class="pln-val">RP <span class="val-admin-fmt">{{ number_format($receipt['admin_fee'], 0, ',', '.') }}, 00</span></span>
                            </div>
                            <div class="pln-row">
                                <span class="pln-lbl-right">MATERAI</span>
                                <span class="pln-sep">:</span>
                                <span class="pln-val">RP {{ number_format($receipt['materai'] ?? 0, 0, ',', '.') }}, 00</span>
                            </div>
                            <div class="pln-row">
                                <span class="pln-lbl-right">PPN</span>
                                <span class="pln-sep">:</span>
                                <span class="pln-val">RP {{ number_format($receipt['ppn'] ?? 0, 0, ',', '.') }}, 00</span>
                            </div>
                            <div class="pln-row">
                                <span class="pln-lbl-right">PPJ</span>
                                <span class="pln-sep">:</span>
                                <span class="pln-val">RP {{ number_format($receipt['ppj'] ?? 0, 0, ',', '.') }}, 00</span>
                            </div>
                            <div class="pln-row">
                                <span class="pln-lbl-right">ANGSURAN</span>
                                <span class="pln-sep">:</span>
                                <span class="pln-val">RP {{ number_format($receipt['angsuran'] ?? 0, 0, ',', '.') }}, 00</span>
                            </div>
                            <div class="pln-row" style="margin-top: 14px;">
                                <span class="pln-lbl-right">JUMLAH KWH</span>
                                <span class="pln-sep">:</span>
                                <span class="pln-val val-kwh">{{ $receipt['jumlah_kwh'] }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Highlight Box STROOM / TOKEN 20 DIGIT -->
                    <div class="pln-highlight-box">
                        <div class="pln-highlight-lbl">STROOM / TOKEN</div>
                        <div class="token-large val-token">{{ $receipt['token_stroom'] }}</div>
                    </div>

                    <!-- Footer -->
                    <div class="pln-footer">
                        <div class="pln-footer-title">Informasi Hubungi</div>
                        <div>• Call Center 123 &nbsp;&nbsp;&nbsp;• Mobile PLN &nbsp;&nbsp;&nbsp;• PLN Terdekat</div>
                    </div>
                </div>

            {{-- ══════════════════════════════════════════════════════════
                 KATEGORI 3: BANK (CONTOH: STRUK BANK.jpeg - BCA / BANK LAIN)
                 ══════════════════════════════════════════════════════════ --}}
            @elseif($receipt['category'] === 'BANK')
                <div class="bank-receipt">
                    <!-- Top Title & Bank Logo -->
                    <div class="bank-top-header">
                        <div class="bank-main-title">Transaction Receipt</div>
                        <div class="bank-logo">
                            <img src="{{ asset($receipt['logo_url']) }}" alt="{{ $receipt['bank_name'] }}">
                        </div>
                    </div>

                    <!-- Section 1 Header Bar -->
                    <div class="bank-section-bar">
                        <span>In-House Transfer Result</span>
                        <span>{{ $receipt['bank_name'] }}</span>
                    </div>

                    <!-- Section 1 Table Details -->
                    <table class="bank-table">
                        <tbody>
                            <tr>
                                <td class="b-label">Amount</td>
                                <td class="b-sep">:</td>
                                <td class="b-value">IDR <span class="val-amount-fmt">{{ number_format($receipt['amount'], 0, ',', '.') }}</span></td>
                            </tr>
                            <tr>
                                <td class="b-label">Remark</td>
                                <td class="b-sep">:</td>
                                <td class="b-value">{{ $receipt['remark'] }}</td>
                            </tr>
                            <tr>
                                <td class="b-label">Remitter Reference No.</td>
                                <td class="b-sep">:</td>
                                <td class="b-value">{{ $receipt['remitter_ref'] }}</td>
                            </tr>
                            <tr>
                                <td class="b-label">Completed Time</td>
                                <td class="b-sep">:</td>
                                <td class="b-value">{{ $receipt['tgl_bank_format'] }}</td>
                            </tr>
                            <tr>
                                <td class="b-label">Beneficiary Reference No.</td>
                                <td class="b-sep">:</td>
                                <td class="b-value">{{ $receipt['beneficiary_ref'] }}</td>
                            </tr>
                            <tr>
                                <td class="b-label">Transaction ID</td>
                                <td class="b-sep">:</td>
                                <td class="b-value">{{ $receipt['trx_id_long'] }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Section 2: Beneficiary Information -->
                    <div class="bank-section-bar">
                        <span>Beneficiary Information</span>
                        <span></span>
                    </div>

                    <table class="bank-table">
                        <tbody>
                            <tr>
                                <td class="b-label">Account Number</td>
                                <td class="b-sep">:</td>
                                <td class="b-value val-tujuan">{{ $receipt['account_number'] }}</td>
                            </tr>
                            <tr>
                                <td class="b-label">Account Name</td>
                                <td class="b-sep">:</td>
                                <td class="b-value val-nama">{{ $receipt['account_name'] }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Section 3: Request Information -->
                    <div class="bank-section-bar">
                        <span>Request Information</span>
                        <span></span>
                    </div>

                    <table class="bank-table">
                        <tbody>
                            <tr>
                                <td class="b-label">Request Time</td>
                                <td class="b-sep">:</td>
                                <td class="b-value">{{ $receipt['request_time'] }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            {{-- ══════════════════════════════════════════════════════════
                 KATEGORI 4: UMUM / PPOB STANDAR
                 ══════════════════════════════════════════════════════════ --}}
            @else
                <div class="bank-receipt">
                    <div class="bank-top-header">
                        <div>
                            <div class="bank-main-title">STRUK PEMBAYARAN</div>
                            <div class="small text-muted">{{ $receipt['store_title'] }}</div>
                        </div>
                        <div class="bank-logo">
                            <img src="{{ asset($receipt['logo_url']) }}" alt="Logo">
                        </div>
                    </div>

                    <div class="bank-section-bar">
                        <span>Rincian Transaksi</span>
                        <span>{{ $receipt['status'] }}</span>
                    </div>

                    <table class="bank-table">
                        <tbody>
                            <tr>
                                <td class="b-label">ID Transaksi</td>
                                <td class="b-sep">:</td>
                                <td class="b-value font-monospace">{{ $receipt['trxid'] }}</td>
                            </tr>
                            <tr>
                                <td class="b-label">Waktu Transaksi</td>
                                <td class="b-sep">:</td>
                                <td class="b-value">{{ $receipt['tgl_bayar'] }}</td>
                            </tr>
                            <tr>
                                <td class="b-label">Produk / Layanan</td>
                                <td class="b-sep">:</td>
                                <td class="b-value">{{ $receipt['nama_produk'] }}</td>
                            </tr>
                            <tr>
                                <td class="b-label">Nomor Tujuan / Akun</td>
                                <td class="b-sep">:</td>
                                <td class="b-value font-monospace val-tujuan">{{ $receipt['tujuan'] }}</td>
                            </tr>
                            @if(!empty($receipt['nama']))
                            <tr>
                                <td class="b-label">Nama Pelanggan</td>
                                <td class="b-sep">:</td>
                                <td class="b-value val-nama">{{ $receipt['nama'] }}</td>
                            </tr>
                            @endif
                            <tr>
                                <td class="b-label">Nominal / Harga</td>
                                <td class="b-sep">:</td>
                                <td class="b-value">Rp {{ number_format($receipt['stroom_token'], 0, ',', '.') }},00</td>
                            </tr>
                            <tr>
                                <td class="b-label">Biaya Layanan / Admin</td>
                                <td class="b-sep">:</td>
                                <td class="b-value">Rp <span class="val-admin-fmt">{{ number_format($receipt['admin_fee'], 0, ',', '.') }}</span>,00</td>
                            </tr>
                            <tr style="background-color: #dbeafe !important; font-weight: 700;">
                                <td class="b-label">TOTAL PEMBAYARAN</td>
                                <td class="b-sep">:</td>
                                <td class="b-value" style="color: #1e3a8a; font-size: 15px;">Rp <span class="val-total-fmt">{{ number_format($receipt['total_bayar_pra'], 0, ',', '.') }}</span>,00</td>
                            </tr>
                            @if(!empty($receipt['sn']))
                            <tr>
                                <td class="b-label">SN / Serial Number</td>
                                <td class="b-sep">:</td>
                                <td class="b-value font-monospace" style="word-break: break-all;">{{ $receipt['sn'] }}</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>

                    <div class="text-center text-muted small mt-3 pt-2 border-top">
                        {{ $receipt['footer_info'] }}
                    </div>
                </div>
            @endif

        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Global variables for calculations
        const baseNominal = {{ (float)($receipt['category'] === 'PASCA BAYAR' ? $receipt['tagihan_pasca'] : ($receipt['category'] === 'BANK' ? $receipt['amount'] : $receipt['stroom_token'])) }};
        const category = "{{ $receipt['category'] }}";

        function updateAdminFee(newAdminVal) {
            const admin = parseFloat(newAdminVal) || 0;
            const total = baseNominal + admin;

            const formatRupiah = (val) => new Intl.NumberFormat('id-ID').format(val);

            // Update admin fee labels
            document.querySelectorAll('.val-admin-fmt').forEach(el => el.innerText = formatRupiah(admin));

            // Update total labels
            document.querySelectorAll('.val-total-fmt').forEach(el => el.innerText = formatRupiah(total));
        }

        // Download receipt as image via html2canvas
        function downloadReceiptImage() {
            const area = document.getElementById('receiptCaptureArea');
            if (!area) return;

            const btn = event.currentTarget;
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Memproses...';
            btn.disabled = true;

            html2canvas(area, {
                scale: 2,
                useCORS: true,
                backgroundColor: '#ffffff'
            }).then(canvas => {
                const link = document.createElement('a');
                link.download = 'struk-{{ $receipt['trxid'] }}.jpg';
                link.href = canvas.toDataURL('image/jpeg', 0.95);
                link.click();

                btn.innerHTML = originalHtml;
                btn.disabled = false;
            }).catch(err => {
                alert('Gagal menghasilkan gambar struk.');
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            });
        }

        // Copy text formatted for WhatsApp
        function copyWhatsappText() {
            const tujuan = document.querySelector('.val-tujuan') ? document.querySelector('.val-tujuan').innerText : '{{ $receipt['tujuan'] }}';
            const nama = document.querySelector('.val-nama') ? document.querySelector('.val-nama').innerText : '{{ $receipt['nama'] }}';
            const admin = document.getElementById('inputAdminFee') ? parseFloat(document.getElementById('inputAdminFee').value) || 0 : {{ $receipt['admin_fee'] }};
            const total = baseNominal + admin;
            const formatRupiah = (val) => new Intl.NumberFormat('id-ID').format(val);

            let text = '';
            if (category === 'PRABAYAR') {
                const token = document.querySelector('.val-token') ? document.querySelector('.val-token').innerText : '{{ $receipt['token_stroom'] }}';
                const kwh = document.querySelector('.val-kwh') ? document.querySelector('.val-kwh').innerText : '{{ $receipt['jumlah_kwh'] }}';
                text = `*STRUK PEMBELIAN LISTRIK PRABAYAR*\n` +
                       `Tanggal: {{ $receipt['tgl_bayar'] }}\n` +
                       `----------------------------------------\n` +
                       `No Meter: ${tujuan}\n` +
                       `Nama: ${nama}\n` +
                       `Tarif/Daya: {{ $receipt['tarif_daya'] }}\n` +
                       `Jumlah KWH: ${kwh}\n` +
                       `Admin Bank: Rp ${formatRupiah(admin)}\n` +
                       `Total Bayar: Rp ${formatRupiah(total)}\n` +
                       `----------------------------------------\n` +
                       `*TOKEN / STROOM:*\n` +
                       `*${token}*\n` +
                       `----------------------------------------\n` +
                       `Terima kasih telah bertransaksi.`;
            } else if (category === 'PASCA BAYAR') {
                text = `*STRUK PEMBAYARAN LISTRIK PASCABAYAR*\n` +
                       `Tanggal: {{ $receipt['tgl_bayar'] }}\n` +
                       `----------------------------------------\n` +
                       `ID Pel: ${tujuan}\n` +
                       `Nama: ${nama}\n` +
                       `Tarif/Daya: {{ $receipt['tarif_daya'] }}\n` +
                       `Tagihan: Rp ${formatRupiah(baseNominal)}\n` +
                       `Admin: Rp ${formatRupiah(admin)}\n` +
                       `*Total Bayar: Rp ${formatRupiah(total)}*\n` +
                       `Reff: {{ $receipt['reff'] }}\n` +
                       `----------------------------------------\n` +
                       `Terima kasih.`;
            } else if (category === 'BANK') {
                text = `*BUKTI TRANSFER BANK - {{ $receipt['bank_name'] }}*\n` +
                       `Waktu: {{ $receipt['tgl_bank_format'] }}\n` +
                       `----------------------------------------\n` +
                       `No Rekening: ${tujuan}\n` +
                       `Penerima: ${nama}\n` +
                       `Nominal: Rp ${formatRupiah(baseNominal)}\n` +
                       `Keterangan: {{ $receipt['remark'] }}\n` +
                       `Ref No: {{ $receipt['remitter_ref'] }}\n` +
                       `Trx ID: {{ $receipt['trx_id_long'] }}\n` +
                       `Status: BERHASIL\n` +
                       `----------------------------------------`;
            } else {
                text = `*STRUK PEMBAYARAN*\n` +
                       `Waktu: {{ $receipt['tgl_bayar'] }}\n` +
                       `Produk: {{ $receipt['nama_produk'] }}\n` +
                       `Tujuan: ${tujuan}\n` +
                       `Total: Rp ${formatRupiah(total)}\n` +
                       `SN: {{ $receipt['sn'] }}\n` +
                       `Terima kasih.`;
            }

            navigator.clipboard.writeText(text).then(() => {
                alert('Teks struk berhasil disalin ke clipboard! Siap dipaste ke WhatsApp.');
            }).catch(() => {
                prompt('Salin teks di bawah ini:', text);
            });
        }
    </script>
</body>
</html>
