<!DOCTYPE html>
<html lang="id" class="light-style" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
    <title>Masuk Admin - {{ $portal_site_title ?? \Illuminate\Support\Facades\Cache::get('site_title', 'OtomaX') }} Panel</title>
    @php $adminLoginFavicon = $portal_logo_url ?? \Illuminate\Support\Facades\Cache::get('site_logo_url'); @endphp
    @if($adminLoginFavicon)
    <link rel="icon" type="image/x-icon" href="{{ $adminLoginFavicon }}">
    <link rel="shortcut icon" href="{{ $adminLoginFavicon }}">
    @endif
    
    <!-- Clean Typography: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Icons: Boxicons -->
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --bs-primary: #2563eb;
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
            max-width: 410px;
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
            width: 40px;
            height: 40px;
            border-radius: 8px;
            background: #0f172a;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
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

        /* Seamless Modern Input Group */
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
            border-color: #0f172a;
            box-shadow: 0 0 0 3px rgba(15, 23, 42, 0.12);
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
            font-size: 0.875rem;
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

        .input-group .btn-eye-toggle {
            background-color: transparent;
            border: none;
            color: #64748b;
            padding: 0 0.85rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            transition: color 0.15s ease;
        }

        .input-group .btn-eye-toggle:hover {
            color: #0f172a;
        }

        .input-group .btn-eye-toggle:focus {
            outline: none;
            color: #0f172a;
        }

        .btn-primary {
            background-color: #0f172a;
            border: 1px solid #0f172a;
            color: #ffffff;
            font-weight: 600;
            padding: 0.7rem 1.25rem;
            border-radius: 8px;
            font-size: 0.9rem;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            transition: all 0.15s ease-in-out;
        }

        .btn-primary:hover, .btn-primary:focus {
            background-color: #1e293b;
            border-color: #1e293b;
            color: #ffffff;
        }

        .btn-primary:active {
            transform: scale(0.99);
        }

        /* reCAPTCHA centering and responsiveness */
        .recaptcha-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            margin: 1.25rem 0 0.5rem;
            overflow: hidden;
        }

        .recaptcha-wrapper .g-recaptcha {
            margin: 0 auto;
            display: inline-block;
        }

        @media (max-width: 380px) {
            .recaptcha-wrapper .g-recaptcha {
                transform: scale(0.88);
                transform-origin: center center;
            }
        }
    </style>

    @if(\Illuminate\Support\Facades\Cache::get('recaptcha_enabled') && \Illuminate\Support\Facades\Cache::get('recaptcha_site_key'))
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var form = document.getElementById('adminLoginForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    var response = grecaptcha.getResponse();
                    if(response.length == 0) {
                        e.preventDefault();
                        alert("Silakan verifikasi CAPTCHA terlebih dahulu.");
                    }
                });
            }
        });
    </script>
    @endif
</head>
<body>

    <div class="authentication-wrapper">
        <div class="authentication-inner">
            
            <div class="card">
                <div class="card-body">
                    
                    <!-- App Brand -->
                    <div class="d-flex align-items-center justify-content-center gap-2 mb-3">
                        @if($adminLoginFavicon)
                            <img src="{{ $adminLoginFavicon }}" alt="Logo" style="height: 46px; max-width: 180px; object-fit: contain;">
                        @else
                            <div class="app-brand-logo">
                                <i class="bx bx-shield-quarter fs-4"></i>
                            </div>
                            <span class="fs-4 fw-bold text-dark">{{ $portal_site_title ?? \Illuminate\Support\Facades\Cache::get('site_title', 'OtomaX') }}</span>
                        @endif
                    </div>

                    <h5 class="mb-1 text-center fw-bold text-dark">Admin Control Panel</h5>
                    <p class="mb-4 text-center text-muted small">Login kredensial administrator sistem.</p>

                    <!-- Notifications -->
                    @if(session('error'))
                    <div class="alert alert-danger d-flex align-items-center py-2 px-3 mb-3 small" role="alert">
                        <i class="bx bx-error-circle fs-5 me-2"></i>
                        <div>{{ session('error') }}</div>
                    </div>
                    @endif

                    @if(session('success'))
                    <div class="alert alert-success d-flex align-items-center py-2 px-3 mb-3 small" role="alert">
                        <i class="bx bx-check-circle fs-5 me-2"></i>
                        <div>{{ session('success') }}</div>
                    </div>
                    @endif

                    <!-- Form -->
                    <form action="{{ route('admin.login.post') }}" method="POST" id="adminLoginForm">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="username" class="form-label">Username Admin</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-user"></i></span>
                                <input type="text" name="username" id="username" class="form-control" placeholder="admin" required autofocus value="{{ old('username') }}">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password Admin</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-lock-alt"></i></span>
                                <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required>
                                <button class="btn-eye-toggle" type="button" onclick="togglePassVisibility()" title="Lihat Password" tabindex="-1">
                                    <i class="bx bx-show" id="passEyeIcon"></i>
                                </button>
                            </div>
                        </div>

                        @if(\Illuminate\Support\Facades\Cache::get('recaptcha_enabled') && \Illuminate\Support\Facades\Cache::get('recaptcha_site_key'))
                        <div class="recaptcha-wrapper">
                            <div class="g-recaptcha" data-sitekey="{{ \Illuminate\Support\Facades\Cache::get('recaptcha_site_key') }}"></div>
                        </div>
                        @endif

                        <button type="submit" class="btn btn-primary w-100 py-2.5 mt-3 fw-semibold">
                            Masuk Panel Admin
                        </button>
                    </form>

                    <div class="text-center mt-4 pt-3 border-top">
                        <a href="{{ route('webreport.login') }}" class="text-muted small text-decoration-none">
                            <i class="bx bx-arrow-back me-1"></i> Kembali ke Portal Reseller
                        </a>
                    </div>

                </div>
            </div>

            <!-- Footer Copyright -->
            <p class="text-center text-muted small mt-3 mb-0">
                &copy; {{ date('Y') }} {{ $portal_site_title ?? \Illuminate\Support\Facades\Cache::get('site_title', 'OtomaX') }} Admin. All rights reserved.
            </p>

        </div>
    </div>

    <script>
        function togglePassVisibility() {
            const passInput = document.getElementById('password');
            const eyeIcon = document.getElementById('passEyeIcon');
            if (passInput.type === 'password') {
                passInput.type = 'text';
                eyeIcon.className = 'bx bx-hide';
            } else {
                passInput.type = 'password';
                eyeIcon.className = 'bx bx-show';
            }
        }
    </script>
</body>
</html>
