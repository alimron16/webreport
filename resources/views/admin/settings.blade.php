@extends('admin.layout')

@section('title', 'Pengaturan Portal')
@section('page-header', 'Pengaturan Portal')

@section('extra-styles')
<style>
/* ═══════════════════════════════════════════════
   ADMIN SETTINGS PAGE — Design System
   Font: Inter (body 14px), Outfit (headings)
   ═══════════════════════════════════════════════ */

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
</style>
@endsection

@section('content')
<div class="settings-layout">

{{-- ─────────────────────────────────────────────
     TAB NAVIGATION
     ───────────────────────────────────────────── --}}
{{-- ─────────────────────────────────────────────
     TAB NAVIGATION MOVED TO SIDEBAR
     ───────────────────────────────────────────── --}}


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
                            placeholder="Contoh: PT. Chika Mandiri" required>
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

                        @php $logoUrl = \Illuminate\Support\Facades\Cache::get('site_logo_url'); @endphp
                        @if($logoUrl)
                        <div class="logo-preview">
                            <img src="{{ $logoUrl }}" alt="Logo aktif">
                            <span class="logo-preview-sub">Logo saat ini</span>
                        </div><br>
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

                    <div style="margin-top: 20px; padding-top: 16px; border-top: 1px solid var(--color-border);">
                        <button type="submit" class="btn btn-primary"
                            style="display:inline-flex; align-items:center; gap:6px; font-weight:600;">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                            Simpan Branding
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

    </div>
</div>


{{-- ═══════════════════════════════════════════════
     TAB 2: PKS
     ═══════════════════════════════════════════════ --}}
<div id="tab-pks" class="stg-tab-pane">
    <div class="stg-grid">

        {{-- Form --}}
        <div class="stg-card">
            <div class="stg-card-hdr">
                <div class="stg-card-title">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Template PKS / Perjanjian Kerja Sama
                </div>
            </div>
            <div class="card-body" style="padding: 20px;">
                @if(session('pks_success'))
                <div class="stg-alert success">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('pks_success') }}</span>
                </div>
                @endif

                {{-- Active Doc --}}
                @php $pksName = \Illuminate\Support\Facades\Cache::get('pks_template_name'); @endphp
                @if($pksName)
                    <div style="margin-bottom: 16px;">
                        <div class="stg-label">Dokumen Aktif</div>
                        <div class="doc-row">
                            <div class="doc-icon">
                                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div>
                                <div class="doc-name">{{ $pksName }}</div>
                                <div class="doc-sub">Dapat diunduh oleh calon reseller</div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="stg-alert warning" style="margin-bottom: 20px;">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <span>Belum ada template PKS. Unggah dokumen agar calon reseller dapat mengunduhnya saat pendaftaran.</span>
                    </div>
                @endif

                {{-- Upload Form --}}
                <div class="stg-label">Unggah Dokumen Baru</div>
                <form action="{{ route('admin.pks.upload') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="stg-field">
                        <div class="stg-field-lbl">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            File Dokumen
                            <span style="font-weight:400; color:var(--color-text-4); font-size:11.5px;">(PDF / DOC / DOCX, maks. 5MB)</span>
                        </div>
                        <input type="file" name="pks_file" class="form-control" accept=".pdf,.doc,.docx" required>
                    </div>
                    <div style="margin-top: 16px;">
                        <button type="submit" class="btn btn-primary"
                            style="display:inline-flex; align-items:center; gap:6px; font-weight:600;">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            Unggah Template PKS
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Info --}}
        <div class="stg-card">
            <div class="stg-card-hdr">
                <div class="stg-card-title">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Informasi
                </div>
            </div>
            <div class="card-body" style="padding: 4px 20px 16px;">
                <div class="tip-item">
                    <div class="tip-icon">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <div class="tip-text">
                        <strong>Fungsi</strong>
                        <span>Dokumen PKS dapat diunduh oleh calon reseller selama proses pendaftaran sebagai acuan perjanjian.</span>
                    </div>
                </div>
                <div class="tip-item">
                    <div class="tip-icon">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div class="tip-text">
                        <strong>Format Disarankan</strong>
                        <span>Gunakan format <strong>PDF</strong> agar tampilan konsisten di semua perangkat dan tidak dapat diedit.</span>
                    </div>
                </div>
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
                        <input type="text" name="db_host" class="form-control" value="{{ env('DB_HOST_OTOMAX', '127.0.0.1') }}" placeholder="127.0.0.1" required>
                    </div>

                    <div class="stg-field">
                        <div class="stg-field-lbl">Database Port</div>
                        <input type="number" name="db_port" class="form-control" value="{{ env('DB_PORT_OTOMAX', '1433') }}" placeholder="1433" required>
                    </div>
                    
                    <div class="stg-field">
                        <div class="stg-field-lbl">Database Name</div>
                        <input type="text" name="db_database" class="form-control" value="{{ env('DB_DATABASE_OTOMAX', 'otomax') }}" placeholder="otomax" required>
                    </div>

                    <div class="stg-field">
                        <div class="stg-field-lbl">Database Username</div>
                        <input type="text" name="db_username" class="form-control" value="{{ env('DB_USERNAME_OTOMAX', 'sa') }}" placeholder="sa" required>
                    </div>

                    <div class="stg-field">
                        <div class="stg-field-lbl">Database Password</div>
                        <input type="password" name="db_password" class="form-control" value="{{ env('DB_PASSWORD_OTOMAX', '') }}" placeholder="Kosongkan jika tidak ada password">
                        <div class="stg-hint">Password untuk terhubung ke SQL Server.</div>
                    </div>

                    <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--color-border); display: flex; justify-content: flex-end;">
                        <button type="submit" class="btn btn-primary" style="display:inline-flex; align-items:center; gap:6px; font-weight:600;">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            Simpan Konfigurasi
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Info --}}
        <div class="stg-card">
            <div class="stg-card-hdr">
                <div class="stg-card-title">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Informasi
                </div>
            </div>
            <div class="card-body" style="padding: 4px 20px 16px;">
                <div class="tip-item">
                    <div class="tip-icon">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <div class="tip-text">
                        <strong>Koneksi Database</strong>
                        <span>Pastikan SQL Server sudah mengizinkan koneksi TCP/IP pada port yang ditentukan (default: 1433).</span>
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
                                    <input type="text" name="banks[{{ $index }}][atas_nama]" class="form-control" value="{{ $bank['atas_nama'] }}" placeholder="PT Chika" required>
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

                    <button type="button" class="btn btn-secondary" onclick="addBank()" style="margin-top: 10px; display:inline-flex; align-items:center; gap:6px; font-weight:600; padding:6px 12px; border-radius:6px; border:1px solid var(--color-border); background:#f9fafb; cursor:pointer;">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        Tambah Rekening
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
                               value="{{ \Illuminate\Support\Facades\Cache::get('recaptcha_secret_key') }}" 
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

    </div> {{-- End .settings-content-area --}}
</div> {{-- End .settings-layout --}}

@endsection

@section('extra-scripts')
<script>
    /* ── Tab Switch ── */
    function switchTab(id, btn) {
        document.querySelectorAll('.stg-tab-pane').forEach(p => p.classList.remove('active'));
        // `.stg-tab-btn` elements were removed, so we don't need to loop them anymore.
        
        const targetTab = document.getElementById(id);
        if (targetTab) {
            targetTab.classList.add('active');
        }
        history.replaceState(null, '', '?tab=' + id);
    }

    /* ── Auto-switch from URL param or session flash ── */
    document.addEventListener('DOMContentLoaded', function () {
        const urlParams = new URLSearchParams(window.location.search);
        const tabParam = urlParams.get('tab');
        
        if (tabParam) {
            switchTab(tabParam, null);
        } else {
            const hash = window.location.hash;
            if (hash === '#gmail') switchTab('tab-gmail', null);
            else if (hash === '#pks') switchTab('tab-pks', null);
            else if (hash === '#bank') switchTab('tab-bank', null);
            else if (hash === '#security') switchTab('tab-security', null);
        }

        @if(session('mail_success') || $errors->any())
            switchTab('tab-gmail', null);
        @elseif(session('pks_success'))
            switchTab('tab-pks', null);
        @elseif(session('bank_success'))
            switchTab('tab-bank', null);
        @elseif(session('security_success'))
            switchTab('tab-security', null);
        @endif
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
                <input type="text" name="banks[${bankIndex}][atas_nama]" class="form-control" placeholder="PT Chika" required>
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
