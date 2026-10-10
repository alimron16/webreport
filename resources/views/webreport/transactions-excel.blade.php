<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 12px;
            color: #333333;
        }
        th {
            background-color: #10b981; /* Professional Green */
            color: #ffffff;
            font-weight: bold;
            padding: 10px 8px;
            border: 1px solid #059669;
            text-align: left;
            text-transform: uppercase;
            font-size: 11px;
        }
        td {
            border: 1px solid #d1d5db;
            padding: 8px;
            text-align: left;
            vertical-align: middle;
        }
        .header {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #111827;
        }
        /* Zebra striping for rows */
        tr:nth-child(even) td {
            background-color: #f9fafb;
        }
    </style>
</head>
<body>
    <div class="header">
        Laporan Riwayat Transaksi - {{ strtoupper($active_reseller_code ?? 'Reseller') }}
    </div>
    <table>
        <thead>
            <tr>
                <th>Trx ID</th>
                <th>Tgl Entri</th>
                <th>Tgl Status</th>
                <th>Produk</th>
                <th>No. Tujuan</th>
                <th>Status</th>
                <th>SN</th>
                <th>Harga</th>
                <th>Saldo Awal</th>
                <th>Saldo Akhir</th>
                <th>Ref ID</th>
                <th>Keterangan</th>
                <th>Kode Reseller</th>
                <th>Nama Reseller</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $trx)
                <tr>
                    <td>{{ $trx['trxid'] }}</td>
                    <td style="mso-number-format:'\@';">{{ !empty($trx['tgl_entri']) ? \Carbon\Carbon::parse($trx['tgl_entri'])->format('d/m/Y H:i:s') : '-' }}</td>
                    <td style="mso-number-format:'\@';">{{ !empty($trx['tgl_status']) ? \Carbon\Carbon::parse($trx['tgl_status'])->format('d/m/Y H:i:s') : '-' }}</td>
                    <td>{{ $trx['kode_produk'] ?? '-' }}</td>
                    <td>'{{ $trx['tujuan'] ?? '-' }}</td>
                    <td>{{ $trx['status'] ?? '-' }}</td>
                    <td>'{{ $trx['sn'] ?? '-' }}</td>
                    <td>{{ (int)($trx['harga'] ?? 0) }}</td>
                    <td>{{ (int)($trx['saldo_awal'] ?? 0) }}</td>
                    <td>{{ (int)($trx['saldo_akhir'] ?? 0) }}</td>
                    <td>{{ $trx['ref_id'] ?? '-' }}</td>
                    <td>{{ $trx['keterangan'] ?? '-' }}</td>
                    <td>{{ $trx['kode_reseller'] ?? $active_reseller_code ?? '-' }}</td>
                    <td>{{ $reseller_name ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
