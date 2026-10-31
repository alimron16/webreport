<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Struk Pembayaran Resmi</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            padding: 20px;
            color: #0f172a;
        }
        .lookup-card {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 16px;
            max-width: 480px;
            width: 100%;
            padding: 36px 32px;
            box-shadow: 0 12px 32px rgba(15, 23, 42, 0.08);
        }
        .icon-circle {
            width: 60px;
            height: 60px;
            border-radius: 14px;
            background: rgba(11, 111, 182, 0.1);
            color: #0B6FB6;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            margin-bottom: 16px;
        }
        .btn-print-lookup {
            background: linear-gradient(135deg, #0B6FB6 0%, #1C8CFB 100%);
            border: none;
            color: #ffffff;
            font-weight: 600;
            padding: 12px 20px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(11, 111, 182, 0.25);
        }
        .btn-print-lookup:hover {
            opacity: 0.95;
            color: #ffffff;
        }
    </style>
</head>
<body>
    <div class="lookup-card text-center">
        <div class="icon-circle">
            <i class="bx bx-receipt"></i>
        </div>
        <h5 class="fw-bold mb-1">Layanan Cetak Struk Digital</h5>
        <div class="text-muted small mb-4">{{ $storeTitle ?? 'PAYMENT POINT ONLINE BANKING' }}</div>

        @if(!empty($errorMsg))
            <div class="alert alert-danger py-2 small text-start mb-3">
                <i class="bx bx-error-circle me-1"></i>{{ $errorMsg }}
            </div>
        @endif

        <form method="GET" action="/" class="text-start">
            <div class="mb-3">
                <label for="trxid" class="form-label small fw-semibold text-secondary">ID Transaksi / Nomor Referensi</label>
                <input type="text" name="trxid" id="trxid" class="form-control form-control-lg font-monospace text-center" placeholder="Contoh: 9823741" required autocomplete="off">
                <div class="form-text text-center" style="font-size: 11.5px;">
                    Masukkan ID Transaksi yang tertera pada bukti pembelian untuk mengunduh atau mencetak struk.
                </div>
            </div>
            <button type="submit" class="btn btn-print-lookup w-100">
                <i class="bx bx-printer me-1"></i>Lihat & Cetak Struk
            </button>
        </form>

        @if(!empty($footerInfo))
            <div class="mt-4 pt-3 border-top text-muted" style="font-size: 11.5px;">
                {{ $footerInfo }}
            </div>
        @endif
    </div>
</body>
</html>

