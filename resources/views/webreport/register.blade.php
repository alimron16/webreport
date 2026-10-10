<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Mitra Baru - {{ $portal_site_title ?? \Illuminate\Support\Facades\Cache::get('site_title', 'OtomaX') }} Webreport</title>
    
    @php
        $siteTitle = $portal_site_title ?? \Illuminate\Support\Facades\Cache::get('site_title', 'OtomaX Webreport');
        $siteSubtitle = $portal_site_subtitle ?? \Illuminate\Support\Facades\Cache::get('site_subtitle', 'Portal Reseller & Server Pulsa Terpercaya');
        $favIcon = $portal_logo_url ?? \Illuminate\Support\Facades\Cache::get('site_logo_url');
        $waAdmin = $portal_whatsapp_number ?? \Illuminate\Support\Facades\Cache::get('whatsapp_number', '');
        
        $cleanWa = preg_replace('/[^0-9]/', '', (string)$waAdmin);
        if (str_starts_with($cleanWa, '0')) {
            $cleanWa = '62' . substr($cleanWa, 1);
        } elseif (str_starts_with($cleanWa, '8')) {
            $cleanWa = '62' . $cleanWa;
        }
    @endphp

    @if($favIcon)
    <link rel="icon" type="image/x-icon" href="{{ $favIcon }}">
    <link rel="shortcut icon" href="{{ $favIcon }}">
    @endif

    <!-- Fonts: Inter & Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    
    <!-- Icons: Boxicons -->
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --primary: #4f46e5;
            --wa-green: #25d366;
            --wa-dark: #128c7e;
            --bg-body: #f8fafc;
            --bg-card: #ffffff;
            --border-color: #e2e8f0;
            --text-main: #0f172a;
            --text-muted: #64748b;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2.5rem 1.25rem;
            margin: 0;
            background-image: radial-gradient(circle at 50% 10%, rgba(37, 211, 102, 0.08) 0%, rgba(79, 70, 229, 0.04) 40%, transparent 70%);
            background-repeat: no-repeat;
        }

        .auth-wrapper {
            width: 100%;
            max-width: 540px;
        }

        .brand-logo-img {
            height: 44px;
            max-width: 180px;
            object-fit: contain;
        }

        .brand-fallback-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--wa-green) 0%, var(--wa-dark) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 24px;
            box-shadow: 0 4px 12px rgba(37, 211, 102, 0.3);
        }

        .auth-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 18px;
            padding: 2.25rem 2rem;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.02);
        }

        .form-label {
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #475569;
            margin-bottom: 0.4rem;
        }

        .input-group-text {
            background: #f8fafc;
            border-color: #cbd5e1;
            color: #64748b;
        }

        .form-control, .form-select {
            border-color: #cbd5e1;
            padding: 0.65rem 0.85rem;
            font-size: 0.925rem;
            border-radius: 8px;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--wa-green);
            box-shadow: 0 0 0 3px rgba(37, 211, 102, 0.2);
        }

        .btn-submit-wa {
            background: linear-gradient(135deg, #25d366 0%, #16a34a 100%);
            color: #ffffff;
            font-weight: 700;
            border: none;
            border-radius: 12px;
            padding: 13px 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-size: 1rem;
            box-shadow: 0 8px 20px -4px rgba(37, 211, 102, 0.5);
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            width: 100%;
        }

        .btn-submit-wa:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 28px -4px rgba(37, 211, 102, 0.65);
            color: #ffffff;
        }

        .info-pill {
            background: rgba(37, 211, 102, 0.1);
            border: 1px solid rgba(37, 211, 102, 0.3);
            border-radius: 10px;
            padding: 12px 14px;
            font-size: 0.825rem;
            color: #15803d;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 1.5rem;
            line-height: 1.5;
        }
    </style>
</head>
<body>

    <div class="auth-wrapper">
        <!-- Logo & Header -->
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center gap-2 mb-2">
                @if($favIcon)
                    <img src="{{ $favIcon }}" alt="Logo" class="brand-logo-img">
                @else
                    <div class="brand-fallback-icon">
                        <i class="bx bxl-whatsapp"></i>
                    </div>
                    <span class="fs-4 fw-bold text-dark">{{ $siteTitle }}</span>
                @endif
            </div>
            <h4 class="fw-bold mb-1" style="font-family: 'Plus Jakarta Sans', sans-serif;">Pendaftaran Mitra Baru</h4>
            <p class="text-muted small mb-0">{{ $siteSubtitle }}</p>
        </div>

        <!-- Main Card -->
        <div class="auth-card">
            <!-- Info Notice: No OTP & Direct WhatsApp -->
            <div class="info-pill">
                <i class="bx bx-check-shield fs-4 flex-shrink-0 mt-0.5"></i>
                <div>
                    <strong>Pendaftaran Instan via WhatsApp:</strong><br>
                    Isi data di bawah ini, lalu klik tombol kirim. Format pendaftaran akan otomatis disiapkan dan langsung diteruskan ke WhatsApp Admin server tanpa perlu verifikasi kode OTP atau simpan data di database.
                </div>
            </div>

            <form id="waRegisterForm" onsubmit="return handleWaSubmit(event)">
                <!-- 1. Nama Outlet -->
                <div class="mb-3">
                    <label for="nama_outlet" class="form-label">Nama Outlet / Konter / Usaha <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bx bx-store"></i></span>
                        <input type="text" id="nama_outlet" class="form-control" placeholder="Contoh: Berkah Cellular" required autofocus>
                    </div>
                </div>

                <!-- 2. Nama Pemilik -->
                <div class="mb-3">
                    <label for="nama_pemilik" class="form-label">Nama Lengkap Pemilik <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bx bx-user"></i></span>
                        <input type="text" id="nama_pemilik" class="form-control" placeholder="Contoh: Ahmad Fauzi" required>
                    </div>
                </div>

                <!-- 3. Nomor WhatsApp -->
                <div class="mb-3">
                    <label for="no_hp" class="form-label">Nomor WhatsApp Anda <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bx bxl-whatsapp text-success"></i></span>
                        <input type="tel" id="no_hp" class="form-control" placeholder="Contoh: 081234567890" required>
                    </div>
                    <span class="text-muted" style="font-size: 0.725rem;">Nomor ini akan digunakan sebagai nomor transaksi & kontak utama Anda.</span>
                </div>

                <!-- 4. Kota / Domisili -->
                <div class="mb-3">
                    <label for="kota" class="form-label">Kota / Wilayah Domisili</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bx bx-map-pin"></i></span>
                        <input type="text" id="kota" class="form-control" placeholder="Contoh: Surabaya, Jawa Timur">
                    </div>
                </div>

                <!-- 5. Tipe Usaha / Layanan -->
                <div class="mb-4">
                    <label for="jenis_layanan" class="form-label">Tipe Layanan / Usaha</label>
                    <select id="jenis_layanan" class="form-select">
                        <option value="Konter Pulsa & Kuota">Konter Pulsa & Kuota Data</option>
                        <option value="Agen Pembayaran & PPOB">Agen Pembayaran & PPOB (Listrik, BPJS, PDAM)</option>
                        <option value="Toko / Warung Kelontong">Toko / Warung Kelontong</option>
                        <option value="H2H / Server Partner">H2H / Server Partner (API Transaksi)</option>
                        <option value="Pengguna Pribadi / Usaha Rumahan">Pengguna Pribadi / Usaha Rumahan</option>
                    </select>
                </div>

                <!-- Alert Feedback Box -->
                <div id="alertFeedback" class="alert alert-success d-none py-2 px-3 small d-flex align-items-center gap-2 mb-3">
                    <i class="bx bx-check-circle fs-5"></i>
                    <div>Membuka WhatsApp Admin... Silakan tekan tombol <strong>Kirim</strong> di aplikasi WhatsApp Anda.</div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-submit-wa" id="btnSubmitWa">
                    <i class="bx bxl-whatsapp fs-4"></i>
                    <span>Kirim Pendaftaran ke WhatsApp Admin</span>
                </button>
            </form>

            <!-- Auth Footer Links -->
            <div class="text-center mt-4 pt-3 border-top small text-muted">
                Sudah memiliki akun? <a href="{{ route('webreport.login') }}" class="fw-semibold text-primary text-decoration-none">Masuk Member Area</a>
                <span class="mx-1">&bull;</span>
                <a href="{{ route('webreport.landing') }}" class="text-muted text-decoration-none">Kembali ke Beranda</a>
            </div>
        </div>

        <p class="text-center text-muted small mt-4 mb-0">
            &copy; {{ date('Y') }} {{ $siteTitle }}. All rights reserved. Powered by OtomaX Engine.
        </p>
    </div>

    <script>
        function handleWaSubmit(event) {
            event.preventDefault();

            const namaOutlet = document.getElementById('nama_outlet').value.trim();
            const namaPemilik = document.getElementById('nama_pemilik').value.trim();
            const noHp = document.getElementById('no_hp').value.trim();
            const kota = document.getElementById('kota').value.trim() || '-';
            const jenis = document.getElementById('jenis_layanan').value;

            if (!namaOutlet || !namaPemilik || !noHp) {
                alert('Mohon isi Nama Outlet, Nama Pemilik, dan Nomor WhatsApp Anda.');
                return false;
            }

            const cleanAdmin = '{{ $cleanWa }}';
            if (!cleanAdmin) {
                alert('Nomor WhatsApp Admin belum disetel di Admin Panel. Silakan hubungi admin server secara langsung.');
                return false;
            }

            const siteTitle = '{{ $siteTitle }}';

            // Susun template pesan rapi
            const text = `*FORMULIR PENDAFTARAN MITRA BARU*\n` +
                         `Portal: ${siteTitle}\n` +
                         `------------------------------------\n` +
                         `• Nama Outlet: ${namaOutlet}\n` +
                         `• Nama Pemilik: ${namaPemilik}\n` +
                         `• No. WhatsApp: ${noHp}\n` +
                         `• Kota / Domisili: ${kota}\n` +
                         `• Tipe Layanan: ${jenis}\n` +
                         `------------------------------------\n` +
                         `Halo Admin, saya ingin mendaftar menjadi agen/reseller baru. Mohon segera dibuatkan akun dan diinfokan Kode Reseller serta panduan transaksinya. Terima kasih!`;

            const waUrl = `https://wa.me/${cleanAdmin}?text=${encodeURIComponent(text)}`;

            // Tampilkan alert
            const alertBox = document.getElementById('alertFeedback');
            if (alertBox) {
                alertBox.classList.remove('d-none');
            }

            // Buka WhatsApp
            window.open(waUrl, '_blank');

            return false;
        }
    </script>
</body>
</html>
