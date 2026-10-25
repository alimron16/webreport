<!DOCTYPE html>
<html lang="id" class="light-style" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
    <title>Aktivasi Lisensi - Webreport</title>
    <meta name="description" content="Aktivasi lisensi aplikasi Webreport. Masukkan license key untuk mengaktifkan aplikasi.">
    <meta name="robots" content="noindex, nofollow">
    
    <!-- Clean Typography: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    
    <!-- Icons: Boxicons -->
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --bs-primary: #0B6FB6;
            --bs-body-bg: #f8fafc;
            --bs-body-color: #334155;
            --bs-border-color: #e2e8f0;
            --theme-heading: #0f172a;
            --theme-card-border: #e2e8f0;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f8fafc;
            color: #334155;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            margin: 0;
            font-size: 0.875rem;
        }

        .authentication-wrapper {
            display: flex;
            width: 100%;
            align-items: center;
            justify-content: center;
        }

        .authentication-inner {
            max-width: 440px;
            width: 100%;
        }

        .card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
        }

        .card-body {
            padding: 2.25rem;
        }

        .app-brand-logo {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            background: #0B6FB6;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            margin: 0 auto 1rem;
        }

        .form-label {
            font-size: 0.725rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 700;
            color: #475569;
            margin-bottom: 0.45rem;
            display: block;
        }

        .input-group {
            position: relative;
            display: flex;
            align-items: stretch;
            width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background-color: #ffffff;
            transition: all 0.15s ease-in-out;
            overflow: hidden;
        }

        .input-group:focus-within {
            border-color: #0B6FB6;
            box-shadow: 0 0 0 3px rgba(11, 111, 182, 0.15);
        }

        .input-group-text {
            background-color: #ffffff;
            border: none;
            color: #64748b;
            padding: 0.65rem 0.75rem 0.65rem 0.9rem;
            font-size: 1.15rem;
            display: flex;
            align-items: center;
        }

        .input-group .form-control {
            border: none;
            border-radius: 0;
            font-size: 0.9rem;
            color: #0f172a;
            padding: 0.65rem 0.75rem 0.65rem 0.25rem;
            background-color: transparent;
            box-shadow: none !important;
            flex-grow: 1;
        }

        .input-group .form-control:focus {
            outline: none;
            border: none;
            box-shadow: none;
        }

        .btn-primary {
            background: #0B6FB6;
            border-color: #0B6FB6;
            color: #ffffff;
            font-weight: 600;
            padding: 0.7rem 1.25rem;
            border-radius: 8px;
            font-size: 0.9rem;
            box-shadow: 0 2px 4px rgba(11, 111, 182, 0.2);
            transition: all 0.15s ease-in-out;
        }

        .btn-primary:hover, .btn-primary:focus {
            background: #095992;
            border-color: #095992;
            color: #ffffff;
            box-shadow: 0 4px 8px rgba(11, 111, 182, 0.3);
        }

        .btn-primary:active {
            transform: scale(0.99);
        }

        .btn-outline-primary {
            border-color: #0B6FB6;
            color: #0B6FB6;
            font-weight: 600;
            border-radius: 8px;
            padding: 0.7rem 1.25rem;
            font-size: 0.9rem;
        }

        .btn-outline-primary:hover {
            background: #0B6FB6;
            border-color: #0B6FB6;
            color: #ffffff;
        }

        .font-mono {
            font-family: 'JetBrains Mono', monospace;
        }
    </style>
</head>
<body>

    <div class="authentication-wrapper">
        <div class="authentication-inner">
            
            <div class="card" id="license-card">
                <div class="card-body">
                    
                    <!-- Icon / Brand -->
                    <div class="app-brand-logo">
                        <i class="bx bx-shield-quarter fs-2"></i>
                    </div>

                    <h5 class="mb-1 text-center fw-bold text-dark">Aktivasi Lisensi</h5>
                    <p class="mb-3 text-center text-muted small">
                        @if($info['valid'])
                            Aplikasi sudah berlisensi dan siap digunakan.
                        @else
                            Aplikasi terkunci. Masukkan license key dari penyedia untuk mengaktifkan.
                        @endif
                    </p>

                    <!-- Status Pill -->
                    <div class="text-center mb-3">
                        <span class="badge {{ $info['valid'] ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle' }} px-3 py-2 rounded-pill fw-semibold" id="license-status">
                            <i class="bx {{ $info['valid'] ? 'bx-check-circle' : 'bx-x-circle' }} me-1 align-middle"></i>
                            {{ $info['valid'] ? 'Lisensi Aktif' : 'Lisensi Tidak Aktif' }}
                        </span>
                    </div>

                    <!-- Alerts -->
                    @if(session('success'))
                    <div class="alert alert-success d-flex align-items-center py-2 px-3 mb-3 small" role="alert" id="license-success">
                        <i class="bx bx-check-circle fs-5 me-2 flex-shrink-0"></i>
                        <div>{{ session('success') }}</div>
                    </div>
                    @endif

                    @if(session('error'))
                    <div class="alert alert-danger d-flex align-items-center py-2 px-3 mb-3 small" role="alert" id="license-error">
                        <i class="bx bx-error-circle fs-5 me-2 flex-shrink-0"></i>
                        <div>{{ session('error') }}</div>
                    </div>
                    @elseif($errors->any())
                    <div class="alert alert-danger d-flex align-items-center py-2 px-3 mb-3 small" role="alert" id="license-error">
                        <i class="bx bx-error-circle fs-5 me-2 flex-shrink-0"></i>
                        <div>{{ $errors->first() }}</div>
                    </div>
                    @elseif(!$info['valid'] && !empty($info['message']))
                    <div class="alert alert-danger d-flex align-items-center py-2 px-3 mb-3 small" role="alert" id="license-error">
                        <i class="bx bx-error-circle fs-5 me-2 flex-shrink-0"></i>
                        <div>{{ $info['message'] }}</div>
                    </div>
                    @endif

                    <!-- License Details Card -->
                    <div class="p-3 mb-3 rounded-3" style="background-color: #f8fafc; border: 1px solid #e2e8f0;">
                        @if($info['has_key'])
                        <div class="d-flex justify-content-between align-items-center py-1 border-bottom border-light-subtle">
                            <span class="text-muted small fw-medium">License Key</span>
                            <span class="font-mono small fw-semibold text-dark">{{ $info['key_mask'] }}</span>
                        </div>
                        @endif
                        @if($info['valid'] && $info['expiry'])
                        <div class="d-flex justify-content-between align-items-center py-1 border-bottom border-light-subtle">
                            <span class="text-muted small fw-medium">Berlaku Hingga</span>
                            <span class="small fw-semibold text-dark">{{ $info['expiry'] }}</span>
                        </div>
                        @endif
                        <div class="d-flex justify-content-between align-items-center pt-1">
                            <span class="text-muted small fw-medium me-2">HWID</span>
                            <div class="d-flex align-items-center gap-1">
                                <span class="font-mono small fw-bold text-dark" id="hwid-text">{{ $info['hwid'] }}</span>
                                <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 ms-1" id="btn-copy-hwid" title="Salin HWID" style="font-size: 0.75rem;">
                                    <i class="bx bx-copy align-middle"></i> <span id="copy-label">Salin</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Activation Form -->
                    @if(!$info['valid'] || $isAdmin)
                    <form method="POST" action="{{ route('license.activate') }}" id="license-form" autocomplete="off">
                        @csrf
                        <div class="mb-3">
                            <label for="license_key" class="form-label">{{ $info['valid'] ? 'Ganti License Key' : 'License Key' }}</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-key"></i></span>
                                <input type="text" id="license_key" name="license_key" class="form-control font-mono" value="{{ old('license_key') }}" placeholder="XXXX-XXXX-XXXX-XXXX" required spellcheck="false">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2.5 fw-semibold" id="btn-activate">
                            <i class="bx bx-check-shield me-1 align-middle"></i> {{ $info['valid'] ? 'Perbarui Lisensi' : 'Aktifkan Sekarang' }}
                        </button>
                    </form>
                    @endif

                    @if($info['valid'])
                    <div class="mt-2">
                        <a href="{{ url('/') }}" class="btn btn-outline-primary w-100 py-2.5 fw-semibold" id="btn-open-app">
                            <i class="bx bx-home-alt me-1 align-middle"></i> Buka Aplikasi Webreport
                        </a>
                    </div>
                    @endif

                    <!-- Helper Notice -->
                    <p class="text-center text-muted small mt-3 mb-0" style="font-size: 0.775rem;">
                        <i class="bx bx-info-circle align-middle"></i> Berikan HWID di atas ke penyedia aplikasi bila diminta untuk mengikat lisensi ke server ini.
                    </p>

                </div>
            </div>

            <!-- Footer Copyright -->
            <p class="text-center text-muted small mt-3 mb-0">
                &copy; {{ date('Y') }} Webreport OtomaX. All rights reserved.
            </p>

        </div>
    </div>

    <script>
        document.getElementById('btn-copy-hwid').addEventListener('click', function () {
            var btnLabel = document.getElementById('copy-label');
            var hwid = document.getElementById('hwid-text').innerText.trim();
            navigator.clipboard.writeText(hwid).then(function () {
                btnLabel.innerText = 'Tersalin!';
                setTimeout(function () { btnLabel.innerText = 'Salin'; }, 1600);
            });
        });
    </script>
</body>
</html>
