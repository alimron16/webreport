@extends('webreport.layout')

@section('title', 'Pengaturan Keamanan & API')
@section('page-header', 'Pengaturan Keamanan & API')

@php
if (!function_exists('maskContactInfo')) {
    function maskContactInfo($str) {
        if (!$str || $str === '-' || $str === 'Belum diatur') return $str;
        $str = trim($str);
        if (str_contains($str, '@')) {
            $parts = explode('@', $str);
            $local = $parts[0];
            $domain = $parts[1];
            if (strlen($local) <= 3) {
                $maskedLocal = substr($local, 0, 1) . str_repeat('*', max(1, strlen($local) - 1));
            } else {
                $maskedLocal = substr($local, 0, 3) . str_repeat('*', max(1, strlen($local) - 3));
            }
            return $maskedLocal . '@' . $domain;
        } else {
            if (strlen($str) <= 6) return $str;
            return substr($str, 0, 3) . str_repeat('*', strlen($str) - 6) . substr($str, -3);
        }
    }
}
@endphp

@section('content')
<div class="row g-4">
    <!-- Left Column: Main Settings Card -->
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header d-flex justify-content-between align-items-center border-bottom bg-transparent py-3">
                <h5 class="card-title mb-0 d-flex align-items-center">
                    <i class="bx bx-shield-quarter text-primary fs-4 me-2"></i> Konfigurasi Keamanan & API
                </h5>
                <button type="button" id="btn-toggle-edit" onclick="openEditMode()" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center">
                    <i class="bx bx-edit me-1"></i> Ubah Data
                </button>
            </div>

            <div class="card-body pt-4">
                <!-- READ MODE -->
                <div id="info-section">
                    <div class="table-responsive">
                        <table class="table table-borderless align-middle mb-0">
                            <tbody>
                                <tr class="border-bottom">
                                    <td class="text-muted py-3" style="width: 35%;">
                                        <i class="bx bx-lock-alt text-primary me-2"></i> Status PIN
                                    </td>
                                    <td class="py-3">
                                        @if(!empty($reseller['pin']))
                                            <span class="badge bg-label-success d-inline-flex align-items-center">
                                                <i class="bx bx-check-circle me-1"></i> Terpasang (••••)
                                            </span>
                                        @else
                                            <span class="badge bg-label-secondary d-inline-flex align-items-center">
                                                <i class="bx bx-x-circle me-1"></i> Belum diatur
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                                <tr class="border-bottom">
                                    <td class="text-muted py-3">
                                        <i class="bx bx-server text-primary me-2"></i> IP Whitelist API
                                    </td>
                                    <td class="py-3">
                                        @if(!empty($reseller['alamat_ip']))
                                            <span class="badge bg-label-info font-monospace">{{ $reseller['alamat_ip'] }}</span>
                                        @else
                                            <span class="badge bg-label-secondary">Semua IP Diizinkan</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr class="border-bottom">
                                    <td class="text-muted py-3">
                                        <i class="bx bx-key text-primary me-2"></i> Password API (IP)
                                    </td>
                                    <td class="py-3">
                                        @if(!empty($reseller['password_ip']))
                                            <span class="badge bg-label-success d-inline-flex align-items-center">
                                                <i class="bx bx-check-circle me-1"></i> Aktif (••••••••)
                                            </span>
                                        @else
                                            <span class="badge bg-label-secondary">Belum diatur</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr class="border-bottom">
                                    <td class="text-muted py-3">
                                        <i class="bx bx-link text-primary me-2"></i> URL Callback / Webhook
                                    </td>
                                    <td class="py-3">
                                        @if(!empty($reseller['url_report']))
                                            <code class="text-primary text-break">{{ $reseller['url_report'] }}</code>
                                        @else
                                            <span class="badge bg-label-secondary">Belum diatur</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted py-3">
                                        <i class="bx bx-envelope text-primary me-2"></i> Email Verifikasi (OTP)
                                    </td>
                                    <td class="py-3 fw-semibold">
                                        {{ maskContactInfo($reseller['email'] ?? 'Belum diatur') }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="alert alert-secondary d-flex align-items-center mt-4 mb-0" role="alert">
                        <i class="bx bx-info-circle fs-4 text-secondary me-2"></i>
                        <div class="small">
                            Klik tombol <strong>Ubah Data</strong> di bagian atas untuk memperbarui PIN transaksi, Whitelist IP, atau URL webhook callback Anda.
                        </div>
                    </div>
                </div>

                <!-- EDIT MODE -->
                <div id="edit-form-section" class="d-none">
                    <div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
                        <i class="bx bx-shield-quarter fs-4 text-warning me-2"></i>
                        <div class="small">
                            Untuk menjaga keamanan akun, seluruh perubahan wajib diverifikasi menggunakan <strong>Kode OTP</strong> yang dikirim ke email terdaftar: <strong class="font-monospace">{{ maskContactInfo($reseller['email'] ?? '-') }}</strong>.
                        </div>
                    </div>

                    <form action="{{ route('webreport.settings.update') }}" method="POST">
                        @csrf

                        <!-- Section: PIN -->
                        <h6 class="fw-bold mb-3 text-uppercase fs-7 text-muted">1. Penggantian PIN Transaksi</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">PIN Baru</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bx bx-lock-alt"></i></span>
                                    <input type="password" name="pin" class="form-control" placeholder="4 - 8 digit angka" maxlength="8">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Konfirmasi PIN Baru</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bx bx-check-shield"></i></span>
                                    <input type="password" name="pin_confirm" class="form-control" placeholder="Ulangi PIN baru" maxlength="8">
                                </div>
                            </div>
                            <div class="col-12">
                                <span class="form-text text-muted small">Kosongkan kedua kolom PIN di atas jika Anda tidak ingin mengganti PIN.</span>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Section: API & Callback -->
                        <h6 class="fw-bold mb-3 text-uppercase fs-7 text-muted">2. Konfigurasi Transaksi API / H2H</h6>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">IP Address API (Whitelist)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-server"></i></span>
                                <input type="text" name="alamat_ip" class="form-control font-monospace"
                                    value="{{ old('alamat_ip', $reseller['alamat_ip'] ?? '') }}"
                                    placeholder="Contoh: 103.123.45.67">
                            </div>
                            <span class="form-text text-muted small">Batasi akses transaksi API hanya dari IP server ini. Pisahkan koma jika lebih dari satu IP.</span>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Password API (Password IP)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-key"></i></span>
                                <input type="password" name="password_ip" class="form-control font-monospace"
                                    placeholder="Biarkan kosong jika tidak ingin mengubah password IP" autocomplete="new-password">
                            </div>
                            <span class="form-text text-muted small">Digunakan untuk parameter autentikasi saat menembak endpoint API H2H.</span>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-semibold">URL Webhook / Callback Report</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-link"></i></span>
                                <input type="url" name="url_report" class="form-control"
                                    value="{{ old('url_report', $reseller['url_report'] ?? '') }}"
                                    placeholder="https://serveranda.com/api/callback">
                            </div>
                            <span class="form-text text-muted small">Sistem kami akan mengirimkan laporan status transaksi secara instan (realtime) ke URL ini.</span>
                        </div>

                        <hr class="my-4">

                        <!-- Section: OTP Verification -->
                        <div class="card bg-label-primary border-0 p-3 mb-4">
                            <label class="form-label fw-bold text-primary mb-2 d-flex align-items-center">
                                <i class="bx bx-mail-send fs-5 me-1"></i> Masukkan Kode Verifikasi OTP Email
                            </label>
                            <div class="row g-2 align-items-center">
                                <div class="col-sm-7 col-md-8">
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="bx bx-key"></i></span>
                                        <input type="text" name="otp" id="otp" class="form-control form-control-lg font-monospace text-center fw-bold"
                                            placeholder="• • • • • •" required maxlength="6" style="letter-spacing: 4px;">
                                    </div>
                                </div>
                                <div class="col-sm-5 col-md-4">
                                    <button type="button" id="btn-request-otp" onclick="requestOtp()" class="btn btn-primary w-100 py-2 d-inline-flex align-items-center justify-content-center">
                                        <i class="bx bx-send me-1"></i> Minta OTP
                                    </button>
                                </div>
                            </div>
                            <div id="otp-status" class="mt-2 small"></div>
                        </div>

                        <!-- Buttons -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary px-4 d-inline-flex align-items-center">
                                <i class="bx bx-save me-1"></i> Simpan Perubahan
                            </button>
                            <button type="button" onclick="closeEditMode()" class="btn btn-outline-secondary px-4 d-inline-flex align-items-center">
                                <i class="bx bx-x me-1"></i> Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Security Guide Card -->
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header border-bottom bg-transparent py-3">
                <h5 class="card-title mb-0 d-flex align-items-center">
                    <i class="bx bx-shield text-primary fs-4 me-2"></i> Panduan Keamanan
                </h5>
            </div>
            <div class="card-body pt-3">
                <div class="d-flex align-items-start mb-3 pb-2 border-bottom">
                    <div class="avatar avatar-sm me-3 flex-shrink-0">
                        <span class="avatar-initial rounded bg-label-primary"><i class="bx bx-lock-alt"></i></span>
                    </div>
                    <div>
                        <h6 class="mb-1 fw-bold fs-7">Jaga Kerahasiaan PIN</h6>
                        <p class="text-muted small mb-0">Gunakan kombinasi angka acak. Hindari tanggal lahir, tahun lahir, atau deretan angka seperti 123456.</p>
                    </div>
                </div>

                <div class="d-flex align-items-start mb-3 pb-2 border-bottom">
                    <div class="avatar avatar-sm me-3 flex-shrink-0">
                        <span class="avatar-initial rounded bg-label-info"><i class="bx bx-server"></i></span>
                    </div>
                    <div>
                        <h6 class="mb-1 fw-bold fs-7">IP Filtering API</h6>
                        <p class="text-muted small mb-0">Wajib daftarkan IP server backend Anda untuk menepis serangan transaksi tidak sah dari pihak luar.</p>
                    </div>
                </div>

                <div class="d-flex align-items-start mb-3 pb-2 border-bottom">
                    <div class="avatar avatar-sm me-3 flex-shrink-0">
                        <span class="avatar-initial rounded bg-label-success"><i class="bx bx-link"></i></span>
                    </div>
                    <div>
                        <h6 class="mb-1 fw-bold fs-7">URL Callback (Webhook)</h6>
                        <p class="text-muted small mb-0">Pastikan URL endpoint callback Anda menggunakan protokol HTTPS valid dan responsif menerima payload notifikasi.</p>
                    </div>
                </div>

                <div class="d-flex align-items-start">
                    <div class="avatar avatar-sm me-3 flex-shrink-0">
                        <span class="avatar-initial rounded bg-label-warning"><i class="bx bx-envelope"></i></span>
                    </div>
                    <div>
                        <h6 class="mb-1 fw-bold fs-7">Verifikasi OTP Email</h6>
                        <p class="text-muted small mb-0">Kode OTP hanya berlaku selama 5 menit dan dikirimkan otomatis ke alamat email yang terdaftar.</p>
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
        document.getElementById('info-section').classList.add('d-none');
        document.getElementById('btn-toggle-edit').classList.add('d-none');
        document.getElementById('edit-form-section').classList.remove('d-none');
    }

    function closeEditMode() {
        document.getElementById('info-section').classList.remove('d-none');
        document.getElementById('btn-toggle-edit').classList.remove('d-none');
        document.getElementById('edit-form-section').classList.add('d-none');
    }

    /* ── OTP Request ── */
    function requestOtp() {
        const btn    = document.getElementById('btn-request-otp');
        const status = document.getElementById('otp-status');

        btn.disabled  = true;
        btn.innerHTML = `<i class="bx bx-loader-alt bx-spin me-1"></i> Mengirim...`;
        status.innerHTML = '';

        fetch('{{ route('webreport.settings.send-otp') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        })
        .then(r => r.json())
        .then(data => {
            const ok = data.status === 'success';
            status.innerHTML = `
                <div class="d-flex align-items-center mt-2 ${ok ? 'text-success' : 'text-danger'}">
                    <i class="bx ${ok ? 'bx-check-circle' : 'bx-error-circle'} fs-5 me-1"></i>
                    <span>${data.message || (ok ? 'Kode OTP berhasil dikirim ke email.' : 'Gagal mengirim OTP.')}</span>
                </div>`;

            if (ok) {
                let s = 60;
                const t = setInterval(() => {
                    s--;
                    if (s <= 0) {
                        clearInterval(t);
                        btn.disabled  = false;
                        btn.innerHTML = `<i class="bx bx-send me-1"></i> Minta OTP`;
                    } else {
                        btn.innerHTML = `<i class="bx bx-time me-1"></i> Kirim Ulang (${s}s)`;
                    }
                }, 1000);
            } else {
                btn.disabled  = false;
                btn.innerHTML = `<i class="bx bx-send me-1"></i> Minta OTP`;
            }
        })
        .catch(() => {
            status.innerHTML = `<div class="d-flex align-items-center mt-2 text-danger"><i class="bx bx-error-circle fs-5 me-1"></i> Gagal menghubungi server. Silakan coba lagi.</div>`;
            btn.disabled  = false;
            btn.innerHTML = `<i class="bx bx-send me-1"></i> Minta OTP`;
        });
    }
</script>
@endsection
