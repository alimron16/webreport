<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Formulir Pendaftaran Reseller OtomaX</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 13px;
            color: #333;
            line-height: 1.6;
            margin: 0;
            padding: 30px;
        }

        .header-table {
            width: 100%;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }

        .logo-area {
            font-size: 28px;
            font-weight: bold;
            color: #7c3aed;
        }

        .title-area {
            text-align: right;
            font-size: 14px;
            color: #666;
        }

        .title-main {
            font-size: 20px;
            font-weight: bold;
            color: #111;
            margin-bottom: 5px;
        }

        h2 {
            font-size: 14px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
            margin-top: 25px;
            margin-bottom: 15px;
            color: #111;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .content-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }

        .content-table td {
            padding: 10px;
            border-bottom: 1px solid #f0f0f0;
            vertical-align: top;
        }

        .content-table td.label {
            width: 30%;
            font-weight: bold;
            color: #555;
        }

        .underline-box {
            display: inline-block;
            width: 100%;
            border-bottom: 1px dashed #777;
            height: 18px;
        }

        .terms {
            background-color: #fafafa;
            border: 1px solid #e0e0e0;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 30px;
            font-size: 11.5px;
            color: #555;
        }

        .terms ol {
            margin: 0;
            padding-left: 20px;
        }

        .terms li {
            margin-bottom: 6px;
        }

        .signature-table {
            width: 100%;
            margin-top: 40px;
        }

        .signature-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
        }

        .signature-line {
            margin-top: 70px;
            width: 200px;
            border-bottom: 1px solid #333;
            display: inline-block;
        }

        .print-btn-container {
            margin-bottom: 25px;
            text-align: center;
        }

        .btn-print {
            background-color: #7c3aed;
            color: white;
            border: none;
            padding: 8px 18px;
            font-size: 13px;
            font-weight: bold;
            border-radius: 4px;
            cursor: pointer;
        }

        @media print {
            .print-btn-container {
                display: none;
            }
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body>

    <div class="print-btn-container">
        <button class="btn-print" onclick="window.print()">Cetak Formulir Ini (Ctrl + P)</button>
        <p style="margin-top: 5px; font-size: 11px; color: #666;">Cetak formulir ini, isi & tanda tangani secara fisik, lalu upload kembali fotonya di portal pendaftaran.</p>
    </div>

    <table class="header-table">
        <tr>
            <td class="logo-area">Ω OtomaX</td>
            <td class="title-area">
                <div class="title-main">FORMULIR PENDAFTARAN RESELLER</div>
                <div>OtomaX Webreport Network</div>
            </td>
        </tr>
    </table>

    <p style="margin-bottom: 25px;">Harap isi formulir pendaftaran di bawah ini secara lengkap dengan huruf cetak/kapital untuk menjadi reseller jaringan transaksi pulsa kami.</p>

    <h2>Data Calon Reseller</h2>
    <table class="content-table">
        <tr>
            <td class="label">Nama Outlet / Reseller</td>
            <td><span class="underline-box"></span></td>
        </tr>
        <tr>
            <td class="label">Nama Lengkap Pemilik</td>
            <td><span class="underline-box"></span></td>
        </tr>
        <tr>
            <td class="label">No. HP / WhatsApp Utama</td>
            <td><span class="underline-box"></span></td>
        </tr>
        <tr>
            <td class="label">Alamat Email</td>
            <td><span class="underline-box"></span></td>
        </tr>
        <tr>
            <td class="label">Alamat Tempat Tinggal</td>
            <td><span class="underline-box" style="height: 40px;"></span></td>
        </tr>
        <tr>
            <td class="label">Nomor KTP (NIK)</td>
            <td><span class="underline-box"></span></td>
        </tr>
        <tr>
            <td class="label">Nomor NPWP</td>
            <td><span class="underline-box"></span></td>
        </tr>
        <tr>
            <td class="label">Alamat IP (Optional)</td>
            <td><span class="underline-box"></span></td>
        </tr>
        <tr>
            <td class="label">Password IP (Optional)</td>
            <td><span class="underline-box"></span></td>
        </tr>
        <tr>
            <td class="label">URL Callback / Report (Optional)</td>
            <td><span class="underline-box"></span></td>
        </tr>
        <tr>
            <td class="label">PIN Keamanan Dipilih (4-8 Angka)</td>
            <td><span class="underline-box" style="width: 150px;"></span> (Tulis PIN Rahasia Anda)</td>
        </tr>
    </table>

    <h2>Syarat & Ketentuan Reseller</h2>
    <div class="terms">
        <ol>
            <li>Reseller bertanggung jawab penuh atas keamanan PIN masing-masing dan kerahasiaan akun portal reseller.</li>
            <li>Reseller bersedia mematuhi seluruh kebijakan transaksi dan batas waktu deposit yang telah ditetapkan oleh server.</li>
            <li>Segala bentuk penyalahgunaan akun webreport untuk tindakan melanggar hukum akan ditindak tegas dan akun akan dinonaktifkan secara sepihak.</li>
            <li>Proses approval pendaftaran membutuhkan waktu maksimal 1x24 jam setelah berkas fisik diupload dan divalidasi oleh admin.</li>
        </ol>
    </div>

    <p>Demikian formulir ini saya isi dengan data yang sebenarnya dan menyatakan setuju dengan seluruh syarat & ketentuan yang berlaku.</p>

    <table class="signature-table">
        <tr>
            <td>
                <div>Mengetahui,</div>
                <div><strong>Admin Server OtomaX</strong></div>
                <div class="signature-line"></div>
                <div style="margin-top: 5px;">( Tanda Tangan & Nama Terang )</div>
            </td>
            <td>
                <div>Pemohon / Calon Reseller,</div>
                <div style="margin-top: 15px; font-size: 11px; color: #777;">Materai 10.000</div>
                <div class="signature-line" style="margin-top: 40px;"></div>
                <div style="margin-top: 5px;">( Tanda Tangan & Nama Terang )</div>
            </td>
        </tr>
    </table>

</body>
</html>
