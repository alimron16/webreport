<!DOCTYPE html>
<html lang="id" style="scroll-behavior: smooth; scroll-padding-top: 80px;">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    
    @php
        $siteTitle = $portal_site_title ?? \Illuminate\Support\Facades\Cache::get('site_title', 'NAWASENA');
        $siteSubtitle = $portal_site_subtitle ?? \Illuminate\Support\Facades\Cache::get('site_subtitle', 'Portal Transaksi Server H2H & Reseller Terpercaya');
        $favIcon = $portal_logo_url ?? (file_exists(public_path('logo.png')) ? asset('logo.png') : \Illuminate\Support\Facades\Cache::get('site_logo_url'));

        // Dynamic Registration & WhatsApp Destination
        $waRegUrl = $portal_whatsapp_reg_url ?? null;
        $waCsUrl = $portal_whatsapp_cs_url ?? null;
        $waAdmin = $portal_whatsapp_number ?? \Illuminate\Support\Facades\Cache::get('whatsapp_number', '');
        
        $cleanWa = preg_replace('/[^0-9]/', '', (string)$waAdmin);
        if (str_starts_with($cleanWa, '0')) {
            $cleanWa = '62' . substr($cleanWa, 1);
        } elseif (str_starts_with($cleanWa, '8')) {
            $cleanWa = '62' . $cleanWa;
        }

        $defaultWaTarget = !empty($cleanWa) ? $cleanWa : '6281234567890';
        $directWaLink = !empty($waRegUrl) ? $waRegUrl : ('https://wa.me/' . $defaultWaTarget . '?text=' . urlencode("Halo Tim Integrasi {$siteTitle}, saya ingin mendaftar sebagai mitra transaksi server H2H baru. Mohon panduannya."));
        $csWaLink = !empty($waCsUrl) ? $waCsUrl : ('https://wa.me/' . $defaultWaTarget . '?text=' . urlencode("Halo CS {$siteTitle}, saya membutuhkan bantuan teknis terkait server H2H."));
    @endphp

    <!-- Primary SEO Meta Tags -->
    <title>{{ $siteTitle }} - {{ $siteSubtitle }}</title>
    <meta name="title" content="{{ $siteTitle }} - {{ $siteSubtitle }}">
    <meta name="description" content="Platform transaksi server H2H (Host-to-Host) {{ $siteTitle }}. Infrastruktur berkecepatan tinggi, latensi rendah, throughput stabil, mutasi saldo transparan, dan rekapitulasi realtime.">
    <meta name="keywords" content="server h2h pulsa, transaksi h2h, webreport h2h, {{ strtolower($siteTitle) }}, mutasi saldo h2h, gateway h2h ppob, rest api pulsa">
    <meta name="author" content="{{ $siteTitle }}">
    <meta name="language" content="Indonesian">

    <!-- Canonical URL -->
    <link rel="canonical" href="{{ url()->current() }}">

    @if($favIcon)
    <link rel="shortcut icon" href="{{ $favIcon }}" type="image/png">
    <link rel="apple-touch-icon" href="{{ $favIcon }}">
    @else
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    @endif

    <!-- Google Fonts: IBM Plex Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Tabler Icons Webfont & Font Awesome -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.36.0/dist/tabler-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        /* ============================================================
           NAWASENA LUXURY DESIGN SYSTEM (NAVY & GOLD THEME)
           Focused on H2H (Host-to-Host) Server Architecture
        ============================================================ */
        :root {
            /* Brand Identity Colors from Logo */
            --nw-navy: #092C5E;
            --nw-navy-dark: #072146;
            --nw-navy-hover: #0c3674;
            --nw-navy-soft: rgba(9, 44, 94, 0.07);
            
            --nw-gold: #C89B3C;
            --nw-gold-dark: #B58728;
            --nw-gold-light: #E0B758;
            --nw-gold-soft: rgba(200, 155, 60, 0.12);
            --nw-gold-gradient: linear-gradient(135deg, #D4A446 0%, #B8892A 100%);

            --nw-bg-light: #F8FAFD;
            --nw-border: #E2E8F0;
            --nw-border-subtle: #EDF2F7;
            
            --bs-primary: #C89B3C;
            --bs-primary-rgb: 200, 155, 60;
            --bs-secondary: #092C5E;
            --bs-secondary-rgb: 9, 44, 94;
            --bs-body-font-family: 'IBM Plex Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            --bs-body-color: #334155;
            --bs-body-bg: #ffffff;
            --bs-border-radius: 16px;
        }

        body {
            font-family: var(--bs-body-font-family);
            color: var(--bs-body-color);
            line-height: 1.65;
            -webkit-font-smoothing: antialiased;
            overflow-x: hidden;
            background-color: #ffffff;
        }

        h1, h2, h3, h4, h5, h6, .h1, .h2, .h3, .h4, .h5, .h6 {
            color: var(--nw-navy);
            font-weight: 700;
            letter-spacing: -0.02em;
        }

        .display-3 {
            font-size: calc(2.1rem + 2.2vw);
            font-weight: 700;
            line-height: 1.18;
            letter-spacing: -0.03em;
            color: var(--nw-navy);
        }

        .display-6 {
            font-size: calc(1.5rem + 1.2vw);
            font-weight: 700;
            letter-spacing: -0.025em;
            color: var(--nw-navy);
        }

        /* Gold highlight text */
        .text-gold {
            color: var(--nw-gold) !important;
        }

        .text-navy {
            color: var(--nw-navy) !important;
        }

        /* Buttons (50px pill style) */
        .btn {
            border-radius: 50px;
            font-weight: 600;
            padding: 0.72rem 1.6rem;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            font-size: 0.95rem;
        }

        .btn-primary {
            background: var(--nw-gold);
            border-color: var(--nw-gold);
            color: #ffffff;
            font-weight: 600;
        }

        .btn-primary:hover, .btn-primary:focus, .btn-primary:active {
            background-color: var(--nw-gold-dark) !important;
            border-color: var(--nw-gold-dark) !important;
            color: #ffffff !important;
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(200, 155, 60, 0.35);
        }

        .btn-secondary {
            background-color: var(--nw-navy);
            border-color: var(--nw-navy);
            color: #ffffff;
        }

        .btn-secondary:hover, .btn-secondary:focus {
            background-color: var(--nw-navy-dark) !important;
            border-color: var(--nw-navy-dark) !important;
            color: #ffffff !important;
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(9, 44, 94, 0.25);
        }

        .btn-outline-secondary {
            color: var(--nw-navy);
            border-color: var(--nw-navy);
            background: transparent;
        }

        .btn-outline-secondary:hover, .btn-outline-secondary:focus {
            background-color: var(--nw-navy-soft);
            border-color: var(--nw-navy);
            color: var(--nw-navy);
            transform: translateY(-2px);
        }

        /* Surfaces & Colors */
        .bg-navy {
            background-color: var(--nw-navy) !important;
        }

        .bg-navy-dark {
            background-color: var(--nw-navy-dark) !important;
        }

        .bg-secondary {
            background-color: var(--nw-navy) !important;
        }

        .text-bg-secondary {
            background-color: var(--nw-navy) !important;
            color: #ffffff !important;
        }

        .text-bg-primary {
            background-color: var(--nw-gold) !important;
            color: #ffffff !important;
        }

        .bg-light {
            background-color: var(--nw-bg-light) !important;
        }

        /* Generous Section Spacing */
        .section-py {
            padding-top: 5.5rem !important;
            padding-bottom: 5.5rem !important;
        }

        @media (min-width: 992px) {
            .section-py {
                padding-top: 7.5rem !important;
                padding-bottom: 7.5rem !important;
            }
        }

        .section-heading-wrap {
            margin-bottom: 3.5rem !important;
        }

        @media (min-width: 992px) {
            .section-heading-wrap {
                margin-bottom: 4.5rem !important;
            }
        }

        .hero-section {
            position: relative;
            padding-top: 5.5rem;
            padding-bottom: 7rem;
            background-image: 
                linear-gradient(135deg, rgba(255, 255, 255, 0.84) 0%, rgba(255, 255, 255, 0.72) 50%, rgba(255, 255, 255, 0.58) 100%),
                url('{{ asset('images/hero-bg.jpg') }}');
            background-size: cover;
            background-position: center top;
            background-repeat: no-repeat;
            overflow: hidden;
        }

        @media (min-width: 992px) {
            .hero-section {
                padding-top: 7rem;
                padding-bottom: 8.5rem;
            }
        }

        .hero-section::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 90px;
            background: linear-gradient(to bottom, rgba(255, 255, 255, 0) 0%, #ffffff 100%);
            pointer-events: none;
        }

        /* Continuous Running Marquee Ticker */
        .pg-marquee-container {
            overflow: hidden;
            position: relative;
            width: 100%;
            padding: 14px 0;
            mask-image: linear-gradient(to right, transparent 0%, black 6%, black 94%, transparent 100%);
            -webkit-mask-image: linear-gradient(to right, transparent 0%, black 6%, black 94%, transparent 100%);
        }

        .pg-marquee-track {
            display: flex;
            gap: 18px;
            width: max-content;
            animation: pg-scroll-left 26s linear infinite;
        }

        .pg-marquee-track:hover {
            animation-play-state: paused;
        }

        @keyframes pg-scroll-left {
            0% {
                transform: translateX(0);
            }
            100% {
                transform: translateX(-50%);
            }
        }

        .pg-logo-card {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #ffffff;
            border: 1px solid var(--nw-border);
            padding: 10px 24px;
            border-radius: 16px;
            box-shadow: 0 2px 8px rgba(9, 44, 94, 0.04);
            white-space: nowrap;
            transition: all 0.25s ease;
            height: 60px;
            min-width: 130px;
            cursor: default;
        }

        .pg-logo-card img, .pg-logo-card svg {
            max-height: 32px;
            max-width: 120px;
            width: auto;
            object-fit: contain;
            display: block;
        }

        .pg-logo-card:hover {
            transform: translateY(-2px);
            border-color: var(--nw-gold);
            box-shadow: 0 8px 20px rgba(200, 155, 60, 0.16);
        }

        /* Stats Cards */
        .stat-card-mc {
            background: #ffffff;
            border-radius: 20px;
            padding: 32px 24px;
            text-align: center;
            border: 1px solid var(--nw-border);
            box-shadow: 0 4px 20px rgba(9, 44, 94, 0.04);
            transition: all 0.25s ease;
            height: 100%;
        }

        .stat-card-mc:hover {
            transform: translateY(-4px);
            box-shadow: 0 14px 32px rgba(9, 44, 94, 0.08);
            border-color: var(--nw-gold);
        }

        .stat-num-mc {
            font-size: 2.6rem;
            font-weight: 700;
            color: var(--nw-navy);
            line-height: 1.1;
            margin-bottom: 8px;
            letter-spacing: -0.02em;
        }

        /* Feature Cards */
        .feature-card .card {
            border-radius: 22px;
            transition: all 0.25s ease;
            padding: 2.5rem 2rem !important;
            border: 1px solid var(--nw-border) !important;
        }

        .feature-card .card:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 40px rgba(9, 44, 94, 0.09) !important;
            border-color: var(--nw-gold) !important;
        }

        .feature-icon-box {
            width: 64px;
            height: 64px;
            border-radius: 18px;
            background: var(--nw-navy-soft);
            color: var(--nw-navy);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.9rem;
            margin-bottom: 1.5rem;
            transition: all 0.25s ease;
        }

        .feature-card .card:hover .feature-icon-box {
            background: var(--nw-gold);
            color: #ffffff;
        }

        /* Spec Pill Card */
        .spec-item-box {
            background: #ffffff;
            border: 1px solid var(--nw-border);
            border-radius: 18px;
            padding: 1.5rem;
            transition: all 0.25s ease;
            height: 100%;
        }

        .spec-item-box:hover {
            border-color: var(--nw-gold);
            transform: translateY(-3px);
            box-shadow: 0 12px 28px rgba(9, 44, 94, 0.06);
        }

        /* Step Guide Cards */
        .step-card-box {
            background: #ffffff;
            border-radius: 22px;
            border: 1px solid var(--nw-border);
            padding: 2.5rem 2rem;
            position: relative;
            height: 100%;
            transition: all 0.25s ease;
        }

        .step-card-box:hover {
            transform: translateY(-5px);
            border-color: var(--nw-gold);
            box-shadow: 0 16px 36px rgba(9, 44, 94, 0.08);
        }

        .step-number-badge {
            width: 44px;
            height: 44px;
            border-radius: 14px;
            background: var(--nw-gold);
            color: #ffffff;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            margin-bottom: 1.5rem;
        }

        /* FAQ Item Box */
        .faq-item-box {
            background: #ffffff;
            border: 1px solid var(--nw-border);
            border-radius: 18px;
            padding: 1.5rem 1.8rem;
            margin-bottom: 1rem;
            transition: all 0.2s ease;
        }

        .faq-item-box:hover {
            border-color: var(--nw-gold);
        }

        .faq-item-box a {
            color: var(--nw-navy);
            font-weight: 600;
        }

        .faq-item-box a[aria-expanded="true"] {
            color: var(--nw-gold-dark);
        }

        /* Floating WhatsApp Button */
        .floating-wa-btn {
            position: fixed;
            bottom: 28px;
            right: 28px;
            z-index: 1050;
            width: 58px;
            height: 58px;
            border-radius: 50%;
            background: #25D366;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            box-shadow: 0 8px 24px rgba(37, 211, 102, 0.4);
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .floating-wa-btn:hover {
            transform: scale(1.1);
            color: #ffffff;
            box-shadow: 0 12px 30px rgba(37, 211, 102, 0.55);
        }

        /* Slim Modern Navbar */
        .navbar {
            padding-top: 0.55rem !important;
            padding-bottom: 0.55rem !important;
            background: #ffffff !important;
            box-shadow: 0 1px 10px rgba(9, 44, 94, 0.05);
            min-height: 60px;
        }

        .navbar .navbar-brand {
            padding: 0;
            margin: 0;
            white-space: nowrap;
        }

        .navbar .nav-link {
            font-size: 0.9rem;
            font-weight: 600;
            color: #334155 !important;
            padding: 0.4rem 0.85rem !important;
            white-space: nowrap;
            transition: all 0.2s ease;
            border-radius: 50px;
        }

        .navbar .nav-link:hover {
            color: var(--nw-gold) !important;
            background: var(--nw-gold-soft);
        }

        .navbar .btn {
            font-size: 0.88rem !important;
            padding: 0.45rem 1.25rem !important;
            white-space: nowrap;
            border-radius: 50px;
            font-weight: 600;
            line-height: 1.4;
        }

        /* Brand text styling */
        .brand-title {
            font-weight: 800;
            font-size: 1.25rem;
            color: var(--nw-navy);
            letter-spacing: -0.02em;
        }

        .brand-subtitle-gold {
            font-size: 0.72rem;
            letter-spacing: 0.18em;
            color: var(--nw-gold);
            font-weight: 700;
            text-transform: uppercase;
        }
    </style>
</head>
<body>

    <!-- ============================================================
         HEADER / NAVBAR (SLIM, COMPACT & MODERN)
    ============================================================ -->
    <nav class="navbar navbar-expand-lg bg-white border-bottom sticky-top py-2">
        <div class="container">
            <a class="navbar-brand d-inline-flex gap-2 align-items-center lh-1 py-0 my-0" href="{{ url('/') }}">
                @if($favIcon)
                    <img src="{{ $favIcon }}" alt="{{ $siteTitle }} Logo" class="img-fluid" style="height: 36px; max-width: 130px; object-fit: contain;">
                @else
                    <div class="rounded-3 d-flex align-items-center justify-content-center text-white" style="width: 36px; height: 36px; background: var(--nw-navy);">
                        <i class="ti ti-server fs-5 text-warning"></i>
                    </div>
                    <span class="brand-title">{{ strtoupper($siteTitle) }}</span>
                @endif
            </a>

            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarWebreport"
                aria-controls="navbarWebreport" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarWebreport">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0 align-items-center gap-1">
                    <li class="nav-item"><a href="{{ url('/') }}#features" class="nav-link">Fitur</a></li>
                    <li class="nav-item"><a href="{{ url('/') }}#techSpecs" class="nav-link">Protokol</a></li>
                    <li class="nav-item"><a href="{{ url('/') }}#howItWork" class="nav-link">Integrasi</a></li>
                    <li class="nav-item"><a href="{{ url('/') }}#channels" class="nav-link">Produk</a></li>
                    <li class="nav-item"><a href="{{ url('/') }}#faq" class="nav-link">FAQ</a></li>
                    <li class="nav-item"><a href="{{ $csWaLink }}" target="_blank" rel="noopener" class="nav-link">Bantuan</a></li>
                </ul>

                <div class="d-flex gap-2 align-items-center">
                    @if(session('reseller_id'))
                        <a href="{{ route('webreport.dashboard') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1">
                            <i class="ti ti-dashboard me-1"></i> <span>Dasbor</span>
                        </a>
                    @else
                        <a href="{{ route('webreport.login') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1">
                            <i class="ti ti-login me-1"></i> <span>Login</span>
                        </a>
                        <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#modalDaftar">
                            <span>Daftar H2H</span> <i class="ti ti-arrow-right fs-6"></i>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </nav>

    <!-- ============================================================
         HERO SECTION (DYNAMIC SERVER NAME & AMBIENT INTEGRATED BACKGROUND)
    ============================================================ -->
    <section class="hero-section">
        <div class="container position-relative" style="z-index: 2;">
            <div class="row justify-content-center text-center">
                <div class="col-xl-9 col-lg-10">
                    <h1 class="display-3 mb-4">
                        Platform Transaksi Server H2H <span class="text-gold">{{ $siteTitle }}</span> Cepat, Stabil &amp; Realtime
                    </h1>

                    <p class="lead text-muted mb-4 mx-auto" style="max-width: 820px; font-size: 1.15rem; line-height: 1.8;">
                        Infrastruktur transaksi Host-to-Host (H2H) {{ $siteTitle }} dengan throughput tinggi dan latensi rendah. Pantau arus transaksi detik-ke-detik, mutasi saldo transparan, respon callback instan, dan stabilitas server 24 jam nonstop.
                    </p>

                    <div class="mt-4 mb-4 d-flex gap-3 justify-content-center flex-wrap">
                        @if(session('reseller_id'))
                            <a href="{{ route('webreport.dashboard') }}" class="btn btn-primary d-inline-flex align-items-center gap-2">
                                <span>Buka Dasbor Mitra</span> <i class="fas fa-arrow-right fs-6"></i>
                            </a>
                        @else
                            <a href="{{ route('webreport.login') }}" class="btn btn-primary d-inline-flex align-items-center gap-2">
                                <span>Masuk ke Member Area</span> <i class="fas fa-arrow-right fs-6"></i>
                            </a>
                            <button type="button" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalDaftar">
                                <i class="fab fa-whatsapp text-success fs-5"></i> <span>Daftar Mitra H2H</span>
                            </button>
                        @endif
                    </div>

                    <div class="mt-4 d-flex align-items-center justify-content-center gap-4 text-secondary small flex-wrap pt-2">
                        <div><i class="fas fa-check-circle text-gold me-1"></i> Jalur Transaksi H2H Cepat</div>
                        <div><i class="fas fa-check-circle text-gold me-1"></i> Rekap Mutasi Saldo Realtime</div>
                        <div><i class="fas fa-check-circle text-gold me-1"></i> Keamanan IP Whitelist &amp; PIN</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================
         OPERATORS & PAYMENT CHANNELS RUNNING MARQUEE (ACTUAL IMAGES)
    ============================================================ -->
    <section class="border-top border-bottom py-3 bg-white" id="channels">
        <div class="container-fluid px-0">
            <div class="pg-marquee-container">
                <div class="pg-marquee-track">
                    <!-- Set 1: Bank, E-Wallet & Operator Logos -->
                    <div class="pg-logo-card">
                        <img src="{{ asset('images/gateways/bri.png') }}" alt="Bank BRI" title="Bank BRI">
                    </div>
                    <div class="pg-logo-card">
                        <img src="{{ asset('images/gateways/bni.png') }}" alt="Bank BNI" title="Bank BNI">
                    </div>
                    <div class="pg-logo-card">
                        <img src="{{ asset('images/gateways/bca.png') }}" alt="Bank BCA" title="Bank BCA">
                    </div>
                    <div class="pg-logo-card">
                        <img src="{{ asset('images/gateways/mandiri.png') }}" alt="Bank Mandiri" title="Bank Mandiri">
                    </div>
                    <div class="pg-logo-card">
                        <img src="{{ asset('images/gateways/qris.png') }}" alt="QRIS" title="QRIS Nasional">
                    </div>
                    <div class="pg-logo-card">
                        <img src="{{ asset('images/gateways/dana.png') }}" alt="DANA E-Wallet" title="DANA E-Wallet">
                    </div>
                    <div class="pg-logo-card">
                        <img src="{{ asset('images/gateways/ovo.png') }}" alt="OVO" title="OVO">
                    </div>
                    <div class="pg-logo-card">
                        <img src="{{ asset('images/gateways/gopay.png') }}" alt="GoPay" title="GoPay">
                    </div>
                    <div class="pg-logo-card">
                        <img src="{{ asset('images/gateways/shopeepay.png') }}" alt="ShopeePay" title="ShopeePay">
                    </div>
                    <div class="pg-logo-card">
                        <img src="{{ asset('images/gateways/telkomsel.svg') }}" alt="Telkomsel" title="Telkomsel">
                    </div>
                    <div class="pg-logo-card">
                        <img src="{{ asset('images/gateways/games.svg') }}" alt="Voucher Game" title="Voucher Game">
                    </div>
                    <div class="pg-logo-card">
                        <img src="{{ asset('images/gateways/indomaret.png') }}" alt="Indomaret" title="Indomaret">
                    </div>
                    <div class="pg-logo-card">
                        <img src="{{ asset('images/gateways/alfamart.png') }}" alt="Alfamart" title="Alfamart">
                    </div>

                    <!-- Set 2: Duplicate for Seamless Infinite Marquee Loop -->
                    <div class="pg-logo-card">
                        <img src="{{ asset('images/gateways/bri.png') }}" alt="Bank BRI" title="Bank BRI">
                    </div>
                    <div class="pg-logo-card">
                        <img src="{{ asset('images/gateways/bni.png') }}" alt="Bank BNI" title="Bank BNI">
                    </div>
                    <div class="pg-logo-card">
                        <img src="{{ asset('images/gateways/bca.png') }}" alt="Bank BCA" title="Bank BCA">
                    </div>
                    <div class="pg-logo-card">
                        <img src="{{ asset('images/gateways/mandiri.png') }}" alt="Bank Mandiri" title="Bank Mandiri">
                    </div>
                    <div class="pg-logo-card">
                        <img src="{{ asset('images/gateways/qris.png') }}" alt="QRIS" title="QRIS Nasional">
                    </div>
                    <div class="pg-logo-card">
                        <img src="{{ asset('images/gateways/dana.png') }}" alt="DANA E-Wallet" title="DANA E-Wallet">
                    </div>
                    <div class="pg-logo-card">
                        <img src="{{ asset('images/gateways/ovo.png') }}" alt="OVO" title="OVO">
                    </div>
                    <div class="pg-logo-card">
                        <img src="{{ asset('images/gateways/gopay.png') }}" alt="GoPay" title="GoPay">
                    </div>
                    <div class="pg-logo-card">
                        <img src="{{ asset('images/gateways/shopeepay.png') }}" alt="ShopeePay" title="ShopeePay">
                    </div>
                    <div class="pg-logo-card">
                        <img src="{{ asset('images/gateways/telkomsel.svg') }}" alt="Telkomsel" title="Telkomsel">
                    </div>
                    <div class="pg-logo-card">
                        <img src="{{ asset('images/gateways/games.svg') }}" alt="Voucher Game" title="Voucher Game">
                    </div>
                    <div class="pg-logo-card">
                        <img src="{{ asset('images/gateways/indomaret.png') }}" alt="Indomaret" title="Indomaret">
                    </div>
                    <div class="pg-logo-card">
                        <img src="{{ asset('images/gateways/alfamart.png') }}" alt="Alfamart" title="Alfamart">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================
         STATS STRIP (H2H PERFORMANCE METRICS)
    ============================================================ -->
    <section class="section-py bg-white">
        <div class="container">
            <div class="row g-4 g-lg-5">
                <div class="col-md-3 col-6">
                    <div class="stat-card-mc">
                        <div class="stat-num-mc">99.9%</div>
                        <div class="small fw-semibold text-muted">Uptime Infrastruktur H2H</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-card-mc">
                        <div class="stat-num-mc">&lt; 1 Detik</div>
                        <div class="small fw-semibold text-muted">Rata-rata Response Callback</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-card-mc">
                        <div class="stat-num-mc">24/7</div>
                        <div class="small fw-semibold text-muted">Monitoring Trafik Server</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-card-mc">
                        <div class="stat-num-mc">100%</div>
                        <div class="small fw-semibold text-muted">Akurasi Mutasi Saldo Realtime</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================
         PROTOKOL & ARSITEKTUR INTEGRASI H2H (SPESIFIKASI TEKNIS)
    ============================================================ -->
    <section class="section-py bg-light border-top border-bottom" id="techSpecs">
        <div class="container">
            <div class="row justify-content-center text-center section-heading-wrap">
                <div class="col-xl-8">
                    <span class="badge text-bg-secondary rounded-pill py-2 px-3 mb-2" style="background: var(--nw-navy) !important;">Arsitektur Integrasi</span>
                    <h2 class="display-6 mb-3">Spesifikasi Protokol Server H2H {{ $siteTitle }}</h2>
                    <p class="text-muted">Kompatibilitas standar industri server pulsa &amp; PPOB dengan dukungan multi-protokol, response cepat, dan sistem failover cerdas.</p>
                </div>
            </div>

            <div class="row g-4 mb-5">
                <!-- Spec 1: IP Center & IP GET -->
                <div class="col-lg-4 col-md-6">
                    <div class="spec-item-box">
                        <div class="d-flex align-items-center gap-2 mb-2 text-gold">
                            <i class="ti ti-network fs-3"></i>
                            <h4 class="h5 mb-0 text-navy fw-bold">IP Center &amp; IP GET</h4>
                        </div>
                        <p class="small text-muted mb-0">Jalur transaksi via HTTP GET URL parameter langsung ke IP Center server dengan latensi ultra-cepat dan eksekusi instan.</p>
                    </div>
                </div>

                <!-- Spec 2: REST API JSON -->
                <div class="col-lg-4 col-md-6">
                    <div class="spec-item-box">
                        <div class="d-flex align-items-center gap-2 mb-2 text-gold">
                            <i class="ti ti-code fs-3"></i>
                            <h4 class="h5 mb-0 text-navy fw-bold">REST API &amp; JSON</h4>
                        </div>
                        <p class="small text-muted mb-0">Endpoint HTTP POST berkecepatan tinggi dengan payload data terenkripsi, status response standard, dan latensi ultra-rendah.</p>
                    </div>
                </div>

                <!-- Spec 3: Jabber XMPP -->
                <div class="col-lg-4 col-md-6">
                    <div class="spec-item-box">
                        <div class="d-flex align-items-center gap-2 mb-2 text-gold">
                            <i class="ti ti-message-2-code fs-3"></i>
                            <h4 class="h5 mb-0 text-navy fw-bold">Jabber / XMPP Center</h4>
                        </div>
                        <p class="small text-muted mb-0">Koneksi XMPP langsung server-ke-server yang ringan dan siap terhubung dengan seluruh software server pulsa di Indonesia.</p>
                    </div>
                </div>

                <!-- Spec 4: Webhook Callback -->
                <div class="col-lg-4 col-md-6">
                    <div class="spec-item-box">
                        <div class="d-flex align-items-center gap-2 mb-2 text-gold">
                            <i class="ti ti-bell-ringing fs-3"></i>
                            <h4 class="h5 mb-0 text-navy fw-bold">Webhook Callback</h4>
                        </div>
                        <p class="small text-muted mb-0">Update Serial Number (SN) dan status akhir dikirim otomatis ke URL server partner secara asinkron dengan auto-retry mechanism.</p>
                    </div>
                </div>

                <!-- Spec 5: Auto Failover -->
                <div class="col-lg-4 col-md-6">
                    <div class="spec-item-box">
                        <div class="d-flex align-items-center gap-2 mb-2 text-gold">
                            <i class="ti ti-arrows-shuffle fs-3"></i>
                            <h4 class="h5 mb-0 text-navy fw-bold">Smart Auto-Failover</h4>
                        </div>
                        <p class="small text-muted mb-0">Jika aggregator atau biller utama mengalami RTO/gangguan, sistem otomatis merutekan transaksi ke jalur cadangan prioritas.</p>
                    </div>
                </div>

                <!-- Spec 6: IP Whitelist & Dual-Auth -->
                <div class="col-lg-4 col-md-6">
                    <div class="spec-item-box">
                        <div class="d-flex align-items-center gap-2 mb-2 text-gold">
                            <i class="ti ti-shield-check fs-3"></i>
                            <h4 class="h5 mb-0 text-navy fw-bold">IP Whitelist &amp; Dual-Auth</h4>
                        </div>
                        <p class="small text-muted mb-0">Proteksi keamanan ketat dengan pembatasan IP publik server partner terdaftar serta verifikasi PIN &amp; API Key signature.</p>
                    </div>
                </div>
            </div>

            <!-- Compatibility Banner -->
            <div class="rounded-4 p-4 text-white d-flex flex-column flex-md-row align-items-center justify-content-between gap-3 shadow-sm" style="background: var(--nw-navy);">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center text-white" style="width: 48px; height: 48px; background: rgba(200, 155, 60, 0.2); flex-shrink: 0;">
                        <i class="ti ti-cpu fs-3 text-gold"></i>
                    </div>
                    <div>
                        <h4 class="h5 mb-1 text-white fw-bold">Kompatibel dengan Segala Software Server</h4>
                        <div class="text-white-50 small">Mendukung koneksi langsung IP Center (HTTP GET), REST API JSON, dan Jabber XMPP dari OtomaX, IRS, Flash Machine, Winstar, Tiger Engine, serta backend kustom (PHP/NodeJS/Python/Go).</div>
                    </div>
                </div>
                <button type="button" class="btn btn-primary text-nowrap px-4 py-2" data-bs-toggle="modal" data-bs-target="#modalDaftar">
                    <i class="ti ti-plug me-1"></i> Mulai Integrasi
                </button>
            </div>
        </div>
    </section>

    <!-- ============================================================
         FEATURES (6-CARD GRID FOCUSED ON H2H SERVER)
    ============================================================ -->
    <section class="section-py bg-white" id="features">
        <div class="container">
            <div class="row justify-content-center text-center section-heading-wrap">
                <div class="col-xl-7">
                    <span class="badge text-bg-secondary rounded-pill py-2 px-3 mb-2" style="background: var(--nw-navy) !important;">Keunggulan Server H2H</span>
                    <h2 class="display-6 mb-3">Fitur Lengkap Kemitraan Transaksi H2H {{ $siteTitle }}</h2>
                    <p class="text-muted">Kecepatan tinggi, stabilitas rute, dan transparansi laporan mutasi yang dirancang khusus untuk kelancaran transaksi antar server.</p>
                </div>
            </div>

            <div class="row g-4 g-lg-5">
                <!-- Feature 1 -->
                <div class="col-lg-4 col-md-6 feature-card">
                    <div class="card bg-white border-0 h-100 shadow-sm">
                        <div class="feature-icon-box">
                            <i class="ti ti-activity"></i>
                        </div>
                        <h3 class="h4 mb-2">Live Monitor Transaksi H2H</h3>
                        <p class="text-muted small mb-0">Pantau aliran transaksi Host-to-Host detik-ke-detik dengan status SN (Serial Number) dan respon callback instan yang otomatis tersinkronisasi.</p>
                    </div>
                </div>

                <!-- Feature 2 -->
                <div class="col-lg-4 col-md-6 feature-card">
                    <div class="card bg-white border-0 h-100 shadow-sm">
                        <div class="feature-icon-box">
                            <i class="ti ti-bolt"></i>
                        </div>
                        <h3 class="h4 mb-2">Throughput Tinggi &amp; Latensi Rendah</h3>
                        <p class="text-muted small mb-0">Didukung jalur dedicated yang siap menangani volume transaksi masif tanpa antrean panjang, memastikan rasio keberhasilan optimal.</p>
                    </div>
                </div>

                <!-- Feature 3 -->
                <div class="col-lg-4 col-md-6 feature-card">
                    <div class="card bg-white border-0 h-100 shadow-sm">
                        <div class="feature-icon-box">
                            <i class="ti ti-wallet"></i>
                        </div>
                        <h3 class="h4 mb-2">Buku Mutasi Saldo Transparan</h3>
                        <p class="text-muted small mb-0">Setiap rupiah penambahan deposit, pemotongan transaksi H2H, dan refund komplain tersusun rapi dengan saldo awal dan saldo akhir yang akurat.</p>
                    </div>
                </div>

                <!-- Feature 4 -->
                <div class="col-lg-4 col-md-6 feature-card">
                    <div class="card bg-white border-0 h-100 shadow-sm">
                        <div class="feature-icon-box">
                            <i class="ti ti-shield-lock"></i>
                        </div>
                        <h3 class="h4 mb-2">Keamanan IP Whitelist &amp; PIN</h3>
                        <p class="text-muted small mb-0">Sistem dilengkapi autentikasi ketat, pembatasan akses hanya melalui IP Address server yang terdaftar, dan proteksi enkripsi keamanan tinggi.</p>
                    </div>
                </div>

                <!-- Feature 5 -->
                <div class="col-lg-4 col-md-6 feature-card">
                    <div class="card bg-white border-0 h-100 shadow-sm">
                        <div class="feature-icon-box">
                            <i class="ti ti-brand-whatsapp"></i>
                        </div>
                        <h3 class="h4 mb-2">Aktivasi Cepat via WhatsApp</h3>
                        <p class="text-muted small mb-0">Hubungi langsung technical support kami via WhatsApp untuk pertukaran parameter integrasi server H2H tanpa prosedur birokrasi berbelit.</p>
                    </div>
                </div>

                <!-- Feature 6 -->
                <div class="col-lg-4 col-md-6 feature-card">
                    <div class="card bg-white border-0 h-100 shadow-sm">
                        <div class="feature-icon-box">
                            <i class="ti ti-file-spreadsheet"></i>
                        </div>
                        <h3 class="h4 mb-2">Rekon &amp; Export Laporan Excel</h3>
                        <p class="text-muted small mb-0">Kemudahan dalam melakukan audit dan rekonsiliasi data transaksi harian atau bulanan dengan fitur ekspor data ke format Excel (XLSX/CSV).</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================
         HOW IT WORKS (3 STEPS H2H INTEGRATION)
    ============================================================ -->
    <section class="section-py bg-light" id="howItWork">
        <div class="container">
            <div class="row justify-content-center text-center section-heading-wrap">
                <div class="col-xl-7">
                    <span class="badge text-bg-secondary rounded-pill py-2 px-3 mb-2" style="background: var(--nw-navy) !important;">Alur Integrasi</span>
                    <h2 class="display-6 mb-3">Mulai Integrasi H2H dalam 3 Langkah</h2>
                    <p class="text-muted">Proses aktivasi jalur kemitraan transaksi server Host-to-Host dirancang cepat, aman, dan langsung siap pakai.</p>
                </div>
            </div>

            <div class="row g-4 g-lg-5">
                <!-- Step 1 -->
                <div class="col-lg-4">
                    <div class="step-card-box">
                        <div class="step-number-badge">1</div>
                        <h3 class="h4 mb-3">Kirim Data Server Partner</h3>
                        <p class="text-muted small mb-0">
                            Hubungi tim support melalui WhatsApp untuk registrasi nama server, IP publik server yang digunakan, dan nomor kontak penanggung jawab teknis.
                        </p>
                    </div>
                </div>

                <!-- Step 2 -->
                <div class="col-lg-4">
                    <div class="step-card-box">
                        <div class="step-number-badge">2</div>
                        <h3 class="h4 mb-3">Whitelist IP &amp; Isi Deposit</h3>
                        <p class="text-muted small mb-0">
                            Kami memvalidasi IP Whitelist server Anda. Lakukan pengisian saldo deposit awal melalui sistem tiket transfer bank otomatis yang tersedia 24 jam.
                        </p>
                    </div>
                </div>

                <!-- Step 3 -->
                <div class="col-lg-4">
                    <div class="step-card-box">
                        <div class="step-number-badge">3</div>
                        <h3 class="h4 mb-3">Mulai Transaksi H2H &amp; Pantau</h3>
                        <p class="text-muted small mb-0">
                            Jalur H2H langsung aktif. Anda dapat memantau seluruh antrean, keberhasilan transaksi, mutasi saldo, dan statistik via Member Area Webreport.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================
         FAQ SECTION (H2H FOCUSED)
    ============================================================ -->
    <section class="section-py bg-white" id="faq">
        <div class="container">
            <div class="row justify-content-center text-center section-heading-wrap">
                <div class="col-xl-7">
                    <span class="badge text-bg-secondary rounded-pill py-2 px-3 mb-2" style="background: var(--nw-navy) !important;">Pertanyaan Umum</span>
                    <h2 class="display-6 mb-3">Tanya Jawab Seputar Transaksi H2H</h2>
                    <p class="text-muted">Pertanyaan yang sering diajukan mengenai kemitraan transaksi server Host-to-Host {{ $siteTitle }} dan fungsionalitas webreport.</p>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="accordion" id="accordionWebreport">
                        <!-- FAQ 1 -->
                        <div class="faq-item-box">
                            <h3 class="h5 mb-0">
                                <a href="#" class="text-reset d-flex justify-content-between align-items-center text-decoration-none" data-bs-toggle="collapse" data-bs-target="#faq1" aria-expanded="true">
                                    <span>Apa itu layanan transaksi server H2H {{ $siteTitle }}?</span>
                                    <i class="fas fa-chevron-down text-muted"></i>
                                </a>
                            </h3>
                            <div id="faq1" class="collapse show" data-bs-parent="#accordionWebreport">
                                <div class="mt-3 text-muted small pt-3 border-top lh-lg">
                                    Layanan transaksi H2H (Host-to-Host) adalah integrasi sistem-ke-sistem langsung antar server pulsa &amp; PPOB. Sistem Anda mengirim permintaan transaksi dan menerima status callback secara otomatis dengan kecepatan respons detik.
                                </div>
                            </div>
                        </div>

                        <!-- FAQ 2 -->
                        <div class="faq-item-box">
                            <h3 class="h5 mb-0">
                                <a href="#" class="text-reset d-flex justify-content-between align-items-center text-decoration-none collapsed" data-bs-toggle="collapse" data-bs-target="#faq2" aria-expanded="false">
                                    <span>Bagaimana cara mendaftar kemitraan server H2H?</span>
                                    <i class="fas fa-chevron-down text-muted"></i>
                                </a>
                            </h3>
                            <div id="faq2" class="collapse" data-bs-parent="#accordionWebreport">
                                <div class="mt-3 text-muted small pt-3 border-top lh-lg">
                                    Cukup klik tombol <strong>Daftar Mitra H2H</strong> pada halaman ini, isi nama server dan nomor kontak, lalu hubungi tim teknis kami via WhatsApp untuk pertukaran IP Whitelist dan pembuatan akun ID Partner.
                                </div>
                            </div>
                        </div>

                        <!-- FAQ 3 -->
                        <div class="faq-item-box">
                            <h3 class="h5 mb-0">
                                <a href="#" class="text-reset d-flex justify-content-between align-items-center text-decoration-none collapsed" data-bs-toggle="collapse" data-bs-target="#faq3" aria-expanded="false">
                                    <span>Apakah mendukung sistem keamanan IP Whitelist?</span>
                                    <i class="fas fa-chevron-down text-muted"></i>
                                </a>
                            </h3>
                            <div id="faq3" class="collapse" data-bs-parent="#accordionWebreport">
                                <div class="mt-3 text-muted small pt-3 border-top lh-lg">
                                    Ya, mutlak. Setiap transaksi H2H hanya akan dieksekusi jika berasal dari IP address publik server partner yang telah didaftarkan dan di-whitelist dalam sistem kami demi menjamin keamanan saldo dan transaksi.
                                </div>
                            </div>
                        </div>

                        <!-- FAQ 4 -->
                        <div class="faq-item-box">
                            <h3 class="h5 mb-0">
                                <a href="#" class="text-reset d-flex justify-content-between align-items-center text-decoration-none collapsed" data-bs-toggle="collapse" data-bs-target="#faq4" aria-expanded="false">
                                    <span>Format integrasi apa saja yang didukung oleh server {{ $siteTitle }}?</span>
                                    <i class="fas fa-chevron-down text-muted"></i>
                                </a>
                            </h3>
                            <div id="faq4" class="collapse" data-bs-parent="#accordionWebreport">
                                <div class="mt-3 text-muted small pt-3 border-top lh-lg">
                                    Server {{ $siteTitle }} mendukung integrasi via IP Center (HTTP GET), RESTful API dengan format JSON / HTTP POST, serta protokol Jabber/XMPP. Seluruh software server pulsa seperti OtomaX, IRS, Flash Machine, Winstar, maupun backend custom dapat terhubung dengan mudah.
                                </div>
                            </div>
                        </div>

                        <!-- FAQ 5 -->
                        <div class="faq-item-box">
                            <h3 class="h5 mb-0">
                                <a href="#" class="text-reset d-flex justify-content-between align-items-center text-decoration-none collapsed" data-bs-toggle="collapse" data-bs-target="#faq5" aria-expanded="false">
                                    <span>Bagaimana sistem pengisian saldo deposit H2H?</span>
                                    <i class="fas fa-chevron-down text-muted"></i>
                                </a>
                            </h3>
                            <div id="faq5" class="collapse" data-bs-parent="#accordionWebreport">
                                <div class="mt-3 text-muted small pt-3 border-top lh-lg">
                                    Anda dapat mengambil tiket deposit melalui menu Deposit di Member Area atau melalui Center Transaksi WhatsApp/Telegram. Transfer nominal sesuai dengan angka tiket unik yang tertera, dan saldo akan masuk secara otomatis dalam hitungan menit.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================
         CTA STRIP (NAVY BACKGROUND WITH GOLD BUTTON)
    ============================================================ -->
    <section class="section-py bg-light">
        <div class="container">
            <div class="p-5 rounded-5 text-white position-relative overflow-hidden" style="background: var(--nw-navy);">
                <div class="row align-items-center position-relative z-1 gy-4">
                    <div class="col-lg-8">
                        <span class="badge rounded-pill py-2 px-3 mb-3 fw-bold" style="background: var(--nw-gold); color: #ffffff;">Kemitraan Server H2H</span>
                        <h2 class="display-6 text-white mb-2">Tingkatkan Kecepatan &amp; Kapasitas Transaksi Server Anda</h2>
                        <p class="text-white-50 mb-0 lead fs-6">
                            Bergabunglah bersama mitra server di seluruh Indonesia dengan kehandalan infrastruktur H2H {{ $siteTitle }}.
                        </p>
                    </div>
                    <div class="col-lg-4 text-lg-end">
                        <div class="d-flex flex-column flex-sm-row gap-3 justify-content-lg-end">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalDaftar">
                                <i class="fab fa-whatsapp me-1"></i> Hubungi Dukungan H2H
                            </button>
                            <a href="{{ route('webreport.login') }}" class="btn btn-outline-light" style="border-radius: 50px;">
                                <i class="ti ti-login me-1"></i> Login Member
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================
         FOOTER (NAWASENA H2H THEME)
    ============================================================ -->
    <footer class="bg-navy-dark footer-section text-white-50" style="padding-top: 5rem; padding-bottom: 3.5rem;">
        <div class="container">
            <div class="row g-5">
                <!-- Col 1: Brand -->
                <div class="col-lg-4 col-12">
                    <a class="d-inline-flex gap-2.5 align-items-center text-decoration-none" href="{{ url('/') }}">
                        @if($favIcon)
                            <img src="{{ $favIcon }}" alt="{{ $siteTitle }} Logo" class="img-fluid" style="height: 40px; max-width: 140px; object-fit: contain;">
                        @else
                            <div class="rounded-3 d-flex align-items-center justify-content-center text-white" style="width: 38px; height: 38px; background: rgba(255,255,255,0.12);">
                                <i class="ti ti-server fs-4 text-warning"></i>
                            </div>
                            <div class="d-flex flex-column">
                                <span style="font-weight: 800; font-size: 1.35rem; color: #ffffff; letter-spacing: -0.02em;">{{ strtoupper($siteTitle) }}</span>
                                <span style="font-size: 0.72rem; letter-spacing: 0.18em; color: var(--nw-gold); font-weight: 700; text-transform: uppercase;">PRIMA DIGITAL</span>
                            </div>
                        @endif
                    </a>
                    <p class="mt-3 text-white-50 small">
                        {{ $siteSubtitle }}. Solusi infrastruktur transaksi Host-to-Host (H2H), monitoring arus transaksi, dan transparansi mutasi saldo server terpercaya di Indonesia.
                    </p>
                </div>

                <!-- Col 2: Fitur Utama -->
                <div class="col-lg-3 col-6">
                    <h4 class="text-white h5 mb-3">Layanan H2H</h4>
                    <ul class="list-unstyled lh-lg small">
                        <li><a href="#features" class="text-white-50 text-decoration-none">Jalur Transaksi H2H Cepat</a></li>
                        <li><a href="#techSpecs" class="text-white-50 text-decoration-none">REST API &amp; Jabber Gateway</a></li>
                        <li><a href="#features" class="text-white-50 text-decoration-none">Live Monitoring Trafik Server</a></li>
                        <li><a href="#features" class="text-white-50 text-decoration-none">Riwayat Mutasi Saldo Realtime</a></li>
                        <li><a href="#howItWork" class="text-white-50 text-decoration-none">Panduan Integrasi H2H</a></li>
                    </ul>
                </div>

                <!-- Col 3: Navigasi Cepat -->
                <div class="col-lg-2 col-6">
                    <h4 class="text-white h5 mb-3">Navigasi</h4>
                    <ul class="list-unstyled lh-lg small">
                        <li><a href="{{ url('/') }}#features" class="text-white-50 text-decoration-none">Fitur H2H</a></li>
                        <li><a href="{{ url('/') }}#techSpecs" class="text-white-50 text-decoration-none">Spesifikasi Protokol</a></li>
                        <li><a href="{{ url('/') }}#howItWork" class="text-white-50 text-decoration-none">Alur Integrasi</a></li>
                        <li><a href="{{ url('/') }}#faq" class="text-white-50 text-decoration-none">Tanya Jawab</a></li>
                        <li><a href="{{ route('webreport.login') }}" class="text-white-50 text-decoration-none">Login Mitra</a></li>
                        <li><a href="#" data-bs-toggle="modal" data-bs-target="#modalDaftar" class="text-white-50 text-decoration-none">Daftar Mitra H2H</a></li>
                    </ul>
                </div>

                <!-- Col 4: Layanan Kontak -->
                <div class="col-lg-3 col-12">
                    <h4 class="text-white h5 mb-3">Dukungan Teknis</h4>
                    <ul class="list-unstyled lh-lg small">
                        <li class="d-flex align-items-center gap-2 mb-3">
                            <i class="ti ti-brand-whatsapp text-gold fs-5"></i> 
                            <a href="{{ $directWaLink }}" target="_blank" rel="noopener" class="text-white-50 text-decoration-none">
                                Hotline H2H ({{ $cleanWa ?: 'WhatsApp Official' }})
                            </a>
                        </li>
                        <li class="d-flex align-items-center gap-2 mb-3">
                            <i class="ti ti-headset text-gold fs-5"></i> 
                            <a href="{{ $csWaLink }}" target="_blank" rel="noopener" class="text-white-50 text-decoration-none">
                                Layanan Customer Service
                            </a>
                        </li>
                        <li class="d-flex align-items-center gap-2 mb-2">
                            <i class="ti ti-clock text-gold fs-5"></i> 
                            <span class="text-white-50">Layanan Otomasi: 24 Jam Nonstop</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="border-top py-4 mt-5 small border-opacity-10 border-white text-white-50 d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
                <p class="mb-0">&copy; {{ date('Y') }} {{ $siteTitle }}. Seluruh hak cipta dilindungi undang-undang.</p>
                <div class="d-flex gap-3">
                    <a href="{{ route('webreport.login') }}" class="text-white-50 text-decoration-none">Member Area</a>
                    <span>&bull;</span>
                    <a href="{{ $csWaLink }}" target="_blank" rel="noopener" class="text-white-50 text-decoration-none">Pusat Bantuan</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- ============================================================
         MODAL DAFTAR MITRA (DIRECT WHATSAPP - ZERO OTP, ZERO DB)
    ============================================================ -->
    <div class="modal fade" id="modalDaftar" tabindex="-1" aria-labelledby="modalDaftarLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 24px; overflow: hidden;">
                <div class="modal-header border-0 pb-0 pt-4 px-4 text-white" style="background: var(--nw-navy);">
                    <div>
                        <span class="badge rounded-pill px-3 py-1 mb-2 fw-semibold" style="background: var(--nw-gold); color: #ffffff;">Kemitraan Server H2H</span>
                        <h4 class="modal-title text-white fw-bold" id="modalDaftarLabel">Pendaftaran Mitra Server H2H</h4>
                        <p class="text-white-50 small mb-3">Isi formulir singkat untuk langsung terhubung ke tim integrasi teknis WhatsApp {{ $siteTitle }}.</p>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 bg-white">
                    <form id="formDaftarWa" onsubmit="handleDirectWaSubmit(event)">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark">Nama Penanggung Jawab / Pemilik <span class="text-danger">*</span></label>
                            <input type="text" id="wa_nama" class="form-control rounded-3 py-2 px-3" placeholder="Contoh: Budi Santoso" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark">Nomor WhatsApp Aktif <span class="text-danger">*</span></label>
                            <input type="tel" id="wa_telepon" class="form-control rounded-3 py-2 px-3" placeholder="Contoh: 081234567890" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark">Nama Brand / Server Pulsa <span class="text-danger">*</span></label>
                            <input type="text" id="wa_konter" class="form-control rounded-3 py-2 px-3" placeholder="Contoh: Berkah Reload Server" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-dark">Kota / Domisili Server <span class="text-danger">*</span></label>
                            <input type="text" id="wa_kota" class="form-control rounded-3 py-2 px-3" placeholder="Contoh: Jakarta Selatan" required>
                        </div>

                        <div class="p-3 rounded-3 mb-4" style="background: var(--nw-gold-soft); border: 1px dashed var(--nw-gold);">
                            <div class="d-flex align-items-center gap-2 small text-dark">
                                <i class="fas fa-info-circle text-gold fs-5"></i>
                                <span>Pendaftaran tidak membutuhkan kode OTP. Begitu diklik, Anda akan langsung terhubung ke Technical Support H2H kami.</span>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2.5 fs-6 fw-bold d-flex align-items-center justify-content-center gap-2" style="border-radius: 50px;">
                            <i class="fab fa-whatsapp fs-5"></i> Hubungi Integrasi via WhatsApp
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================================
         FLOATING WHATSAPP BUTTON
    ============================================================ -->
    <a href="{{ $directWaLink }}" target="_blank" rel="noopener" class="floating-wa-btn" title="Chat WhatsApp Customer Service">
        <i class="fab fa-whatsapp"></i>
    </a>

    <!-- Bootstrap 5.3 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function handleDirectWaSubmit(e) {
            e.preventDefault();
            const nama = document.getElementById('wa_nama').value.trim();
            const telepon = document.getElementById('wa_telepon').value.trim();
            const konter = document.getElementById('wa_konter').value.trim();
            const kota = document.getElementById('wa_kota').value.trim();

            if (!nama || !telepon || !konter || !kota) {
                alert('Silakan lengkapi seluruh formulir pendaftaran.');
                return;
            }

            const targetWa = '{{ $defaultWaTarget }}';
            const siteTitle = '{{ $siteTitle }}';

            const message = `Halo Tim H2H ${siteTitle},\n\nSaya ingin mengajukan integrasi kemitraan transaksi server H2H:\n` +
                            `• Nama PIC: ${nama}\n` +
                            `• No. WhatsApp: ${telepon}\n` +
                            `• Nama Server: ${konter}\n` +
                            `• Kota/Domisili: ${kota}\n\n` +
                            `Mohon dibantu untuk proses pertukaran IP Whitelist dan panduan integrasi H2H. Terima kasih!`;

            const waUrl = `https://wa.me/${targetWa}?text=${encodeURIComponent(message)}`;
            
            // Tutup modal
            const modalEl = document.getElementById('modalDaftar');
            const modalInstance = bootstrap.Modal.getInstance(modalEl);
            if (modalInstance) {
                modalInstance.hide();
            }

            // Buka WhatsApp di tab baru
            window.open(waUrl, '_blank');
        }
    </script>
</body>
</html>
