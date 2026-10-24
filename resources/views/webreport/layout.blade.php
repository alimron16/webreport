<!DOCTYPE html>
<html lang="id" class="light-style" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
    <title>@yield('title', 'Webreport') - {{ $portal_site_title ?? \Illuminate\Support\Facades\Cache::get('site_title', 'OtomaX') }}</title>
    @php $memberFavicon = $portal_logo_url ?? \Illuminate\Support\Facades\Cache::get('site_logo_url'); @endphp
    @if($memberFavicon)
    <link rel="icon" type="image/x-icon" href="{{ $memberFavicon }}">
    <link rel="shortcut icon" href="{{ $memberFavicon }}">
    @endif

    <script>
        (function() {
            const savedTheme = localStorage.getItem('webreport_theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            if (savedTheme === 'dark') {
                document.documentElement.setAttribute('data-bs-theme', 'dark');
                document.documentElement.classList.add('dark-style');
                document.documentElement.classList.remove('light-style');
            } else {
                document.documentElement.setAttribute('data-bs-theme', 'light');
                document.documentElement.classList.add('light-style');
                document.documentElement.classList.remove('dark-style');
            }
            if (window.innerWidth >= 1200 && localStorage.getItem('webreport_sidebar_collapsed') === 'true') {
                document.addEventListener('DOMContentLoaded', function() {
                    document.body.classList.add('layout-menu-collapsed');
                });
            }
        })();
    </script>

    <!-- Clean Typography: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Icons: Boxicons -->
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Clean White Fintech Design Tokens & Core Styles -->
    <style>
        :root {
            --bs-primary: #0B6FB6;
            --bs-primary-rgb: 11, 111, 182;
            --bs-secondary: #0A2540;
            --bs-secondary-rgb: 10, 37, 64;
            --bs-success: #10b981;
            --bs-danger: #DE3638;
            --bs-danger-rgb: 222, 54, 56;
            --bs-warning: #f59e0b;
            --bs-info: #1C8CFB;
            --bs-info-rgb: 28, 140, 251;
            --bs-dark: #0f172a;
            --bs-body-bg: #f8fafc;
            --bs-body-color: #334155;
            --bs-border-color: #e2e8f0;
            --theme-heading: #0f172a;
            --theme-muted: #64748b;
            --theme-card-bg: #ffffff;
            --theme-card-border: #e2e8f0;
            --theme-sidebar-width: 250px;
            --theme-card-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05), 0 1px 2px -1px rgba(0, 0, 0, 0.05);
            --theme-radius: 8px;
            /* Saudagar Host Brand Accents */
            --sh-blue: #0B6FB6;
            --sh-sky: #1C8CFB;
            --sh-red: #DE3638;
            --sh-navy: #0A2540;
            --sh-gradient: linear-gradient(135deg, #0B6FB6 0%, #1C8CFB 100%);
        }

        /* ─── DARK THEME OVERRIDES ──────────────────────── */
        html[data-bs-theme="dark"], html.dark-style {
            --bs-body-bg: #0b0f19;
            --bs-body-color: #cbd5e1;
            --bs-border-color: #1e293b;
            --theme-heading: #f8fafc;
            --theme-muted: #94a3b8;
            --theme-card-bg: #111827;
            --theme-card-border: #1f2937;
            --theme-card-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.4);
        }

        html[data-bs-theme="dark"] body {
            background-color: #0b0f19 !important;
            color: #cbd5e1 !important;
        }

        html[data-bs-theme="dark"] .layout-menu {
            background: #111827 !important;
            border-right-color: #1f2937 !important;
        }

        html[data-bs-theme="dark"] .app-brand {
            background: #111827 !important;
            border-bottom-color: #1f2937 !important;
        }

        html[data-bs-theme="dark"] .app-brand-text {
            color: #f8fafc !important;
        }

        html[data-bs-theme="dark"] .app-brand-badge {
            background: #1e293b !important;
            color: #94a3b8 !important;
            border-color: #334155 !important;
        }

        html[data-bs-theme="dark"] .menu-link {
            color: #94a3b8 !important;
        }

        html[data-bs-theme="dark"] .menu-link:hover {
            background-color: #1e293b !important;
            color: #f8fafc !important;
        }

        html[data-bs-theme="dark"] .menu-link:hover i.menu-icon {
            color: #60a5fa !important;
        }

        html[data-bs-theme="dark"] .menu-item.active > .menu-link {
            background: #1e293b !important;
            color: #60a5fa !important;
            border-left-color: #3b82f6 !important;
        }

        html[data-bs-theme="dark"] .menu-item.active > .menu-link i.menu-icon {
            color: #60a5fa !important;
        }

        html[data-bs-theme="dark"] .menu-footer {
            background: #111827 !important;
            border-top-color: #1f2937 !important;
        }

        html[data-bs-theme="dark"] .avatar-initial {
            background: #1e293b !important;
            color: #f8fafc !important;
            border-color: #334155 !important;
        }

        html[data-bs-theme="dark"] .user-preview-name {
            color: #f8fafc !important;
        }

        html[data-bs-theme="dark"] .layout-page {
            background: #0b0f19 !important;
        }

        html[data-bs-theme="dark"] .layout-navbar {
            background: #111827 !important;
            border-bottom-color: #1f2937 !important;
        }

        html[data-bs-theme="dark"] .layout-menu-toggle {
            color: #f8fafc !important;
            border-color: #334155 !important;
            background: #1e293b !important;
        }

        html[data-bs-theme="dark"] .navbar-page-title {
            color: #f8fafc !important;
        }

        html[data-bs-theme="dark"] .card {
            background: #111827 !important;
            border-color: #1f2937 !important;
        }

        html[data-bs-theme="dark"] .card-header {
            background: #111827 !important;
            border-bottom-color: #1f2937 !important;
            color: #f8fafc !important;
        }

        html[data-bs-theme="dark"] .card-footer {
            background-color: #111827 !important;
            border-top-color: #1f2937 !important;
            color: #94a3b8 !important;
        }

        html[data-bs-theme="dark"] .card-title {
            color: #f8fafc !important;
        }

        html[data-bs-theme="dark"] .table {
            color: #cbd5e1 !important;
            border-color: #1f2937 !important;
        }

        html[data-bs-theme="dark"] .table thead th {
            background-color: #172033 !important;
            color: #94a3b8 !important;
            border-bottom-color: #1f2937 !important;
        }

        html[data-bs-theme="dark"] .table tbody td {
            border-bottom-color: #1e293b !important;
            color: #cbd5e1 !important;
        }

        html[data-bs-theme="dark"] .table-hover tbody tr:hover {
            background-color: #1e293b !important;
        }

        html[data-bs-theme="dark"] .form-control, 
        html[data-bs-theme="dark"] .form-select {
            background-color: #1e293b !important;
            border-color: #334155 !important;
            color: #f8fafc !important;
        }

        html[data-bs-theme="dark"] .form-control:focus, 
        html[data-bs-theme="dark"] .form-select:focus {
            border-color: #3b82f6 !important;
            background-color: #1e293b !important;
            color: #f8fafc !important;
        }

        html[data-bs-theme="dark"] .input-group-text {
            background-color: #1e293b !important;
            border-color: #334155 !important;
            color: #94a3b8 !important;
        }

        html[data-bs-theme="dark"] .bg-light {
            background-color: #1e293b !important;
            color: #f8fafc !important;
        }

        html[data-bs-theme="dark"] .bg-white {
            background-color: #111827 !important;
            color: #cbd5e1 !important;
        }

        html[data-bs-theme="dark"] .text-dark {
            color: #f8fafc !important;
        }

        html[data-bs-theme="dark"] .text-muted {
            color: #94a3b8 !important;
        }

        html[data-bs-theme="dark"] .border {
            border-color: #1f2937 !important;
        }

        html[data-bs-theme="dark"] .border-bottom {
            border-bottom-color: #1f2937 !important;
        }

        html[data-bs-theme="dark"] .border-top {
            border-top-color: #1f2937 !important;
        }

        html[data-bs-theme="dark"] .list-group-item {
            background-color: #111827 !important;
            border-color: #1f2937 !important;
            color: #cbd5e1 !important;
        }

        html[data-bs-theme="dark"] .btn-outline-secondary {
            border-color: #334155 !important;
            color: #cbd5e1 !important;
            background: #1e293b !important;
        }

        html[data-bs-theme="dark"] .btn-outline-secondary:hover {
            background: #334155 !important;
            color: #ffffff !important;
        }

        html[data-bs-theme="dark"] .btn-outline-secondary:disabled,
        html[data-bs-theme="dark"] .btn-outline-secondary.disabled {
            background-color: #161e2e !important;
            border-color: #1f2937 !important;
            color: #475569 !important;
            opacity: 0.6;
        }

        html[data-bs-theme="dark"] .bg-label-primary {
            background-color: rgba(37, 99, 235, 0.18) !important;
            color: #93c5fd !important;
            border-color: rgba(37, 99, 235, 0.3) !important;
        }

        html[data-bs-theme="dark"] .bg-label-success {
            background-color: rgba(16, 185, 129, 0.18) !important;
            color: #6ee7b7 !important;
            border-color: rgba(16, 185, 129, 0.3) !important;
        }

        html[data-bs-theme="dark"] .bg-label-danger {
            background-color: rgba(239, 68, 68, 0.18) !important;
            color: #fca5a5 !important;
            border-color: rgba(239, 68, 68, 0.3) !important;
        }

        html[data-bs-theme="dark"] .bg-label-warning {
            background-color: rgba(245, 158, 11, 0.18) !important;
            color: #fcd34d !important;
            border-color: rgba(245, 158, 11, 0.3) !important;
        }

        html[data-bs-theme="dark"] .bg-label-secondary {
            background-color: #1e293b !important;
            color: #94a3b8 !important;
            border-color: #334155 !important;
        }

        html[data-bs-theme="dark"] .alert-success {
            background-color: rgba(16, 185, 129, 0.15) !important;
            border-color: rgba(16, 185, 129, 0.3) !important;
            color: #6ee7b7 !important;
        }

        html[data-bs-theme="dark"] .alert-danger {
            background-color: rgba(239, 68, 68, 0.15) !important;
            border-color: rgba(239, 68, 68, 0.3) !important;
            color: #fca5a5 !important;
        }

        html[data-bs-theme="dark"] .modal-content {
            background-color: #111827 !important;
            border-color: #1f2937 !important;
            color: #cbd5e1 !important;
        }

        html[data-bs-theme="dark"] .modal-header,
        html[data-bs-theme="dark"] .modal-footer {
            border-color: #1f2937 !important;
        }

        html[data-bs-theme="dark"] .dropdown-menu {
            background-color: #111827 !important;
            border-color: #1f2937 !important;
            color: #cbd5e1 !important;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.5) !important;
        }

        html[data-bs-theme="dark"] .dropdown-item {
            color: #cbd5e1 !important;
        }

        html[data-bs-theme="dark"] .dropdown-item:hover,
        html[data-bs-theme="dark"] .dropdown-item:focus {
            background-color: #1e293b !important;
            color: #f8fafc !important;
        }

        html[data-bs-theme="dark"] .page-link {
            background-color: #1e293b !important;
            border-color: #334155 !important;
            color: #cbd5e1 !important;
        }

        html[data-bs-theme="dark"] .page-item.active .page-link {
            background-color: #0B6FB6 !important;
            border-color: #0B6FB6 !important;
            color: #ffffff !important;
        }

        html[data-bs-theme="dark"] .page-item.disabled .page-link {
            background-color: #0f172a !important;
            border-color: #1e293b !important;
            color: #64748b !important;
        }

        html[data-bs-theme="dark"] code,
        html[data-bs-theme="dark"] pre {
            background-color: #0b0f19 !important;
            color: #38bdf8 !important;
            border-color: #1e293b !important;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: var(--bs-body-bg);
            color: var(--bs-body-color);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            overflow-x: hidden;
            font-size: 0.875rem;
            letter-spacing: -0.01em;
        }

        h1, h2, h3, h4, h5, h6, .h1, .h2, .h3, .h4, .h5, .h6 {
            color: var(--theme-heading);
            font-weight: 600;
            letter-spacing: -0.02em;
        }

        /* ─── LAYOUT WRAPPER ────────────────────────────── */
        .layout-wrapper {
            display: flex;
            min-height: 100vh;
            width: 100%;
        }

        /* ─── PURE WHITE SIDEBAR ────────────────────────── */
        .layout-menu {
            width: var(--theme-sidebar-width);
            background: #ffffff;
            border-right: 1px solid var(--theme-card-border);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 1040;
            transition: transform 0.2s ease-in-out;
        }

        .app-brand {
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 60px;
            padding: 0 1.25rem;
            flex-shrink: 0;
            border-bottom: 1px solid var(--theme-card-border);
            background: #ffffff;
        }

        .app-brand-link {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        .app-brand-logo {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            background: #0f172a;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
        }

        .app-brand-text {
            font-size: 1.1rem;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: -0.02em;
        }

        .app-brand-badge {
            font-size: 0.65rem;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 4px;
            background: #f1f5f9;
            color: #475569;
            text-transform: uppercase;
            border: 1px solid #e2e8f0;
        }

        /* Menu Items */
        .menu-inner-wrapper {
            flex-grow: 1;
            overflow-y: auto;
            padding: 0.75rem 0.5rem;
        }

        .menu-header {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--theme-muted);
            font-weight: 700;
            padding: 0.75rem 0.75rem 0.35rem;
            margin: 0;
        }

        .menu-item {
            list-style: none;
            margin: 0 0 2px 0;
        }

        .menu-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.55rem 0.75rem;
            border-radius: 6px;
            color: #475569;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.12s ease-in-out;
            border-left: 3px solid transparent;
        }

        .menu-link i.menu-icon {
            font-size: 1.15rem;
            color: #64748b;
            transition: all 0.12s ease-in-out;
        }

        .menu-link:hover {
            background-color: #f8fafc;
            color: #0f172a;
        }

        .menu-link:hover i.menu-icon {
            color: #0f172a;
        }

        .menu-item.active > .menu-link {
            background: #f1f5f9 !important;
            color: #0f172a !important;
            font-weight: 600;
            border-left-color: #0B6FB6 !important;
        }

        .menu-item.active > .menu-link i.menu-icon {
            color: #0B6FB6 !important;
        }

        /* Sidebar Footer / User Info */
        .menu-footer {
            border-top: 1px solid var(--theme-card-border);
            padding: 0.85rem 1rem;
            background: #ffffff;
            flex-shrink: 0;
        }

        .user-preview {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 0.65rem;
        }

        .avatar-initial {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            background: #f1f5f9;
            color: #0f172a;
            border: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.8rem;
            flex-shrink: 0;
        }

        .user-preview-name {
            font-size: 0.825rem;
            font-weight: 600;
            color: #0f172a;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .user-preview-role {
            font-size: 0.72rem;
            color: var(--theme-muted);
        }

        /* ─── PAGE CONTENT WRAPPER ──────────────────────── */
        .layout-page {
            flex-grow: 1;
            margin-left: var(--theme-sidebar-width);
            display: flex;
            flex-direction: column;
            min-width: 0;
            min-height: 100vh;
            transition: margin-left 0.2s ease-in-out;
            background: #f8fafc;
        }

        /* ─── TOP WHITE NAVBAR ─────────────────────────── */
        .layout-navbar {
            height: 60px;
            background: #ffffff;
            margin: 0;
            border-bottom: 1px solid var(--theme-card-border);
            padding: 0 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 1020;
        }

        .navbar-nav-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .layout-menu-toggle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #ffffff;
            border: 1px solid var(--theme-card-border);
            font-size: 1.25rem;
            color: #0f172a;
            cursor: pointer;
            width: 36px;
            height: 36px;
            padding: 0;
            border-radius: 6px;
            transition: all 0.15s ease-in-out;
        }

        .layout-menu-toggle:hover {
            background-color: #f1f5f9;
            color: #0B6FB6;
        }

        .navbar-page-title {
            font-size: 1rem;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
        }

        /* Content Container */
        .content-wrapper {
            padding: 1.5rem;
            flex-grow: 1;
        }

        /* ─── PURE WHITE SOLID CARDS ──────────────────────────── */
        .card {
            background: #ffffff;
            border: 1px solid var(--theme-card-border);
            border-radius: var(--theme-radius);
            box-shadow: var(--theme-card-shadow);
            margin-bottom: 1.25rem;
        }

        .card-header {
            background: #ffffff;
            border-bottom: 1px solid var(--theme-card-border);
            padding: 1rem 1.25rem;
            font-weight: 600;
            color: #0f172a;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .card-title {
            font-size: 0.95rem;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .card-title i {
            font-size: 1.15rem;
            color: #0B6FB6;
        }

        .card-body {
            padding: 1.25rem;
        }

        .card-footer {
            background: #ffffff;
            border-top: 1px solid var(--theme-card-border);
            padding: 0.85rem 1.25rem;
            color: #475569;
        }

        /* ─── CLEAN SOLID BADGES ─────────────────────────────── */
        .badge {
            font-weight: 600;
            padding: 0.35em 0.65em;
            font-size: 0.75rem;
            border-radius: 4px;
            letter-spacing: 0.01em;
        }

        .bg-label-primary {
            background-color: #e8f4fd !important;
            color: #0B6FB6 !important;
            border: 1px solid #bae0fb;
        }

        .bg-label-success {
            background-color: #ecfdf5 !important;
            color: #047857 !important;
            border: 1px solid #a7f3d0;
        }

        .bg-label-danger {
            background-color: #fef2f2 !important;
            color: #b91c1c !important;
            border: 1px solid #fecaca;
        }

        .bg-label-warning {
            background-color: #fffbeb !important;
            color: #b45309 !important;
            border: 1px solid #fde68a;
        }

        .bg-label-info {
            background-color: #f0f9ff !important;
            color: #0369a1 !important;
            border: 1px solid #bae6fd;
        }

        .bg-label-secondary {
            background-color: #f8fafc !important;
            color: #475569 !important;
            border: 1px solid #e2e8f0;
        }

        /* ─── SOLID BUTTONS (ANTI-GRADASI) ───────────────────── */
        .btn {
            font-weight: 600;
            font-size: 0.85rem;
            border-radius: 6px;
            padding: 0.5rem 0.9rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            transition: all 0.12s ease-in-out;
        }

        .btn-primary {
            background-color: #0B6FB6;
            border-color: #0B6FB6;
            color: #ffffff;
            box-shadow: none;
        }

        .btn-primary:hover, .btn-primary:focus {
            background-color: #08568e;
            border-color: #08568e;
            color: #ffffff;
            box-shadow: none;
        }

        .btn-outline-primary {
            color: #0B6FB6;
            border-color: #0B6FB6;
            background: transparent;
        }

        .btn-outline-primary:hover {
            background-color: #0B6FB6;
            border-color: #0B6FB6;
            color: #ffffff;
        }

        .btn-outline-secondary {
            color: #475569;
            border-color: #cbd5e1;
            background: #ffffff;
        }

        .btn-outline-secondary:hover {
            background-color: #f8fafc;
            border-color: #94a3b8;
            color: #0f172a;
        }

        .btn-outline-danger {
            color: #dc2626;
            border-color: #fca5a5;
            background: transparent;
        }

        .btn-outline-danger:hover {
            background-color: #dc2626;
            border-color: #dc2626;
            color: #ffffff;
        }

        /* ─── CRISP WHITE TABLES ─────────────────────────────── */
        .table {
            color: var(--bs-body-color);
            margin-bottom: 0;
            vertical-align: middle;
        }

        .table thead th {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #475569;
            background-color: #f8fafc;
            border-bottom: 1px solid var(--theme-card-border);
            padding: 0.75rem 1rem;
            font-weight: 600;
        }

        .table tbody td {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.85rem;
            color: #334155;
        }

        .table-hover tbody tr:hover {
            background-color: #f8fafc;
        }

        /* ─── FORM CONTROLS ───────────────────────────────────── */
        .form-control, .form-select {
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 0.85rem;
            padding: 0.5rem 0.75rem;
            color: #0f172a;
            background-color: #ffffff;
        }

        .form-control:focus, .form-select:focus {
            border-color: #0B6FB6;
            box-shadow: 0 0 0 2px rgba(11, 111, 182, 0.15);
        }

        .input-group-text {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            color: #64748b;
            font-size: 0.85rem;
        }

        /* Responsive Mobile Drawer */
        .layout-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 23, 42, 0.4);
            z-index: 1030;
            display: none;
        }

        @media (min-width: 1200px) {
            body.layout-menu-collapsed .layout-menu {
                transform: translateX(-100%);
            }
            body.layout-menu-collapsed .layout-page {
                margin-left: 0 !important;
            }
        }

        @media (max-width: 1199.98px) {
            .layout-menu {
                transform: translateX(-100%);
            }
            .layout-page {
                margin-left: 0;
            }
            body.layout-menu-expanded .layout-menu {
                transform: translateX(0);
            }
            body.layout-menu-expanded .layout-overlay {
                display: block;
            }
            .content-wrapper {
                padding: 1rem;
            }
        }
    </style>

    @yield('extra-styles')
</head>
<body>

    <div class="layout-wrapper">
        
        <!-- SIDEBAR (MENU) -->
        @if(empty($is_public))
        <aside id="layout-menu" class="layout-menu">
            <!-- App Brand -->
            <div class="app-brand">
                <a href="{{ route('webreport.dashboard') }}" class="app-brand-link">
                    @php 
                        $memberBrandLogo = $portal_logo_url ?? (file_exists(public_path('logo.png')) ? asset('logo.png') : \Illuminate\Support\Facades\Cache::get('site_logo_url')); 
                    @endphp
                    @if($memberBrandLogo)
                        <img src="{{ $memberBrandLogo }}" alt="Logo" style="max-height: 36px; max-width: 145px; width: auto; object-fit: contain;">
                    @else
                        <span class="app-brand-logo" style="background: linear-gradient(135deg, #0B6FB6 0%, #1C8CFB 100%);">
                            <i class="bx bx-server fs-5 text-white"></i>
                        </span>
                        <span class="app-brand-text">{{ $portal_site_title ?? \Illuminate\Support\Facades\Cache::get('site_title', 'Saudagar Host') }}</span>
                    @endif
                </a>
                <span class="app-brand-badge">Member</span>
            </div>

            <!-- Menu Inner Items -->
            <div class="menu-inner-wrapper">
                <ul class="p-0 m-0">
                    <li class="menu-header">Menu Utama</li>

                    <li class="menu-item {{ ($active_tab ?? '') === 'dashboard' ? 'active' : '' }}">
                        <a href="{{ route('webreport.dashboard') }}" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-home-circle"></i>
                            <div>Dashboard</div>
                        </a>
                    </li>

                    <li class="menu-item {{ ($active_tab ?? '') === 'transactions' ? 'active' : '' }}">
                        <a href="{{ route('webreport.transactions') }}" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-history"></i>
                            <div>History Transaksi</div>
                        </a>
                    </li>

                    <li class="menu-item {{ ($active_tab ?? '') === 'deposit' ? 'active' : '' }}">
                        <a href="{{ route('webreport.deposit') }}" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-wallet"></i>
                            <div>Isi Saldo / Mutasi</div>
                        </a>
                    </li>

                    <li class="menu-item {{ ($active_tab ?? '') === 'prices' ? 'active' : '' }}">
                        <a href="{{ route('webreport.prices') }}" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-purchase-tag-alt"></i>
                            <div>Daftar Harga</div>
                        </a>
                    </li>

                    <li class="menu-header">Akun & Bantuan</li>

                    <li class="menu-item {{ ($active_tab ?? '') === 'api-docs' ? 'active' : '' }}">
                        <a href="{{ route('webreport.api-docs') }}" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-code-alt"></i>
                            <div>Format Transaksi</div>
                        </a>
                    </li>

                    <li class="menu-item {{ ($active_tab ?? '') === 'settings' ? 'active' : '' }}">
                        <a href="{{ route('webreport.settings') }}" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-cog"></i>
                            <div>Pengaturan Akun</div>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- User Footer Preview & Logout -->
            <div class="menu-footer">
                <div class="user-preview">
                    <div class="avatar-initial">
                        {{ strtoupper(substr($active_reseller_code ?? 'ME', 0, 2)) }}
                    </div>
                    <div class="overflow-hidden">
                        <div class="user-preview-name">{{ strtoupper($active_reseller_code ?? 'MEMBER BARU') }}</div>
                        <div class="user-preview-role">{{ isset($active_reseller_code) ? 'Reseller Aktif' : 'Reseller' }}</div>
                    </div>
                </div>
                <form action="{{ route('webreport.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger w-100 btn-sm">
                        <i class="bx bx-power-off"></i> Keluar
                    </button>
                </form>
            </div>
        </aside>
        @endif

        <!-- LAYOUT PAGE (HEADER + CONTENT) -->
        <div class="layout-page" style="{{ !empty($is_public) ? 'margin-left: 0; width: 100%;' : '' }}">
            
            <!-- TOP NAVBAR -->
            <nav class="layout-navbar">
                <div class="navbar-nav-left">
                    @if(empty($is_public))
                    <button class="layout-menu-toggle" type="button" onclick="toggleSidebar()">
                        <i class="bx bx-menu"></i>
                    </button>
                    @endif
                    <h1 class="navbar-page-title">@yield('page-header')</h1>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <!-- Theme Mode Toggle -->
                    <button class="btn btn-outline-secondary d-flex align-items-center justify-content-center p-0 rounded-circle" id="themeToggleBtn" type="button" onclick="toggleTheme()" title="Ubah Mode Gelap / Terang" style="width: 36px; height: 36px;">
                        <i class="bx bx-moon fs-5" id="themeToggleIcon"></i>
                    </button>

                    @if(!empty($is_public))
                    <a href="{{ route('webreport.landing') }}" class="btn btn-outline-secondary btn-sm d-none d-sm-inline-flex align-items-center gap-1">
                        <i class="bx bx-home-alt"></i> Beranda
                    </a>
                    <a href="{{ route('webreport.login') }}" class="btn btn-primary btn-sm">
                        <i class="bx bx-log-in"></i> Login Reseller
                    </a>
                    @else
                    <div class="d-none d-sm-flex align-items-center bg-label-secondary px-3 py-1 rounded">
                        <i class="bx bx-user me-1 text-primary"></i>
                        <span class="fw-semibold text-dark" style="font-size: 0.8rem;">
                            {{ strtoupper($active_reseller_code ?? 'MEMBER') }}
                        </span>
                    </div>
                    @endif
                </div>
            </nav>

            <!-- MAIN CONTENT WRAPPER -->
            <div class="content-wrapper">
                
                <!-- Flash Alerts -->
                @if(session('success'))
                <div class="alert alert-success alert-dismissible d-flex align-items-center mb-3" role="alert">
                    <i class="bx bx-check-circle me-2 fs-5"></i>
                    <div>{{ session('success') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif

                @if(session('error'))
                <div class="alert alert-danger alert-dismissible d-flex align-items-center mb-3" role="alert">
                    <i class="bx bx-error-circle me-2 fs-5"></i>
                    <div>{{ session('error') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif

                @yield('content')
            </div>

        </div>

    </div>

    <!-- Mobile Overlay Backdrop -->
    <div class="layout-overlay" onclick="toggleSidebar()"></div>

    <!-- JS Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            if (window.innerWidth >= 1200) {
                document.body.classList.toggle('layout-menu-collapsed');
                const isCollapsed = document.body.classList.contains('layout-menu-collapsed');
                localStorage.setItem('webreport_sidebar_collapsed', isCollapsed ? 'true' : 'false');
            } else {
                document.body.classList.toggle('layout-menu-expanded');
            }
        }

        function updateThemeIcon(theme) {
            const icon = document.getElementById('themeToggleIcon');
            if (!icon) return;
            if (theme === 'dark') {
                icon.className = 'bx bx-sun fs-5 text-warning';
            } else {
                icon.className = 'bx bx-moon fs-5';
            }
        }

        function toggleTheme() {
            const currentTheme = document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'dark' : 'light';
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            document.documentElement.setAttribute('data-bs-theme', newTheme);
            if (newTheme === 'dark') {
                document.documentElement.classList.add('dark-style');
                document.documentElement.classList.remove('light-style');
            } else {
                document.documentElement.classList.add('light-style');
                document.documentElement.classList.remove('dark-style');
            }
            localStorage.setItem('webreport_theme', newTheme);
            updateThemeIcon(newTheme);
        }

        document.addEventListener('DOMContentLoaded', function() {
            const activeTheme = document.documentElement.getAttribute('data-bs-theme') || 'light';
            updateThemeIcon(activeTheme);
        });
    </script>
    @yield('extra-scripts')

</body>
</html>
