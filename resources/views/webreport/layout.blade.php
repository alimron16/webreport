<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - OtomaX Webreport</title>

    <!-- Fonts: Inter (body) + Outfit (headings) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            /* Blue primary accent + Red secondary highlights */
            --color-purple:      #2563eb;   /* Blue-600 */
            --color-purple-dark: #1d4ed8;   /* Blue-700 */
            --color-purple-light:#eff6ff;   /* Blue-50 */
            --color-bg:          #f3f4f6;   /* Gray-100 (Laravel Breeze Default) */
            --color-white:       #ffffff;
            --color-border:      #e5e7eb;   /* Gray-200 */
            --color-border-dark: #d1d5db;   /* Gray-300 */
            --color-text:        #1f2937;   /* Gray-800 */
            --color-text-2:      #4b5563;   /* Gray-600 */
            --color-text-3:      #6b7280;   /* Gray-500 */
            --color-text-4:      #9ca3af;   /* Gray-400 */
            --color-success:     #059669;
            --color-success-bg:  #ecfdf5;
            --color-success-bd:  #a7f3d0;
            --color-danger:      #e11d48;   /* Red-600 */
            --color-danger-bg:   #fff1f2;   /* Red-50 */
            --color-danger-bd:   #fecdd3;   /* Red-200 */
            --color-warning:     #d97706;
            --color-info:        #0891b2;
            --sidebar-w:         210px;
            --topbar-h:          64px;
            --radius:            8px;
            --shadow-sm:         0 1px 2px 0 rgba(0,0,0,.05);
            --shadow:            0 1px 3px 0 rgba(0,0,0,.1), 0 1px 2px -1px rgba(0,0,0,.06);
            --shadow-md:         0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1);
            --transition:        150ms cubic-bezier(0.4,0,0.2,1);
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html, body { height: 100%; }

        body {
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            line-height: 1.5;
            color: var(--color-text);
            background: var(--color-bg);
            display: flex;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* ── SIDEBAR (Breeze Sidebar Layout) ──────────────────── */
        .sidebar {
            width: var(--sidebar-w);
            background: var(--color-white);
            border-right: 1px solid var(--color-border);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0; bottom: 0; left: 0;
            z-index: 100;
            transition: transform var(--transition);
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 0 1.5rem;
            height: var(--topbar-h);
            border-bottom: 1px solid var(--color-border);
            text-decoration: none;
            flex-shrink: 0;
        }

        .brand-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: var(--color-danger);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            font-size: 1.1rem;
            color: #fff;
            box-shadow: 0 2px 8px rgba(225, 29, 72, 0.2);
        }

        .brand-name {
            font-family: 'Outfit', sans-serif;
            font-size: 1.15rem;
            font-weight: 800;
            color: #111827;
            letter-spacing: -0.3px;
        }

        .sidebar-nav {
            padding: 1.25rem 0.75rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 12px;
            border-radius: var(--radius);
            color: var(--color-text-2);
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 500;
            transition: all var(--transition);
        }

        .nav-link svg {
            width: 16px;
            height: 16px;
            color: var(--color-text-3);
            transition: all var(--transition);
        }

        .nav-link:hover {
            background: #f3f4f6;
            color: var(--color-text);
        }

        .nav-link.active {
            background: var(--color-purple-light);
            color: var(--color-purple);
            font-weight: 600;
        }

        .nav-link.active svg {
            color: var(--color-purple);
        }

        /* Sidebar Footer */
        .sidebar-footer {
            border-top: 1px solid var(--color-border);
            padding: 1rem 0.75rem;
            flex-shrink: 0;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
            padding: 0 6px;
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: var(--color-purple-light);
            color: var(--color-purple);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 700;
        }

        .user-details {
            overflow: hidden;
        }

        .user-name {
            font-size: 13px;
            font-weight: 600;
            color: var(--color-text);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .user-role {
            font-size: 11px;
            color: var(--color-text-3);
        }

        .btn-logout-sidebar {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            width: 100%;
            padding: 8px;
            background: #ffffff;
            border: 1px solid var(--color-border);
            border-radius: var(--radius);
            color: var(--color-text-2);
            font-size: 12.5px;
            font-weight: 600;
            cursor: pointer;
            transition: all var(--transition);
        }

        .btn-logout-sidebar:hover {
            background: var(--color-danger-bg);
            color: var(--color-danger);
            border-color: var(--color-danger-bd);
        }

        .btn-logout-sidebar svg {
            width: 14px;
            height: 14px;
        }

        /* ── MAIN WRAPPER ─────────────────────────────────────── */
        .main-wrapper {
            margin-left: var(--sidebar-w);
            flex-grow: 1;
            min-width: 0; /* Critical for dynamic charts & responsive grids */
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            transition: margin-left var(--transition);
        }

        /* Collapsed Sidebar State */
        body.sidebar-hidden .sidebar {
            transform: translateX(-100%);
        }
        body.sidebar-hidden .main-wrapper {
            margin-left: 0;
        }

        .btn-toggle-sidebar {
            background: none;
            border: none;
            cursor: pointer;
            color: var(--color-text-3);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            padding: 6px;
            border-radius: 6px;
            transition: var(--transition);
        }

        .btn-toggle-sidebar:hover {
            background: #e5e7eb;
            color: var(--color-text);
        }

        /* Page Header */
        .page-header {
            height: 44px;
            background: var(--color-white);
            border-bottom: 1px solid var(--color-border);
            display: flex;
            align-items: center;
            padding: 0 2rem;
            position: sticky;
            top: 0;
            z-index: 90;
        }

        .page-header-title {
            font-family: 'Inter', sans-serif;
            font-size: 13px;
            font-weight: 400;
            color: var(--color-text-3);
            letter-spacing: 0;
        }

        /* Content Area */
        .main-content {
            padding: 2rem;
            flex-grow: 1;
        }

        /* ── ALERTS ──────────────────────────────────────────── */
        .alert {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            border-radius: var(--radius);
            font-size: 13.5px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            animation: fadeUp 0.3s ease;
        }

        .alert-success {
            background: var(--color-success-bg);
            color: var(--color-success);
            border-color: var(--color-success-bd);
        }

        .alert-danger {
            background: var(--color-danger-bg);
            color: var(--color-danger);
            border-color: var(--color-danger-bd);
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(-6px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ── CARDS (Tailwind Breeze Style) ────────────────────── */
        .card {
            background: var(--color-white);
            border: 1px solid var(--color-border);
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
        }

        .card-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--color-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .card-title {
            font-family: 'Outfit', sans-serif;
            font-size: 14.5px;
            font-weight: 700;
            color: var(--color-text);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .card-title svg {
            width: 16px;
            height: 16px;
            color: var(--color-purple);
        }

        .card-body {
            padding: 20px;
        }

        /* ── FORMS ───────────────────────────────────────────── */
        .form-control, select.form-control {
            display: block;
            width: 100%;
            padding: 8px 12px;
            font-size: 13.5px;
            font-family: 'Inter', sans-serif;
            color: var(--color-text);
            background: var(--color-white);
            border: 1px solid var(--color-border-dark);
            border-radius: var(--radius);
            outline: none;
            transition: border-color var(--transition), box-shadow var(--transition);
        }

        .form-control:focus {
            border-color: var(--color-purple);
            box-shadow: 0 0 0 1px var(--color-purple);
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: var(--color-text-2);
            margin-bottom: 5px;
        }

        /* ── BUTTONS ─────────────────────────────────────────── */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 8px 14px;
            border-radius: var(--radius);
            font-size: 13px;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            border: 1px solid transparent;
            transition: all var(--transition);
            text-decoration: none;
            white-space: nowrap;
        }

        .btn svg { width: 14px; height: 14px; }

        .btn-primary {
            background: var(--color-purple);
            color: #fff;
            border-color: var(--color-purple);
        }

        .btn-primary:hover { background: var(--color-purple-dark); }

        .btn-success {
            background: var(--color-success);
            color: #fff;
            border-color: var(--color-success);
        }

        .btn-success:hover { background: #047857; }

        .btn-danger {
            background: var(--color-danger);
            color: #fff;
            border-color: var(--color-danger);
        }

        .btn-danger:hover { background: #b91c1c; }

        .btn-secondary {
            background: var(--color-white);
            color: var(--color-text-2);
            border-color: var(--color-border-dark);
        }

        .btn-secondary:hover { background: #f3f4f6; }

        .btn-sm { padding: 5px 10px; font-size: 12px; }

        /* ── TABLES ──────────────────────────────────────────── */
        .table-wrapper {
            overflow-x: auto;
            border-radius: var(--radius);
            border: 1px solid var(--color-border);
        }

        table.ak-table {
            width: 100%;
            border-collapse: collapse;
            background: var(--color-white);
            font-size: 13.5px;
        }

        table.ak-table thead th {
            background: #f9fafb;
            color: var(--color-text-3);
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            padding: 12px 16px;
            text-align: left;
            border-bottom: 1px solid var(--color-border);
            white-space: nowrap;
        }

        table.ak-table tbody tr {
            border-bottom: 1px solid var(--color-border);
            transition: background var(--transition);
        }

        table.ak-table tbody tr:last-child { border-bottom: none; }

        table.ak-table tbody tr:hover { background: #fcfcfc; }

        table.ak-table tbody td {
            padding: 12px 16px;
            color: var(--color-text-2);
            vertical-align: middle;
        }

        /* Badges */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 8px;
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 600;
        }

        .badge-success { background: var(--color-success-bg); color: var(--color-success); }
        .badge-danger  { background: var(--color-danger-bg);  color: var(--color-danger); }
        .badge-warning { background: #fffbeb; color: var(--color-warning); }
        .badge-info    { background: #ecfeff; color: var(--color-info); }
        .badge-gray    { background: #f3f4f6; color: var(--color-text-3); }

        .sidebar-overlay {
            display: none;
            position: fixed; top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.4);
            z-index: 90;
            backdrop-filter: blur(2px);
        }

        /* Responsive Mobile menu */
        @media (max-width: 768px) {
            .sidebar { 
                transform: translateX(-100%); 
                box-shadow: none;
            }
            .main-wrapper { margin-left: 0; width: 100%; }
            .main-content { padding: 1rem; }
            
            body.sidebar-mobile-open .sidebar {
                transform: translateX(0);
                box-shadow: var(--shadow-md);
            }
            body.sidebar-mobile-open .sidebar-overlay {
                display: block;
            }
            body.sidebar-mobile-open {
                overflow: hidden;
            }
        }
    </style>
    @yield('extra-styles')
</head>
<body>
    <script>
        if (localStorage.getItem('sidebar-hidden') === '1') {
            document.body.classList.add('sidebar-hidden');
        }
    </script>

    <!-- SIDEBAR NAVIGATION (Laravel Breeze Left Sidebar style) -->
    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>
    <aside class="sidebar">
        <!-- Reseller Code Header -->
        <div class="sidebar-brand" style="justify-content: center; font-weight: 600; color: var(--color-text); font-size: 0.95rem; letter-spacing: 0.5px;">
            {{ strtoupper($active_reseller_code ?? 'MEMBER BARU') }}
        </div>

        <!-- Navigation Links -->
        <nav class="sidebar-nav">
            <a href="{{ route('webreport.dashboard') }}" 
               class="nav-link {{ ($active_tab ?? '') === 'dashboard' ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Dashboard
            </a>
            <a href="{{ route('webreport.transactions') }}" 
               class="nav-link {{ ($active_tab ?? '') === 'transactions' ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                History Transaksi
            </a>
            <a href="{{ route('webreport.deposit') }}" 
               class="nav-link {{ ($active_tab ?? '') === 'deposit' ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Isi Saldo / Mutasi
            </a>
            <a href="{{ route('webreport.prices') }}" 
               class="nav-link {{ ($active_tab ?? '') === 'prices' ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/>
                </svg>
                Daftar Harga
            </a>
            <a href="{{ route('webreport.settings') }}" 
               class="nav-link {{ ($active_tab ?? '') === 'settings' ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Pengaturan
            </a>
        </nav>

        <!-- Sidebar User Profile Info & Log out -->
        <div class="sidebar-footer">
            <div class="user-info">
                <div class="user-avatar">
                    {{ strtoupper(substr($active_reseller_code ?? 'ME', 0, 2)) }}
                </div>
                <div class="user-details">
                    <div class="user-name">{{ strtoupper($active_reseller_code ?? 'MEMBER BARU') }}</div>
                    <div class="user-role">{{ isset($active_reseller_code) ? 'Reseller' : 'Pending' }}</div>
                </div>
            </div>
            <form action="{{ route('webreport.logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn-logout-sidebar">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Log Out
                </button>
            </form>
        </div>
    </aside>

    <!-- MAIN SECTION -->
    <div class="main-wrapper">
        <!-- PAGE HEADER -->
        <header class="page-header">
            <button type="button" class="btn-toggle-sidebar" onclick="toggleSidebar()">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
            <h1 class="page-header-title">@yield('page-header')</h1>
        </header>

        <!-- MAIN CONTENT AREA -->
        <main class="main-content">
            @if(session('success'))
                <div class="alert alert-success">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <script>
        function toggleSidebar() {
            if (window.innerWidth <= 768) {
                document.body.classList.toggle('sidebar-mobile-open');
            } else {
                document.body.classList.toggle('sidebar-hidden');
                const isHidden = document.body.classList.contains('sidebar-hidden');
                localStorage.setItem('sidebar-hidden', isHidden ? '1' : '0');
            }
        }
    </script>
    @yield('extra-scripts')
</body>
</html>
