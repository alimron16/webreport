@extends('admin.layout')

@section('title', 'Tinjau Registrasi')
@section('page-header', 'Detail Registrasi')

@section('extra-styles')
<style>
    .detail-grid {
        display: grid;
        grid-template-columns: 1fr 350px;
        gap: 20px;
        align-items: start;
    }

    @media (max-width: 900px) {
        .detail-grid { grid-template-columns: 1fr; }
    }

    .info-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 1.5rem;
    }

    .info-table tr { border-bottom: 1px solid var(--color-border); }
    .info-table tr:last-child { border-bottom: none; }

    .info-table td {
        padding: 12px 0;
        font-size: 13.5px;
        color: var(--color-text-2);
    }

    .info-table td.label {
        font-weight: 600;
        color: var(--color-text-3);
        width: 35%;
    }

    .info-table td.val {
        font-weight: 500;
        text-align: right;
    }

    .action-card {
        background: var(--color-white);
        border: 1px solid var(--color-border);
        border-radius: var(--radius);
        padding: 20px;
        box-shadow: var(--shadow-sm);
        margin-bottom: 16px;
    }

    .action-title {
        font-family: 'Outfit', sans-serif;
        font-size: 14px;
        font-weight: 700;
        color: var(--color-text);
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .action-group {
        margin-bottom: 12px;
    }

    .btn-block {
        width: 100%;
        display: flex;
        justify-content: center;
        margin-top: 10px;
    }

    .text-area-reason {
        min-height: 80px;
        resize: vertical;
        font-size: 13px;
    }

    /* Modal Styles */
    .modal-overlay {
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0,0,0,0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
    }

    .modal-content {
        background: #fff;
        border-radius: var(--radius);
        width: 90%;
        max-width: 400px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        overflow: hidden;
    }

    .modal-header {
        padding: 16px 20px;
        border-bottom: 1px solid var(--color-border);
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #f9fafb;
    }

    .modal-close {
        background: none;
        border: none;
        font-size: 20px;
        cursor: pointer;
        color: var(--color-text-3);
    }

    .modal-close:hover {
        color: var(--color-text);
    }
</style>
@endsection

@section('content')

<div style="margin-bottom: 15px;">
    <a href="{{ route('admin.dashboard', ['status' => $reg->status]) }}" class="btn btn-secondary btn-sm">
        ← Kembali ke Daftar
    </a>
</div>

<div class="detail-grid">
    
    <!-- Left Column: Details -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Formulir Pendaftaran Reseller
            </div>
            <span class="badge badge-{{ $reg->statusColor() }}">
                {{ $reg->statusLabel() }}
            </span>
        </div>
        <div class="card-body">
            <table class="info-table">
                <tr>
                    <td class="label">Nama Outlet / Reseller</td>
                    <td class="val" style="font-weight:700; color:var(--color-text);">{{ $reg->nama }}</td>
                </tr>
                <tr>
                    <td class="label">Nama Pemilik</td>
                    <td class="val" style="font-weight:600; color:var(--color-text);">{{ $reg->nama_pemilik ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">No. HP / WhatsApp</td>
                    <td class="val">{{ $reg->no_hp }}</td>
                </tr>
                <tr>
                    <td class="label">Email</td>
                    <td class="val">{{ $reg->email ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Alamat Lengkap</td>
                    <td class="val" style="text-align:left; word-break: break-word;">{{ $reg->alamat ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Nomor KTP (NIK)</td>
                    <td class="val">{{ $reg->nomor_ktp ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Nomor NPWP</td>
                    <td class="val">{{ $reg->npwp ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Alamat IP</td>
                    <td class="val">{{ $reg->alamat_ip ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Password IP</td>
                    <td class="val">{{ $reg->password_ip ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">URL Callback</td>
                    <td class="val" style="text-align:left; word-break: break-all;">{{ $reg->url_report ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">PIN Pilihan (Login)</td>
                    <td class="val" style="font-family: monospace; font-size:14px; font-weight:700;">{{ $reg->pin }}</td>
                </tr>
                <tr>
                    <td class="label">Nama Berkas Fisik</td>
                    <td class="val" style="color: var(--color-purple); font-size: 12.5px;">{{ $reg->berkas_nama ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Tanggal Masuk</td>
                    <td class="val">{{ $reg->created_at->format('d M Y, H:i:s') }}</td>
                </tr>
                @if($reg->reviewed_at)
                    <tr>
                        <td class="label">Diproses Pada</td>
                        <td class="val">{{ $reg->reviewed_at->format('d M Y, H:i:s') }} oleh {{ $reg->reviewed_by }}</td>
                    </tr>
                @endif
                @if($reg->kode_reseller)
                    <tr>
                        <td class="label">Kode Reseller OtomaX</td>
                        <td class="val" style="font-size:15px; font-weight:800; color:var(--color-purple);">{{ $reg->kode_reseller }}</td>
                    </tr>
                @endif
                @if($reg->catatan_admin)
                    <tr>
                        <td class="label">Catatan Admin</td>
                        <td class="val" style="text-align:left; background:#f9fafb; padding:10px; border-radius:6px; border:1px solid #e5e7eb;">
                            {{ $reg->catatan_admin }}
                        </td>
                    </tr>
                @endif
            </table>

            <div style="display: flex; flex-direction: column; gap: 8px;">
                @if($reg->berkas_path)
                    <a href="{{ route('admin.registrations.berkas', $reg->id) }}" class="btn btn-secondary" style="width:100%; justify-content: flex-start;">
                        <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Unduh Berkas PKS yang Telah Diisi
                    </a>
                @endif

                @if($reg->foto_ktp_path)
                    <a href="{{ route('admin.registrations.berkas', [$reg->id, 'type' => 'ktp']) }}" class="btn btn-secondary" style="width:100%; justify-content: flex-start;">
                        <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Unduh Berkas Foto KTP
                    </a>
                @endif

                @if($reg->foto_npwp_path)
                    <a href="{{ route('admin.registrations.berkas', [$reg->id, 'type' => 'npwp']) }}" class="btn btn-secondary" style="width:100%; justify-content: flex-start;">
                        <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Unduh Berkas Foto NPWP
                    </a>
                @endif
            </div>
        </div>
    </div>

    <!-- Right Column: Verification actions -->
    <div>
        @if($reg->isPending())
            <div class="action-card" style="display: flex; flex-direction: column; gap: 12px; padding: 24px;">
                <div style="font-weight: 600; font-size: 14px; text-align: center; margin-bottom: 8px;">Tindakan Verifikasi</div>
                <button type="button" class="btn btn-primary btn-block" onclick="openModal('approveModal')" style="padding: 12px;">
                    Setujui Pendaftaran
                </button>
                <button type="button" class="btn btn-secondary btn-block" onclick="openModal('rejectModal')" style="padding: 12px; color: #dc2626; border-color: #f87171; background: #fef2f2;">
                    Tolak Pendaftaran
                </button>
            </div>

            <!-- Modal Approve -->
            <div id="approveModal" class="modal-overlay" style="display: none;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 style="margin:0; font-size: 16px;">Setujui Pendaftaran</h3>
                        <button type="button" class="modal-close" onclick="closeModal('approveModal')">&times;</button>
                    </div>
                    <form action="{{ route('admin.registrations.approve', $reg->id) }}" method="POST" style="padding: 20px;">
                        @csrf
                        <div class="action-group">
                            <label class="form-label" for="kode_reseller">Kode Reseller Baru</label>
                            <input type="text" name="kode_reseller" id="kode_reseller" class="form-control" 
                                   value="{{ $suggestedKode }}" style="font-weight:700; font-size:15px; text-transform:uppercase;" required>
                            <small style="color:var(--color-text-3); font-size:11px; display:block; margin-top:4px;">
                                Dibuat otomatis: awalan <strong>CMP</strong> + 5 digit angka acak.
                            </small>
                        </div>
                        <div class="action-group" style="margin-top:15px;">
                            <label class="form-label" for="catatan_admin_approve">Catatan Tambahan (Opsional)</label>
                            <textarea name="catatan_admin" id="catatan_admin_approve" class="form-control text-area-reason" placeholder="Contoh: Selamat bergabung di jaringan kami..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block" style="margin-top: 20px;" onclick="return confirm('Apakah Anda yakin ingin menyetujui pendaftaran ini?')">
                            Setujui & Buat Akun
                        </button>
                    </form>
                </div>
            </div>

            <!-- Modal Reject -->
            <div id="rejectModal" class="modal-overlay" style="display: none;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 style="margin:0; font-size: 16px;">Tolak Pendaftaran</h3>
                        <button type="button" class="modal-close" onclick="closeModal('rejectModal')">&times;</button>
                    </div>
                    <form action="{{ route('admin.registrations.reject', $reg->id) }}" method="POST" style="padding: 20px;">
                        @csrf
                        <div class="action-group">
                            <label class="form-label" for="alasan_tolak">Alasan Penolakan</label>
                            <textarea name="catatan_admin" id="alasan_tolak" class="form-control text-area-reason" placeholder="Tulis alasan penolakan berkas..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-secondary btn-block" style="margin-top: 20px; color: #dc2626; border-color: #dc2626;" onclick="return confirm('Apakah Anda yakin ingin menolak pendaftaran ini?')">
                            Konfirmasi Penolakan
                        </button>
                    </form>
                </div>
            </div>
        @else
            <!-- Handled State -->
            <div class="action-card" style="text-align:center; padding: 24px 16px;">
                <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:var(--color-text-3); margin-bottom:8px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                <div style="font-weight:700; font-size:13.5px; color:var(--color-text);">Pendaftaran Sudah Diproses</div>
                <p style="font-size:12px; color:var(--color-text-3); margin-top:4px;">Berkas ini telah ditinjau dan berstatus <strong>{{ $reg->statusLabel() }}</strong>.</p>
            </div>
        @endif
    </div>

</div>

<script>
    function openModal(id) {
        document.getElementById(id).style.display = 'flex';
    }

    function closeModal(id) {
        document.getElementById(id).style.display = 'none';
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        if (event.target.classList.contains('modal-overlay')) {
            event.target.style.display = 'none';
        }
    }
</script>

@endsection
