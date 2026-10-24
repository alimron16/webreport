@extends('admin.layout')

@section('title', 'Pengaturan Portal')
@section('page-header', 'Pengaturan Portal')

@section('extra-styles')
<style>
/* ═══════════════════════════════════════════════
   ADMIN SETTINGS PAGE — Design System
   Font: Inter (body 14px), Outfit (headings)
   ═══════════════════════════════════════════════ */

:root {
    --color-border: #e2e8f0;
    --color-border-dark: #cbd5e1;
    --color-bg: #f8fafc;
    --color-text: #0f172a;
    --color-text-2: #334155;
    --color-text-3: #64748b;
    --color-text-4: #94a3b8;
    --color-purple: #0B6FB6;
    --color-purple-light: #e8f4fd;
    --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}

html[data-bs-theme="dark"], html.dark-style {
    --color-border: #1f2937;
    --color-border-dark: #374151;
    --color-bg: #111827;
    --color-text: #f8fafc;
    --color-text-2: #cbd5e1;
    --color-text-3: #94a3b8;
    --color-text-4: #64748b;
    --color-purple: #1C8CFB;
    --color-purple-light: rgba(11, 111, 182, 0.2);
    --shadow-sm: 0 1px 3px 0 rgba(0, 0, 0, 0.4);
}

/* ── Settings Layout ────────────────────────────── */
.settings-layout {
    display: grid;
    grid-template-columns: 1fr;
    gap: 24px;
    align-items: start;
}

/* ── Tab Nav ──────────────────────────────────── */
.stg-tabs {
    display: flex;
    flex-direction: column;
    gap: 8px;
    position: sticky;
    top: 24px;
}
.stg-tab-btn {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    gap: 12px;
    padding: 14px 16px;
    font-family: 'Inter', sans-serif;
    font-size: 14px;
    font-weight: 600;
    color: var(--color-text-3);
    background: #fff;
    border: 1px solid var(--color-border);
    border-radius: 10px;
    cursor: pointer;
    transition: all .2s;
    box-shadow: 0 2px 4px rgba(0,0,0,0.01);
}
.stg-tab-btn svg { flex-shrink: 0; width: 18px; height: 18px; }
.stg-tab-btn:hover {
    color: var(--color-text-2);
    border-color: #cbd5e1;
    background: #f8fafc;
}
.stg-tab-btn.active {
    color: var(--color-text);
    border-color: var(--color-border-dark);
    background: #f8fafc;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.stg-tab-pane { display: none; }
.stg-tab-pane.active { display: block; }

/* ── Status Badge ──────────────────────────────── */
.stg-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 2px 9px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    margin-left: 8px;
}
.stg-badge.ok  { background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0; }
.stg-badge.warn{ background:#f9fafb; color:#6b7280; border:1px solid #e5e7eb; }

/* ── Page Grid ─────────────────────────────────── */
.stg-grid {
    display: grid;
    grid-template-columns: 1fr 300px;
    gap: 24px;
    align-items: start;
}
@media (max-width: 860px) { .stg-grid { grid-template-columns: 1fr; } }

/* ── Card override ─────────────────────────────── */
.stg-card {
    background: #fff;
    border: 1px solid var(--color-border);
    border-radius: 10px;
    box-shadow: var(--shadow-sm);
    overflow: hidden;
}

/* ── Card Header bar ───────────────────────────── */
.stg-card-hdr {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 15px 20px;
    border-bottom: 1px solid var(--color-border);
}
.stg-card-title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-family: 'Outfit', sans-serif;
    font-size: 15px;
    font-weight: 700;
    color: var(--color-text);
}
.stg-card-title svg { color: var(--color-purple); }

/* ── Section Label ─────────────────────────────── */
.stg-label {
    font-family: 'Outfit', sans-serif;
    font-size: 10.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--color-text-4);
    margin-bottom: 10px;
}

/* ── Form Fields ───────────────────────────────── */
.stg-field { margin-bottom: 14px; }
.stg-field:last-child { margin-bottom: 0; }
.stg-field-lbl {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12.5px;
    font-weight: 600;
    color: var(--color-text-2);
    margin-bottom: 6px;
}
.stg-field-lbl svg { color: var(--color-text-3); flex-shrink: 0; }
.stg-field-lbl .lbl-warn {
    font-weight: 500;
    color: #dc2626;
    font-size: 11.5px;
    font-family: 'Inter', sans-serif;
}
.stg-hint {
    font-size: 11.5px;
    color: var(--color-text-4);
    margin-top: 5px;
    line-height: 1.45;
}

/* ── Password field ────────────────────────────── */
.pwd-wrap { position: relative; }
.pwd-wrap input { padding-right: 42px; }
.pwd-eye {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    cursor: pointer;
    color: var(--color-text-4);
    display: flex;
    align-items: center;
    padding: 0;
    transition: color .15s;
}
.pwd-eye:hover { color: var(--color-text-2); }

/* ── SMTP Info Row ─────────────────────────────── */
.smtp-row {
    display: flex;
    align-items: center;
    gap: 10px;
    background: var(--color-bg);
    border: 1px solid var(--color-border);
    border-radius: 8px;
    padding: 10px 14px;
    margin-bottom: 16px;
    font-size: 12px;
    color: var(--color-text-3);
}
.smtp-row code {
    background: #fff;
    border: 1px solid var(--color-border);
    padding: 1px 6px;
    border-radius: 4px;
    font-size: 11.5px;
    font-family: 'JetBrains Mono', monospace;
}

/* ── Test Email Section ────────────────────────── */
.test-section {
    margin-top: 20px;
    padding-top: 18px;
    border-top: 1px dashed var(--color-border);
}
.test-title {
    display: flex;
    align-items: center;
    gap: 7px;
    font-size: 13px;
    font-weight: 600;
    color: var(--color-text-2);
    margin-bottom: 10px;
    font-family: 'Outfit', sans-serif;
}
.test-row {
    display: flex;
    gap: 8px;
    align-items: center;
}
#test-result {
    margin-top: 8px;
    font-size: 12px;
    min-height: 18px;
    display: flex;
    align-items: center;
    gap: 6px;
}

/* ── Logo Preview ──────────────────────────────── */
.logo-preview {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 8px 12px;
    background: var(--color-bg);
    border: 1px solid var(--color-border);
    border-radius: 8px;
    margin-bottom: 10px;
}
.logo-preview img { max-height: 36px; max-width: 140px; object-fit: contain; }
.logo-preview-sub { font-size: 11px; color: var(--color-text-4); }

/* ── File row (active PKS doc) ─────────────────── */
.doc-row {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 14px;
    background: var(--color-bg);
    border: 1px solid var(--color-border);
    border-radius: 8px;
    margin-bottom: 16px;
}
.doc-icon {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    background: var(--color-purple-light);
    color: var(--color-purple);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.doc-name { font-size: 13px; font-weight: 600; color: var(--color-text); }
.doc-sub  { font-size: 11px; color: var(--color-text-4); margin-top: 2px; }

/* ── Alerts ────────────────────────────────────── */
.stg-alert {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 11px 14px;
    border-radius: 8px;
    font-size: 13px;
    line-height: 1.5;
    margin-bottom: 16px;
}
.stg-alert svg { flex-shrink: 0; margin-top: 1px; }
.stg-alert.success { background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; }
.stg-alert.error   { background: #fef2f2; border: 1px solid #fca5a5; color: #991b1b; }
.stg-alert.warning { background: #f9fafb; border: 1px solid #e5e7eb; color: var(--color-text-2); }

/* ── Step List ─────────────────────────────────── */
.step-list { list-style: none; padding: 0; margin: 0; counter-reset: stp; }
.step-list li {
    counter-increment: stp;
    display: flex;
    gap: 10px;
    padding: 10px 0;
    border-bottom: 1px solid var(--color-border);
    font-size: 12.5px;
    line-height: 1.55;
    color: var(--color-text-2);
}
.step-list li:last-child { border-bottom: none; }
.step-list li::before {
    content: counter(stp);
    background: var(--color-purple);
    color: #fff;
    border-radius: 50%;
    min-width: 20px;
    height: 20px;
    font-size: 10.5px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    margin-top: 2px;
}

/* ── Tips / guide items ────────────────────────── */
.tip-item {
    display: flex;
    gap: 11px;
    padding: 12px 0;
    border-bottom: 1px solid var(--color-border);
    align-items: flex-start;
}
.tip-item:last-child { border-bottom: none; }
.tip-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: var(--color-purple-light);
    color: var(--color-purple);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.tip-text strong { display: block; font-size: 12.5px; font-weight: 600; color: var(--color-text-2); margin-bottom: 2px; }
.tip-text span   { font-size: 12px; color: var(--color-text-3); line-height: 1.5; }

/* ── Security Tips Block ───────────────────────── */
.sec-tips {
    margin-top: 14px;
    padding: 12px 14px;
    background: var(--color-purple-light);
    border: 1px solid rgba(37,99,235,.15);
    border-radius: 8px;
    font-size: 12px;
    color: var(--color-text-2);
    line-height: 1.65;
}
.sec-tips strong { display: flex; align-items: center; gap: 6px; font-size: 12.5px; color: var(--color-text); margin-bottom: 6px; }

/* ── Banner Shortcut Style ───────────────────────── */
.stg-banner-shortcut {
    grid-column: 1 / -1;
    margin-top: -10px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
}
.stg-banner-shortcut .banner-icon {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    background: #e8f4fd;
    color: #0B6FB6;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.stg-banner-shortcut .banner-title {
    font-weight: 700;
    font-size: 14.5px;
    color: #0A2540;
}
.stg-banner-shortcut .banner-desc {
    font-size: 12.5px;
    color: #475569;
    margin-top: 2px;
}

/* ── Dark Mode Overrides for Settings ─────────── */
html[data-bs-theme="dark"] .stg-card {
    background: #111827 !important;
    border-color: #1f2937 !important;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.4) !important;
}

html[data-bs-theme="dark"] .stg-card-hdr {
    background: #111827 !important;
    border-bottom-color: #1f2937 !important;
}

html[data-bs-theme="dark"] .stg-card-title {
    color: #f8fafc !important;
}

html[data-bs-theme="dark"] .stg-tab-btn {
    background: #111827 !important;
    border-color: #1f2937 !important;
    color: #94a3b8 !important;
}

html[data-bs-theme="dark"] .stg-tab-btn:hover {
    background: #1e293b !important;
    color: #f8fafc !important;
    border-color: #334155 !important;
}

html[data-bs-theme="dark"] .stg-tab-btn.active {
    background: #1e293b !important;
    color: #60a5fa !important;
    border-color: #3b82f6 !important;
}

html[data-bs-theme="dark"] .stg-label {
    color: #60a5fa !important;
}

html[data-bs-theme="dark"] .stg-field-lbl {
    color: #cbd5e1 !important;
}

html[data-bs-theme="dark"] .stg-hint {
    color: #94a3b8 !important;
}

html[data-bs-theme="dark"] .tip-item {
    border-bottom-color: #1f2937 !important;
}

html[data-bs-theme="dark"] .tip-icon {
    background: rgba(37, 99, 235, 0.15) !important;
    color: #60a5fa !important;
}

html[data-bs-theme="dark"] .tip-text strong {
    color: #f8fafc !important;
}

html[data-bs-theme="dark"] .tip-text span {
    color: #94a3b8 !important;
}

html[data-bs-theme="dark"] .logo-preview,
html[data-bs-theme="dark"] .doc-row,
html[data-bs-theme="dark"] .smtp-row {
    background: #1e293b !important;
    border-color: #334155 !important;
    color: #cbd5e1 !important;
}

html[data-bs-theme="dark"] .smtp-row code {
    background: #0f172a !important;
    border-color: #1e293b !important;
    color: #38bdf8 !important;
}

html[data-bs-theme="dark"] .sec-tips {
    background: rgba(37, 99, 235, 0.12) !important;
    border-color: rgba(37, 99, 235, 0.25) !important;
    color: #cbd5e1 !important;
}

html[data-bs-theme="dark"] .sec-tips strong {
    color: #93c5fd !important;
}

html[data-bs-theme="dark"] .step-list li {
    border-bottom-color: #1f2937 !important;
    color: #cbd5e1 !important;
}

html[data-bs-theme="dark"] .test-section {
    border-top-color: #1f2937 !important;
}

html[data-bs-theme="dark"] .test-title {
    color: #f8fafc !important;
}

html[data-bs-theme="dark"] .nav-pills .nav-link {
    background-color: #111827;
    color: #94a3b8;
    border: 1px solid #1f2937;
}

html[data-bs-theme="dark"] .nav-pills .nav-link:hover {
    background-color: #1e293b;
    color: #f8fafc;
}

html[data-bs-theme="dark"] .nav-pills .nav-link.active {
    background-color: #0B6FB6 !important;
    color: #ffffff !important;
    border-color: #0B6FB6 !important;
}

html[data-bs-theme="dark"] .stg-banner-shortcut {
    background: #111827 !important;
    border-color: #1f2937 !important;
}

html[data-bs-theme="dark"] .stg-banner-shortcut .banner-icon {
    background: rgba(11, 111, 182, 0.25) !important;
    color: #1C8CFB !important;
}

html[data-bs-theme="dark"] .stg-banner-shortcut .banner-title {
    color: #f8fafc !important;
}

html[data-bs-theme="dark"] .stg-banner-shortcut .banner-desc {
    color: #94a3b8 !important;
}
</style>
@endsection

@section('content')

{{-- ── Sneat Nav Pills Tab Navigation ── --}}
<ul class="nav nav-pills flex-column flex-sm-row mb-4 gap-2" id="settingsTabs">
    <li class="nav-item">
        <button class="nav-link active" onclick="switchTab('tab-branding', this)">
            <i class="bx bx-image-alt me-1"></i> Branding & Info
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" onclick="switchTab('tab-receipt', this)">
            <i class="bx bx-receipt me-1"></i> Pengaturan Struk & Logo
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" onclick="switchTab('tab-account', this)">
            <i class="bx bx-user-pin me-1"></i> Akun & Password
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" onclick="switchTab('tab-otomax', this)">
            <i class="bx bx-data me-1"></i> Database SQL Server
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" onclick="switchTab('tab-bank', this)">
            <i class="bx bx-credit-card me-1"></i> Rekening Bank
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" onclick="switchTab('tab-gmail', this)">
            <i class="bx bx-envelope me-1"></i> Notifikasi Email
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" onclick="switchTab('tab-security', this)">
            <i class="bx bx-shield-quarter me-1"></i> Keamanan & Captcha
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" onclick="switchTab('tab-apicenter', this)">
            <i class="bx bx-broadcast me-1"></i> IP Center & Regex API
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" onclick="switchTab('tab-logs', this)">
            <i class="bx bx-terminal me-1"></i> System Logs
        </button>
    </li>
</ul>

<div class="settings-layout">


{{-- ═══════════════════════════════════════════════
     TAB 1: BRANDING
     ═══════════════════════════════════════════════ --}}
<div id="tab-branding" class="stg-tab-pane active">
    <div class="stg-grid">

        {{-- Form --}}
        <div class="stg-card">
            <div class="stg-card-hdr">
                <div class="stg-card-title">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Branding Portal
                </div>
            </div>
            <div class="card-body" style="padding: 20px;">
                @if(session('branding_success'))
                <div class="stg-alert success">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('branding_success') }}</span>
                </div>
                @endif

                <form action="{{ route('admin.branding.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="stg-label">Informasi Portal</div>

                    <div class="stg-field">
                        <div class="stg-field-lbl">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                            Nama Portal / Instansi
                        </div>
                        <input type="text" name="site_title" class="form-control"
                            value="{{ old('site_title', \Illuminate\Support\Facades\Cache::get('site_title', 'OtomaX')) }}"
                            placeholder="Contoh: OtomaX Server / Perusahaan Anda" required>
                    </div>

                    <div class="stg-field">
                        <div class="stg-field-lbl">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7"/></svg>
                            Tagline / Deskripsi Singkat
                        </div>
                        <input type="text" name="site_subtitle" class="form-control"
                            value="{{ old('site_subtitle', \Illuminate\Support\Facades\Cache::get('site_subtitle', 'Portal Reseller Digital')) }}"
                            placeholder="Contoh: Solusi Pembayaran Digital Terpercaya" required>
                        <div class="stg-hint">Muncul di bawah nama portal pada halaman login dan header.</div>
                    </div>

                    <div style="margin-top: 20px; padding-top: 16px; border-top: 1px solid var(--color-border);">
                        <div class="stg-label">Logo Portal</div>

                        @php $logoUrl = $portal_logo_url ?? \Illuminate\Support\Facades\Cache::get('site_logo_url'); @endphp
                        @if($logoUrl)
                        <div class="logo-preview d-flex align-items-center gap-3 p-3 bg-light rounded border mb-3">
                            <img src="{{ $logoUrl }}" alt="Logo aktif" style="max-height: 48px; max-width: 150px; object-fit: contain;">
                            <div>
                                <span class="d-block fw-semibold text-dark small">Logo Aktif Saat Ini</span>
                                <label class="form-check-label text-danger small mt-1 d-flex align-items-center gap-1 cursor-pointer">
                                    <input type="checkbox" name="remove_logo" value="1" class="form-check-input"> Hapus logo & kembali ke ikon default
                                </label>
                            </div>
                        </div>
                        @endif

                        <div class="stg-field">
                            <div class="stg-field-lbl">
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                Unggah Logo Baru
                                <span style="font-weight:400; color:var(--color-text-4); font-size:11.5px;">(PNG / JPG / SVG, maks. 2MB)</span>
                            </div>
                            <input type="file" name="site_logo" class="form-control" accept="image/png,image/jpeg,image/svg+xml">
                            <div class="stg-hint">Kosongkan jika tidak ingin mengganti logo.</div>
                        </div>
                    </div>

                    <div style="margin-top: 24px; padding-top: 18px; border-top: 1px solid var(--color-border);">
                        <div class="stg-label d-flex align-items-center gap-1 text-success">
                            <i class="bx bxl-whatsapp fs-5"></i> Kontak WhatsApp & Pendaftaran Calon Mitra
                        </div>

                        <div class="stg-field">
                            <div class="stg-field-lbl">
                                <i class="bx bxl-whatsapp text-success me-1"></i> Nomor WhatsApp CS / Pendaftaran
                            </div>
                            <input type="text" name="whatsapp_number" class="form-control"
                                value="{{ old('whatsapp_number', \Illuminate\Support\Facades\Cache::get('whatsapp_number', '')) }}"
                                placeholder="Contoh: 08123456789 atau 628123456789">
                            <div class="stg-hint">Nomor admin yang otomatis dibuka saat pengunjung mengklik tombol <strong>Daftar</strong> atau tombol <strong>Hubungi CS</strong> di halaman beranda.</div>
                        </div>

                        <div class="stg-field">
                            <div class="stg-field-lbl">
                                <i class="bx bx-message-rounded-dots text-primary me-1"></i> Template Pesan Otomatis Tombol "Daftar"
                            </div>
                            <textarea name="whatsapp_registration_message" rows="3" class="form-control"
                                placeholder="Tuliskan template pesan WhatsApp pendaftaran...">{{ old('whatsapp_registration_message', \Illuminate\Support\Facades\Cache::get('whatsapp_registration_message', 'Halo Admin, saya ingin mendaftar menjadi agen/reseller di {site_title}. Mohon panduan cara dan syarat pendaftarannya. Terima kasih!')) }}</textarea>
                            <div class="stg-hint">Pesan yang otomatis terketik di WhatsApp calon mitra. Gunakan token <code>{site_title}</code> untuk otomatis menyisipkan nama portal Anda.</div>
                        </div>

                        <div class="stg-field">
                            <div class="stg-field-lbl">
                                <i class="bx bx-support text-info me-1"></i> Template Pesan Otomatis CS / Pertanyaan Umum
                            </div>
                            <textarea name="whatsapp_cs_message" rows="2" class="form-control"
                                placeholder="Tuliskan template pesan bantuan CS...">{{ old('whatsapp_cs_message', \Illuminate\Support\Facades\Cache::get('whatsapp_cs_message', 'Halo Customer Service {site_title}, saya membutuhkan informasi/bantuan seputar transaksi Webreport. Terima kasih.')) }}</textarea>
                        </div>

                        <div class="stg-field">
                            <div class="stg-field-lbl">
                                <i class="bx bx-time text-warning me-1"></i> Jam Operasional Layanan CS
                            </div>
                            <input type="text" name="cs_working_hours" class="form-control"
                                value="{{ old('cs_working_hours', \Illuminate\Support\Facades\Cache::get('cs_working_hours', '07.00 - 23.00 WIB (Setiap Hari)')) }}"
                                placeholder="Contoh: 07.00 - 23.00 WIB atau 24 Jam Nonstop">
                        </div>
                    </div>

                    <div style="margin-top: 24px; padding-top: 18px; border-top: 1px solid var(--color-border);">
                        <div class="stg-label d-flex align-items-center gap-1 text-info">
                            <i class="bx bxl-telegram fs-5"></i> Akun & Saluran Telegram (Opsional)
                        </div>

                        <div class="row g-2">
                            <div class="col-md-6">
                                <div class="stg-field">
                                    <div class="stg-field-lbl">Username CS Telegram</div>
                                    <input type="text" name="telegram_cs_username" class="form-control"
                                        value="{{ old('telegram_cs_username', \Illuminate\Support\Facades\Cache::get('telegram_cs_username', '')) }}"
                                        placeholder="Contoh: @cs_otomax atau cs_otomax">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="stg-field">
                                    <div class="stg-field-lbl">Link Saluran / Channel Info</div>
                                    <input type="url" name="telegram_channel_url" class="form-control"
                                        value="{{ old('telegram_channel_url', \Illuminate\Support\Facades\Cache::get('telegram_channel_url', '')) }}"
                                        placeholder="Contoh: https://t.me/info_otomax">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div style="margin-top: 24px; padding-top: 18px; border-top: 1px solid var(--color-border);">
                        <div class="stg-label d-flex align-items-center gap-1">
                            <i class="bx bx-navigation fs-5 text-primary"></i> Aksi Tombol "Daftar" di Landing Page
                        </div>

                        <div class="stg-field">
                            <div class="stg-field-lbl">Pilihan Alur Pendaftaran</div>
                            @php $currRegMode = old('registration_mode', \Illuminate\Support\Facades\Cache::get('registration_mode', 'whatsapp')); @endphp
                            <select name="registration_mode" class="form-select">
                                <option value="whatsapp" {{ $currRegMode === 'whatsapp' ? 'selected' : '' }}>
                                    ✅ Langsung Buka Chat WhatsApp Admin dengan Pesan Otomatis (Rekomendasi)
                                </option>
                                <option value="form" {{ $currRegMode === 'form' ? 'selected' : '' }}>
                                    📄 Buka Halaman Formulir Pendaftaran Web (/register)
                                </option>
                                <option value="both" {{ $currRegMode === 'both' ? 'selected' : '' }}>
                                    🔀 Tampilkan Tombol WhatsApp sebagai Utama dan Opsi Formulir Web
                                </option>
                            </select>
                            <div class="stg-hint">Menentukan apa yang terjadi saat calon mitra mengklik tombol <strong>Daftar Mitra</strong> pada landing page.</div>
                        </div>
                    </div>

                    <div style="margin-top: 24px; padding-top: 18px; border-top: 1px solid var(--color-border);">
                        <div class="stg-label d-flex align-items-center gap-1">
                            <i class="bx bx-layout fs-5 text-purple" style="color: #8b5cf6;"></i> Kustomisasi Teks Hero Landing Page (Opsional)
                        </div>

                        <div class="stg-field">
                            <div class="stg-field-lbl">Judul Hero Landing Page</div>
                            <input type="text" name="hero_headline" class="form-control"
                                value="{{ old('hero_headline', \Illuminate\Support\Facades\Cache::get('hero_headline', '')) }}"
                                placeholder="Kosongkan untuk menggunakan judul bawaan (Portal Webreport Cepat, Akurat & Real-Time)">
                        </div>

                        <div class="stg-field">
                            <div class="stg-field-lbl">Deskripsi / Subtitle Hero</div>
                            <textarea name="hero_subheadline" rows="2" class="form-control"
                                placeholder="Kosongkan untuk menggunakan teks deskripsi bawaan...">{{ old('hero_subheadline', \Illuminate\Support\Facades\Cache::get('hero_subheadline', '')) }}</textarea>
                        </div>
                    </div>

                    <div style="margin-top: 24px; padding-top: 18px; border-top: 1px solid var(--color-border);">
                        <button type="submit" class="btn btn-primary"
                            style="display:inline-flex; align-items:center; gap:6px; font-weight:600; padding: 10px 20px;">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                            Simpan Pengaturan Branding & Kontak
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Tips --}}
        <div class="stg-card">
            <div class="stg-card-hdr">
                <div class="stg-card-title">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Panduan
                </div>
            </div>
            <div class="card-body" style="padding: 4px 20px 16px;">
                <div class="tip-item">
                    <div class="tip-icon">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    </div>
                    <div class="tip-text">
                        <strong>Nama Portal</strong>
                        <span>Tampil di header, login page, dan semua email OTP yang dikirim ke member.</span>
                    </div>
                </div>
                <div class="tip-item">
                    <div class="tip-icon">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <div class="tip-text">
                        <strong>Logo</strong>
                        <span>Ukuran ideal <strong>180 × 50 px</strong>, format PNG transparan. Tampil di header dan halaman login.</span>
                    </div>
                </div>
                <div class="tip-item">
                    <div class="tip-icon">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h12"/></svg>
                    </div>
                    <div class="tip-text">
                        <strong>Tagline</strong>
                        <span>Singkat maksimal 60 karakter agar tidak terpotong pada tampilan mobile.</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Banner Shortcut ke Kelola Tautan Harga --}}
        <div class="stg-card stg-banner-shortcut">
            <div class="card-body" style="padding: 18px 22px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
                <div style="display: flex; align-items: center; gap: 14px;">
                    <div class="banner-icon">
                        <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                        </svg>
                    </div>
                    <div>
                        <div class="banner-title">
                            Kelola Tautan Publik & Masa Aktif Daftar Harga
                        </div>
                        <div class="banner-desc">
                            Sekarang Anda dapat men-generate tautan khusus level grup lengkap dengan batas masa kadaluarsa (expired) di menu khusus.
                        </div>
                    </div>
                </div>
                <a href="{{ route('admin.price-links') }}" class="btn btn-primary" style="font-weight: 600; padding: 9px 18px; font-size: 13px; gap: 6px; display: inline-flex; align-items: center;">
                    Buka Kelola Tautan Harga →
                </a>
            </div>
        </div>

    </div>
</div>



{{-- ═══════════════════════════════════════════════
     TAB OTOMAX SQL SERVER
     ═══════════════════════════════════════════════ --}}
<div id="tab-otomax" class="stg-tab-pane">
    <div class="stg-grid">

        {{-- Form --}}
        <div class="stg-card">
            <div class="stg-card-hdr">
                <div class="stg-card-title">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>
                    </svg>
                    Database SQL Server (OtomaX)
                    <span style="margin-left: auto; font-size: 11px; padding: 3px 8px; background: rgba(16, 185, 129, 0.12); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.25); border-radius: 6px; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        Terenkripsi di DB (Bukan .ENV)
                    </span>
                </div>
            </div>
            <div class="card-body" style="padding: 20px;">
                @if(session('otomax_success'))
                <div class="stg-alert success">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('otomax_success') }}</span>
                </div>
                @endif
                
                <form action="{{ route('admin.settings.otomax') }}" method="POST">
                    @csrf
                    
                    <div class="stg-field">
                        <div class="stg-field-lbl">Database Host / IP</div>
                        <input type="text" id="otomax_db_host" name="db_host" class="form-control" value="{{ $otomaxDbConfig['host'] ?? '127.0.0.1' }}" placeholder="127.0.0.1" required>
                    </div>

                    <div class="stg-field">
                        <div class="stg-field-lbl">Database Port</div>
                        <input type="number" id="otomax_db_port" name="db_port" class="form-control" value="{{ $otomaxDbConfig['port'] ?? '1433' }}" placeholder="1433" required>
                    </div>
                    
                    <div class="stg-field">
                        <div class="stg-field-lbl">Database Name</div>
                        <input type="text" id="otomax_db_database" name="db_database" class="form-control" value="{{ $otomaxDbConfig['database'] ?? 'otomax' }}" placeholder="otomax" required>
                    </div>

                    <div class="stg-field">
                        <div class="stg-field-lbl">Database Username</div>
                        <input type="text" id="otomax_db_username" name="db_username" class="form-control" value="{{ $otomaxDbConfig['username'] ?? 'sa' }}" placeholder="sa" required>
                    </div>

                    <div class="stg-field">
                        <div class="stg-field-lbl">Database Password</div>
                        <input type="password" id="otomax_db_password" name="db_password" class="form-control" value="" placeholder="{{ !empty($otomaxDbConfig['has_password']) ? '•••••••••••• (Tersimpan terenkripsi di database)' : 'Kosongkan jika tidak ada password' }}">
                        <div class="stg-hint">Kredensial disimpan terenkripsi (AES-256) di database, bukan di file .env. Kosongkan kolom ini jika tidak ingin mengubah password saat ini.</div>
                    </div>

                    <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--color-border); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                        <button type="button" id="btn-test-otomax" class="btn btn-secondary" onclick="testOtomaxConnection()" style="display:inline-flex; align-items:center; gap:6px; font-weight:600;">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            Uji Coba Koneksi
                        </button>
                        <button type="submit" class="btn btn-primary" style="display:inline-flex; align-items:center; gap:6px; font-weight:600;">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            Simpan Konfigurasi
                        </button>
                    </div>
                    <div id="test-otomax-result" style="margin-top: 14px;"></div>
                </form>
            </div>
        </div>

        {{-- Info --}}
        <div class="stg-card">
            <div class="stg-card-hdr">
                <div class="stg-card-title">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Informasi & Status Server
                </div>
            </div>
            <div class="card-body" style="padding: 12px 20px 20px;">
                @php 
                    $hasSqlsrv = extension_loaded('pdo_sqlsrv') || extension_loaded('sqlsrv');
                @endphp
                <div class="tip-item">
                    <div class="tip-icon" style="{{ $hasSqlsrv ? 'background:#ecfdf5; color:#059669;' : 'background:#fef2f2; color:#dc2626;' }}">
                        @if($hasSqlsrv)
                            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        @else
                            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        @endif
                    </div>
                    <div class="tip-text">
                        <strong>Driver PHP Microsoft SQL Server</strong>
                        @if($hasSqlsrv)
                            <span style="color:#059669; font-weight:600;">Terpasang & Aktif (pdo_sqlsrv).</span>
                        @else
                            <span style="color:#dc2626; font-weight:600;">Belum aktif di PHP server ini.</span>
                            <div style="font-size:11.5px; color:var(--color-text-3); margin-top:3px; line-height:1.4;">
                                Tambahkan <code>extension=pdo_sqlsrv</code> di <code>php.ini</code> agar web dapat berkomunikasi dengan database SQL Server.
                            </div>
                        @endif
                    </div>
                </div>

                <div class="tip-item" style="margin-top:12px;">
                    <div class="tip-icon">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <div class="tip-text">
                        <strong>Koneksi TCP/IP & Port 1433</strong>
                        <span>Pastikan SQL Server sudah mengaktifkan protokol TCP/IP di SQL Server Configuration Manager dan port 1433 diizinkan oleh Windows Firewall.</span>
                    </div>
                </div>

                <div class="tip-item" style="margin-top:12px;">
                    <div class="tip-icon">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </div>
                    <div class="tip-text">
                        <strong>Mode Autentikasi</strong>
                        <span>Gunakan SQL Server Authentication (mode Mixed Mode), bukan Windows Authentication Only.</span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>


{{-- ═══════════════════════════════════════════════
     TAB 3: GMAIL
     ═══════════════════════════════════════════════ --}}
<div id="tab-gmail" class="stg-tab-pane">
    <div class="stg-grid">

        {{-- Form Gmail --}}
        <div class="stg-card">
            <div class="stg-card-hdr">
                <div class="stg-card-title">
                    {{-- Gmail logo --}}
                    <svg width="18" height="18" viewBox="0 0 24 24">
                        <path fill="#EA4335" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#4285F4" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    Konfigurasi Gmail SMTP
                </div>
                @if(\Illuminate\Support\Facades\Cache::has('mail_password'))
                    <span class="stg-badge ok">
                        <svg width="9" height="9" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        Aktif
                    </span>
                @else
                    <span class="stg-badge warn">
                        <svg width="9" height="9" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                        Belum Diatur
                    </span>
                @endif
            </div>

            <div class="card-body" style="padding: 20px;">

                @if(session('mail_success'))
                <div class="stg-alert success">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('mail_success') }}</span>
                </div>
                @endif

                @if($errors->any())
                <div class="stg-alert error">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div>@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
                </div>
                @endif

                <form action="{{ route('admin.settings.mail') }}" method="POST">
                    @csrf

                    <div class="stg-label">Akun Gmail</div>

                    <div class="stg-field">
                        <div class="stg-field-lbl">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            Alamat Gmail Pengirim
                        </div>
                        <input type="email" name="gmail_address" class="form-control"
                            value="{{ old('gmail_address', $mailConfig['from_address']) }}"
                            placeholder="contoh@gmail.com" required>
                        <div class="stg-hint">Gmail yang sudah mengaktifkan Verifikasi 2 Langkah.</div>
                    </div>

                    <div class="stg-field">
                        <div class="stg-field-lbl">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            App Password Gmail
                            <span class="lbl-warn">— Bukan password akun biasa!</span>
                        </div>
                        <div class="pwd-wrap">
                            <input type="password" id="gmail_password" name="gmail_password" class="form-control"
                                placeholder="xxxx xxxx xxxx xxxx (16 karakter)" required>
                            <button type="button" class="pwd-eye" onclick="togglePwd()" title="Tampilkan/Sembunyikan">
                                <svg id="eye-icon" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                        </div>
                        <div class="stg-hint">Baca panduan di sebelah kanan untuk cara membuat App Password.</div>
                    </div>

                    <div class="stg-field">
                        <div class="stg-field-lbl">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            Nama Pengirim Email
                        </div>
                        <input type="text" name="from_name" class="form-control"
                            value="{{ old('from_name', $mailConfig['from_name']) }}"
                            placeholder="Contoh: Portal Reseller" required>
                        <div class="stg-hint">Nama yang tampil di kotak masuk penerima email.</div>
                    </div>

                    {{-- SMTP Info --}}
                    <div class="smtp-row">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color:var(--color-purple); flex-shrink:0;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>SMTP otomatis: Host <code>smtp.gmail.com</code> &nbsp;·&nbsp; Port <code>587</code> &nbsp;·&nbsp; Enkripsi <code>TLS</code></span>
                    </div>

                    <button type="submit" class="btn btn-primary"
                        style="width:100%; display:flex; align-items:center; justify-content:center; gap:6px; font-weight:600;">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                        Simpan Konfigurasi Gmail
                    </button>
                </form>

                {{-- Test Email --}}
                <div class="test-section">
                    <div class="test-title">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                        Uji Coba Kirim Email
                    </div>
                    <div class="test-row">
                        <input type="email" id="test-email-input" class="form-control"
                            placeholder="Masukkan email tujuan uji coba" style="flex:1;">
                        <button type="button" onclick="sendTestEmail()" id="btn-test"
                            class="btn btn-secondary"
                            style="white-space:nowrap; display:inline-flex; align-items:center; gap:5px; font-size:13px; font-weight:600;">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                            Kirim Test
                        </button>
                    </div>
                    <div id="test-result"></div>
                </div>

            </div>
        </div>

        {{-- Guide --}}
        <div class="stg-card">
            <div class="stg-card-hdr">
                <div class="stg-card-title">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    Panduan App Password
                </div>
            </div>
            <div class="card-body" style="padding: 12px 20px 20px;">

                <div class="stg-alert warning">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <span>Gunakan <strong>App Password</strong>, bukan password Gmail biasa Anda.</span>
                </div>

                <ol class="step-list">
                    <li><div>Buka <a href="https://myaccount.google.com/security" target="_blank" style="color:var(--color-purple); font-weight:600; text-decoration:none;">Google Account Security ↗</a></div></li>
                    <li><div>Pastikan <strong>Verifikasi 2 Langkah</strong> sudah <strong>aktif</strong>. Tanpa ini, App Password tidak tersedia.</div></li>
                    <li><div>Ketik <strong>"App Password"</strong> di kolom pencarian atau buka <a href="https://myaccount.google.com/apppasswords" target="_blank" style="color:var(--color-purple); font-weight:600; text-decoration:none;">myaccount.google.com/apppasswords ↗</a></div></li>
                    <li><div>Di kolom <strong>App name</strong>, ketik nama bebas (contoh: <em>Portal Reseller</em>), lalu klik <strong>Create</strong>.</div></li>
                    <li><div>Google menampilkan kode <strong>16 huruf</strong> (contoh: <code style="background:#f3f4f6; padding:1px 5px; border-radius:3px; border:1px solid #e5e7eb; font-size:11px;">abcd efgh ijkl mnop</code>). Salin kode ini.</div></li>
                    <li><div>Tempelkan ke kolom <strong>App Password Gmail</strong> di form sebelah kiri.</div></li>
                    <li><div>Simpan, lalu gunakan <strong>Uji Coba</strong> untuk memverifikasi email berhasil terkirim.</div></li>
                </ol>

                <div class="sec-tips">
                    <strong>
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        Tips Keamanan
                    </strong>
                    App Password hanya berlaku untuk layanan ini.<br>
                    Jika bocor, hapus di Google dan buat baru.<br>
                    Gunakan akun Gmail khusus, bukan akun pribadi.
                </div>

            </div>
        </div>

    </div>
</div>

{{-- ═══════════════════════════════════════════════
     TAB 4: BANK DEPOSIT
     ═══════════════════════════════════════════════ --}}
<div id="tab-bank" class="stg-tab-pane">
    <div class="stg-grid">
        <div class="stg-card">
            <div class="stg-card-hdr">
                <div class="stg-card-title">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                    Daftar Bank Deposit
                </div>
            </div>

            <div class="card-body" style="padding: 20px;">
                @if(session('bank_success'))
                <div class="stg-alert success">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('bank_success') }}</span>
                </div>
                @endif

                <form action="{{ route('admin.settings.bank') }}" method="POST">
                    @csrf
                    <div id="bank-list">
                        @php $banks = \Illuminate\Support\Facades\Cache::get('deposit_banks', []); @endphp
                        @if(empty($banks))
                            <div class="stg-alert warning bank-empty-msg">
                                <span>Belum ada bank yang ditambahkan. Silakan tambah bank baru.</span>
                            </div>
                        @else
                            @foreach($banks as $index => $bank)
                            <div class="bank-item" style="display:flex; gap:10px; margin-bottom:12px; align-items:flex-end;">
                                <div style="flex:1;">
                                    <label class="stg-field-lbl" style="font-size:11px;">Nama Bank</label>
                                    <input type="text" name="banks[{{ $index }}][bank]" class="form-control" value="{{ $bank['bank'] }}" placeholder="Contoh: BCA" required>
                                </div>
                                <div style="flex:1;">
                                    <label class="stg-field-lbl" style="font-size:11px;">No. Rekening</label>
                                    <input type="text" name="banks[{{ $index }}][rekening]" class="form-control" value="{{ $bank['rekening'] }}" placeholder="12345678" required>
                                </div>
                                <div style="flex:1;">
                                    <label class="stg-field-lbl" style="font-size:11px;">Atas Nama</label>
                                    <input type="text" name="banks[{{ $index }}][atas_nama]" class="form-control" value="{{ $bank['atas_nama'] }}" placeholder="Nama Pemilik Rekening" required>
                                </div>
                                <div>
                                    <button type="button" class="btn btn-danger" onclick="this.parentElement.parentElement.remove()" style="padding: 0.65rem; border-radius: 6px; background:#dc2626; color:#fff; border:none; cursor:pointer;">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </div>
                            @endforeach
                        @endif
                    </div>

                    <button type="button" class="btn btn-outline-primary" onclick="addBank()" style="margin-top: 12px; font-weight: 600;">
                        <i class="bx bx-plus me-1"></i> Tambah Rekening
                    </button>

                    <div style="margin-top: 20px; padding-top: 16px; border-top: 1px solid var(--color-border);">
                        <button type="submit" class="btn btn-primary" style="display:inline-flex; align-items:center; gap:6px; font-weight:600;">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                            Simpan Daftar Bank
                        </button>
                    </div>
                </form>

            </div>
        </div>
        
        {{-- Panduan Bank --}}
        <div class="stg-card">
            <div class="stg-card-hdr">
                <div class="stg-card-title">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Panduan Deposit
                </div>
            </div>
            <div class="card-body" style="padding: 4px 20px 16px;">
                <div class="tip-item">
                    <div class="tip-icon">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    </div>
                    <div class="tip-text">
                        <strong>Daftar Bank</strong>
                        <span>Rekening yang ditambahkan di sini akan tampil sebagai pilihan di halaman <b>Isi Saldo</b> member.</span>
                    </div>
                </div>
                <div class="tip-item">
                    <div class="tip-icon">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    </div>
                    <div class="tip-text">
                        <strong>Otomatisasi</strong>
                        <span>Ketika member mengambil tiket, tiket akan langsung masuk ke tabel `tiket_deposit` di database OtomaX. Modul internet banking OtomaX akan otomatis memprosesnya.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════
     TAB 5: SYSTEM LOGS
     ═══════════════════════════════════════════════ --}}
<div id="tab-logs" class="stg-tab-pane">
    <div class="stg-card">
        <div class="stg-card-hdr">
            <div class="stg-card-title">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7"/>
                </svg>
                System Logs (laravel.log)
            </div>
            <form action="{{ route('admin.logs.clear') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus semua log? Tindakan ini tidak bisa dibatalkan.');">
                @csrf
                <button type="submit" class="btn btn-danger btn-sm">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Clear Logs
                </button>
            </form>
        </div>

        <div class="card-body" style="padding: 0;">
            <div id="logContainer" style="background: #1e1e1e; color: #d4d4d4; font-family: 'Consolas', 'Courier New', monospace; font-size: 13px; padding: 16px; max-height: 600px; overflow-y: auto; white-space: pre-wrap; word-wrap: break-word;">
@if(empty($logs))
Tidak ada log tersedia.
@else
{{ $logs }}
@endif
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════
     TAB KEAMANAN (reCAPTCHA)
     ═══════════════════════════════════════════════ --}}
<div id="tab-security" class="stg-tab-pane">
    <div class="stg-grid">
        <div class="stg-card">
            <div class="stg-card-hdr">
                <div class="stg-card-title">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    Google reCAPTCHA v2
                </div>
            </div>
            
            <form action="{{ route('admin.settings.security') }}" method="POST">
                @csrf
                <div class="card-body" style="padding: 24px;">
                    @if(session('security_success'))
                        <div class="alert alert-success" style="margin-bottom: 20px;">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>{{ session('security_success') }}</span>
                        </div>
                    @endif

                    <div class="form-group" style="margin-bottom: 16px;">
                        <label class="stg-field-lbl">Status reCAPTCHA</label>
                        <select name="recaptcha_enabled" class="form-control" style="max-width: 200px;">
                            <option value="1" {{ \Illuminate\Support\Facades\Cache::get('recaptcha_enabled', false) ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ !\Illuminate\Support\Facades\Cache::get('recaptcha_enabled', false) ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom: 16px;">
                        <label class="stg-field-lbl">Site Key</label>
                        <input type="text" name="recaptcha_site_key" class="form-control" 
                               value="{{ \Illuminate\Support\Facades\Cache::get('recaptcha_site_key') }}" 
                               placeholder="Contoh: 6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI">
                        <small style="color: var(--color-text-3); display: block; margin-top: 6px;">
                            Didapatkan dari konsol admin Google reCAPTCHA (v2 Checkbox).
                        </small>
                    </div>

                    <div class="form-group" style="margin-bottom: 24px;">
                        <label class="stg-field-lbl">Secret Key</label>
                        <input type="text" name="recaptcha_secret_key" class="form-control" 
                               value="{{ $recaptchaSecret ?? '' }}" 
                               placeholder="Contoh: 6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe">
                    </div>

                    <button type="submit" class="btn btn-primary" style="padding: 10px 20px;">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        Simpan Pengaturan
                    </button>
                </div>
            </form>
        </div>

        <div class="stg-card">
            <div class="stg-card-hdr">
                <div class="stg-card-title">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Informasi
                </div>
            </div>
            <div class="card-body" style="padding: 16px 20px;">
                <p style="font-size: 13.5px; color: var(--color-text-2); line-height: 1.6; margin-bottom: 12px;">
                    Google reCAPTCHA akan melindungi form Login dan Pendaftaran dari bot dan serangan brute-force otomatis.
                </p>
                <ol style="font-size: 13px; color: var(--color-text-3); padding-left: 20px; line-height: 1.6; margin: 0;">
                    <li>Buka <a href="https://www.google.com/recaptcha/admin/create" target="_blank" style="color: var(--color-primary); text-decoration: none;">Google reCAPTCHA</a>.</li>
                    <li>Pilih versi <strong>reCAPTCHA v2 (Kotak centang "Saya bukan robot")</strong>.</li>
                    <li>Daftarkan domain Anda (misal: <em>namadomain.com</em>).</li>
                    <li>Salin Site Key dan Secret Key ke form di samping.</li>
                </ol>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════
     TAB: IP CENTER & REGEX BALASAN JAWABAN
     ═══════════════════════════════════════════════ --}}
<div id="tab-apicenter" class="stg-tab-pane">
    <div class="stg-grid">
        <div class="stg-card">
            <div class="stg-card-hdr">
                <div class="stg-card-title">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="2"></circle>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.24 7.76a6 6 0 0 1 0 8.49m-8.48-.01a6 6 0 0 1 0-8.49m11.31-2.82a10 10 0 0 1 0 14.14m-14.14 0a10 10 0 0 1 0-14.14"/>
                    </svg>
                    IP Center & Regex Balasan Jawaban Transaksi
                </div>
            </div>

            <form action="{{ route('admin.settings.apicenter') }}" method="POST">
                @csrf
                <div class="card-body" style="padding: 24px;">
                    @if(session('apicenter_success'))
                        <div class="stg-alert success">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>{{ session('apicenter_success') }}</span>
                        </div>
                    @endif

                    <div class="stg-label">IP Center & Endpoint URL</div>

                    <div class="stg-field">
                        <label class="stg-field-lbl">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" stroke-linejoin="round" d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                            IP Center / Endpoint URL
                        </label>
                        <input type="text" name="api_endpoint" class="form-control font-monospace"
                               value="{{ old('api_endpoint', \Illuminate\Support\Facades\Cache::get('api_endpoint', 'http://103.166.91.114:5558/')) }}"
                               placeholder="http://103.166.91.114:5558/" required>
                        <div class="stg-hint">URL endpoint IP Center ini akan ditampilkan kepada member di menu <b>Format Transaksi & API</b>.</div>
                    </div>

                    <div style="margin-top: 24px; padding-top: 18px; border-top: 1px solid var(--color-border);">
                        <div class="stg-label">Regex Penangkapan Balasan Server (Parser Response)</div>

                        <div class="stg-field">
                            <label class="stg-field-lbl">
                                <span class="badge bg-label-success me-1" style="font-size: 10px;">Sukses</span>
                                Pola Regex Balasan Sukses
                            </label>
                            <input type="text" name="regex_success" class="form-control font-monospace"
                                   value="{{ old('regex_success', \Illuminate\Support\Facades\Cache::get('api_regex_success', '(?i).*SUKSES.*|.*BERHASIL.*|.*SN[:\s=]+(?<sn>[^\s,]+).*')) }}"
                                   placeholder="(?i).*SUKSES.*|.*BERHASIL.*|.*SN[:\s=]+(?<sn>[^\s,]+).*" required>
                            <div class="stg-hint">Pola ekspresi reguler untuk menangkap status transaksi berhasil dan nomor Serial Number (SN).</div>
                        </div>

                        <div class="stg-field">
                            <label class="stg-field-lbl">
                                <span class="badge bg-label-danger me-1" style="font-size: 10px;">Gagal</span>
                                Pola Regex Balasan Gagal
                            </label>
                            <input type="text" name="regex_failed" class="form-control font-monospace"
                                   value="{{ old('regex_failed', \Illuminate\Support\Facades\Cache::get('api_regex_failed', '(?i).*GAGAL.*|.*SALDO TIDAK CUKUP.*|.*NOMOR SALAH.*')) }}"
                                   placeholder="(?i).*GAGAL.*|.*SALDO TIDAK CUKUP.*|.*NOMOR SALAH.*" required>
                            <div class="stg-hint">Pola ekspresi reguler untuk menangkap balasan transaksi gagal atau ditolak.</div>
                        </div>

                        <div class="stg-field">
                            <label class="stg-field-lbl">
                                <span class="badge bg-label-warning me-1" style="font-size: 10px;">Menunggu</span>
                                Pola Regex Balasan Menunggu / Diproses
                            </label>
                            <input type="text" name="regex_pending" class="form-control font-monospace"
                                   value="{{ old('regex_pending', \Illuminate\Support\Facades\Cache::get('api_regex_pending', '(?i).*DIPROSES.*|.*MENUNGGU.*|.*PENDING.*')) }}"
                                   placeholder="(?i).*DIPROSES.*|.*MENUNGGU.*|.*PENDING.*" required>
                            <div class="stg-hint">Pola ekspresi reguler untuk menangkap status transaksi yang sedang diproses antrean server.</div>
                        </div>
                    </div>

                    <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--color-border);">
                        <button type="submit" class="btn btn-primary" style="padding: 10px 20px;">
                            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                            Simpan Pengaturan IP Center & Regex
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="stg-card">
            <div class="stg-card-hdr">
                <div class="stg-card-title">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Informasi & Integrasi
                </div>
            </div>
            <div class="card-body" style="padding: 18px 20px;">
                <p style="font-size: 13px; color: var(--color-text-2); line-height: 1.6; margin-bottom: 12px;">
                    Pengaturan ini mengontrol konfigurasi yang dipublikasikan ke mitra/reseller di halaman <strong>Format Transaksi & API</strong> member:
                </p>
                <div class="tip-item mb-3">
                    <div class="tip-icon">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" stroke-linejoin="round" d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                    </div>
                    <div class="tip-text">
                        <strong>Endpoint URL</strong>
                        <span>Alamat IP dan Port Center OtomaX yang dihubungi oleh modul H2H / bot mitra untuk kirim transaksi.</span>
                    </div>
                </div>
                <div class="tip-item">
                    <div class="tip-icon">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                    </div>
                    <div class="tip-text">
                        <strong>Regex Balasan</strong>
                        <span>Digunakan oleh parser regex mitra untuk membedakan respon transaksi Sukses, Gagal, atau Menunggu.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════
     TAB: AKUN & PASSWORD ADMINISTRATOR
     ═══════════════════════════════════════════════ --}}
<div id="tab-account" class="stg-tab-pane">
    <div class="stg-grid">

        {{-- Form Ganti Password & Username --}}
        <div class="stg-card">
            <div class="stg-card-hdr">
                <div class="stg-card-title">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    Kredensial Administrator
                </div>
                <span class="badge bg-label-primary">Keamanan Utama</span>
            </div>
            <div class="card-body" style="padding: 20px;">
                @if(session('account_success'))
                <div class="stg-alert success">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('account_success') }}</span>
                </div>
                @endif

                @if(session('account_error'))
                <div class="stg-alert error">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>{{ session('account_error') }}</span>
                </div>
                @endif

                <form action="{{ route('admin.settings.account') }}" method="POST">
                    @csrf

                    <div class="stg-label">Identitas Login</div>

                    <div class="stg-field">
                        <div class="stg-field-lbl">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            Username Administrator
                        </div>
                        <input type="text" name="new_username" class="form-control"
                            value="{{ old('new_username', $currentAdminUsername ?? 'admin') }}" required>
                        <div class="stg-hint">Gunakan username unik yang mudah Anda ingat.</div>
                    </div>

                    <div style="margin-top: 24px; padding-top: 18px; border-top: 1px solid var(--color-border);">
                        <div class="stg-label">Pergantian Password</div>

                        <div class="stg-field">
                            <div class="stg-field-lbl">
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                Password Saat Ini <span class="lbl-warn">* Wajib untuk konfirmasi</span>
                            </div>
                            <div class="pwd-wrap">
                                <input type="password" name="current_password" id="admin_curr_pass" class="form-control" placeholder="Masukkan password admin saat ini" required>
                                <button type="button" class="pwd-eye" onclick="toggleInputVisibility('admin_curr_pass', this)">
                                    <i class="bx bx-show fs-5"></i>
                                </button>
                            </div>
                            <div class="stg-hint">Wajib dimasukkan sebelum menyimpan perubahan.</div>
                        </div>

                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <div class="stg-field">
                                    <div class="stg-field-lbl">
                                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                                        Password Baru (Opsional)
                                    </div>
                                    <div class="pwd-wrap">
                                        <input type="password" name="new_password" id="admin_new_pass" class="form-control" placeholder="Kosongkan jika tidak ingin ganti">
                                        <button type="button" class="pwd-eye" onclick="toggleInputVisibility('admin_new_pass', this)">
                                            <i class="bx bx-show fs-5"></i>
                                        </button>
                                    </div>
                                    <div class="stg-hint">Minimal 6 karakter, kombinasi huruf & angka.</div>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="stg-field">
                                    <div class="stg-field-lbl">
                                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                        Ulangi Password Baru
                                    </div>
                                    <div class="pwd-wrap">
                                        <input type="password" name="new_password_confirmation" id="admin_conf_pass" class="form-control" placeholder="Ketik ulang password baru">
                                        <button type="button" class="pwd-eye" onclick="toggleInputVisibility('admin_conf_pass', this)">
                                            <i class="bx bx-show fs-5"></i>
                                        </button>
                                    </div>
                                    <div class="stg-hint">Harus sama dengan password baru di samping.</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div style="margin-top: 24px; padding-top: 18px; border-top: 1px solid var(--color-border);">
                        <button type="submit" class="btn btn-primary" style="font-weight:600; gap:6px;">
                            <i class="bx bx-save fs-5"></i> Simpan Perubahan Akun
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Panel Informasi Keamanan --}}
        <div class="stg-card">
            <div class="stg-card-hdr">
                <div class="stg-card-title">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Keamanan Kredensial
                </div>
            </div>
            <div class="card-body" style="padding: 16px 20px;">
                <div class="tip-item">
                    <div class="tip-icon">
                        <i class="bx bx-lock-alt fs-5"></i>
                    </div>
                    <div class="tip-text">
                        <strong>Enkripsi Password Bcrypt</strong>
                        <span>Password tidak lagi disimpan dalam bentuk teks polos di file <code>.env</code>, melainkan di-hash dengan algoritma Bcrypt (12 rounds) standar industri.</span>
                    </div>
                </div>

                <div class="tip-item">
                    <div class="tip-icon">
                        <i class="bx bx-shield-check fs-5"></i>
                    </div>
                    <div class="tip-text">
                        <strong>Perlindungan Brute Force</strong>
                        <span>Login admin dilindungi dengan Rate Limiting (pembatasan percobaan login salah berturut-turut) dan proteksi session fixation.</span>
                    </div>
                </div>

                <div class="tip-item">
                    <div class="tip-icon">
                        <i class="bx bx-history fs-5"></i>
                    </div>
                    <div class="tip-text">
                        <strong>Pencatatan Audit Log</strong>
                        <span>Setiap percobaan masuk dan perubahan kredensial login dicatat ke dalam audit log server beserta alamat IP penyerang/pengguna.</span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

    {{-- ═══════════════════════════════════════════════
         TAB 9: PENGATURAN STRUK & LOGO PRODUK
         ═══════════════════════════════════════════════ --}}
    <div id="tab-receipt" class="stg-tab-pane">
        <div class="d-flex flex-column gap-4">

            @if(session('receipt_success'))
                <div class="stg-alert success">
                    <i class="bx bx-check-circle fs-5"></i>
                    <div>{{ session('receipt_success') }}</div>
                </div>
            @endif

            @if($errors->has('name') || $errors->has('keywords') || $errors->has('target') || $errors->has('logo_file'))
                <div class="stg-alert error">
                    <i class="bx bx-error-circle fs-5"></i>
                    <div>{{ $errors->first() }}</div>
                </div>
            @endif

            <!-- Card 1: Form Tambah / Edit Aturan Logo Struk (FULL WIDTH) -->
            <div class="stg-card">
                <div class="stg-card-hdr">
                    <div class="stg-card-hdr-left">
                        <div class="stg-icon-box">
                            <i class="bx bx-receipt fs-5"></i>
                        </div>
                        <div>
                            <div class="stg-card-title" id="ruleFormTitle">Tambah Aturan Logo & Format Struk</div>
                            <div class="stg-card-sub">Tentukan logo, kategori struk, dan target provider atau kode produk yang cocok</div>
                        </div>
                    </div>
                </div>

                <div class="stg-card-body">
                    <form method="POST" action="{{ route('admin.settings.receipt.rule') }}" enctype="multipart/form-data" id="formReceiptRule">
                        @csrf
                        <input type="hidden" name="rule_id" id="rule_id" value="">
                        <input type="hidden" name="existing_logo_url" id="existing_logo_url" value="/images/receipt/pln.svg">

                        <div class="row g-3">
                            <!-- Kategori Struk -->
                            <div class="col-12 col-md-6">
                                <label class="stg-field-lbl">
                                    Kategori Format Struk <span class="text-danger">*</span>
                                </label>
                                <select name="category" id="rule_category" class="form-select form-control" required onchange="handleCategoryChange(this.value)">
                                    <option value="PRABAYAR">PRABAYAR (PLN Token / Stroom - Format Gambar 2)</option>
                                    <option value="PASCA BAYAR">PASCA BAYAR (PLN Tagihan / Rekening - Format Gambar 1)</option>
                                    <option value="BANK">BANK (Transfer Uang Bank Resmi - Format Gambar 3)</option>
                                    <option value="UMUM">UMUM (Pulsa, Data, E-Wallet, PPOB Biasa)</option>
                                </select>
                                <div class="stg-hint">Pilih layout struk yang cocok persis dengan jenis produk</div>
                            </div>

                            <!-- Nama Aturan -->
                            <div class="col-12 col-md-6">
                                <label class="stg-field-lbl">
                                    Nama Label Struk <span class="text-muted fw-normal">(Opsional)</span>
                                </label>
                                <input type="text" name="name" id="rule_name" class="form-control" placeholder="Contoh: Transfer Bank BCA / PLN Token (Otomatis jika kosong)">
                                <div class="stg-hint">Jika dikosongkan, nama otomatis diambil dari Provider atau Kode Produk</div>
                            </div>

                            <!-- Opsi Target Pencocokan: Provider vs Kode Produk -->
                            <div class="col-12 col-md-6">
                                <label class="stg-field-lbl d-flex align-items-center justify-content-between">
                                    <span>Target By Provider / Operator</span>
                                    <span class="badge bg-label-primary font-monospace fw-normal" style="font-size: 11px;">Otomatis Semua Produk</span>
                                </label>
                                <input type="text" name="provider" id="rule_provider" class="form-control" placeholder="Contoh: PLN, BCA, BRI, MANDIRI, TELKOMSEL">
                                <div class="stg-hint text-primary">
                                    <i class="bx bx-check-shield me-1"></i>Otomatis berlaku untuk <strong>SEMUA produk</strong> di bawah provider ini.
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="stg-field-lbl d-flex align-items-center justify-content-between">
                                    <span>Target By Kode Produk OtomaX</span>
                                    <span class="badge bg-label-secondary font-monospace fw-normal" style="font-size: 11px;">Khusus Kode Tertentu</span>
                                </label>
                                <input type="text" name="keywords" id="rule_keywords" class="form-control" placeholder="Contoh: PLNPASCA, TFBCA, S10 (Pisahkan tanda koma)">
                                <div class="stg-hint">
                                    <i class="bx bx-target-lock me-1"></i>Hanya berlaku spesifik untuk transaksi dengan kode produk tersebut.
                                </div>
                            </div>

                            <!-- File Logo -->
                            <div class="col-12 col-md-6">
                                <label class="stg-field-lbl">
                                    Upload File Logo (PNG / JPG / SVG / WebP)
                                </label>
                                <input type="file" name="logo_file" id="rule_logo_file" class="form-control" accept="image/*" onchange="previewSelectedLogo(this)">
                                <div class="stg-hint">Disarankan gambar transparan dengan resolusi tajam</div>
                            </div>

                            <!-- Biaya Admin Bawaan -->
                            <div class="col-12 col-md-6">
                                <label class="stg-field-lbl">
                                    Biaya Admin Bawaan (Rp)
                                </label>
                                <input type="number" name="default_admin" id="rule_admin" class="form-control" value="2500" min="0" step="100">
                                <div class="stg-hint">Akan otomatis terjumlahkan pada Total Bayar saat cetak</div>
                            </div>

                            <!-- Khusus Kategori BANK: Nama Bank & Remark -->
                            <div class="col-12 col-md-6 field-bank-only" style="display: none;">
                                <label class="stg-field-lbl">
                                    Nama Resmi Bank (Header Kanan Bar Biru)
                                </label>
                                <input type="text" name="bank_name" id="rule_bank_name" class="form-control" placeholder="Contoh: BANK CENTRAL ASIA">
                            </div>

                            <div class="col-12 col-md-6 field-bank-only" style="display: none;">
                                <label class="stg-field-lbl">
                                    Keterangan Transfer (Remark)
                                </label>
                                <input type="text" name="remark" id="rule_remark" class="form-control" value="Transfer Uang" placeholder="Contoh: Transfer Uang">
                            </div>

                            <!-- Catatan Footer -->
                            <div class="col-12">
                                <label class="stg-field-lbl">
                                    Catatan / Kontak di Bawah Struk
                                </label>
                                <input type="text" name="footer_info" id="rule_footer" class="form-control" placeholder="Contoh: Call Center 123 • Mobile PLN • PLN Terdekat">
                            </div>

                            <!-- ═══════════════════════════════════════════════
                                 PENGATURAN POLA & PARSER SN SUPLIER (FULL WIDTH)
                                 ═══════════════════════════════════════════════ -->
                            <!-- ═══════════════════════════════════════════════
                                 PENGATURAN POLA & PARSER SN SUPLIER (FULL WIDTH)
                                 ═══════════════════════════════════════════════ -->
                            <div class="col-12 mt-3 pt-3 border-top">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <label class="stg-field-lbl fw-bold mb-0 text-dark">
                                        <i class="bx bx-code-curly me-1 text-primary"></i>Pola Ekstraksi / Parser Balasan SN Suplier
                                    </label>
                                    <span class="badge bg-label-info font-monospace" style="font-size: 11px;">Fleksibel & Presisi</span>
                                </div>
                                <div class="small text-muted mb-3">
                                    Pilih cara sistem membaca data dari teks SN balasan suplier. Anda dapat mengatur regex per kolom struk secara mandiri (misal: menentukan regex mana untuk Nomor Meter, ID Pelanggan, Nama, Token, dll).
                                </div>

                                <!-- Mode Parser Selector (4 Mode) -->
                                <div class="row g-2 mb-3">
                                    <div class="col-12 col-md-3">
                                        <div class="form-check border rounded p-2 px-3 h-100" style="background: #f8fafc; cursor: pointer;">
                                            <input class="form-check-input" type="radio" name="parser_mode" id="parser_auto" value="auto" checked onchange="handleParserModeChange(this.value)">
                                            <label class="form-check-label small fw-semibold" for="parser_auto" style="cursor: pointer;">
                                                Smart Auto-Detect
                                                <div class="text-muted fw-normal" style="font-size: 11px;">Deteksi cerdas otomatis tanpa setting</div>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <div class="form-check border border-primary rounded p-2 px-3 h-100" style="background: #eff6ff; cursor: pointer;">
                                            <input class="form-check-input" type="radio" name="parser_mode" id="parser_fields" value="fields" onchange="handleParserModeChange(this.value)">
                                            <label class="form-check-label small fw-bold text-primary" for="parser_fields" style="cursor: pointer;">
                                                Regex Per Kolom Struk
                                                <div class="text-muted fw-normal" style="font-size: 11px;">Setel regex khusus No Meter, Nama, Token, dll</div>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <div class="form-check border rounded p-2 px-3 h-100" style="background: #f8fafc; cursor: pointer;">
                                            <input class="form-check-input" type="radio" name="parser_mode" id="parser_delimiter" value="delimiter" onchange="handleParserModeChange(this.value)">
                                            <label class="form-check-label small fw-semibold" for="parser_delimiter" style="cursor: pointer;">
                                                Pemisah Delimiter
                                                <div class="text-muted fw-normal" style="font-size: 11px;">Dipisahkan simbol garis miring (/) atau (#)</div>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <div class="form-check border rounded p-2 px-3 h-100" style="background: #f8fafc; cursor: pointer;">
                                            <input class="form-check-input" type="radio" name="parser_mode" id="parser_regex" value="regex" onchange="handleParserModeChange(this.value)">
                                            <label class="form-check-label small fw-semibold" for="parser_regex" style="cursor: pointer;">
                                                Full Regex (1 Baris)
                                                <div class="text-muted fw-normal" style="font-size: 11px;">Pola satu baris named capture groups</div>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Sub-Form 1: REGEX PER KOLOM STRUK (SANGAT MUDAH & INTUITIF) -->
                                <div id="box_parser_fields" class="p-3 border border-primary rounded mb-3" style="background: #f0f7ff; display: none;">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <div class="fw-bold text-primary small">
                                            <i class="bx bx-slider-alt me-1"></i>Atur Regex untuk Setiap Kolom yang Ada di Struk:
                                        </div>
                                        <span class="small text-muted" style="font-size: 11px;">Kolom yang dikosongkan akan otomatis memakai deteksi cerdas bawaan.</span>
                                    </div>

                                    <div class="row g-2">
                                        <!-- No Meter -->
                                        <div class="col-12 col-md-6">
                                            <label class="small fw-semibold text-dark mb-1">
                                                Regex Nomor Meter (PLN):
                                            </label>
                                            <input type="text" name="regex_meter" id="rule_regex_meter" class="form-control form-control-sm font-monospace" placeholder="/(?:METER|NO\.?\s*METER)[:\s]*(\d+)/i" oninput="updateLivePreview()">
                                            <div class="text-muted" style="font-size: 10px;">Mengambil nomor meter fisik pelanggan dari teks SN</div>
                                        </div>

                                        <!-- ID Pelanggan -->
                                        <div class="col-12 col-md-6">
                                            <label class="small fw-semibold text-dark mb-1">
                                                Regex ID Pelanggan (IDPEL):
                                            </label>
                                            <input type="text" name="regex_idpel" id="rule_regex_idpel" class="form-control form-control-sm font-monospace" placeholder="/(?:IDPEL|ID\s*PEL)[:\s]*(\d+)/i" oninput="updateLivePreview()">
                                            <div class="text-muted" style="font-size: 10px;">Mengambil ID pelanggan PLN / PDAM / BPJS</div>
                                        </div>

                                        <!-- Nama Pelanggan -->
                                        <div class="col-12 col-md-6">
                                            <label class="small fw-semibold text-dark mb-1">
                                                Regex Nama Pelanggan:
                                            </label>
                                            <input type="text" name="regex_nama" id="rule_regex_nama" class="form-control form-control-sm font-monospace" placeholder="/(?:NAMA|AN|A\/N)[:\s]*([^\/,;]+)/i" oninput="updateLivePreview()">
                                            <div class="text-muted" style="font-size: 10px;">Mengambil nama lengkap pelanggan dari SN</div>
                                        </div>

                                        <!-- Tarif / Daya -->
                                        <div class="col-12 col-md-6">
                                            <label class="small fw-semibold text-dark mb-1">
                                                Regex Tarif & Daya:
                                            </label>
                                            <input type="text" name="regex_tarif" id="rule_regex_tarif" class="form-control form-control-sm font-monospace" placeholder="/(?:TARIF|DAYA)[:\s]*([^\/,;]+)/i" oninput="updateLivePreview()">
                                            <div class="text-muted" style="font-size: 10px;">Contoh hasil: R1/900VA, R1M/900VA, B1/450VA</div>
                                        </div>

                                        <!-- Token 20 Digit -->
                                        <div class="col-12 col-md-6">
                                            <label class="small fw-semibold text-dark mb-1">
                                                Regex Nomor Token 20 Digit (Prabayar):
                                            </label>
                                            <input type="text" name="regex_token" id="rule_regex_token" class="form-control form-control-sm font-monospace" placeholder="/(\d{4}[-\s]?\d{4}[-\s]?\d{4}[-\s]?\d{4}[-\s]?\d{4}|\b\d{20}\b)/" oninput="updateLivePreview()">
                                            <div class="text-muted" style="font-size: 10px;">Mengambil 20 digit nomor stroom listrik</div>
                                        </div>

                                        <!-- Jumlah KWH -->
                                        <div class="col-12 col-md-6">
                                            <label class="small fw-semibold text-dark mb-1">
                                                Regex Jumlah kWh (Prabayar):
                                            </label>
                                            <input type="text" name="regex_kwh" id="rule_regex_kwh" class="form-control form-control-sm font-monospace" placeholder="/(?:KWH|JML\s*KWH)[:\s]*([\d\.,]+)/i" oninput="updateLivePreview()">
                                            <div class="text-muted" style="font-size: 10px;">Mengambil nilai kWh yang diperoleh pembeli</div>
                                        </div>

                                        <!-- Stand Meter -->
                                        <div class="col-12 col-md-6">
                                            <label class="small fw-semibold text-dark mb-1">
                                                Regex Stand Meter (Pascabayar):
                                            </label>
                                            <input type="text" name="regex_stand" id="rule_regex_stand" class="form-control form-control-sm font-monospace" placeholder="/(?:STAND|STAND\s*METER)[:\s]*([^\/,;]+)/i" oninput="updateLivePreview()">
                                            <div class="text-muted" style="font-size: 10px;">Contoh: 7447-6541</div>
                                        </div>

                                        <!-- Bulan / Tahun (BL/TH) -->
                                        <div class="col-12 col-md-6">
                                            <label class="small fw-semibold text-dark mb-1">
                                                Regex Bulan/Tahun Tagihan (BL/TH):
                                            </label>
                                            <input type="text" name="regex_blth" id="rule_regex_blth" class="form-control form-control-sm font-monospace" placeholder="/(?:BLN|BL\/TH|PERIODE)[:\s]*([^\/,;]+)/i" oninput="updateLivePreview()">
                                            <div class="text-muted" style="font-size: 10px;">Contoh: OKT/2026 atau OKT26</div>
                                        </div>

                                        <!-- Tagihan / Nominal -->
                                        <div class="col-12 col-md-6">
                                            <label class="small fw-semibold text-dark mb-1">
                                                Regex Tagihan / Nominal Produk:
                                            </label>
                                            <input type="text" name="regex_tagihan" id="rule_regex_tagihan" class="form-control form-control-sm font-monospace" placeholder="/(?:TAGIHAN|TAG|RP|NOMINAL)[:\s]*Rp?\s*([\d\.,]+)/i" oninput="updateLivePreview()">
                                            <div class="text-muted" style="font-size: 10px;">Mengambil nominal tagihan listrik atau transfer</div>
                                        </div>

                                        <!-- Kode REFF / Ref Suplier -->
                                        <div class="col-12 col-md-6">
                                            <label class="small fw-semibold text-dark mb-1">
                                                Regex Kode Referensi (REFF):
                                            </label>
                                            <input type="text" name="regex_reff" id="rule_regex_reff" class="form-control form-control-sm font-monospace" placeholder="/(?:REFF|REF|SN)[:\s]*([A-Za-z0-9]+)/i" oninput="updateLivePreview()">
                                            <div class="text-muted" style="font-size: 10px;">Kode unik referensi transaksi dari PLN / Bank</div>
                                        </div>

                                        <!-- No Rekening (Khusus Bank) -->
                                        <div class="col-12 col-md-6 field-bank-only" style="display: none;">
                                            <label class="small fw-semibold text-dark mb-1">
                                                Regex Nomor Rekening Bank:
                                            </label>
                                            <input type="text" name="regex_norek" id="rule_regex_norek" class="form-control form-control-sm font-monospace" placeholder="/(?:NOREK|REK|REKENING)[:\s]*(\d+)/i" oninput="updateLivePreview()">
                                            <div class="text-muted" style="font-size: 10px;">Nomor rekening tujuan penerima transfer</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Sub-Form 2: Delimiter Template -->
                                <div id="box_parser_delimiter" class="p-3 border rounded mb-3 bg-light" style="display: none;">
                                    <div class="row g-2">
                                        <div class="col-12 col-md-3">
                                            <label class="small fw-semibold text-secondary mb-1">Simbol Pembatas (Delimiter)</label>
                                            <input type="text" name="sn_delimiter" id="rule_sn_delimiter" class="form-control form-control-sm font-monospace text-center" value="/" placeholder="/" maxlength="5" oninput="updateLivePreview()">
                                        </div>
                                        <div class="col-12 col-md-9">
                                            <label class="small fw-semibold text-secondary mb-1">Susunan Kolom Balasan Suplier</label>
                                            <input type="text" name="sn_pattern" id="rule_sn_pattern" class="form-control form-control-sm font-monospace" placeholder="[TOKEN]/[METER]/[IDPEL]/[NAMA]/[TARIF]/[KWH]" oninput="updateLivePreview()">
                                        </div>
                                        <div class="col-12">
                                            <div class="small text-muted mb-1" style="font-size: 11px;">Klik tag untuk menambahkan ke susunan:</div>
                                            <div class="d-flex flex-wrap gap-1">
                                                <button type="button" class="btn btn-xs btn-outline-primary py-0 px-2" style="font-size: 11px;" onclick="insertPatternTag('[TOKEN]')">+ [TOKEN]</button>
                                                <button type="button" class="btn btn-xs btn-outline-primary py-0 px-2" style="font-size: 11px;" onclick="insertPatternTag('[METER]')">+ [METER]</button>
                                                <button type="button" class="btn btn-xs btn-outline-primary py-0 px-2" style="font-size: 11px;" onclick="insertPatternTag('[IDPEL]')">+ [IDPEL]</button>
                                                <button type="button" class="btn btn-xs btn-outline-primary py-0 px-2" style="font-size: 11px;" onclick="insertPatternTag('[NAMA]')">+ [NAMA]</button>
                                                <button type="button" class="btn btn-xs btn-outline-primary py-0 px-2" style="font-size: 11px;" onclick="insertPatternTag('[TARIF]')">+ [TARIF]</button>
                                                <button type="button" class="btn btn-xs btn-outline-primary py-0 px-2" style="font-size: 11px;" onclick="insertPatternTag('[KWH]')">+ [KWH]</button>
                                                <button type="button" class="btn btn-xs btn-outline-primary py-0 px-2" style="font-size: 11px;" onclick="insertPatternTag('[NOREK]')">+ [NOREK]</button>
                                                <button type="button" class="btn btn-xs btn-outline-primary py-0 px-2" style="font-size: 11px;" onclick="insertPatternTag('[TAGIHAN]')">+ [TAGIHAN]</button>
                                                <button type="button" class="btn btn-xs btn-outline-primary py-0 px-2" style="font-size: 11px;" onclick="insertPatternTag('[STAND]')">+ [STAND]</button>
                                                <button type="button" class="btn btn-xs btn-outline-primary py-0 px-2" style="font-size: 11px;" onclick="insertPatternTag('[BLTH]')">+ [BLTH]</button>
                                                <button type="button" class="btn btn-xs btn-outline-primary py-0 px-2" style="font-size: 11px;" onclick="insertPatternTag('[REFF]')">+ [REFF]</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Sub-Form 3: Custom Regex (1 Baris) -->
                                <div id="box_parser_regex" class="p-3 border rounded mb-3 bg-light" style="display: none;">
                                    <label class="small fw-semibold text-secondary mb-1">Pola Regex (Named Capture Group Satu Baris)</label>
                                    <input type="text" name="sn_regex" id="rule_sn_regex" class="form-control form-control-sm font-monospace" placeholder="/(?P<token>\d{20}).*NAMA:(?P<nama>[^\/]+)/i" oninput="updateLivePreview()">
                                    <div class="stg-hint" style="font-size: 11px;">
                                        Gunakan named group seperti <code>(?P&lt;token&gt;...)</code>, <code>(?P&lt;meter&gt;...)</code>, <code>(?P&lt;idpel&gt;...)</code>, <code>(?P&lt;nama&gt;...)</code>, <code>(?P&lt;tarif&gt;...)</code>, <code>(?P&lt;kwh&gt;...)</code>.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="resetRuleForm()">
                                <i class="bx bx-reset me-1"></i>Batal / Reset Form
                            </button>
                            <div class="d-flex gap-2">
                                <a href="#box_live_preview" class="btn btn-outline-primary btn-sm">
                                    <i class="bx bx-show me-1"></i>Lihat Preview Struk di Bawah
                                </a>
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="bx bx-save me-1"></i>Simpan Aturan Struk
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Card 2: Uji Coba Balasan SN Suplier & Live Preview Struk (FULL WIDTH) -->
            <div class="stg-card" id="box_live_preview">
                <div class="stg-card-hdr">
                    <div class="stg-card-hdr-left">
                        <div class="stg-icon-box" style="background: rgba(11, 111, 182, 0.1); color: #0B6FB6;">
                            <i class="bx bx-show fs-5"></i>
                        </div>
                        <div>
                            <div class="stg-card-title d-flex align-items-center gap-2">
                                <span>Uji Coba Balasan SN & Live Preview Struk</span>
                                <span class="badge bg-success font-monospace" style="font-size: 10px; padding: 2px 6px;">
                                    <span style="display:inline-block; width:6px; height:6px; background:#fff; border-radius:50%; margin-right:3px;"></span>Real-Time
                                </span>
                            </div>
                            <div class="stg-card-sub">Tes ekstraksi teks SN dan lihat hasil render struk sebelum disimpan</div>
                        </div>
                    </div>
                    <div class="d-flex gap-1 flex-wrap">
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="loadSampleSn('pln_token')">
                            <i class="bx bx-zap me-1"></i>Contoh Token PLN
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="loadSampleSn('pln_pasca')">
                            <i class="bx bx-receipt me-1"></i>Contoh Tagihan PLN
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="loadSampleSn('bank_bca')">
                            <i class="bx bx-credit-card me-1"></i>Contoh Transfer Bank
                        </button>
                    </div>
                </div>

                <div class="stg-card-body p-4">
                    <div class="row g-4 align-items-start">
                        
                        <!-- Sisi Kiri: Kotak Input SN & Status Ekstraksi (col-12 col-lg-5) -->
                        <div class="col-12 col-lg-5">
                            <label class="stg-field-lbl fw-bold text-dark mb-1">
                                <i class="bx bx-vial me-1 text-primary"></i>Teks Balasan SN dari Suplier
                            </label>
                            <textarea name="sample_sn" id="rule_sample_sn" rows="4" class="form-control font-monospace mb-3" placeholder="Paste teks SN dari suplier di sini untuk melihat ekstraksi & preview struk secara instan..." oninput="updateLivePreview()" form="formReceiptRule">SN: 2920-3211-4567-8910-0987/PLN20/01112233445/BUDI SANTOSO/R1/900VA/13.2/20000</textarea>

                            <div class="p-3 bg-light border rounded">
                                <div class="fw-bold text-dark mb-2 small">
                                    <i class="bx bx-check-circle me-1 text-success"></i>Data yang Berhasil Terbaca (Real-Time):
                                </div>
                                <div class="d-flex flex-column gap-2" id="previewBadges">
                                    <div class="d-flex justify-content-between align-items-center border-bottom pb-1 small">
                                        <span class="text-muted">No. Meter (PLN):</span>
                                        <span class="badge bg-secondary text-white font-monospace" id="badge_meter">-</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center border-bottom pb-1 small">
                                        <span class="text-muted">ID Pelanggan:</span>
                                        <span class="badge bg-secondary text-white font-monospace" id="badge_idpel">-</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center border-bottom pb-1 small">
                                        <span class="text-muted">Token 20 Digit:</span>
                                        <span class="badge bg-primary text-white font-monospace" id="badge_token">-</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center border-bottom pb-1 small">
                                        <span class="text-muted">Nama Pelanggan:</span>
                                        <span class="badge bg-info text-white" id="badge_nama">-</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center border-bottom pb-1 small">
                                        <span class="text-muted">Tarif / Daya:</span>
                                        <span class="badge bg-secondary text-white font-monospace" id="badge_tarif">-</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center border-bottom pb-1 small">
                                        <span class="text-muted">Jumlah Kwh:</span>
                                        <span class="badge bg-dark text-white font-monospace" id="badge_kwh">-</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center pt-1 small fw-bold">
                                        <span class="text-dark">Estimasi Total Bayar:</span>
                                        <span class="badge bg-success text-white font-monospace fs-6" id="badge_total">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sisi Kanan: Miniatur Struk Kertas Fisik (col-12 col-lg-7) -->
                        <div class="col-12 col-lg-7 d-flex justify-content-center">
                            <div class="preview-receipt-paper shadow-sm w-100" id="previewPaper" style="background:#ffffff; border:1px solid #71717a; border-radius:0; padding: 22px 26px; font-size:12px; line-height:1.6; color:#0f172a; max-width: 520px;">
                                
                                <!-- ── 1. BODY PLN PRABAYAR (Persis STRUK PRABAYAR.jpeg) ── -->
                                <div id="pv_body_prabayar" style="font-family:'Courier Prime', 'Roboto Slab', monospace;">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div style="font-size: 11.5px; font-weight: 700; line-height: 1.5;">
                                            <div>TGL BELI &nbsp;&nbsp;&nbsp;&nbsp;: {{ date('d / m / Y') }} &nbsp;&nbsp;{{ date('H : i : s') }}</div>
                                            <div>BANK &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <span id="pv_pra_bank"></span></div>
                                        </div>
                                        <div style="height: 42px; max-width: 130px; display:flex; align-items:center;">
                                            <img id="pv_logo_pra" src="/images/receipt/pln.svg" alt="PLN Logo" style="max-height: 42px; max-width: 130px; object-fit: contain;">
                                        </div>
                                    </div>

                                    <div class="text-center fw-bold my-2" style="font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">
                                        STRUK PEMBELIAN LISTRIK PRABAYAR
                                    </div>

                                    <div class="d-flex justify-content-between gap-3 mt-2" style="font-size: 11px;">
                                        <!-- Left Col -->
                                        <div style="flex: 1.1;">
                                            <div class="d-flex"><span style="width: 100px;">NO METER</span><span class="me-1">:</span><span class="fw-bold" id="pv_pra_meter">14512345678</span></div>
                                            <div class="d-flex"><span style="width: 100px;">ID PEL</span><span class="me-1">:</span><span class="fw-bold" id="pv_pra_idpel">098765432100</span></div>
                                            <div class="d-flex"><span style="width: 100px;">NAMA</span><span class="me-1">:</span><span class="fw-bold text-truncate" id="pv_pra_nama">PRABOWO SUBIANTO WARDJOYO</span></div>
                                            <div class="d-flex"><span style="width: 100px;">TARIF/DAYA</span><span class="me-1">:</span><span class="fw-bold" id="pv_pra_tarif">R1/900VA</span></div>
                                            <div class="d-flex" style="margin-top: 10px;"><span style="width: 100px;">RP STROOM/TOKEN</span><span class="me-1">:</span><span class="fw-bold" id="pv_pra_nominal">RP 876.543, 00</span></div>
                                            <div class="d-flex"><span style="width: 100px;">RP BAYAR</span><span class="me-1">:</span><span class="fw-bold" id="pv_pra_total">RP 1.000.000, 00</span></div>
                                        </div>
                                        <!-- Right Col -->
                                        <div style="flex: 0.9;">
                                            <div class="d-flex"><span style="width: 85px;">ADMIN BANK</span><span class="me-1">:</span><span class="fw-bold" id="pv_pra_admin">RP 0, 00</span></div>
                                            <div class="d-flex"><span style="width: 85px;">MATERAI</span><span class="me-1">:</span><span class="fw-bold">RP 0, 00</span></div>
                                            <div class="d-flex"><span style="width: 85px;">PPN</span><span class="me-1">:</span><span class="fw-bold">RP 0, 00</span></div>
                                            <div class="d-flex"><span style="width: 85px;">PPJ</span><span class="me-1">:</span><span class="fw-bold">RP 43.210, 00</span></div>
                                            <div class="d-flex"><span style="width: 85px;">ANGSURAN</span><span class="me-1">:</span><span class="fw-bold">RP 0, 00</span></div>
                                            <div class="d-flex" style="margin-top: 10px;"><span style="width: 85px;">JUMLAH KWH</span><span class="me-1">:</span><span class="fw-bold" id="pv_pra_kwh">503</span></div>
                                        </div>
                                    </div>

                                    <!-- Box Token (Persis Struk Prabayar) -->
                                    <div class="text-center my-3 py-2" style="background:#D8EEFB; border-radius:0;">
                                        <div style="font-size: 11px; font-weight: 700; color: #0f172a; margin-bottom: 2px;">STROOM / TOKEN</div>
                                        <div class="fw-bold" style="font-size: 17px; letter-spacing: 1.5px; font-family:'Roboto Slab', monospace; color: #0f172a;" id="pv_pra_token">2920 - 3211 - 4567 - 8910 - 0987</div>
                                    </div>

                                    <div style="font-size: 10.5px; color: #1e293b;">
                                        <div style="font-style: italic; font-weight: 600; margin-bottom: 2px;">Informasi Hubungi</div>
                                        <div>• Call Center 123 &nbsp;&nbsp;&nbsp;• Mobile PLN &nbsp;&nbsp;&nbsp;• PLN Terdekat</div>
                                    </div>
                                </div>

                                <!-- ── 2. BODY PLN PASCA BAYAR (Persis STRUK PASCABAYAR.jpeg) ── -->
                                <div id="pv_body_pascabayar" style="display: none; font-family:'Courier Prime', 'Roboto Slab', monospace;">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div style="font-size: 11.5px; font-weight: 700; line-height: 1.5;">
                                            <div>TGL Bayar &nbsp;&nbsp;&nbsp;&nbsp;: {{ date('d / m / Y') }} &nbsp;&nbsp;{{ date('H : i : s') }}</div>
                                        </div>
                                        <div style="height: 42px; max-width: 130px; display:flex; align-items:center;">
                                            <img id="pv_logo_pasca" src="/images/receipt/pln.svg" alt="PLN Logo" style="max-height: 42px; max-width: 130px; object-fit: contain;">
                                        </div>
                                    </div>

                                    <div class="text-center fw-bold my-2" style="font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">
                                        STRUK PEMBAYARAN LISTRIK PASCABAYAR
                                    </div>

                                    <div class="d-flex justify-content-between gap-3 mt-2" style="font-size: 11px;">
                                        <!-- Left Col -->
                                        <div style="flex: 1.1;">
                                            <div class="d-flex"><span style="width: 95px;">ID PEL</span><span class="me-1">:</span><span class="fw-bold" id="pv_pasca_idpel">098765432100</span></div>
                                            <div class="d-flex"><span style="width: 95px;">NAMA</span><span class="me-1">:</span><span class="fw-bold text-truncate" id="pv_pasca_nama">PRABOWO SUBIANTO WARDJOYO</span></div>
                                            <div class="d-flex"><span style="width: 95px;">TARIF/DAYA</span><span class="me-1">:</span><span class="fw-bold" id="pv_pasca_tarif">R1/900VA</span></div>
                                            <div class="d-flex"><span style="width: 95px;">BL/TH</span><span class="me-1">:</span><span class="fw-bold" id="pv_pasca_blth">OKT/2026</span></div>
                                        </div>
                                        <!-- Right Col -->
                                        <div style="flex: 0.9;">
                                            <div class="d-flex"><span style="width: 85px;">STAND METER</span><span class="me-1">:</span><span class="fw-bold" id="pv_pasca_stand">7447-6541</span></div>
                                            <div class="d-flex"><span style="width: 85px;">TAGIHAN</span><span class="me-1">:</span><span class="fw-bold" id="pv_pasca_tagihan">Rp 210.345, 00</span></div>
                                            <div class="d-flex"><span style="width: 85px;">ADMIN BANK</span><span class="me-1">:</span><span class="fw-bold" id="pv_pasca_admin">Rp 3.750, 00</span></div>
                                            <div class="d-flex"><span style="width: 85px;">TOTAL BAYAR</span><span class="me-1">:</span><span class="fw-bold" id="pv_pasca_total">Rp 214.095, 00</span></div>
                                        </div>
                                    </div>

                                    <!-- Box REFF (Persis Struk Pascabayar) -->
                                    <div class="text-center my-3 py-2" style="background:#D8EEFB; border-radius:0;">
                                        <div style="font-size: 11px; font-weight: 700; color: #0f172a; margin-bottom: 2px;">REFF:</div>
                                        <div class="fw-bold" style="font-size: 16px; letter-spacing: 1px; font-family:'Roboto Slab', monospace; color: #0f172a;" id="pv_reff">2TKT23R3D5994E77D4FA6B977F3A1F3</div>
                                    </div>

                                    <div style="font-size: 10.5px; color: #1e293b;">
                                        <div style="font-style: italic; font-weight: 600; margin-bottom: 2px;">Informasi Hubungi</div>
                                        <div>• Call Center 123 &nbsp;&nbsp;&nbsp;• Mobile PLN &nbsp;&nbsp;&nbsp;• PLN Terdekat</div>
                                    </div>
                                </div>

                                <!-- ── 3. BODY BANK TRANSFER (Persis STRUK BANK.jpeg BCA) ── -->
                                <div id="pv_body_bank" style="display: none; font-family:'Inter', sans-serif;">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div style="font-size: 16px; font-weight: 700; color: #1e293b;">Transaction Receipt</div>
                                        <div style="height: 36px; max-width: 120px; display:flex; align-items:center;">
                                            <img id="pv_logo_bank" src="/images/receipt/bca.png" alt="Bank Logo" style="max-height: 36px; max-width: 120px; object-fit: contain;">
                                        </div>
                                    </div>

                                    <!-- Bar 1: In-House Transfer Result -->
                                    <div class="d-flex justify-content-between align-items-center px-2 py-1 fw-bold" style="background:#79B5FB; font-size: 11.5px; color: #0f172a;">
                                        <span>In-House Transfer Result</span>
                                        <span id="pv_bank_title">BANK CENTRAL ASIA</span>
                                    </div>
                                    <table style="width: 100%; font-size: 11px; border-collapse: collapse;">
                                        <tr style="background:#EDF5FF;"><td style="padding: 4px 8px; width: 145px; color: #334155;">Amount</td><td style="width: 12px;">:</td><td class="fw-bold" style="padding: 4px 8px;" id="pv_bank_nominal">IDR 4.000.000</td></tr>
                                        <tr style="background:#ffffff;"><td style="padding: 4px 8px; color: #334155;">Remark</td><td>:</td><td class="fw-bold" style="padding: 4px 8px;" id="pv_bank_remark">Transfer Uang</td></tr>
                                        <tr style="background:#EDF5FF;"><td style="padding: 4px 8px; color: #334155;">Remitter Reference No.</td><td>:</td><td class="fw-bold" style="padding: 4px 8px;" id="pv_bank_remitter">261021VD3UDD</td></tr>
                                        <tr style="background:#ffffff;"><td style="padding: 4px 8px; color: #334155;">Completed Time</td><td>:</td><td class="fw-bold" style="padding: 4px 8px;">{{ date('d-m-Y H:i:s') }}</td></tr>
                                        <tr style="background:#EDF5FF;"><td style="padding: 4px 8px; color: #334155;">Beneficiary Reference No.</td><td>:</td><td class="fw-bold" style="padding: 4px 8px;">-</td></tr>
                                        <tr style="background:#ffffff;"><td style="padding: 4px 8px; color: #334155;">Transaction ID</td><td>:</td><td class="fw-bold" style="padding: 4px 8px;" id="pv_bank_trxid">032312023948239990</td></tr>
                                    </table>

                                    <!-- Bar 2: Beneficiary Information -->
                                    <div class="px-2 py-1 fw-bold mt-2" style="background:#79B5FB; font-size: 11.5px; color: #0f172a;">
                                        Beneficiary Information
                                    </div>
                                    <table style="width: 100%; font-size: 11px; border-collapse: collapse;">
                                        <tr style="background:#EDF5FF;"><td style="padding: 4px 8px; width: 145px; color: #334155;">Account Number</td><td style="width: 12px;">:</td><td class="fw-bold" style="padding: 4px 8px;" id="pv_bank_rek">614501012345</td></tr>
                                        <tr style="background:#ffffff;"><td style="padding: 4px 8px; color: #334155;">Account Name</td><td>:</td><td class="fw-bold" style="padding: 4px 8px;" id="pv_bank_nama">PRABOWO SUBIANTO WARDJOYO</td></tr>
                                    </table>

                                    <!-- Bar 3: Request Information -->
                                    <div class="px-2 py-1 fw-bold mt-2" style="background:#79B5FB; font-size: 11.5px; color: #0f172a;">
                                        Request Information
                                    </div>
                                    <table style="width: 100%; font-size: 11px; border-collapse: collapse;">
                                        <tr style="background:#EDF5FF;"><td style="padding: 4px 8px; width: 145px; color: #334155;">Request Time</td><td style="width: 12px;">:</td><td class="fw-bold" style="padding: 4px 8px;">{{ date('d-m-Y H:i:s') }}</td></tr>
                                    </table>
                                </div>

                                <!-- ── 4. BODY UMUM (PPOB / Pulsa / Standar) ── -->
                                <div id="pv_body_umum" style="display: none; font-family:'Inter', sans-serif;">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div>
                                            <div style="font-size: 15px; font-weight: 700; color: #1e293b;">STRUK PEMBAYARAN</div>
                                            <div class="small text-muted">PAYMENT POINT SYSTEM</div>
                                        </div>
                                        <div style="height: 38px; max-width: 110px; display:flex; align-items:center;">
                                            <img id="pv_logo_umum" src="/logo" alt="Logo" style="max-height: 38px; max-width: 110px; object-fit: contain;">
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center px-2 py-1 fw-bold" style="background:#79B5FB; font-size: 11.5px; color: #0f172a;">
                                        <span>Rincian Transaksi</span>
                                        <span>SUKSES</span>
                                    </div>
                                    <table style="width: 100%; font-size: 11px; border-collapse: collapse;">
                                        <tr style="background:#EDF5FF;"><td style="padding: 4px 8px; width: 145px; color: #334155;">Nomor Tujuan</td><td style="width: 12px;">:</td><td class="fw-bold" style="padding: 4px 8px;" id="pv_umum_tujuan">081234567890</td></tr>
                                        <tr style="background:#ffffff;"><td style="padding: 4px 8px; color: #334155;">Produk / Layanan</td><td>:</td><td class="fw-bold" style="padding: 4px 8px;" id="pv_umum_produk">Top Up Saldo</td></tr>
                                        <tr style="background:#EDF5FF;"><td style="padding: 4px 8px; color: #334155;">Nama Pelanggan</td><td>:</td><td class="fw-bold" style="padding: 4px 8px;" id="pv_umum_nama">PRABOWO SUBIANTO WARDJOYO</td></tr>
                                        <tr style="background:#ffffff;"><td style="padding: 4px 8px; color: #334155;">Harga / Nominal</td><td>:</td><td class="fw-bold" style="padding: 4px 8px;" id="pv_umum_harga">Rp 50.000</td></tr>
                                        <tr style="background:#EDF5FF;"><td style="padding: 4px 8px; color: #334155;">Biaya Admin</td><td>:</td><td class="fw-bold" style="padding: 4px 8px;" id="pv_umum_admin">Rp 2.000</td></tr>
                                        <tr style="background:#dbeafe; font-weight: 700;"><td style="padding: 4px 8px; color: #1e3a8a;">TOTAL BAYAR</td><td>:</td><td class="fw-bold text-primary" style="padding: 4px 8px;" id="pv_umum_total">Rp 52.000</td></tr>
                                    </table>

                                    <div class="text-center border-top mt-3 pt-2 text-muted" style="font-size: 10px;" id="pv_footer_info">
                                        Informasi Hubungi: Call Center 123 • Mobile PLN • PLN Terdekat
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Card Tabel Daftar Aturan Logo & Struk Aktif (FULL WIDTH DI BAWAH FORM) -->
            <div class="stg-card">
                <div class="stg-card-hdr">
                    <div class="stg-card-hdr-left">
                        <div class="stg-icon-box">
                            <i class="bx bx-list-ul fs-5"></i>
                        </div>
                        <div>
                            <div class="stg-card-title">Daftar Aturan Logo & Format Aktif ({{ count($receiptRules) }})</div>
                            <div class="stg-card-sub">Urutan prioritas pencocokan: Kode produk spesifik diprioritaskan terlebih dahulu, disusul oleh aturan berbasis provider</div>
                        </div>
                    </div>
                    <div>
                        <form method="POST" action="{{ route('admin.settings.receipt.rule.reset') }}" onsubmit="return confirm('Kembalikan semua aturan logo struk ke setelan bawaan pabrik?')">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger btn-sm" title="Kembalikan Template Default">
                                <i class="bx bx-refresh me-1"></i>Reset ke Default
                            </button>
                        </form>
                    </div>
                </div>

                <div class="stg-card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 40px;" class="text-center">No</th>
                                    <th style="width: 90px;" class="text-center">Logo</th>
                                    <th>Nama Label / Bank</th>
                                    <th>Kategori Layout</th>
                                    <th>Target Pencocokan Transaksi</th>
                                    <th>Admin Fee</th>
                                    <th class="text-center" style="width: 100px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($receiptRules as $idx => $r)
                                    @php
                                        $cat = $r['category'] ?? 'UMUM';
                                        $badgeClass = 'bg-label-secondary';
                                        if ($cat === 'PRABAYAR') $badgeClass = 'bg-label-success';
                                        elseif ($cat === 'PASCA BAYAR') $badgeClass = 'bg-label-info';
                                        elseif ($cat === 'BANK') $badgeClass = 'bg-label-primary';
                                    @endphp
                                    <tr>
                                        <td class="text-center small text-muted">{{ $idx + 1 }}</td>
                                        <td class="text-center">
                                            <div class="p-1 bg-white border rounded text-center d-inline-flex align-items-center justify-content-center" style="width: 72px; height: 38px;">
                                                <img src="{{ asset($r['logo_url'] ?? '/logo') }}" alt="Logo" style="max-height: 30px; max-width: 66px; object-fit: contain;">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $r['name'] ?? '-' }}</div>
                                            @if(!empty($r['bank_name']))
                                                <div class="small text-primary font-monospace">{{ $r['bank_name'] }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge {{ $badgeClass }}">{{ $cat }}</span>
                                        </td>
                                        <td>
                                            @if(!empty($r['provider']))
                                                <div class="mb-1">
                                                    <span class="badge bg-primary text-white" title="Otomatis mencakup semua produk provider ini">
                                                        <i class="bx bx-building me-1"></i>Provider: <strong>{{ strtoupper($r['provider']) }}</strong> (Semua Produk)
                                                    </span>
                                                </div>
                                            @endif
                                            @if(!empty($r['keywords']))
                                                <div class="d-flex flex-wrap gap-1 align-items-center">
                                                    @if(empty($r['provider']))
                                                        <span class="text-muted small me-1">Kode:</span>
                                                    @else
                                                        <span class="text-muted small me-1">Khusus Kode:</span>
                                                    @endif
                                                    @foreach(explode(',', $r['keywords']) as $kw)
                                                        @if(trim($kw))
                                                            <span class="badge bg-light text-dark border font-monospace">{{ trim($kw) }}</span>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            @endif
                                            @if(empty($r['provider']) && empty($r['keywords']))
                                                <span class="badge bg-light text-secondary">Semua / Default Fallback</span>
                                            @endif
                                        </td>
                                        <td class="font-monospace small fw-semibold text-dark">
                                            Rp {{ number_format($r['default_admin'] ?? 0, 0, ',', '.') }}
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-primary" onclick='editReceiptRule(@json($r))' title="Edit Aturan Ini">
                                                    <i class="bx bx-edit"></i>
                                                </button>
                                                <form method="POST" action="{{ route('admin.settings.receipt.rule.delete', $r['id'] ?? '') }}" onsubmit="return confirm('Hapus aturan struk ini?')">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-danger" title="Hapus Aturan">
                                                        <i class="bx bx-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">
                                            Belum ada aturan logo & format struk yang terpasang.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Card Pengaturan Umum Header & Footer Struk (FULL WIDTH) -->
            <div class="stg-card">
                <div class="stg-card-hdr">
                    <div class="stg-card-hdr-left">
                        <div class="stg-icon-box">
                            <i class="bx bx-slider fs-5"></i>
                        </div>
                        <div>
                            <div class="stg-card-title">Pengaturan Umum Struk</div>
                            <div class="stg-card-sub">Nama brand outlet dan catatan standar pada struk</div>
                        </div>
                    </div>
                </div>

                <div class="stg-card-body">
                    <form method="POST" action="{{ route('admin.settings.receipt') }}">
                        @csrf
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="stg-field-lbl">Nama Outlet / Header Struk Umum</label>
                                <input type="text" name="receipt_store_title" class="form-control" value="{{ $receiptStoreTitle }}" placeholder="Contoh: PAYMENT POINT ONLINE BANKING">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="stg-field-lbl">Catatan Footer Standar</label>
                                <input type="text" name="receipt_footer_info" class="form-control" value="{{ $receiptFooterInfo }}" placeholder="Contoh: Simpan struk ini sebagai bukti pembayaran sah.">
                            </div>
                        </div>
                        <div class="text-end mt-3">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="bx bx-save me-1"></i>Simpan Pengaturan Umum
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

    </div> {{-- End .settings-content-area --}}
</div> {{-- End .settings-layout --}}

@endsection

@section('extra-scripts')
<script>
    /* ── Tab Switch ── */
    function switchTab(id, btn) {
        document.querySelectorAll('.stg-tab-pane').forEach(p => p.classList.remove('active'));
        document.querySelectorAll('#settingsTabs .nav-link').forEach(b => b.classList.remove('active'));
        
        const targetTab = document.getElementById(id);
        if (targetTab) {
            targetTab.classList.add('active');
        }

        if (btn) {
            btn.classList.add('active');
        } else {
            const foundBtn = document.querySelector(`#settingsTabs button[onclick*="${id}"]`);
            if (foundBtn) foundBtn.classList.add('active');
        }

        history.replaceState(null, '', '?tab=' + id);
    }

    /* ── Show/Hide Password Toggle Helper ── */
    function toggleInputVisibility(inputId, btn) {
        const inp = document.getElementById(inputId);
        const icon = btn.querySelector('i');
        if (!inp) return;
        if (inp.type === 'password') {
            inp.type = 'text';
            if (icon) {
                icon.className = 'bx bx-hide fs-5';
            }
        } else {
            inp.type = 'password';
            if (icon) {
                icon.className = 'bx bx-show fs-5';
            }
        }
    }

    /* ── Auto-switch from URL param or session flash ── */
    document.addEventListener('DOMContentLoaded', function () {
        const urlParams = new URLSearchParams(window.location.search);
        const tabParam = urlParams.get('tab');
        if (tabParam) {
            const target = tabParam.startsWith('tab-') ? tabParam : ('tab-' + tabParam);
            switchTab(target, null);
        } else {
            const hash = window.location.hash;
            if (hash === '#receipt') switchTab('tab-receipt', null);
            else if (hash === '#account') switchTab('tab-account', null);
            else if (hash === '#gmail') switchTab('tab-gmail', null);
            else if (hash === '#bank') switchTab('tab-bank', null);
            else if (hash === '#security') switchTab('tab-security', null);
            else if (hash === '#otomax') switchTab('tab-otomax', null);
            else if (hash === '#apicenter') switchTab('tab-apicenter', null);
        }

        @if(session('receipt_success') || session('receipt_error'))
            switchTab('tab-receipt', null);
        @elseif(session('account_success') || session('account_error'))
            switchTab('tab-account', null);
        @elseif(session('mail_success') || $errors->any())
            switchTab('tab-gmail', null);
        @elseif(session('bank_success'))
            switchTab('tab-bank', null);
        @elseif(session('security_success'))
            switchTab('tab-security', null);
        @elseif(session('otomax_success'))
            switchTab('tab-otomax', null);
        @elseif(session('apicenter_success'))
            switchTab('tab-apicenter', null);
        @endif
    });

    /* ── Receipt Settings & Interactive Live Preview Engine ── */
    function handleCategoryChange(cat) {
        document.querySelectorAll('.field-bank-only').forEach(el => {
            el.style.display = (cat === 'BANK') ? 'block' : 'none';
        });
        updateLivePreview();
    }

    function handleParserModeChange(mode) {
        const boxFields = document.getElementById('box_parser_fields');
        const boxDelim  = document.getElementById('box_parser_delimiter');
        const boxRegex  = document.getElementById('box_parser_regex');
        if (boxFields) boxFields.style.display = (mode === 'fields') ? 'block' : 'none';
        if (boxDelim)  boxDelim.style.display  = (mode === 'delimiter') ? 'block' : 'none';
        if (boxRegex)  boxRegex.style.display  = (mode === 'regex') ? 'block' : 'none';
        updateLivePreview();
    }

    function insertPatternTag(tag) {
        const input = document.getElementById('rule_sn_pattern');
        const delim = document.getElementById('rule_sn_delimiter')?.value || '/';
        if (!input) return;
        if (!input.value.trim()) {
            input.value = tag;
        } else {
            input.value = input.value.trim().endsWith(delim) ? (input.value.trim() + tag) : (input.value.trim() + delim + tag);
        }
        updateLivePreview();
    }

    function previewSelectedLogo(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                ['pv_logo_pra', 'pv_logo_pasca', 'pv_logo_bank', 'pv_logo_umum'].forEach(id => {
                    const el = document.getElementById(id);
                    if (el) el.src = e.target.result;
                });
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function loadSampleSn(type) {
        const snInput = document.getElementById('rule_sample_sn');
        const catSelect = document.getElementById('rule_category');
        if (!snInput) return;

        if (type === 'pln_token') {
            snInput.value = 'SN: 2920-3211-4567-8910-0987/14512345678/098765432100/PRABOWO SUBIANTO WARDJOYO/R1/900VA/503/876543/1000000';
            if (catSelect) catSelect.value = 'PRABAYAR';
        } else if (type === 'pln_pasca') {
            snInput.value = 'STAND: 7447-6541 / NAMA: PRABOWO SUBIANTO WARDJOYO / R1/900VA / BLN: OKT26 / TAG: 210345 / ADM: 3750 / REFF: 2TKT23R3D5994E77D4FA6B977F3A1F3';
            if (catSelect) catSelect.value = 'PASCA BAYAR';
        } else if (type === 'bank_bca') {
            snInput.value = 'TRXID: 032312023948239990 / NOREK: 614501012345 / NAMA: PRABOWO SUBIANTO WARDJOYO / NOMINAL: 4000000 / REFF: 261021VD3UDD / BERHASIL';
            if (catSelect) catSelect.value = 'BANK';
        }

        if (catSelect) handleCategoryChange(catSelect.value);
        updateLivePreview();
    }

    function formatRupiah(num) {
        return 'Rp ' + Number(num || 0).toLocaleString('id-ID');
    }

    function formatPlnRupiah(num, prefix = 'RP ') {
        return prefix + Number(num || 0).toLocaleString('id-ID') + ', 00';
    }

    function formatBankIdr(num) {
        return 'IDR ' + Number(num || 0).toLocaleString('id-ID');
    }

    function updateLivePreview() {
        const cat = document.getElementById('rule_category')?.value || 'PRABAYAR';
        const nameVal = document.getElementById('rule_name')?.value?.trim();
        const provVal = document.getElementById('rule_provider')?.value?.trim();
        const adminFee = parseFloat(document.getElementById('rule_admin')?.value) || 0;
        const bankName = document.getElementById('rule_bank_name')?.value?.trim() || 'BANK CENTRAL ASIA';
        const remark = document.getElementById('rule_remark')?.value?.trim() || 'Transfer Uang';
        const footerInfo = document.getElementById('rule_footer')?.value?.trim() || 'Informasi Hubungi: Call Center 123 • Mobile PLN • PLN Terdekat';
        const rawSn = document.getElementById('rule_sample_sn')?.value || '';

        const parserMode = document.querySelector('input[name="parser_mode"]:checked')?.value || 'auto';
        const delim = document.getElementById('rule_sn_delimiter')?.value || '/';
        const pattern = document.getElementById('rule_sn_pattern')?.value?.trim() || '';
        const regexStr = document.getElementById('rule_sn_regex')?.value?.trim() || '';

        // Default parsing state matching STRUK samples
        let token = '';
        let custName = 'PRABOWO SUBIANTO WARDJOYO';
        let tarifDaya = 'R1/900VA';
        let kwh = '503';
        let standMeter = '7447-6541';
        let blth = 'OKT/2026';
        let noMeter = '14512345678';
        let idPel = '098765432100';
        let noRek = '614501012345';
        let tagihan = (cat === 'PASCA BAYAR') ? 210345 : ((cat === 'BANK') ? 4000000 : 876543);
        let reff = '2TKT23R3D5994E77D4FA6B977F3A1F3';

        // ── Helper: Evaluasi Regex Per Kolom ──
        const evalFieldRegex = (inputId, setter) => {
            const patStr = document.getElementById(inputId)?.value?.trim();
            if (!patStr) return false;
            try {
                const reMatch = patStr.match(/^\/(.*)\/([gimsuy]*)$/);
                const reg = reMatch ? new RegExp(reMatch[1], reMatch[2]) : new RegExp(patStr, 'i');
                const m = reg.exec(rawSn);
                if (m) {
                    const val = (m[1] !== undefined) ? m[1].trim() : m[0].trim();
                    if (val) {
                        setter(val);
                        return true;
                    }
                }
            } catch (e) {}
            return false;
        };

        // 0. Jalankan Mapping Regex Per Kolom (Prioritas Tertinggi)
        evalFieldRegex('rule_regex_meter', v => noMeter = v);
        evalFieldRegex('rule_regex_idpel', v => idPel = v);
        evalFieldRegex('rule_regex_nama', v => custName = v);
        evalFieldRegex('rule_regex_tarif', v => tarifDaya = v);
        evalFieldRegex('rule_regex_token', v => token = v);
        evalFieldRegex('rule_regex_kwh', v => kwh = v);
        evalFieldRegex('rule_regex_stand', v => standMeter = v);
        evalFieldRegex('rule_regex_blth', v => blth = v);
        evalFieldRegex('rule_regex_tagihan', v => {
            const n = parseFloat(v.replace(/[^\d]/g, ''));
            if (n > 0) tagihan = n;
        });
        evalFieldRegex('rule_regex_reff', v => reff = v);
        evalFieldRegex('rule_regex_norek', v => noRek = v);

        // 1. Delimiter mode
        if (parserMode === 'delimiter' && pattern) {
            const snParts = rawSn.split(delim);
            const patParts = pattern.split(delim);
            patParts.forEach((tag, idx) => {
                if (snParts[idx]) {
                    const clean = snParts[idx].trim();
                    const cleanTag = tag.replace(/[{}\[\]()]/g, '').trim().toUpperCase();
                    if (cleanTag === 'TOKEN') token = clean;
                    else if (cleanTag === 'NAMA' || cleanTag === 'AN') custName = clean;
                    else if (cleanTag === 'TARIF' || cleanTag === 'DAYA') tarifDaya = clean;
                    else if (cleanTag === 'KWH') kwh = clean;
                    else if (cleanTag === 'STAND') standMeter = clean;
                    else if (cleanTag === 'BLTH') blth = clean;
                    else if (cleanTag === 'IDPEL') idPel = clean;
                    else if (cleanTag === 'METER' || cleanTag === 'NOMETER') noMeter = clean;
                    else if (cleanTag === 'NOREK' || cleanTag === 'REKENING') noRek = clean;
                    else if (cleanTag === 'REFF' || cleanTag === 'REF') reff = clean;
                    else if (cleanTag === 'TAGIHAN' || cleanTag === 'NOMINAL' || cleanTag === 'TOTAL') {
                        const n = parseFloat(clean.replace(/[^\d]/g, ''));
                        if (n > 0) tagihan = n;
                    }
                }
            });
        }

        // 2. Custom Regex mode (1 baris)
        if (parserMode === 'regex' && regexStr) {
            try {
                const reMatch = regexStr.match(/^\/(.*)\/([gimsuy]*)$/);
                const reg = reMatch ? new RegExp(reMatch[1], reMatch[2]) : new RegExp(regexStr, 'i');
                const m = reg.exec(rawSn);
                if (m && m.groups) {
                    if (m.groups.token) token = m.groups.token;
                    if (m.groups.nama) custName = m.groups.nama;
                    if (m.groups.tarif) tarifDaya = m.groups.tarif;
                    if (m.groups.kwh) kwh = m.groups.kwh;
                    if (m.groups.stand) standMeter = m.groups.stand;
                    if (m.groups.blth) blth = m.groups.blth;
                    if (m.groups.idpel) idPel = m.groups.idpel;
                    if (m.groups.meter) noMeter = m.groups.meter;
                    if (m.groups.norek) noRek = m.groups.norek;
                    if (m.groups.reff) reff = m.groups.reff;
                    if (m.groups.tagihan) {
                        const n = parseFloat(m.groups.tagihan.replace(/[^\d]/g, ''));
                        if (n > 0) tagihan = n;
                    }
                }
            } catch (e) {}
        }

        // 3. Smart Auto Regex Fallback (jika belum terisi dari langkah di atas)
        if (!token) {
            const mTok = rawSn.match(/(\d{4})[-\s]?(\d{4})[-\s]?(\d{4})[-\s]?(\d{4})[-\s]?(\d{4})/) || rawSn.match(/\b\d{20}\b/);
            if (mTok) {
                const digits = mTok[0].replace(/[-\s]/g, '');
                token = `${digits.substr(0,4)} - ${digits.substr(4,4)} - ${digits.substr(8,4)} - ${digits.substr(12,4)} - ${digits.substr(16,4)}`;
            } else {
                token = '2920 - 3211 - 4567 - 8910 - 0987';
            }
        } else if (token.replace(/[-\s]/g, '').length === 20) {
            const digits = token.replace(/[-\s]/g, '');
            token = `${digits.substr(0,4)} - ${digits.substr(4,4)} - ${digits.substr(8,4)} - ${digits.substr(12,4)} - ${digits.substr(16,4)}`;
        }

        const mName = rawSn.match(/(?:NAMA|NAME|NAMA\sPELANGGAN|NAMA\sREK|AN|A\/N)\s*[:=]\s*([^\/,;]+)/i) || rawSn.match(/\/([A-Z\s]{4,35})\//);
        if (mName && mName[1] && custName === 'PRABOWO SUBIANTO WARDJOYO') custName = mName[1].trim();

        const mTarif = rawSn.match(/(?:TARIF|DAYA|TARIF\/DAYA|TRF)\s*[:=]\s*([^\/,;]+)/i) || rawSn.match(/\b(R\d[A-Z]?\/\d+[A-Z]*|B\d\/\d+[A-Z]*|I\d\/\d+[A-Z]*)\b/i);
        if (mTarif && mTarif[1]) tarifDaya = mTarif[1].trim().toUpperCase();

        const mKwh = rawSn.match(/(?:KWH|JML\sKWH|JUMLAH\sKWH)\s*[:=]\s*([\d\.,]+)/i);
        if (mKwh && mKwh[1]) kwh = mKwh[1].trim();

        const mStand = rawSn.match(/(?:STAND|STAND\sMETER)\s*[:=]\s*([^\/,;]+)/i);
        if (mStand && mStand[1]) standMeter = mStand[1].trim();

        const mBlth = rawSn.match(/(?:BLN?\/THN?|BL\/TH|PERIODE)\s*[:=]\s*([^\/,;]+)/i);
        if (mBlth && mBlth[1]) blth = mBlth[1].trim();

        const mIdpel = rawSn.match(/(?:IDPEL|ID\s*PEL|ID\s*PELANGGAN)\s*[:=]\s*(\d{10,13})/i);
        if (mIdpel && mIdpel[1]) { idPel = mIdpel[1].trim(); }

        const mNorek = rawSn.match(/(?:NOREK|NO\.?\s*REK|REK|REKENING)\s*[:=]?\s*(\d{8,25})/i);
        if (mNorek && mNorek[1]) noRek = mNorek[1].trim();

        const mTagihan = rawSn.match(/(?:TAG|TAGIHAN|RP|NOMINAL|JML)\s*[:=]?\s*Rp?\s*([\d\.,]+)/i);
        if (mTagihan && mTagihan[1]) {
            const num = parseFloat(mTagihan[1].replace(/[^\d]/g, ''));
            if (num > 0) tagihan = num;
        }

        const mReff = rawSn.match(/(?:REFF|REF|SN)\s*[:=]\s*([A-Za-z0-9]+)/i);
        if (mReff && mReff[1]) reff = mReff[1].trim();

        const totalBayar = (cat === 'PRABAYAR' && tagihan < 1000000) ? 1000000 : (tagihan + adminFee);

        // Update Extracted Status Badges (Real-Time)
        const elMeter = document.getElementById('badge_meter');
        if (elMeter) elMeter.innerText = noMeter || '-';
        const elIdpel = document.getElementById('badge_idpel');
        if (elIdpel) elIdpel.innerText = idPel || '-';
        const elTok = document.getElementById('badge_token');
        if (elTok) elTok.innerText = token ? (token.substr(0,11) + '...') : '-';
        const elNam = document.getElementById('badge_nama');
        if (elNam) elNam.innerText = custName || '-';
        const elTrf = document.getElementById('badge_tarif');
        if (elTrf) elTrf.innerText = tarifDaya || '-';
        const elKwh = document.getElementById('badge_kwh');
        if (elKwh) elKwh.innerText = kwh || '-';
        const elTot = document.getElementById('badge_total');
        if (elTot) elTot.innerText = formatRupiah(totalBayar);

        // Update Paper Template Visibility
        const bPra = document.getElementById('pv_body_prabayar');
        const bPas = document.getElementById('pv_body_pascabayar');
        const bBnk = document.getElementById('pv_body_bank');
        const bUmm = document.getElementById('pv_body_umum');
        if (bPra) bPra.style.display = (cat === 'PRABAYAR') ? 'block' : 'none';
        if (bPas) bPas.style.display = (cat === 'PASCA BAYAR') ? 'block' : 'none';
        if (bBnk) bBnk.style.display = (cat === 'BANK') ? 'block' : 'none';
        if (bUmm) bUmm.style.display = (cat === 'UMUM') ? 'block' : 'none';

        const setTxt = (id, val) => {
            const el = document.getElementById(id);
            if (el) el.innerText = val;
        };

        setTxt('pv_reff', reff);
        setTxt('pv_footer_info', footerInfo);

        // Prabayar fields (Persis STRUK PRABAYAR.jpeg)
        setTxt('pv_pra_bank', (bankName && bankName !== 'PLN PAYMENT POINT' && bankName !== 'BANK CENTRAL ASIA') ? bankName : '');
        setTxt('pv_pra_meter', noMeter);
        setTxt('pv_pra_idpel', idPel);
        setTxt('pv_pra_nama', custName);
        setTxt('pv_pra_tarif', tarifDaya);
        setTxt('pv_pra_kwh', kwh);
        setTxt('pv_pra_token', token);
        setTxt('pv_pra_nominal', formatPlnRupiah(tagihan, 'RP '));
        setTxt('pv_pra_admin', formatPlnRupiah(adminFee, 'RP '));
        setTxt('pv_pra_total', formatPlnRupiah(totalBayar, 'RP '));

        // Pasca fields (Persis STRUK PASCABAYAR.jpeg)
        setTxt('pv_pasca_idpel', idPel);
        setTxt('pv_pasca_nama', custName);
        setTxt('pv_pasca_tarif', tarifDaya);
        setTxt('pv_pasca_stand', standMeter);
        setTxt('pv_pasca_blth', blth);
        setTxt('pv_pasca_tagihan', formatPlnRupiah(tagihan, 'Rp '));
        setTxt('pv_pasca_admin', formatPlnRupiah(adminFee, 'Rp '));
        setTxt('pv_pasca_total', formatPlnRupiah(totalBayar, 'Rp '));

        // Bank fields (Persis STRUK BANK.jpeg BCA)
        setTxt('pv_bank_title', bankName.toUpperCase());
        setTxt('pv_bank_nominal', formatBankIdr(tagihan));
        setTxt('pv_bank_remark', remark);
        setTxt('pv_bank_remitter', reff.length > 12 ? '261021VD3UDD' : reff);
        setTxt('pv_bank_rek', noRek);
        setTxt('pv_bank_nama', custName);

        // Umum fields
        setTxt('pv_umum_tujuan', idPel || '081234567890');
        setTxt('pv_umum_produk', nameVal || provVal || 'Transaksi PPOB');
        setTxt('pv_umum_nama', custName);
        setTxt('pv_umum_harga', formatRupiah(tagihan));
        setTxt('pv_umum_admin', formatRupiah(adminFee));
        setTxt('pv_umum_total', formatRupiah(totalBayar));
    }

    function editReceiptRule(rule) {
        const titleEl = document.getElementById('ruleFormTitle');
        if (titleEl) titleEl.innerText = 'Edit Aturan Struk: ' + (rule.name || '');

        const setVal = (id, val) => {
            const el = document.getElementById(id);
            if (el) el.value = val;
        };

        setVal('rule_id', rule.id || '');
        setVal('existing_logo_url', rule.logo_url || '');
        setVal('rule_category', rule.category || 'UMUM');
        setVal('rule_name', rule.name || '');
        setVal('rule_provider', rule.provider || '');
        setVal('rule_keywords', rule.keywords || '');
        setVal('rule_admin', rule.default_admin || 2500);
        setVal('rule_bank_name', rule.bank_name || '');
        setVal('rule_remark', rule.remark || 'Transfer Uang');
        setVal('rule_footer', rule.footer_info || '');

        // Parser settings
        const mode = rule.parser_mode || 'auto';
        const radio = document.querySelector(`input[name="parser_mode"][value="${mode}"]`);
        if (radio) radio.checked = true;
        setVal('rule_sn_delimiter', rule.sn_delimiter || '/');
        setVal('rule_sn_pattern', rule.sn_pattern || '');
        setVal('rule_sn_regex', rule.sn_regex || '');
        if (rule.sample_sn) {
            setVal('rule_sample_sn', rule.sample_sn);
        }

        // Field-by-Field Regex
        setVal('rule_regex_meter', rule.regex_meter || '');
        setVal('rule_regex_idpel', rule.regex_idpel || '');
        setVal('rule_regex_nama', rule.regex_nama || '');
        setVal('rule_regex_tarif', rule.regex_tarif || '');
        setVal('rule_regex_token', rule.regex_token || '');
        setVal('rule_regex_kwh', rule.regex_kwh || '');
        setVal('rule_regex_stand', rule.regex_stand || '');
        setVal('rule_regex_blth', rule.regex_blth || '');
        setVal('rule_regex_tagihan', rule.regex_tagihan || '');
        setVal('rule_regex_reff', rule.regex_reff || '');
        setVal('rule_regex_norek', rule.regex_norek || '');

        // Preview logo
        const pvLogo = document.getElementById('pv_logo');
        if (pvLogo && rule.logo_url) pvLogo.src = rule.logo_url;

        handleCategoryChange(rule.category);
        handleParserModeChange(mode);
        updateLivePreview();

        const formCard = document.getElementById('formReceiptRule');
        if (formCard) {
            formCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    function resetRuleForm() {
        const form = document.getElementById('formReceiptRule');
        if (form) form.reset();
        
        const setVal = (id, val) => {
            const el = document.getElementById(id);
            if (el) el.value = val;
        };

        setVal('rule_id', '');
        setVal('existing_logo_url', '/images/receipt/pln.svg');
        setVal('rule_provider', '');
        setVal('rule_keywords', '');
        setVal('rule_name', '');
        setVal('rule_sn_delimiter', '/');
        setVal('rule_sn_pattern', '');
        setVal('rule_sn_regex', '');
        setVal('rule_regex_meter', '');
        setVal('rule_regex_idpel', '');
        setVal('rule_regex_nama', '');
        setVal('rule_regex_tarif', '');
        setVal('rule_regex_token', '');
        setVal('rule_regex_kwh', '');
        setVal('rule_regex_stand', '');
        setVal('rule_regex_blth', '');
        setVal('rule_regex_tagihan', '');
        setVal('rule_regex_reff', '');
        setVal('rule_regex_norek', '');

        const rAuto = document.getElementById('parser_auto');
        if (rAuto) rAuto.checked = true;

        const pvLogo = document.getElementById('pv_logo');
        if (pvLogo) pvLogo.src = '/images/receipt/pln.svg';

        const titleEl = document.getElementById('ruleFormTitle');
        if (titleEl) titleEl.innerText = 'Tambah Aturan Logo & Format Struk';

        handleCategoryChange('PRABAYAR');
        handleParserModeChange('auto');
        loadSampleSn('pln_token');
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Run initial live preview on tab receipt
        updateLivePreview();
        // Add live input listeners
        ['rule_category', 'rule_name', 'rule_provider', 'rule_admin', 'rule_bank_name', 'rule_remark', 'rule_footer'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.addEventListener('input', updateLivePreview);
        });
    });

    /* ── Bank Settings ── */
    let bankIndex = {{ count(\Illuminate\Support\Facades\Cache::get('deposit_banks', [])) }};
    function addBank() {
        const list = document.getElementById('bank-list');
        const emptyMsg = list.querySelector('.bank-empty-msg');
        if (emptyMsg) emptyMsg.remove();

        const div = document.createElement('div');
        div.className = 'bank-item';
        div.style.cssText = 'display:flex; gap:10px; margin-bottom:12px; align-items:flex-end;';
        div.innerHTML = `
            <div style="flex:1;">
                <label class="stg-field-lbl" style="font-size:11px;">Nama Bank</label>
                <input type="text" name="banks[${bankIndex}][bank]" class="form-control" placeholder="Contoh: BCA" required>
            </div>
            <div style="flex:1;">
                <label class="stg-field-lbl" style="font-size:11px;">No. Rekening</label>
                <input type="text" name="banks[${bankIndex}][rekening]" class="form-control" placeholder="12345678" required>
            </div>
            <div style="flex:1;">
                <label class="stg-field-lbl" style="font-size:11px;">Atas Nama</label>
                <input type="text" name="banks[${bankIndex}][atas_nama]" class="form-control" placeholder="Nama Pemilik Rekening" required>
            </div>
            <div>
                <button type="button" class="btn btn-danger" onclick="this.parentElement.parentElement.remove()" style="padding: 0.65rem; border-radius: 6px; background:#dc2626; color:#fff; border:none; cursor:pointer;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            </div>
        `;
        list.appendChild(div);
        bankIndex++;
    }

    /* ── Password Toggle ── */
    function togglePwd() {
        const input = document.getElementById('gmail_password');
        const icon  = document.getElementById('eye-icon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>`;
        } else {
            input.type = 'password';
            icon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>`;
        }
    }

    /* ── Send Test Email ── */
    function sendTestEmail() {
        const email  = document.getElementById('test-email-input').value.trim();
        const btn    = document.getElementById('btn-test');
        const result = document.getElementById('test-result');

        if (!email) {
            result.innerHTML = `<svg width="13" height="13" fill="none" stroke="#dc2626" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> <span style="color:#dc2626;">Masukkan email tujuan terlebih dahulu.</span>`;
            return;
        }

        btn.disabled  = true;
        btn.innerHTML = `<svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="animation:spin .7s linear infinite;"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg> Mengirim...`;
        result.innerHTML = '';

        fetch('{{ route('admin.settings.mail.test') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ test_email: email })
        })
        .then(r => r.json())
        .then(data => {
            const ok = data.status === 'success';
            const c  = ok ? '#059669' : '#dc2626';
            result.innerHTML = `
                <svg width="13" height="13" fill="none" stroke="${c}" stroke-width="2.5" viewBox="0 0 24 24">
                    ${ok
                        ? '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>'
                        : '<path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>'}
                </svg>
                <span style="color:${c};">${data.message}</span>`;
        })
        .catch(() => {
            result.innerHTML = `<span style="color:#dc2626;">Gagal menghubungi server.</span>`;
        })
        .finally(() => {
            btn.disabled  = false;
            btn.innerHTML = `<svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg> Kirim Test`;
        });
    }

    /* ── Copy Public Price URL ── */
    function copyPublicPriceUrl() {
        const input = document.getElementById('publicPriceUrl');
        const btnText = document.getElementById('btnCopyText');
        input.select();
        input.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(input.value).then(() => {
            btnText.innerText = 'Tersalin!';
            setTimeout(() => {
                btnText.innerText = 'Salin Tautan';
            }, 2000);
        }).catch(() => {
            document.execCommand('copy');
            btnText.innerText = 'Tersalin!';
            setTimeout(() => {
                btnText.innerText = 'Salin Tautan';
            }, 2000);
        });
    }

    const basePublicPriceUrl = "{{ route('webreport.public-prices') }}";

    function updateShareUrl() {
        const sel = document.getElementById('adminShareLevelSelect');
        const customInp = document.getElementById('adminCustomLevelInput');
        if (customInp) customInp.value = '';
        applyShareLevelUrl(sel.value);
    }

    function updateCustomShareUrl() {
        const sel = document.getElementById('adminShareLevelSelect');
        const customInp = document.getElementById('adminCustomLevelInput');
        if (sel) sel.value = '';
        applyShareLevelUrl(customInp.value.trim());
    }

    function applyShareLevelUrl(level) {
        const urlInp = document.getElementById('publicPriceUrl');
        const previewBtn = document.getElementById('btnPreviewPublicPrice');
        const topLink = document.getElementById('btnTopOpenPrice');
        
        let target = basePublicPriceUrl;
        if (level && level.trim() !== '') {
            target += '?level=' + encodeURIComponent(level.trim().toUpperCase());
        }
        if (urlInp) urlInp.value = target;
        if (previewBtn) previewBtn.href = target;
        if (topLink) topLink.href = target;
    }

    /* ── Test SQL Server Connection ── */
    function testOtomaxConnection() {
        const btn    = document.getElementById('btn-test-otomax');
        const result = document.getElementById('test-otomax-result');

        const host     = document.getElementById('otomax_db_host').value.trim();
        const port     = document.getElementById('otomax_db_port').value.trim();
        const database = document.getElementById('otomax_db_database').value.trim();
        const username = document.getElementById('otomax_db_username').value.trim();
        const password = document.getElementById('otomax_db_password').value;

        btn.disabled  = true;
        btn.innerHTML = `<svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="animation:spin .7s linear infinite;"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg> Menguji Koneksi...`;
        result.innerHTML = '';

        fetch('{{ route('admin.settings.otomax.test') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({
                db_host: host,
                db_port: port,
                db_database: database,
                db_username: username,
                db_password: password
            })
        })
        .then(r => r.json())
        .then(data => {
            const ok     = data.status === 'success';
            const color  = ok ? '#059669' : '#dc2626';
            const bg     = ok ? '#ecfdf5' : '#fef2f2';
            const border = ok ? '#a7f3d0' : '#fecaca';

            result.innerHTML = `
                <div style="background:${bg}; border:1px solid ${border}; border-radius:6px; padding:11px 15px; display:flex; align-items:flex-start; gap:9px; font-size:12.5px; color:${color}; line-height:1.5;">
                    <svg width="16" height="16" fill="none" stroke="${color}" stroke-width="2.5" viewBox="0 0 24 24" style="flex-shrink:0; margin-top:2px;">
                        ${ok
                            ? '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>'
                            : '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>'}
                    </svg>
                    <span>${data.message}</span>
                </div>`;
        })
        .catch(() => {
            result.innerHTML = `
                <div style="background:#fef2f2; border:1px solid #fecaca; border-radius:6px; padding:11px 15px; font-size:12.5px; color:#dc2626;">
                    Gagal menghubungi server web untuk melakukan uji coba.
                </div>`;
        })
        .finally(() => {
            btn.disabled  = false;
            btn.innerHTML = `<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg> Uji Coba Koneksi`;
        });
    }

    // Auto-scroll to bottom of logs on load if tab-logs is active or exists
    document.addEventListener("DOMContentLoaded", function() {
        const logContainer = document.getElementById("logContainer");
        if(logContainer) {
            logContainer.scrollTop = logContainer.scrollHeight;
        }
    });
</script>
<style>
    @keyframes spin { to { transform: rotate(360deg); } }
</style>
@endsection
