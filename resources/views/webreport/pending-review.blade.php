@extends('webreport.layout')

@section('title', 'Status Pendaftaran Reseller')
@section('page-header', 'Status Pendaftaran')

@section('content')
<div class="row justify-content-center py-4">
    <div class="col-12 col-md-8 col-lg-6">
        <div class="card shadow-sm border-0 text-center p-3 p-md-4">
            <div class="card-body">
                @if(empty($reg->foto_ktp_path))
                    <div class="avatar avatar-xl mx-auto mb-3" style="width: 72px; height: 72px;">
                        <span class="avatar-initial rounded-circle bg-label-danger fs-1">
                            <i class="bx bx-upload"></i>
                        </span>
                    </div>

                    <h4 class="card-title fw-bold text-dark mb-2">Lengkapi Dokumen Anda</h4>
                    <p class="text-muted small mb-4">
                        Halo <strong>{{ $reg->nama_pemilik }}</strong>, untuk melanjutkan pendaftaran, Anda diwajibkan untuk mengunggah foto KTP dan berkas Perjanjian Kerja Sama (PKS).
                    </p>

                    @if(session('success'))
                        <div class="alert alert-success d-flex align-items-center mb-4" role="alert">
                            <i class="bx bx-check-circle fs-4 me-2"></i>
                            <div>{{ session('success') }}</div>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger mb-4 text-start" role="alert">
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('webreport.pending.upload') }}" method="POST" enctype="multipart/form-data" class="text-start">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Jenis Pendaftaran / PKS <span class="text-danger">*</span></label>
                            <select name="jenis_pks" id="jenis_pks_selector" required class="form-select">
                                <option value="perorangan" {{ old('jenis_pks') == 'perorangan' ? 'selected' : '' }}>Perorangan (Pribadi)</option>
                                <option value="badan_usaha" {{ old('jenis_pks') == 'badan_usaha' ? 'selected' : '' }}>Badan Usaha / Badan Hukum (PT/CV)</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Foto KTP Pemilik <span class="text-danger">*</span></label>
                            <input type="file" name="foto_ktp" accept=".jpg,.jpeg,.png" required class="form-control">
                        </div>

                        <div class="card bg-label-secondary border-0 p-3 mb-3">
                            <label class="form-label small fw-bold mb-1">Perjanjian Kerja Sama (PKS) <span class="text-danger">*</span></label>
                            <p class="text-muted small mb-2">Unduh template surat, lengkapi tanda tangan/materai, lalu unggah kembali scan dokumennya.</p>
                            <div class="mb-2">
                                <a id="pks_download_link" href="{{ route('webreport.register.template', ['type' => 'perorangan']) }}" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center">
                                    <i class="bx bx-download me-1"></i> Unduh Template PKS
                                </a>
                            </div>
                            <input type="file" name="berkas" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" required class="form-control bg-white">
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-semibold">Foto NPWP (Opsional)</label>
                            <input type="file" name="foto_npwp" accept=".jpg,.jpeg,.png" class="form-control">
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 d-inline-flex align-items-center justify-content-center">
                            <i class="bx bx-cloud-upload me-1"></i> Unggah Dokumen Pendaftaran
                        </button>
                    </form>

                    <script>
                        document.getElementById('jenis_pks_selector').addEventListener('change', function() {
                            var type = this.value;
                            var link = document.getElementById('pks_download_link');
                            var baseUrl = "{{ route('webreport.register.template') }}";
                            link.href = baseUrl + "?type=" + type;
                        });
                        document.getElementById('jenis_pks_selector').dispatchEvent(new Event('change'));
                    </script>

                @else
                    <div class="avatar avatar-xl mx-auto mb-3" style="width: 72px; height: 72px;">
                        <span class="avatar-initial rounded-circle bg-label-warning fs-1">
                            <i class="bx bx-time-five"></i>
                        </span>
                    </div>

                    <h4 class="card-title fw-bold text-dark mb-2">Akun Sedang Ditinjau</h4>
                    <p class="text-muted small mb-4">
                        Halo <strong>{{ $reg->nama_pemilik }}</strong>, berkas pendaftaran Anda telah kami terima dan saat ini sedang dalam proses verifikasi tim admin. Kami akan memberitahukan perkembangan lebih lanjut.
                    </p>

                    <div class="card bg-label-secondary border-0 p-3 mb-4 text-start">
                        <div class="d-flex justify-content-between py-1 border-bottom">
                            <span class="text-muted small">Nama Outlet:</span>
                            <span class="fw-semibold small">{{ $reg->nama }}</span>
                        </div>
                        <div class="d-flex justify-content-between py-1 border-bottom">
                            <span class="text-muted small">Email Terdaftar:</span>
                            <span class="fw-semibold small font-monospace">{{ $reg->email }}</span>
                        </div>
                        <div class="d-flex justify-content-between py-1">
                            <span class="text-muted small">Tanggal Pendaftaran:</span>
                            <span class="fw-semibold small">{{ $reg->created_at->format('d M Y, H:i') }} WIB</span>
                        </div>
                    </div>

                    <a href="{{ route('webreport.dashboard') }}" class="btn btn-primary px-4 d-inline-flex align-items-center">
                        <i class="bx bx-refresh me-1"></i> Muat Ulang Status
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
