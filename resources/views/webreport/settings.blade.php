@extends('webreport.layout')

@section('title', 'Pengaturan Keamanan')
@section('page-header', 'Pengaturan Keamanan')

@section('extra-styles')
<style>
/* ═══════════════════════════════════════════════
   MEMBER SETTINGS PAGE — Design System
   Font: Inter (body 14px), Outfit (headings)
   ═══════════════════════════════════════════════ */

/* Layout */
.pg-settings {
    display: grid;
    grid-template-columns: 1fr 300px;
    gap: 24px;
    align-items: start;
    width: 100%;
}
@media (max-width: 860px) {
    .pg-settings { grid-template-columns: 1fr; }
}

/* ── Card overrides ──────────────────────────── */
.pg-settings .card {
    border-radius: 10px;
    background: #fff;
    border: 1px solid var(--color-border);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
}

/* ── Section header inside card body ── */
.section-label {
    font-family: 'Outfit', sans-serif;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.07em;
    color: var(--color-text-4);
    margin-bottom: 4px;
}

/* ── Info rows (read mode) ──────────────────── */
.info-table {
    width: 100%;
    border-collapse: collapse;
}
.info-table tr {
    border-bottom: 1px solid var(--color-border);
}
.info-table tr:last-child {
    border-bottom: none;
}
.info-table td {
    padding: 12px 0;
    vertical-align: middle;
    font-size: 13.5px;
    line-height: 1.4;
}
.info-table td:first-child {
    width: 44%;
    color: var(--color-text-3);
    font-weight: 500;
    font-size: 13px;
    padding-right: 12px;
}
.info-table td:last-child {
    color: var(--color-text);
    font-weight: 600;
    text-align: right;
}
.info-table .val-mono {
    font-family: 'JetBrains Mono', 'Courier New', monospace;
    font-size: 13px;
}
.info-table .val-empty {
    color: var(--color-text-4);
    font-style: italic;
    font-weight: 400;
    font-family: inherit;
}
.info-table .val-pin {
    letter-spacing: 0.2em;
    font-size: 11px;
    color: var(--color-text-3);
}

/* ── Header action bar ──────────────────────── */
.card-header-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    border-bottom: 1px solid var(--color-border);
    background: #fff;
}
.card-header-bar .chb-title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-family: 'Outfit', sans-serif;
    font-size: 15px;
    font-weight: 700;
    color: var(--color-text);
}
.card-header-bar .chb-title svg {
    color: var(--color-purple);
}

/* ── Form elements ──────────────────────────── */
.form-section {
    border-top: 1px solid var(--color-border);
    padding-top: 20px;
    margin-top: 20px;
}
.form-section:first-of-type {
    border-top: none;
    padding-top: 0;
    margin-top: 0;
}

.field {
    margin-bottom: 14px;
}
.field:last-child { margin-bottom: 0; }

.field-label {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12.5px;
    font-weight: 600;
    color: var(--color-text-2);
    margin-bottom: 6px;
}
.field-label svg { color: var(--color-text-3); flex-shrink: 0; }
.field-hint {
    font-size: 11.5px;
    color: var(--color-text-4);
    margin-top: 5px;
    line-height: 1.4;
}

.field-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}
@media (max-width: 500px) {
    .field-row { grid-template-columns: 1fr; }
}

/* ── OTP row ────────────────────────────────── */
.otp-block {
    background: var(--color-bg);
    border: 1px solid var(--color-border);
    border-radius: 8px;
    padding: 14px 16px;
    margin-top: 20px;
}
.otp-block-title {
    display: flex;
    align-items: center;
    gap: 7px;
    font-size: 12.5px;
    font-weight: 700;
    color: var(--color-text-2);
    margin-bottom: 10px;
    font-family: 'Outfit', sans-serif;
}
.otp-block-title svg { color: var(--color-purple); }

.otp-input-row {
    display: flex;
    gap: 8px;
    align-items: center;
}
.otp-input-row input {
    flex: 1;
    text-align: center;
    font-size: 18px;
    font-weight: 700;
    letter-spacing: 0.22em;
    padding: 9px 12px;
    font-family: 'Outfit', sans-serif;
}
#otp-status {
    margin-top: 8px;
    font-size: 12px;
    min-height: 18px;
    display: flex;
    align-items: center;
    gap: 5px;
}

/* ── Buttons ────────────────────────────────── */
.btn-row {
    display: flex;
    gap: 10px;
    margin-top: 20px;
    padding-top: 16px;
    border-top: 1px solid var(--color-border);
    align-items: center;
}
.btn { font-size: 13.5px; font-weight: 600; }
.btn-sm { font-size: 12.5px; padding: 6px 14px; }

/* ── Alerts ─────────────────────────────────── */
.pg-alert {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 12px 14px;
    border-radius: 8px;
    font-size: 13px;
    line-height: 1.5;
    margin-bottom: 16px;
}
.pg-alert svg { flex-shrink: 0; margin-top: 1px; }
.pg-alert.success {
    background: var(--color-success-bg);
    border: 1px solid var(--color-success-bd);
    color: #065f46;
}
.pg-alert.error {
    background: var(--color-danger-bg);
    border: 1px solid var(--color-danger-bd);
    color: #9f1239;
}
.pg-alert.warning {
    background: #fffbeb;
    border: 1px solid #fde68a;
    color: #78350f;
}

/* ── Guide card ─────────────────────────────── */
.guide-list {
    display: flex;
    flex-direction: column;
    gap: 0;
}
.guide-item {
    display: flex;
    gap: 12px;
    padding: 13px 0;
    border-bottom: 1px solid var(--color-border);
    align-items: flex-start;
}
.guide-item:last-child { border-bottom: none; }
.guide-icon-box {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    background: var(--color-purple-light);
    color: var(--color-purple);
    margin-top: 1px;
}
.guide-text strong {
    display: block;
    font-size: 12.5px;
    font-weight: 600;
    color: var(--color-text-2);
    margin-bottom: 2px;
}
.guide-text span {
    font-size: 12px;
    color: var(--color-text-3);
    line-height: 1.55;
}

/* Edit form hidden by default */
#edit-form-section { display: none; }
#edit-form-section.visible { display: block; }
</style>
@endsection

@section('content')
<div class="pg-settings">

    {{-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
         LEFT COLUMN — Main Card
         ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ --}}
    <div class="card">

        {{-- Header --}}
        <div class="card-header-bar">
            <div class="chb-title">
                <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                Keamanan &amp; API
            </div>
            <button id="btn-toggle-edit" onclick="toggleEdit()" class="btn btn-secondary btn-sm"
                style="display:inline-flex; align-items:center; gap:5px;">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Ubah Data
            </button>
        </div>

        <div class="card-body" style="padding: 20px;">

            {{-- Flash Messages --}}
            @if(session('success'))
            <div class="pg-alert success">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
            @endif

            @if(session('error'))
            <div class="pg-alert error">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
            @endif

            @if($errors->any())
            <div class="pg-alert error">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- ── READ MODE ── --}}
            <div id="info-section">
                <div class="section-label" style="margin-bottom: 12px;">Data Akun Saat Ini</div>

                <table class="info-table">
                    <tr>
                        <td>
                            <span style="display:flex; align-items:center; gap:7px;">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color:var(--color-text-4);">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                                </svg>
                                Kode Reseller
                            </span>
                        </td>
                        <td>
                            <span class="val-mono" style="background:var(--color-purple-light); color:var(--color-purple); padding:2px 8px; border-radius:4px; font-size:12px; font-weight:700;">
                                {{ strtoupper($active_reseller_code) }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <span style="display:flex; align-items:center; gap:7px;">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color:var(--color-text-4);">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                PIN Keamanan
                            </span>
                        </td>
                        <td class="val-pin">●●●●●●</td>
                    </tr>
                    <tr>
                        <td>
                            <span style="display:flex; align-items:center; gap:7px;">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color:var(--color-text-4);">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                                </svg>
                                IP Address API
                            </span>
                        </td>
                        <td class="{{ empty($reseller['alamat_ip']) ? 'val-empty' : 'val-mono' }}">
                            {{ $reseller['alamat_ip'] ?? 'Belum diatur' }}
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <span style="display:flex; align-items:center; gap:7px;">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color:var(--color-text-4);">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                Password API / IP
                            </span>
                        </td>
                        <td class="{{ empty($reseller['password_ip']) ? 'val-empty' : 'val-mono' }}">
                            {{ empty($reseller['password_ip']) ? 'Belum diatur' : str_repeat('•', 8) }}
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <span style="display:flex; align-items:center; gap:7px;">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color:var(--color-text-4);">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                </svg>
                                URL Callback
                            </span>
                        </td>
                        <td class="{{ empty($reseller['url_report']) ? 'val-empty' : '' }}"
                            style="font-size:12px; word-break:break-all;">
                            {{ $reseller['url_report'] ?? 'Belum diatur' }}
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <span style="display:flex; align-items:center; gap:7px;">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color:var(--color-text-4);">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                Email Terdaftar
                            </span>
                        </td>
                        @php
                            function maskContactInfo($str) {
                                if (!$str || $str === '-' || $str === 'Belum diatur') return $str;
                                $str = trim($str);
                                if (str_contains($str, '@')) {
                                    $parts = explode('@', $str);
                                    $local = $parts[0];
                                    $domain = $parts[1];
                                    if (strlen($local) <= 3) {
                                        $maskedLocal = substr($local, 0, 1) . str_repeat('*', strlen($local) - 1);
                                    } else {
                                        $maskedLocal = substr($local, 0, 3) . str_repeat('*', strlen($local) - 3);
                                    }
                                    return $maskedLocal . '@' . $domain;
                                } else {
                                    if (strlen($str) <= 6) return $str;
                                    return substr($str, 0, 3) . str_repeat('*', strlen($str) - 6) . substr($str, -3);
                                }
                            }
                        @endphp
                        <td class="{{ empty($reseller['email']) ? 'val-empty' : '' }}"
                            style="font-size:12.5px;">
                            {{ maskContactInfo($reseller['email'] ?? 'Belum diatur') }}
                        </td>
                    </tr>
                </table>

                <p style="font-size:12px; color:var(--color-text-4); margin-top:14px; display:flex; align-items:center; gap:5px;">
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Klik <strong>&nbsp;Ubah Data&nbsp;</strong> di pojok kanan atas untuk mengubah pengaturan.
                </p>
            </div>

            {{-- ── EDIT MODE ── --}}
            <div id="edit-form-section">

                {{-- OTP Warning --}}
                <div class="pg-alert warning" style="margin-bottom: 20px;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div>
                        Perubahan akan diverifikasi melalui <strong>OTP Email</strong> yang dikirim ke
                        <strong style="font-family:monospace;">{{ maskContactInfo($reseller['email'] ?? '-') }}</strong>
                    </div>
                </div>

                <form action="{{ route('webreport.settings.update') }}" method="POST">
                    @csrf

                    {{-- PIN --}}
                    <div style="margin-bottom: 20px;">
                        <div class="section-label" style="margin-bottom: 10px;">PIN Keamanan</div>
                        <div class="field-row">
                            <div class="field">
                                <div class="field-label">
                                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                    PIN Baru
                                </div>
                                <input type="password" name="pin" class="form-control" placeholder="4–8 digit angka">
                            </div>
                            <div class="field">
                                <div class="field-label">
                                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Konfirmasi PIN
                                </div>
                                <input type="password" name="pin_confirm" class="form-control" placeholder="Ulangi PIN baru">
                            </div>
                        </div>
                        <div class="field-hint">Kosongkan kedua kolom jika tidak ingin mengganti PIN.</div>
                    </div>

                    {{-- API Settings --}}
                    <div style="border-top: 1px solid var(--color-border); padding-top: 16px; margin-bottom: 20px;">
                        <div class="section-label" style="margin-bottom: 10px;">Konfigurasi API</div>

                        <div class="field">
                            <div class="field-label">
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                                </svg>
                                IP Address API
                            </div>
                            <input type="text" name="alamat_ip" class="form-control val-mono"
                                value="{{ old('alamat_ip', $reseller['alamat_ip'] ?? '') }}"
                                placeholder="Contoh: 192.168.1.1">
                            <div class="field-hint">Batasi akses API hanya dari IP server ini. Kosongkan untuk tidak membatasi.</div>
                        </div>

                        <div class="field" style="margin-top: 12px;">
                            <div class="field-label">
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                Password API (Password IP)
                            </div>
                            <input type="password" name="password_ip" class="form-control val-mono"
                                placeholder="Biarkan kosong jika tidak diubah" autocomplete="new-password">
                            <div class="field-hint">Password untuk akses transaksi via IP / H2H.</div>
                        </div>

                        <div class="field" style="margin-top: 12px;">
                            <div class="field-label">
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                </svg>
                                URL Callback / Report
                            </div>
                            <input type="url" name="url_report" class="form-control"
                                value="{{ old('url_report', $reseller['url_report'] ?? '') }}"
                                placeholder="https://domainanda.com/api/callback">
                            <div class="field-hint">URL ini menerima notifikasi status transaksi secara real-time dari server kami.</div>
                        </div>
                    </div>

                    {{-- OTP Verification Block --}}
                    <div class="otp-block">
                        <div class="otp-block-title">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            Verifikasi OTP Email
                        </div>
                        <div class="otp-input-row">
                            <input type="text" name="otp" id="otp" class="form-control"
                                placeholder="• • • • • •" required maxlength="6">
                            <button type="button" id="btn-request-otp" onclick="requestOtp()"
                                class="btn btn-secondary btn-sm"
                                style="white-space:nowrap; display:inline-flex; align-items:center; gap:5px;">
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                </svg>
                                Minta OTP
                            </button>
                        </div>
                        <div id="otp-status"></div>
                    </div>

                    {{-- Actions --}}
                    <div class="btn-row">
                        <button type="submit" class="btn btn-primary"
                            style="display:inline-flex; align-items:center; gap:6px;">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                            </svg>
                            Simpan Perubahan
                        </button>
                        <button type="button" onclick="toggleEdit()" class="btn btn-secondary"
                            style="display:inline-flex; align-items:center; gap:6px;">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Batal
                        </button>
                    </div>
                </form>
            </div>

        </div>{{-- /card-body --}}
    </div>{{-- /main card --}}

    {{-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
         RIGHT COLUMN — Guide Card
         ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ --}}
    <div class="card">
        <div class="card-header-bar">
            <div class="chb-title">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                Panduan Keamanan
            </div>
        </div>
        <div class="card-body" style="padding: 4px 20px 16px;">
            <div class="guide-list">
                <div class="guide-item">
                    <div class="guide-icon-box">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <div class="guide-text">
                        <strong>Jaga Kerahasiaan PIN</strong>
                        <span>Gunakan PIN unik dan acak. Hindari tanggal lahir atau angka berurutan seperti 1234.</span>
                    </div>
                </div>
                <div class="guide-item">
                    <div class="guide-icon-box">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                        </svg>
                    </div>
                    <div class="guide-text">
                        <strong>IP Address Filtering</strong>
                        <span>Batasi akses API hanya dari IP server Anda untuk mencegah penyalahgunaan bila token bocor.</span>
                    </div>
                </div>
                <div class="guide-item">
                    <div class="guide-icon-box">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                        </svg>
                    </div>
                    <div class="guide-text">
                        <strong>URL Callback</strong>
                        <span>Server kami mengirim notifikasi status transaksi real-time ke URL ini setiap ada update.</span>
                    </div>
                </div>
                <div class="guide-item">
                    <div class="guide-icon-box">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div class="guide-text">
                        <strong>OTP Email</strong>
                        <span>Setiap perubahan data keamanan memerlukan kode OTP 6-digit yang dikirim ke email terdaftar.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@section('extra-scripts')
<script>
    @if($errors->any())
    document.addEventListener('DOMContentLoaded', function () {
        openEditMode();
    });
    @endif

    function openEditMode() {
        document.getElementById('info-section').style.display    = 'none';
        document.getElementById('btn-toggle-edit').style.display = 'none';
        document.getElementById('edit-form-section').classList.add('visible');
    }

    function closeEditMode() {
        document.getElementById('info-section').style.display    = 'block';
        document.getElementById('btn-toggle-edit').style.display = 'inline-flex';
        document.getElementById('edit-form-section').classList.remove('visible');
    }

    function toggleEdit() {
        const editSection = document.getElementById('edit-form-section');
        editSection.classList.contains('visible') ? closeEditMode() : openEditMode();
    }

    /* ── OTP Request ── */
    function requestOtp() {
        const btn    = document.getElementById('btn-request-otp');
        const status = document.getElementById('otp-status');

        btn.disabled  = true;
        btn.innerHTML = `<svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="animation:spin .7s linear infinite;"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg> Mengirim...`;
        status.innerHTML = '';

        fetch('{{ route('webreport.settings.send-otp') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        })
        .then(r => r.json())
        .then(data => {
            const ok = data.status === 'success';
            status.innerHTML = `
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="color:${ok ? '#059669' : '#dc2626'}; flex-shrink:0;">
                    ${ok
                        ? '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>'
                        : '<path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>'}
                </svg>
                <span style="color:${ok ? '#059669' : '#dc2626'};">${data.message || (ok ? 'OTP terkirim.' : 'Gagal mengirim OTP.')}</span>`;

            if (ok) {
                let s = 60;
                const t = setInterval(() => {
                    s--;
                    if (s <= 0) {
                        clearInterval(t);
                        btn.disabled  = false;
                        btn.innerHTML = `<svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg> Minta OTP`;
                    } else {
                        btn.innerHTML = `<svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="animation:spin .7s linear infinite;"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg> Kirim Ulang (${s}s)`;
                    }
                }, 1000);
            } else {
                btn.disabled  = false;
                btn.innerHTML = `<svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg> Minta OTP`;
            }
        })
        .catch(() => {
            status.innerHTML = `<span style="color:#dc2626;">Gagal menghubungi server. Coba lagi.</span>`;
            btn.disabled  = false;
            btn.innerHTML = `<svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg> Minta OTP`;
        });
    }
</script>
<style>
    @keyframes spin { to { transform: rotate(360deg); } }
</style>
@endsection
