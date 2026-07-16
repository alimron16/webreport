<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Reseller - OtomaX Webreport</title>
    
    <!-- Fonts: Inter & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --bg-primary: #f3f4f6; /* Gray-100 */
            --bg-card: #ffffff;
            --accent-primary: #2563eb; /* Blue-600 */
            --accent-hover: #1d4ed8;
            --text-primary: #1f2937;
            --text-secondary: #4b5563;
            --text-muted: #9ca3af;
            --danger: #e11d48;
            --success: #059669;
            --border-color: #e5e7eb;
            --radius: 8px;
            --transition: all 0.2s ease-in-out;
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
            padding: 2rem 1.5rem;
        }

        .auth-wrapper {
            width: 100%;
            max-width: 580px;
        }

        .logo-wrap {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .logo-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: #e11d48;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            font-size: 1.4rem;
            color: #fff;
            box-shadow: 0 4px 10px rgba(225, 29, 72, 0.2);
            margin-bottom: 0.5rem;
        }

        .logo-text {
            font-family: 'Outfit', sans-serif;
            font-size: 1.35rem;
            font-weight: 800;
            color: #111827;
        }

        .auth-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 2.25rem 2rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        }

        /* Wizard Steps Progress Indicator */
        .steps-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            position: relative;
        }

        .steps-line {
            position: absolute;
            top: 15px;
            left: 0;
            right: 0;
            height: 2px;
            background: #e5e7eb;
            z-index: 1;
        }

        .steps-line-active {
            position: absolute;
            top: 15px;
            left: 0;
            width: 0%;
            height: 2px;
            background: var(--accent-primary);
            z-index: 2;
            transition: var(--transition);
        }

        .step-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            z-index: 3;
            width: 80px;
        }

        .step-number {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #e5e7eb;
            color: #6b7280;
            font-size: 13px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #ffffff;
            transition: var(--transition);
        }

        .step-label {
            font-size: 11px;
            font-weight: 600;
            color: #9ca3af;
            margin-top: 6px;
            text-align: center;
            white-space: nowrap;
        }

        .step-item.active .step-number {
            background: var(--accent-primary);
            color: #ffffff;
        }

        .step-item.active .step-label {
            color: var(--accent-primary);
        }

        .step-item.completed .step-number {
            background: var(--success);
            color: #ffffff;
        }

        .step-item.completed .step-label {
            color: var(--success);
        }

        /* Form Wizard Steps */
        .wizard-step {
            display: none;
        }

        .wizard-step.active {
            display: block;
            animation: fadeIn 0.25s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(4px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .step-title {
            font-family: 'Outfit', sans-serif;
            font-size: 16px;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 1.25rem;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 8px;
        }

        .pks-box {
            background: #f8fafc;
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            padding: 1rem;
            margin-bottom: 1.25rem;
            font-size: 13px;
            color: var(--text-secondary);
        }

        .btn-download {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #ffffff;
            border: 1px solid #d1d5db;
            color: var(--text-primary);
            padding: 6px 12px;
            border-radius: var(--radius);
            font-weight: 600;
            font-size: 12px;
            cursor: pointer;
            text-decoration: none;
            transition: var(--transition);
            margin-top: 6px;
        }

        .btn-download:hover {
            background: #f9fafb;
        }

        .btn-download svg {
            width: 16px;
            height: 16px;
            flex-shrink: 0;
        }

        /* Grid */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        @media (max-width: 480px) {
            .form-grid {
                grid-template-columns: 1fr;
                gap: 0;
            }
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
            border: 1px solid #d1d5db;
            border-radius: var(--radius);
            padding: 0.6rem 0.8rem;
            color: var(--text-primary);
            outline: none;
            transition: var(--transition);
            font-size: 13.5px;
        }

        .form-control:focus {
            border-color: var(--accent-primary);
            box-shadow: 0 0 0 1px var(--accent-primary);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 80px;
        }

        /* Buttons Navigation */
        .button-nav-group {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            margin-top: 1.5rem;
            border-top: 1px solid var(--border-color);
            padding-top: 1.25rem;
        }

        .btn-nav {
            padding: 0.65rem 1.25rem;
            border-radius: var(--radius);
            font-weight: 600;
            font-size: 13.5px;
            cursor: pointer;
            transition: var(--transition);
            outline: none;
            border: 1px solid transparent;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .btn-prev {
            background: #ffffff;
            border-color: #d1d5db;
            color: var(--text-secondary);
        }

        .btn-prev:hover {
            background: #f9fafb;
            border-color: #9ca3af;
        }

        .btn-next {
            background: var(--accent-primary);
            color: #ffffff;
        }

        .btn-next:hover {
            background: var(--accent-hover);
        }

        .alert {
            padding: 10px 12px;
            border-radius: var(--radius);
            font-size: 13px;
            margin-bottom: 1.25rem;
            border: 1px solid #fca5a5;
            background: #fef2f2;
            color: #991b1b;
        }

        .auth-footer {
            text-align: center;
            margin-top: 1.25rem;
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
    </style>

    @if(\Illuminate\Support\Facades\Cache::get('recaptcha_enabled') && \Illuminate\Support\Facades\Cache::get('recaptcha_site_key'))
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
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
            <div class="logo-text">Pendaftaran Reseller</div>
            <div style="font-size: 13px; color: var(--text-secondary); margin-top: 4px;">{{ \Illuminate\Support\Facades\Cache::get('site_title', 'OtomaX Portal') }}</div>
        </div>

        <!-- Card -->
        <div class="auth-card">

            <!-- Steps indicator -->
            <div class="steps-container">
                <div class="steps-line"></div>
                <div class="steps-line-active" id="progress-line"></div>
                
                <div class="step-item active" id="step-tab-1">
                    <div class="step-number">1</div>
                    <div class="step-label">Profil Toko</div>
                </div>
                
                <div class="step-item" id="step-tab-2">
                    <div class="step-number">2</div>
                    <div class="step-label">Akses & API</div>
                </div>
                
                <div class="step-item" id="step-tab-3">
                    <div class="step-number">3</div>
                    <div class="step-label">Verifikasi OTP</div>
                </div>
            </div>

            <!-- Validation Errors -->
            @if($errors->any())
                <div class="alert">
                    <div style="font-weight: 700; margin-bottom: 4px;">Harap perbaiki kesalahan berikut:</div>
                    @foreach ($errors->all() as $error)
                        <div>• {{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <!-- Form -->
            <form action="{{ route('webreport.register.post') }}" method="POST" enctype="multipart/form-data" id="wizard-form">
                @csrf
                
                <!-- STEP 1: Profil Toko & Kontak -->
                <div class="wizard-step active" id="step-content-1">
                    <div class="step-title">Langkah 1: Profil Toko & Kontak</div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="nama">Nama Outlet / Reseller <span style="color:var(--danger)">*</span></label>
                            <input type="text" name="nama" id="nama" class="form-control" placeholder="Contoh: Barokah Cell" value="{{ old('nama') }}" required>
                        </div>

                        <div class="form-group">
                            <label for="nama_pemilik">Nama Lengkap Pemilik <span style="color:var(--danger)">*</span></label>
                            <input type="text" name="nama_pemilik" id="nama_pemilik" class="form-control" placeholder="Contoh: Ahmad Syarif" value="{{ old('nama_pemilik') }}" required>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="no_hp">No. HP / WhatsApp Pengirim <span style="color:var(--danger)">*</span></label>
                            <input type="text" name="no_hp" id="no_hp" class="form-control" placeholder="Contoh: 0812345678" value="{{ old('no_hp') }}" required>
                        </div>

                        <div class="form-group">
                            <label for="email">Alamat Email <span style="color:var(--danger)">*</span></label>
                            <input type="email" name="email" id="email" class="form-control" placeholder="Contoh: owner@kios.com" value="{{ old('email') }}" required>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="alamat">Alamat Lengkap</label>
                        <textarea name="alamat" id="alamat" class="form-control" placeholder="Tulis alamat fisik lengkap outlet Anda...">{{ old('alamat') }}</textarea>
                    </div>
                </div>

                <!-- STEP 2: Keamanan & API (Optional) -->
                <div class="wizard-step" id="step-content-2">
                    <div class="step-title">Langkah 2: Keamanan & Akses API</div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="pin">PIN Keamanan (4-8 Angka) <span style="color:var(--danger)">*</span></label>
                            <input type="password" name="pin" id="pin" class="form-control" placeholder="Contoh: 1234" value="{{ old('pin') }}" required>
                        </div>

                        <div class="form-group">
                            <label for="pin_confirm">Konfirmasi PIN Keamanan <span style="color:var(--danger)">*</span></label>
                            <input type="password" name="pin_confirm" id="pin_confirm" class="form-control" placeholder="Ketik ulang PIN" value="{{ old('pin_confirm') }}" required>
                        </div>
                    </div>

                    <div class="form-grid" style="margin-top: 8px; border-top: 1px dashed var(--border-color); padding-top: 12px;">
                        <div class="form-group">
                            <label for="nomor_ktp">Nomor KTP (NIK)</label>
                            <input type="text" name="nomor_ktp" id="nomor_ktp" class="form-control" placeholder="Tulis 16 digit NIK" value="{{ old('nomor_ktp') }}">
                        </div>

                        <div class="form-group">
                            <label for="npwp">Nomor NPWP (Optional)</label>
                            <input type="text" name="npwp" id="npwp" class="form-control" placeholder="Tulis nomor NPWP" value="{{ old('npwp') }}">
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="alamat_ip">Alamat IP (Optional)</label>
                            <input type="text" name="alamat_ip" id="alamat_ip" class="form-control" placeholder="Contoh: 123.45.67.89" value="{{ old('alamat_ip') }}">
                        </div>

                        <div class="form-group">
                            <label for="password_ip">Password IP (Optional)</label>
                            <input type="password" name="password_ip" id="password_ip" class="form-control" placeholder="IP API Password" value="{{ old('password_ip') }}">
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="url_report">URL Callback / Report (Optional)</label>
                        <input type="url" name="url_report" id="url_report" class="form-control" placeholder="https://domainanda.com/api/callback" value="{{ old('url_report') }}">
                    </div>
                </div>

                <!-- STEP 3: Verifikasi OTP -->
                <div class="wizard-step" id="step-content-3">
                    <div class="step-title">Langkah 3: Verifikasi Kode OTP</div>
                    
                    <div style="background: #f8fafc; padding: 14px; border-radius: 8px; border: 1px solid var(--border-color); margin-bottom: 20px; font-size: 13px; color: var(--text-secondary); line-height: 1.5;">
                        Kami telah mengirimkan kode verifikasi 6-digit ke alamat email Anda: 
                        <strong style="color: var(--text-primary); font-family: monospace; display: block; margin-top: 4px;" id="otp-target-email"></strong>
                        Silakan masukkan kode tersebut di bawah ini untuk merampungkan pendaftaran.
                    </div>

                    <div class="form-group" style="max-width: 280px; margin: 0 auto 16px;">
                        <label for="otp" style="text-align: center; display: block; font-weight: 700; margin-bottom: 8px; font-size: 13.5px;">Masukkan 6 Digit OTP <span style="color:var(--danger)">*</span></label>
                        <input type="text" name="otp" id="otp" class="form-control" placeholder="______" maxlength="6" style="text-align: center; font-size: 20px; font-weight: 700; letter-spacing: 0.2em; height: 48px;">
                    </div>

                    <div style="text-align: center; margin-bottom: 20px;">
                        <button type="button" id="btn-resend-otp" onclick="sendRegisterOtp()" style="background: transparent; border: none; color: var(--accent-primary); font-weight: 600; font-size: 13px; text-decoration: underline; cursor: pointer;">
                            Minta / Kirim Ulang OTP
                        </button>
                        <div id="otp-send-status" style="font-size: 11.5px; color: var(--text-secondary); margin-top: 6px; font-weight: 500;"></div>
                    </div>

                    @if(\Illuminate\Support\Facades\Cache::get('recaptcha_enabled') && \Illuminate\Support\Facades\Cache::get('recaptcha_site_key'))
                    <div class="form-group" style="display: flex; justify-content: center; margin-bottom: 20px;">
                        <div class="g-recaptcha" data-sitekey="{{ \Illuminate\Support\Facades\Cache::get('recaptcha_site_key') }}"></div>
                    </div>
                    @endif
                </div>

                <!-- Navigation Buttons -->
                <div class="button-nav-group">
                    <button type="button" class="btn-nav btn-prev" id="btn-prev" style="display:none;" onclick="changeStep(-1)">
                        ← Kembali
                    </button>
                    <button type="button" class="btn-nav btn-next" id="btn-next" style="margin-left:auto;" onclick="changeStep(1)">
                        Lanjut →
                    </button>
                </div>
            </form>

            <div class="auth-footer">
                Sudah memiliki akun? <a href="{{ route('webreport.login') }}">Masuk Portal</a>
            </div>

        </div>
    </div>

    <!-- Wizard Handler Logic -->
    <script>
        let currentStep = 1;
        const totalSteps = 3;

        function sendRegisterOtp() {
            const emailInput = document.getElementById('email').value;
            document.getElementById('otp-target-email').innerText = emailInput;
            
            const statusDiv = document.getElementById('otp-send-status');
            const resendBtn = document.getElementById('btn-resend-otp');
            
            resendBtn.disabled = true;
            statusDiv.innerText = 'Mengirimkan kode OTP ke email Anda...';
            statusDiv.style.color = '#4b5563';

            fetch('{{ route('webreport.register.send-otp') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ email: emailInput })
            })
            .then(r => r.json())
            .then(data => {
                if (data.status === 'success') {
                    statusDiv.innerText = data.message;
                    statusDiv.style.color = 'var(--success)';
                    
                    // Countdown 60 seconds
                    let seconds = 60;
                    const timer = setInterval(() => {
                        seconds--;
                        if (seconds <= 0) {
                            clearInterval(timer);
                            resendBtn.disabled = false;
                            resendBtn.innerText = 'Kirim Ulang OTP';
                        } else {
                            resendBtn.innerText = `Kirim Ulang (${seconds}s)`;
                        }
                    }, 1000);
                } else {
                    statusDiv.innerText = data.message || 'Gagal mengirim OTP.';
                    statusDiv.style.color = 'var(--danger)';
                    resendBtn.disabled = false;
                }
            })
            .catch(() => {
                statusDiv.innerText = 'Gagal menghubungi server untuk mengirim OTP.';
                statusDiv.style.color = 'var(--danger)';
                resendBtn.disabled = false;
            });
        }

        function changeStep(direction) {
            // Validation check before going forward
            if (direction === 1) {
                const currentFormSection = document.getElementById(`step-content-${currentStep}`);
                const requiredInputs = currentFormSection.querySelectorAll('[required]');
                
                let isValid = true;
                requiredInputs.forEach(input => {
                    if (!input.value.trim()) {
                        input.style.borderColor = 'var(--danger)';
                        isValid = false;
                    } else {
                        input.style.borderColor = '';
                    }
                });

                // Custom validation for step 2: PIN check
                if (currentStep === 2 && isValid) {
                    const pin = document.getElementById('pin');
                    const pinConfirm = document.getElementById('pin_confirm');
                    if (pin.value !== pinConfirm.value) {
                        alert('Konfirmasi PIN tidak cocok dengan PIN keamanan.');
                        pinConfirm.style.borderColor = 'var(--danger)';
                        isValid = false;
                    } else {
                        pinConfirm.style.borderColor = '';
                    }
                }

                if (!isValid) {
                    alert('Harap isi semua kolom wajib (*) sebelum melanjutkan.');
                    return;
                }

                // If going from Step 2 to Step 3, trigger OTP sending
                if (currentStep === 2) {
                    sendRegisterOtp();
                }
            }

            // Step progression logic
            const currentTab = document.getElementById(`step-tab-${currentStep}`);
            if (direction === 1) {
                currentTab.classList.remove('active');
                currentTab.classList.add('completed');
            } else {
                currentTab.classList.remove('active', 'completed');
            }

            currentStep += direction;

            // Boundary safeguard
            if (currentStep > totalSteps) {
                // Submit form
                @if(\Illuminate\Support\Facades\Cache::get('recaptcha_enabled') && \Illuminate\Support\Facades\Cache::get('recaptcha_site_key'))
                    if (typeof grecaptcha !== 'undefined') {
                        var response = grecaptcha.getResponse();
                        if(response.length == 0) {
                            alert("Silakan centang kotak reCAPTCHA (I'm not a robot).");
                            currentStep--; // revert step
                            return;
                        }
                    }
                @endif
                document.getElementById('wizard-form').submit();
                return;
            }

            // Show current step content
            document.querySelectorAll('.wizard-step').forEach(step => step.classList.remove('active'));
            document.getElementById(`step-content-${currentStep}`).classList.add('active');

            // Update Tab active class
            document.getElementById(`step-tab-${currentStep}`).classList.add('active');
            document.getElementById(`step-tab-${currentStep}`).classList.remove('completed');

            // Update Buttons layout
            const btnPrev = document.getElementById('btn-prev');
            const btnNext = document.getElementById('btn-next');

            if (currentStep === 1) {
                btnPrev.style.display = 'none';
            } else {
                btnPrev.style.display = 'inline-flex';
            }

            if (currentStep === totalSteps) {
                btnNext.innerHTML = 'Kirim Pendaftaran ✓';
                btnNext.style.background = 'var(--success)';
            } else {
                btnNext.innerHTML = 'Lanjut →';
                btnNext.style.background = 'var(--accent-primary)';
            }

            // Update Progress bar percentage width
            const progressPct = ((currentStep - 1) / (totalSteps - 1)) * 100;
            document.getElementById('progress-line').style.width = `${progressPct}%`;
        }

        // Initialize state if there are validation errors
        document.addEventListener('DOMContentLoaded', () => {
            @if($errors->any())
                // Skip directly to Step 3 so the user can fix the OTP
                // We bypass changeStep() to avoid sending the OTP again
                document.querySelectorAll('.wizard-step').forEach(step => step.classList.remove('active'));
                document.getElementById('step-content-3').classList.add('active');
                
                document.getElementById('step-tab-1').classList.replace('active', 'completed');
                document.getElementById('step-tab-2').classList.replace('active', 'completed');
                document.getElementById('step-tab-3').classList.add('active');

                currentStep = 3;

                document.getElementById('btn-prev').style.display = 'inline-flex';
                document.getElementById('btn-next').innerHTML = 'Kirim Pendaftaran ✓';
                document.getElementById('btn-next').style.background = 'var(--success)';
                document.getElementById('progress-line').style.width = '100%';
            @endif
        });
    </script>
</body>
</html>
