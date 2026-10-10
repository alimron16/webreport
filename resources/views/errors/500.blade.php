<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Terjadi Kendala Sistem</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            color: #334155;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            margin: 0;
        }
        .error-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 2.5rem 2rem;
            max-width: 480px;
            width: 100%;
            text-align: center;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        .error-icon {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background-color: #fef2f2;
            color: #ef4444;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin-bottom: 1.25rem;
        }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="error-icon">
            <i class="bx bx-error-circle"></i>
        </div>
        <h4 class="fw-bold text-dark mb-2">Terjadi Kendala Sistem</h4>
        <p class="text-muted small mb-4">
            {{ $message ?? 'Permintaan Anda sementara tidak dapat diproses. Tim kami telah mencatat peristiwa ini untuk ditangani.' }}
        </p>
        <div class="d-flex justify-content-center gap-2">
            <a href="{{ url('/') }}" class="btn btn-primary px-4 py-2" style="background:#2563eb; border-color:#2563eb; font-weight:600; border-radius:8px;">
                <i class="bx bx-home-alt me-1"></i> Kembali ke Beranda
            </a>
        </div>
    </div>
</body>
</html>
