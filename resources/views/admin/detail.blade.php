@extends('admin.layout')

@section('title', 'Tinjau Registrasi - ' . $reg->nama)
@section('page-header', 'Detail Registrasi Reseller')

@section('content')

<div class="mb-4">
    <a href="{{ route('admin.dashboard', ['status' => $reg->status]) }}" class="btn btn-outline-secondary btn-sm">
        <i class="bx bx-arrow-back me-1"></i> Kembali ke Daftar
    </a>
</div>

<div class="row g-4">
    
    <!-- Kolom Kiri: Formulir Data Registrasi -->
    <div class="col-12 col-lg-8">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="card-title mb-0">
                    <i class="bx bx-id-card text-primary fs-4"></i>
                    <span>Formulir Pendaftaran Reseller</span>
                </div>
                @php
                    $s = strtolower($reg->status ?? '');
                    $badgeClass = 'bg-label-warning';
                    if ($s === 'approved' || $s === 'disetujui') $badgeClass = 'bg-label-success';
                    elseif ($s === 'rejected' || $s === 'ditolak') $badgeClass = 'bg-label-danger';
                @endphp
                <span class="badge {{ $badgeClass }} fs-6">
                    {{ $reg->statusLabel() }}
                </span>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-borderless">
                        <tbody>
                            <tr>
                                <td class="text-muted small fw-semibold" style="width: 35%;">Nama Outlet / Reseller</td>
                                <td class="fw-bold text-dark fs-6">{{ $reg->nama }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted small fw-semibold">Nama Pemilik</td>
                                <td class="fw-semibold text-dark">{{ $reg->nama_pemilik ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted small fw-semibold">No. HP / WhatsApp</td>
                                <td class="font-monospace fw-semibold">{{ $reg->no_hp }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted small fw-semibold">Email</td>
                                <td>{{ $reg->email ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted small fw-semibold">Alamat Lengkap</td>
                                <td class="text-wrap">{{ $reg->alamat ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted small fw-semibold">Nomor KTP (NIK)</td>
                                <td class="font-monospace">{{ $reg->nomor_ktp ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted small fw-semibold">Nomor NPWP</td>
                                <td class="font-monospace">{{ $reg->npwp ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted small fw-semibold">Alamat IP Whitelist</td>
                                <td class="font-monospace">{{ $reg->alamat_ip ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted small fw-semibold">PIN Pilihan</td>
                                <td class="font-monospace fw-bold text-primary">{{ $reg->pin }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted small fw-semibold">Jenis PKS</td>
                                <td>
                                    @if($reg->jenis_pks === 'badan_usaha')
                                        <span class="badge bg-label-info">Badan Usaha / Badan Hukum</span>
                                    @else
                                        <span class="badge bg-label-primary">Perorangan (Pribadi)</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted small fw-semibold">Tanggal Masuk</td>
                                <td>{{ $reg->created_at->format('d M Y, H:i:s') }}</td>
                            </tr>
                            @if($reg->reviewed_at)
                            <tr>
                                <td class="text-muted small fw-semibold">Diproses Pada</td>
                                <td class="text-muted">{{ $reg->reviewed_at->format('d M Y, H:i:s') }} oleh {{ $reg->reviewed_by }}</td>
                            </tr>
                            @endif
                            @if($reg->kode_reseller)
                            <tr>
                                <td class="text-muted small fw-semibold">Kode Reseller OtomaX</td>
                                <td class="fw-bold fs-5 text-primary font-monospace">{{ $reg->kode_reseller }}</td>
                            </tr>
                            @endif
                            @if($reg->catatan_admin)
                            <tr>
                                <td class="text-muted small fw-semibold">Catatan Admin</td>
                                <td>
                                    <div class="p-2 bg-light rounded border small text-dark">{{ $reg->catatan_admin }}</div>
                                </td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                <div class="d-flex flex-column gap-2 mt-3 pt-3 border-top">
                    @if($reg->berkas_path)
                        <a href="{{ route('admin.registrations.berkas', $reg->id) }}" class="btn btn-outline-secondary btn-sm justify-content-start">
                            <i class="bx bx-download me-2"></i> Unduh Berkas PKS yang Telah Diisi
                        </a>
                    @endif
                    @if($reg->foto_ktp_path)
                        <a href="{{ route('admin.registrations.berkas', [$reg->id, 'type' => 'ktp']) }}" class="btn btn-outline-secondary btn-sm justify-content-start">
                            <i class="bx bx-image me-2"></i> Unduh Berkas Foto KTP
                        </a>
                    @endif
                    @if($reg->foto_npwp_path)
                        <a href="{{ route('admin.registrations.berkas', [$reg->id, 'type' => 'npwp']) }}" class="btn btn-outline-secondary btn-sm justify-content-start">
                            <i class="bx bx-file me-2"></i> Unduh Berkas Foto NPWP
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Tindakan Verifikasi -->
    <div class="col-12 col-lg-4">
        @if($reg->isPending())
            <div class="card mb-4">
                <div class="card-header py-3">
                    <div class="card-title mb-0" style="font-size: 0.95rem;">
                        <i class="bx bx-shield-alt text-primary fs-5"></i>
                        <span>Tindakan Verifikasi</span>
                    </div>
                </div>
                <div class="card-body d-flex flex-column gap-2">
                    <button type="button" class="btn btn-primary w-100 py-2" data-bs-toggle="modal" data-bs-target="#approveModal">
                        <i class="bx bx-check-circle me-1"></i> Setujui Pendaftaran
                    </button>
                    <button type="button" class="btn btn-outline-danger w-100 py-2" data-bs-toggle="modal" data-bs-target="#rejectModal">
                        <i class="bx bx-x-circle me-1"></i> Tolak Pendaftaran
                    </button>
                </div>
            </div>

            <!-- Modal Approve -->
            <div class="modal fade" id="approveModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg" style="border-radius: 0.75rem;">
                        <div class="modal-header border-bottom py-3">
                            <h5 class="modal-title fw-bold text-success">
                                <i class="bx bx-check-circle me-1"></i> Setujui Pendaftaran
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form action="{{ route('admin.registrations.approve', $reg->id) }}" method="POST">
                            @csrf
                            <div class="modal-body p-4">
                                <div class="mb-3">
                                    <label class="form-label" for="kode_reseller">Kode Reseller Baru</label>
                                    <input type="text" name="kode_reseller" id="kode_reseller" class="form-control font-monospace fw-bold text-uppercase fs-6" 
                                           value="{{ $suggestedKode }}" required>
                                    <small class="text-muted d-block mt-1">Dibuat otomatis: awalan <strong>RS</strong> + 5 digit acak.</small>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="catatan_admin_approve">Catatan Tambahan (Opsional)</label>
                                    <textarea name="catatan_admin" id="catatan_admin_approve" class="form-control" rows="3" placeholder="Contoh: Selamat bergabung di jaringan reseller kami..."></textarea>
                                </div>
                            </div>
                            <div class="modal-footer border-top py-2">
                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary btn-sm" onclick="return confirm('Apakah Anda yakin ingin menyetujui pendaftaran ini?')">
                                    Setujui & Buat Akun
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modal Reject -->
            <div class="modal fade" id="rejectModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg" style="border-radius: 0.75rem;">
                        <div class="modal-header border-bottom py-3">
                            <h5 class="modal-title fw-bold text-danger">
                                <i class="bx bx-x-circle me-1"></i> Tolak Pendaftaran
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form action="{{ route('admin.registrations.reject', $reg->id) }}" method="POST">
                            @csrf
                            <div class="modal-body p-4">
                                <div class="mb-3">
                                    <label class="form-label" for="alasan_tolak">Alasan Penolakan</label>
                                    <textarea name="catatan_admin" id="alasan_tolak" class="form-control" rows="3" placeholder="Tulis alasan penolakan berkas..." required></textarea>
                                </div>
                            </div>
                            <div class="modal-footer border-top py-2">
                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menolak pendaftaran ini?')">
                                    Konfirmasi Penolakan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @else
            <div class="card text-center p-4">
                <div class="avatar avatar-lg mx-auto mb-3 bg-label-secondary rounded-circle d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                    <i class="bx bx-check-shield fs-2 text-primary"></i>
                </div>
                <h5 class="fw-bold mb-1">Sudah Diproses</h5>
                <p class="text-muted small mb-3">Berkas pendaftaran ini telah selesai ditinjau.</p>
                
                @if($reg->status === 'approved')
                    <form action="{{ route('admin.registrations.resend-otomax', $reg->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-outline-primary w-100" onclick="return confirm('Apakah Anda yakin ingin mengirim ulang data reseller ini ke OtomaX?')">
                            <i class="bx bx-sync me-1"></i> Sinkron Ulang ke OtomaX
                        </button>
                    </form>
                @endif
            </div>
        @endif
    </div>

</div>

@endsection
