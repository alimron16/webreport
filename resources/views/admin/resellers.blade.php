@extends('admin.layout')

@section('title', 'Data Member & Agen')
@section('page-header', 'Manajemen Member & Agen')

@section('content')

@php
    $totalMember = (int)($kpi['total_member'] ?? 0);
    $totalAktif = (int)($kpi['total_aktif'] ?? 0);
    $totalNonaktif = (int)($kpi['total_nonaktif'] ?? 0);
    $totalSuspend = (int)($kpi['total_suspend'] ?? 0);
    $totalSaldo = (float)($kpi['total_saldo'] ?? 0);
    $totalSaldoNol = (int)($kpi['total_saldo_nol'] ?? 0);
    $totalSaldoKritis = (int)($kpi['total_saldo_kritis'] ?? 0);
@endphp

<!-- Baris Atas: 4 Kartu KPI Ringkasan Reseller -->
<div class="row g-3 mb-4">
    <!-- Total Member -->
    <div class="col-6 col-lg-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-uppercase">Total Member</span>
                    <div class="p-2 rounded bg-label-primary text-primary">
                        <i class="bx bx-group fs-5"></i>
                    </div>
                </div>
                <h4 class="mb-1 fw-bold text-dark">{{ number_format($totalMember, 0, ',', '.') }}</h4>
                <div class="d-flex align-items-center gap-2 small text-muted">
                    <span class="text-success fw-medium">{{ number_format($totalAktif, 0, ',', '.') }} Aktif</span>
                    <span>&bull;</span>
                    <span class="text-secondary">{{ number_format($totalNonaktif, 0, ',', '.') }} Nonaktif</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Saldo Mengendap -->
    <div class="col-6 col-lg-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-uppercase">Total Saldo Deposit</span>
                    <div class="p-2 rounded bg-label-success text-success">
                        <i class="bx bx-wallet fs-5"></i>
                    </div>
                </div>
                <h4 class="mb-1 fw-bold text-dark">Rp {{ number_format($totalSaldo, 0, ',', '.') }}</h4>
                <div class="small text-muted">
                    <span>Dana mengendap di server</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Akun Aktif -->
    <div class="col-6 col-lg-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-uppercase">Tingkat Keaktifan</span>
                    <div class="p-2 rounded bg-label-info text-info">
                        <i class="bx bx-user-check fs-5"></i>
                    </div>
                </div>
                @php $aktifPercent = $totalMember > 0 ? round(($totalAktif / $totalMember) * 100, 1) : 0; @endphp
                <h4 class="mb-1 fw-bold text-dark">{{ $aktifPercent }}%</h4>
                <div class="small text-muted">
                    <span>{{ $totalSuspend }} akun berstatus Suspend</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Saldo Kritis / Nol -->
    <div class="col-6 col-lg-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-uppercase">Saldo Nol & Kritis</span>
                    <div class="p-2 rounded bg-label-warning text-warning">
                        <i class="bx bx-error-circle fs-5"></i>
                    </div>
                </div>
                <h4 class="mb-1 fw-bold text-dark">{{ number_format($totalSaldoNol + $totalSaldoKritis, 0, ',', '.') }}</h4>
                <div class="d-flex align-items-center gap-2 small text-muted">
                    <span class="text-danger fw-medium">{{ $totalSaldoNol }} Kosong (Rp 0)</span>
                    <span>&bull;</span>
                    <span class="text-warning fw-medium">{{ $totalSaldoKritis }} &lt; 50 Ribu</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Card Filter & Pencarian Reseller -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3 p-lg-4">
        <form method="GET" action="{{ route('admin.resellers') }}" id="filterForm">
            <div class="row g-2 align-items-end">
                <!-- Pencarian Keyword -->
                <div class="col-12 col-md-4 col-lg-3">
                    <label class="form-label small fw-semibold text-muted mb-1">
                        <i class="bx bx-search me-1"></i>Cari Member
                    </label>
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Ketik Kode / Nama / No HP..." value="{{ $search }}">
                </div>

                <!-- Filter Status Akun -->
                <div class="col-6 col-md-2 col-lg-2">
                    <label class="form-label small fw-semibold text-muted mb-1">
                        <i class="bx bx-check-shield me-1"></i>Status Akun
                    </label>
                    <select name="status" class="form-select form-select-sm" onchange="document.getElementById('filterForm').submit()">
                        <option value="all" {{ $status === 'all' ? 'selected' : '' }}>Semua Status</option>
                        <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                        <option value="suspend" {{ $status === 'suspend' ? 'selected' : '' }}>Ditangguhkan (Suspend)</option>
                    </select>
                </div>

                <!-- Filter Level Member -->
                <div class="col-6 col-md-2 col-lg-2">
                    <label class="form-label small fw-semibold text-muted mb-1">
                        <i class="bx bx-layer me-1"></i>Level Agen
                    </label>
                    <select name="level" class="form-select form-select-sm" onchange="document.getElementById('filterForm').submit()">
                        <option value="all" {{ $level === 'all' ? 'selected' : '' }}>Semua Level</option>
                        @foreach($levels as $lvl)
                            <option value="{{ $lvl }}" {{ $level === $lvl ? 'selected' : '' }}>Level {{ $lvl }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter Saldo -->
                <div class="col-6 col-md-2 col-lg-2">
                    <label class="form-label small fw-semibold text-muted mb-1">
                        <i class="bx bx-wallet me-1"></i>Kondisi Saldo
                    </label>
                    <select name="saldo_filter" class="form-select form-select-sm" onchange="document.getElementById('filterForm').submit()">
                        <option value="all" {{ $saldo_filter === 'all' ? 'selected' : '' }}>Semua Saldo</option>
                        <option value="positive" {{ $saldo_filter === 'positive' ? 'selected' : '' }}>Ada Saldo (&gt; Rp 0)</option>
                        <option value="zero" {{ $saldo_filter === 'zero' ? 'selected' : '' }}>Saldo Kosong (Rp 0)</option>
                        <option value="low" {{ $saldo_filter === 'low' ? 'selected' : '' }}>Kritis (&lt; Rp 50.000)</option>
                        <option value="minus" {{ $saldo_filter === 'minus' ? 'selected' : '' }}>Saldo Minus (&lt; Rp 0)</option>
                    </select>
                </div>

                <!-- Urutan Data -->
                <div class="col-6 col-md-2 col-lg-2">
                    <label class="form-label small fw-semibold text-muted mb-1">
                        <i class="bx bx-sort me-1"></i>Urutan
                    </label>
                    <select name="sort" class="form-select form-select-sm" onchange="document.getElementById('filterForm').submit()">
                        <option value="saldo_desc" {{ $sort === 'saldo_desc' ? 'selected' : '' }}>Saldo Terbanyak</option>
                        <option value="saldo_asc" {{ $sort === 'saldo_asc' ? 'selected' : '' }}>Saldo Terkecil</option>
                        <option value="nama_asc" {{ $sort === 'nama_asc' ? 'selected' : '' }}>Nama Agen (A-Z)</option>
                        <option value="kode_asc" {{ $sort === 'kode_asc' ? 'selected' : '' }}>Kode Agen (A-Z)</option>
                        <option value="tgl_desc" {{ $sort === 'tgl_desc' ? 'selected' : '' }}>Pendaftaran Terbaru</option>
                    </select>
                </div>

                <!-- Tombol Aksi -->
                <div class="col-12 col-md-2 col-lg-1 d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm w-100" title="Terapkan Filter">
                        <i class="bx bx-filter"></i>
                    </button>
                    <a href="{{ route('admin.resellers') }}" class="btn btn-outline-secondary btn-sm" title="Reset Filter">
                        <i class="bx bx-reset"></i>
                    </a>
                </div>
            </div>

            <!-- Toolbar Bawah: Ekspor & Rincian Jumlah -->
            <div class="d-flex flex-wrap justify-content-between align-items-center mt-3 pt-2 border-top">
                <div class="small text-muted">
                    Menampilkan <strong class="text-dark">{{ count($items) }}</strong> dari <strong class="text-dark">{{ number_format($total, 0, ',', '.') }}</strong> agen terfilter
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.reseller-profit') }}" class="btn btn-sm btn-outline-primary">
                        <i class="bx bx-line-chart me-1"></i> Analisis Profit Agen
                    </a>
                    <a href="{{ route('admin.resellers.export', request()->all()) }}" class="btn btn-sm btn-outline-success">
                        <i class="bx bx-download me-1"></i> Ekspor CSV / Excel
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Card Tabel Data Reseller -->
<div class="card border-0 shadow-sm">
    <div class="table-responsive text-nowrap">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>Member / Agen</th>
                    <th>Level</th>
                    <th>Kontak / WhatsApp</th>
                    <th class="text-end">Sisa Saldo</th>
                    <th class="text-end">Poin & Komisi</th>
                    <th class="text-center">Status</th>
                    <th class="text-center" style="width: 130px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $idx => $r)
                    @php
                        $rKode = $r['kode'] ?? '';
                        $rNama = $r['nama'] ?? '';
                        $rPemilik = $r['nama_pemilik'] ?? '';
                        $rLevel = $r['kode_level'] ?? 'O';
                        $rSaldo = (float)($r['saldo'] ?? 0);
                        $rPoin = (int)($r['poin'] ?? 0);
                        $rKomisi = (float)($r['komisi'] ?? 0);
                        $rAktif = (int)($r['aktif'] ?? 1);
                        $rSuspend = (int)($r['suspend'] ?? 0);
                        $rHp = $r['nomor_hp'] ?? '';
                        $rAlamat = $r['alamat'] ?? '';
                        $rTgl = !empty($r['tgl_daftar']) ? date('d M Y', strtotime($r['tgl_daftar'])) : '-';

                        // Format nomor WA untuk link klik langsung
                        $waClean = preg_replace('/[^0-9]/', '', $rHp);
                        if (str_starts_with($waClean, '08')) {
                            $waClean = '628' . substr($waClean, 2);
                        }

                        // Warna badge saldo
                        $saldoBadgeClass = 'text-success';
                        if ($rSaldo <= 0) {
                            $saldoBadgeClass = 'text-muted';
                        } elseif ($rSaldo < 50000) {
                            $saldoBadgeClass = 'text-warning';
                        }
                    @endphp
                    <tr>
                        <td class="small text-muted">{{ ($page - 1) * $per_page + $idx + 1 }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-initial rounded-circle bg-label-primary text-primary fw-bold me-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; font-size: 0.85rem;">
                                    {{ strtoupper(substr($rNama ?: $rKode, 0, 2)) }}
                                </div>
                                <div>
                                    <div class="fw-bold font-monospace text-dark">{{ $rKode }}</div>
                                    <div class="fw-medium text-dark">{{ $rNama }}</div>
                                    @if(!empty($rPemilik) && $rPemilik !== $rNama)
                                        <div class="small text-muted"><i class="bx bx-user me-1"></i>{{ $rPemilik }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-label-info">{{ $rLevel }}</span>
                        </td>
                        <td>
                            @if(!empty($rHp))
                                <div class="d-flex align-items-center gap-1 font-monospace small">
                                    <span>{{ $rHp }}</span>
                                    @if(!empty($waClean))
                                        <a href="https://wa.me/{{ $waClean }}" target="_blank" class="text-success ms-1" title="Hubungi via WhatsApp">
                                            <i class="bx bxl-whatsapp fs-6"></i>
                                        </a>
                                    @endif
                                </div>
                            @else
                                <span class="text-muted small">-</span>
                            @endif
                            @if(!empty($rAlamat))
                                <div class="small text-muted text-truncate" style="max-width: 160px;" title="{{ $rAlamat }}">
                                    <i class="bx bx-map-pin me-1"></i>{{ $rAlamat }}
                                </div>
                            @endif
                        </td>
                        <td class="text-end font-monospace">
                            <div class="fw-bold fs-6 {{ $saldoBadgeClass }}">
                                Rp {{ number_format($rSaldo, 0, ',', '.') }}
                            </div>
                            @if($rSaldo <= 0)
                                <span class="badge bg-label-secondary" style="font-size: 0.65rem;">Saldo Kosong</span>
                            @elseif($rSaldo < 50000)
                                <span class="badge bg-label-warning" style="font-size: 0.65rem;">Perlu Top Up</span>
                            @endif
                        </td>
                        <td class="text-end font-monospace small">
                            <div><span class="text-muted">Komisi:</span> Rp {{ number_format($rKomisi, 0, ',', '.') }}</div>
                            <div class="text-muted"><span class="text-muted">Poin:</span> {{ number_format($rPoin, 0, ',', '.') }}</div>
                        </td>
                        <td class="text-center">
                            @if($rSuspend == 1)
                                <span class="badge bg-label-warning"><i class="bx bx-block me-1"></i>Suspend</span>
                            @elseif($rAktif == 1)
                                <span class="badge bg-label-success"><i class="bx bx-check-circle me-1"></i>Aktif</span>
                            @else
                                <span class="badge bg-label-secondary"><i class="bx bx-x-circle me-1"></i>Nonaktif</span>
                            @endif
                            <div class="small text-muted" style="font-size: 0.7rem;">Gabung: {{ $rTgl }}</div>
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-primary" onclick="showMemberProfile('{{ $rKode }}')" title="Lihat Profil Lengkap">
                                    <i class="bx bx-id-card"></i>
                                </button>
                                <a href="{{ route('admin.reseller-profit', ['kode_reseller' => $rKode]) }}" class="btn btn-outline-success" title="Analisis Profit & Transaksi Member Ini">
                                    <i class="bx bx-line-chart"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <div class="text-muted mb-2"><i class="bx bx-user-x fs-1"></i></div>
                            <h6 class="text-muted fw-normal">Tidak ada data member yang sesuai dengan filter.</h6>
                            <a href="{{ route('admin.resellers') }}" class="btn btn-sm btn-outline-primary mt-1">
                                <i class="bx bx-reset me-1"></i> Reset Filter
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination Footer -->
    @if($last_page > 1)
        <div class="card-footer d-flex flex-wrap justify-content-between align-items-center py-2 px-3 border-top">
            <div class="small text-muted">
                Halaman <strong>{{ $page }}</strong> dari <strong>{{ $last_page }}</strong>
            </div>
            <nav aria-label="Page navigation">
                <ul class="pagination pagination-sm mb-0">
                    <!-- Prev -->
                    <li class="page-item {{ $page <= 1 ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ route('admin.resellers', array_merge(request()->all(), ['page' => $page - 1])) }}">
                            <i class="bx bx-chevron-left"></i>
                        </a>
                    </li>

                    @php
                        $start = max(1, $page - 2);
                        $end = min($last_page, $page + 2);
                    @endphp

                    @if($start > 1)
                        <li class="page-item"><a class="page-link" href="{{ route('admin.resellers', array_merge(request()->all(), ['page' => 1])) }}">1</a></li>
                        @if($start > 2) <li class="page-item disabled"><span class="page-link">&hellip;</span></li> @endif
                    @endif

                    @for($p = $start; $p <= $end; $p++)
                        <li class="page-item {{ $p == $page ? 'active' : '' }}">
                            <a class="page-link" href="{{ route('admin.resellers', array_merge(request()->all(), ['page' => $p])) }}">{{ $p }}</a>
                        </li>
                    @endfor

                    @if($end < $last_page)
                        @if($end < $last_page - 1) <li class="page-item disabled"><span class="page-link">&hellip;</span></li> @endif
                        <li class="page-item"><a class="page-link" href="{{ route('admin.resellers', array_merge(request()->all(), ['page' => $last_page])) }}">{{ $last_page }}</a></li>
                    @endif

                    <!-- Next -->
                    <li class="page-item {{ $page >= $last_page ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ route('admin.resellers', array_merge(request()->all(), ['page' => $page + 1])) }}">
                            <i class="bx bx-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    @endif
</div>

<!-- Modal Detail Profil Lengkap Member (AJAX) -->
<div class="modal fade" id="modalMemberProfile" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom py-3">
                <div class="d-flex align-items-center">
                    <div class="p-2 rounded bg-label-primary text-primary me-2">
                        <i class="bx bx-id-card fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="modalProfileName">Profil Member</h5>
                        <div class="small text-muted font-monospace" id="modalProfileKode">KODE</div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3 p-lg-4">
                <!-- Spinner Loading -->
                <div id="modalProfileLoading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Memuat profil...</span>
                    </div>
                    <div class="text-muted small mt-2">Mengambil data agen dari server...</div>
                </div>

                <!-- Konten Profil -->
                <div id="modalProfileContent" style="display: none;">
                    <!-- Ringkasan Saldo & Metrik Cepat -->
                    <div class="row g-2 mb-4" id="modalProfileMetrics">
                        <!-- Diisi via JS -->
                    </div>

                    <!-- Tabs Informasi -->
                    <ul class="nav nav-tabs nav-fill mb-3" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active small fw-semibold" data-bs-toggle="tab" data-bs-target="#tab-biodata" type="button" role="tab">
                                <i class="bx bx-user me-1"></i>Data Pribadi & Kontak
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link small fw-semibold" data-bs-toggle="tab" data-bs-target="#tab-pengirim" type="button" role="tab">
                                <i class="bx bx-devices me-1"></i>Daftar Pengirim / No HP
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link small fw-semibold" data-bs-toggle="tab" data-bs-target="#tab-transaksi" type="button" role="tab">
                                <i class="bx bx-history me-1"></i>Transaksi Terbaru
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content pt-1">
                        <!-- Tab Biodata & Akun -->
                        <div class="tab-pane fade show active" id="tab-biodata" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered mb-0">
                                    <tbody>
                                        <tr>
                                            <td class="text-muted small" style="width: 35%;">Kode Reseller</td>
                                            <td class="fw-bold font-monospace" id="p_kode">-</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted small">Nama Outlet / Konter</td>
                                            <td class="fw-semibold" id="p_nama">-</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted small">Nama Pemilik</td>
                                            <td id="p_pemilik">-</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted small">Tingkat Level</td>
                                            <td><span class="badge bg-label-info" id="p_level">-</span></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted small">Status Akun</td>
                                            <td id="p_status">-</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted small">Nomor HP / WhatsApp Utama</td>
                                            <td class="font-monospace" id="p_hp">-</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted small">Email</td>
                                            <td id="p_email">-</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted small">Alamat Lengkap</td>
                                            <td id="p_alamat">-</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted small">Nomor KTP / Identitas</td>
                                            <td class="font-monospace" id="p_ktp">-</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted small">NPWP</td>
                                            <td class="font-monospace" id="p_npwp">-</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted small">Tanggal Terdaftar</td>
                                            <td id="p_tgl_daftar">-</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted small">Alamat IP / IP Center</td>
                                            <td class="font-monospace" id="p_ip">-</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Tab Daftar Pengirim -->
                        <div class="tab-pane fade" id="tab-pengirim" role="tabpanel">
                            <div class="text-muted small mb-2">
                                Nomor HP, WhatsApp, Telegram, atau Jabber yang diizinkan bertransaksi atas nama agen ini:
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm table-striped table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 40px;">No</th>
                                            <th>Nomor Pengirim / Akun</th>
                                            <th>Tipe Pengirim</th>
                                        </tr>
                                    </thead>
                                    <tbody id="p_senders_body">
                                        <!-- Diisi via JS -->
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Tab Transaksi Terbaru -->
                        <div class="tab-pane fade" id="tab-transaksi" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small text-muted">5 Transaksi Terakhir:</span>
                                <a href="#" id="p_link_profit" class="btn btn-sm btn-outline-primary py-0">
                                    <i class="bx bx-line-chart me-1"></i>Analisis Profit Agen Ini
                                </a>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Waktu</th>
                                            <th>Produk / Tujuan</th>
                                            <th class="text-end">Harga</th>
                                            <th class="text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="p_trx_body">
                                        <!-- Diisi via JS -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top py-2">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('extra-scripts')
<script>
function showMemberProfile(kode) {
    const modalEl = document.getElementById('modalMemberProfile');
    const modal = new bootstrap.Modal(modalEl);

    document.getElementById('modalProfileName').innerText = 'Memuat Profil...';
    document.getElementById('modalProfileKode').innerText = kode;
    document.getElementById('modalProfileLoading').style.display = 'block';
    document.getElementById('modalProfileContent').style.display = 'none';

    modal.show();

    fetch(`{{ url('/admin/resellers/profile') }}/${encodeURIComponent(kode)}`)
        .then(res => res.json())
        .then(res => {
            document.getElementById('modalProfileLoading').style.display = 'none';

            if (!res.ok) {
                alert(res.desc || 'Gagal mengambil data agen.');
                modal.hide();
                return;
            }

            const data = res.result;
            const r = data.reseller || {};
            const stats = data.stats_30d || {};
            const senders = data.senders || [];
            const recentTrx = data.recent_trx || [];

            // Title
            document.getElementById('modalProfileName').innerText = r.nama || r.kode;
            document.getElementById('modalProfileKode').innerText = `${r.kode} - Level: ${r.kode_level || 'O'}`;

            // Metrics Cards
            const saldo = Number(r.saldo || 0);
            const komisi = Number(r.komisi || 0);
            const poin = Number(r.poin || 0);
            const omset30d = Number(stats.omset || 0);

            document.getElementById('modalProfileMetrics').innerHTML = `
                <div class="col-6 col-md-3">
                    <div class="p-2 bg-light rounded border text-center">
                        <div class="text-muted small">Saldo Saat Ini</div>
                        <div class="fw-bold fs-6 text-dark font-monospace">Rp ${new Intl.NumberFormat('id-ID').format(saldo)}</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-2 bg-light rounded border text-center">
                        <div class="text-muted small">Komisi</div>
                        <div class="fw-semibold text-secondary font-monospace">Rp ${new Intl.NumberFormat('id-ID').format(komisi)}</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-2 bg-light rounded border text-center">
                        <div class="text-muted small">Poin Reward</div>
                        <div class="fw-semibold text-info font-monospace">${new Intl.NumberFormat('id-ID').format(poin)}</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-2 bg-light rounded border text-center">
                        <div class="text-muted small">Trx 30 Hari</div>
                        <div class="fw-bold text-success font-monospace">${stats.total_trx || 0} Trx</div>
                    </div>
                </div>
            `;

            // Tab Biodata
            document.getElementById('p_kode').innerText = r.kode || '-';
            document.getElementById('p_nama').innerText = r.nama || '-';
            document.getElementById('p_pemilik').innerText = r.nama_pemilik || '-';
            document.getElementById('p_level').innerText = `Level ${r.kode_level || 'O'}`;

            let statusBadge = '<span class="badge bg-label-success">Aktif</span>';
            if (r.suspend == 1) {
                statusBadge = '<span class="badge bg-label-warning">Ditangguhkan (Suspend)</span>';
            } else if (r.aktif == 0) {
                statusBadge = '<span class="badge bg-label-secondary">Nonaktif</span>';
            }
            document.getElementById('p_status').innerHTML = statusBadge;

            document.getElementById('p_hp').innerText = r.nomor_hp || '-';
            document.getElementById('p_email').innerText = r.email || '-';
            document.getElementById('p_alamat').innerText = r.alamat || '-';
            document.getElementById('p_ktp').innerText = r.nomor_ktp || '-';
            document.getElementById('p_npwp').innerText = r.npwp || '-';
            document.getElementById('p_tgl_daftar').innerText = r.tgl_daftar || '-';
            document.getElementById('p_ip').innerText = r.alamat_ip || '-';

            // Tab Senders
            let sendersHtml = '';
            if (senders.length === 0) {
                sendersHtml = `<tr><td colspan="3" class="text-center py-2 text-muted small">Belum ada pengirim terdaftar di database pengirim.</td></tr>`;
            } else {
                senders.forEach((s, i) => {
                    let tipeDesc = 'Nomor HP / SMS';
                    const t = String(s.tipe_pengirim || s.tipe || '').toUpperCase();
                    if (t === 'W') tipeDesc = 'WhatsApp';
                    else if (t === 'T') tipeDesc = 'Telegram';
                    else if (t === 'J') tipeDesc = 'Jabber / XMPP';

                    sendersHtml += `
                        <tr>
                            <td class="text-muted small">${i + 1}</td>
                            <td class="font-monospace fw-semibold">${s.pengirim || '-'}</td>
                            <td><span class="badge bg-label-primary">${tipeDesc}</span></td>
                        </tr>
                    `;
                });
            }
            document.getElementById('p_senders_body').innerHTML = sendersHtml;

            // Tab Transaksi
            let trxHtml = '';
            if (recentTrx.length === 0) {
                trxHtml = `<tr><td colspan="4" class="text-center py-2 text-muted small">Tidak ada transaksi tercatat baru-baru ini.</td></tr>`;
            } else {
                recentTrx.forEach(t => {
                    const st = t.status_text || 'Sukses';
                    let badgeClass = 'bg-label-secondary';
                    if (st === 'Sukses') badgeClass = 'bg-label-success';
                    else if (st === 'Proses') badgeClass = 'bg-label-warning';
                    else badgeClass = 'bg-label-danger';

                    trxHtml += `
                        <tr>
                            <td class="font-monospace small text-muted">${t.tgl_entri || '-'}</td>
                            <td>
                                <div class="font-monospace fw-semibold">${t.kode_produk || '-'}</div>
                                <div class="small text-muted font-monospace">${t.tujuan || '-'}</div>
                            </td>
                            <td class="text-end font-monospace">Rp ${new Intl.NumberFormat('id-ID').format(t.harga || 0)}</td>
                            <td class="text-center"><span class="badge ${badgeClass}">${st}</span></td>
                        </tr>
                    `;
                });
            }
            document.getElementById('p_trx_body').innerHTML = trxHtml;

            // Link Profit
            document.getElementById('p_link_profit').href = `{{ url('/admin/reseller-profit') }}?kode_reseller=${encodeURIComponent(r.kode || '')}`;

            document.getElementById('modalProfileContent').style.display = 'block';
        })
        .catch(err => {
            document.getElementById('modalProfileLoading').style.display = 'none';
            alert('Terjadi kesalahan saat memuat rincian agen.');
            modal.hide();
        });
}
</script>
@endsection
