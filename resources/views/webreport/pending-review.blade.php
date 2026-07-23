@extends('webreport.layout')

@section('title', 'Akun Sedang Ditinjau')
@section('page-header', 'Status Pendaftaran')

@section('extra-styles')
<style>
    .pending-wrap {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: calc(100vh - 180px);
        padding: 2rem;
    }
    .pending-card {
        background: #fff;
        border: 1px solid var(--color-border);
        border-radius: 12px;
        box-shadow: var(--shadow-sm);
        padding: 40px;
        max-width: 700px;
        width: 100%;
        text-align: center;
    }
    .pending-icon {
        width: 64px;
        height: 64px;
        background: #fef3c7;
        color: #d97706;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
    }
    .pending-icon svg {
        width: 32px;
        height: 32px;
    }
    .pending-title {
        font-family: 'Outfit', sans-serif;
        font-size: 20px;
        font-weight: 700;
        color: var(--color-text);
        margin-bottom: 12px;
    }
    .pending-desc {
        font-size: 14px;
        color: var(--color-text-3);
        line-height: 1.6;
        margin-bottom: 24px;
    }
    .pending-info {
        background: var(--color-bg);
        border: 1px solid var(--color-border);
        border-radius: 8px;
        padding: 16px;
        text-align: left;
        margin-bottom: 24px;
    }
    .pending-info-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
        font-size: 13.5px;
    }
    .pending-info-row:last-child {
        margin-bottom: 0;
    }
    .pending-info-label {
        color: var(--color-text-3);
    }
    .pending-info-value {
        font-weight: 600;
        color: var(--color-text-2);
    }
    .btn-refresh {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        background: var(--color-primary);
        color: #fff;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        font-size: 13.5px;
        cursor: pointer;
        transition: all var(--transition);
        text-decoration: none;
    }
    .btn-refresh:hover {
        background: var(--color-primary-hover);
    }
</style>
@endsection

@section('content')
<div class="pending-wrap">
    <div class="pending-card">
        @if(empty($reg->foto_ktp_path))
            <div class="pending-icon" style="background:#fee2e2; color:#dc2626;">
                <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                </svg>
            </div>
            
            <h2 class="pending-title">Lengkapi Dokumen Anda</h2>
            <p class="pending-desc">
                Halo <strong>{{ $reg->nama_pemilik }}</strong>, untuk melanjutkan pendaftaran, Anda diwajibkan untuk mengunggah foto KTP dan dokumen PKS. NPWP bersifat opsional.
            </p>

            @if(session('success'))
                <div style="background: #ecfdf5; color: #059669; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 13.5px;">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div style="background: #fef2f2; color: #dc2626; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 13.5px; text-align: left;">
                    <ul style="margin:0; padding-left: 20px;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('webreport.pending.upload') }}" method="POST" enctype="multipart/form-data" style="text-align: left;">
                @csrf
                <div style="margin-bottom: 15px;">
                    <label style="display:block; font-size: 13px; font-weight: 600; margin-bottom: 6px;">Jenis Pendaftaran / PKS <span style="color:#dc2626;">*</span></label>
                    <select name="jenis_pks" id="jenis_pks_selector" required style="width:100%; border:1px solid var(--color-border); padding:8px; border-radius:6px; font-size:13px; background:#fff; height: 38px;">
                        <option value="perorangan" {{ old('jenis_pks') == 'perorangan' ? 'selected' : '' }}>Perorangan (Pribadi)</option>
                        <option value="badan_usaha" {{ old('jenis_pks') == 'badan_usaha' ? 'selected' : '' }}>Badan Usaha / Badan Hukum (seperti PT atau CV)</option>
                    </select>
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display:block; font-size: 13px; font-weight: 600; margin-bottom: 6px;">Foto KTP Pemilik <span style="color:#dc2626;">*</span></label>
                    <input type="file" name="foto_ktp" accept=".jpg,.jpeg,.png" required style="width:100%; border:1px solid var(--color-border); padding:8px; border-radius:6px; font-size:13px;">
                </div>

                <div style="margin-bottom: 15px; padding: 12px; background: #f8fafc; border: 1px solid var(--color-border); border-radius: 8px;">
                    <div style="font-size: 13px; font-weight: 600; margin-bottom: 4px;">Perjanjian Kerja Sama (PKS) <span style="color:#dc2626;">*</span></div>
                    <p style="font-size: 11px; color: var(--color-text-3); margin-bottom: 8px;">Anda bisa mengunduh template, menandatanganinya, dan mengunggahnya kembali.</p>
                    <a id="pks_download_link" href="{{ route('webreport.register.template', ['type' => 'perorangan']) }}" style="display:inline-block; font-size:12px; color:#2563eb; text-decoration:none; margin-bottom: 8px; font-weight: 600;">Unduh Template PKS</a>
                    <input type="file" name="berkas" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" required style="width:100%; border:1px solid var(--color-border); padding:8px; border-radius:6px; font-size:13px; background: #fff;">
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display:block; font-size: 13px; font-weight: 600; margin-bottom: 6px;">Foto NPWP</label>
                    <input type="file" name="foto_npwp" accept=".jpg,.jpeg,.png" style="width:100%; border:1px solid var(--color-border); padding:8px; border-radius:6px; font-size:13px;">
                </div>

                <button type="submit" class="btn-refresh" style="width: 100%; justify-content: center; background: #2563eb;">
                    Unggah Dokumen
                </button>
            </form>

            <script>
                document.getElementById('jenis_pks_selector').addEventListener('change', function() {
                    var type = this.value;
                    var link = document.getElementById('pks_download_link');
                    var baseUrl = "{{ route('webreport.register.template') }}";
                    link.href = baseUrl + "?type=" + type;
                });
                // Trigger change on load to ensure correct state if old input exists
                document.getElementById('jenis_pks_selector').dispatchEvent(new Event('change'));
            </script>

        @else
            <div class="pending-icon">
                <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            
            <h2 class="pending-title">Akun Sedang Ditinjau</h2>
            
            <p class="pending-desc">
                Halo <strong>{{ $reg->nama_pemilik }}</strong>, terima kasih telah melengkapi dokumen. Saat ini berkas pendaftaran Anda sedang dalam proses peninjauan oleh tim admin kami. Mohon ditunggu beberapa saat lagi.
            </p>

            <div class="pending-info">
                <div class="pending-info-row">
                    <span class="pending-info-label">Nama Outlet</span>
                    <span class="pending-info-value">{{ $reg->nama }}</span>
                </div>
                <div class="pending-info-row">
                    <span class="pending-info-label">Email</span>
                    <span class="pending-info-value">{{ $reg->email }}</span>
                </div>
                <div class="pending-info-row">
                    <span class="pending-info-label">Tanggal Daftar</span>
                    <span class="pending-info-value">{{ $reg->created_at->format('d M Y, H:i') }}</span>
                </div>
            </div>

            <a href="{{ route('webreport.dashboard') }}" class="btn-refresh">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Muat Ulang Status
            </a>
        @endif
    </div>
</div>
@endsection
