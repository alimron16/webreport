<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - OtomaX Webreport</title>
    
    <!-- Fonts: Inter & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --bg-primary: #f3f4f6; /* Slate/Gray-100 like Laravel Breeze default background */
            --bg-card: #ffffff;
            --accent-primary: #2563eb; /* Blue-600 */
            --accent-hover: #1d4ed8;
            --text-primary: #1f2937; /* Gray-800 */
            --text-secondary: #4b5563; /* Gray-600 */
            --text-muted: #9ca3af; /* Gray-400 */
            --danger: #e11d48; /* Red-600 */
            --success: #059669; /* Green-600 */
            --border-color: #e5e7eb; /* Gray-200 */
            --radius: 8px;
            --transition: all 0.15s ease-in-out;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        .auth-wrapper {
            width: 100%;
            max-width: 420px;
        }

        .logo-wrap {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 2rem;
        }

        .logo-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: #e11d48; /* Accent Red */
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            font-size: 1.5rem;
            color: #fff;
            box-shadow: 0 4px 12px rgba(225, 29, 72, 0.25);
            margin-bottom: 0.75rem;
        }

        .logo-text {
            font-family: 'Outfit', sans-serif;
            font-size: 1.5rem;
            font-weight: 800;
            color: #111827;
            letter-spacing: -0.5px;
        }

        .logo-desc {
            font-size: 13px;
            color: var(--text-secondary);
            margin-top: 2px;
        }

        .auth-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: var(--text-primary);
            margin-bottom: 0.4rem;
        }

        .form-control {
            width: 100%;
            background: #ffffff;
            border: 1px solid #d1d5db; /* Gray-300 */
            border-radius: var(--radius);
            padding: 0.65rem 0.85rem;
            color: var(--text-primary);
            outline: none;
            transition: var(--transition);
            font-size: 14px;
            font-family: inherit;
        }

        .form-control:focus {
            border-color: var(--accent-primary);
            box-shadow: 0 0 0 1px var(--accent-primary);
        }

        .btn-submit {
            width: 100%;
            background: var(--accent-primary);
            color: #fff;
            border: none;
            padding: 0.65rem;
            border-radius: var(--radius);
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: var(--transition);
            margin-top: 0.5rem;
            font-family: inherit;
        }

        .btn-submit:hover {
            background: var(--accent-hover);
        }

        /* Alerts */
        .alert {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 12px;
            border-radius: var(--radius);
            font-size: 13px;
            margin-bottom: 1.25rem;
            border: 1px solid transparent;
        }

        .alert-danger {
            background: #fef2f2;
            color: #991b1b;
            border-color: #fca5a5;
        }

        .alert-success {
            background: #ecfdf5;
            color: #065f46;
            border-color: #a7f3d0;
        }

        .alert svg {
            flex-shrink: 0;
        }

        .auth-footer {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 13.5px;
            color: var(--text-secondary);
        }

        .auth-footer a {
            color: var(--accent-primary);
            font-weight: 600;
            text-decoration: none;
        }

        .auth-footer a:hover {
            text-decoration: underline;
        }

        .demo-box {
            margin-top: 1.5rem;
            border-top: 1px solid var(--border-color);
            padding-top: 1.25rem;
            font-size: 12px;
            color: var(--text-secondary);
        }

        .demo-title {
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 4px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
    </style>

    @if(\Illuminate\Support\Facades\Cache::get('recaptcha_enabled') && \Illuminate\Support\Facades\Cache::get('recaptcha_site_key'))
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var form = document.getElementById('loginForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    var response = grecaptcha.getResponse();
                    if(response.length == 0) {
                        e.preventDefault();
                        alert("Silakan centang kotak reCAPTCHA (I'm not a robot).");
                    }
                });
            }
        });
    </script>
    @endif
</head>
<body>

    <div class="auth-wrapper">
        
        <!-- Logo -->
        <div class="logo-wrap">
            @if(\Illuminate\Support\Facades\Cache::get('site_logo_url'))
                <img src="{{ \Illuminate\Support\Facades\Cache::get('site_logo_url') }}" alt="Logo" style="height: 48px; max-width: 160px; object-fit: contain; margin-bottom: 0.5rem;">
            @else
                <div class="logo-icon">Ω</div>
            @endif
            <div class="logo-text">{{ \Illuminate\Support\Facades\Cache::get('site_title', 'OtomaX Portal') }}</div>
            <div class="logo-desc">{{ \Illuminate\Support\Facades\Cache::get('site_subtitle', 'Webreport Reseller Network') }}</div>
        </div>

        <!-- Auth Card -->
        <div class="auth-card">
            
            <!-- Notifications -->
            @if(session('error'))
                <div class="alert alert-danger">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <form action="{{ route('webreport.login.post') }}" method="POST" id="loginForm">
                @csrf
                <div class="form-group">
                    <label for="kode_reseller">Kode Reseller / No. HP / Email</label>
                    <input type="text" name="kode_reseller" id="kode_reseller" class="form-control" placeholder="Contoh: CMP001 atau 0812345678" required autofocus>
                </div>

                <div class="form-group">
                    <label for="pin">PIN Keamanan</label>
                    <input type="password" name="pin" id="pin" class="form-control" placeholder="••••••" required>
                </div>

                @if(\Illuminate\Support\Facades\Cache::get('recaptcha_enabled') && \Illuminate\Support\Facades\Cache::get('recaptcha_site_key'))
                <div class="form-group" style="display: flex; justify-content: center; margin-bottom: 20px;">
                    <div class="g-recaptcha" data-sitekey="{{ \Illuminate\Support\Facades\Cache::get('recaptcha_site_key') }}"></div>
                </div>
                @endif

                <button type="submit" class="btn-submit">Masuk</button>
            </form>

            <div class="auth-footer">
                Belum terdaftar? <a href="{{ route('webreport.register') }}">Daftar Reseller Baru</a>
            </div>

        </div>

    </div>

</body>
</html>
