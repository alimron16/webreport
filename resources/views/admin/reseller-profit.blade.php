@extends('admin.layout')

@section('title', 'Laporan Pemakaian & Profit Agen')
@section('page-header', 'Pemakaian & Analisis Profit Agen')

@section('content')

@php
    $sumOmset = (float)($summary['total_omset'] ?? 0);
    $sumModal = (float)($summary['total_modal'] ?? 0);
    $sumProfit = (float)($summary['total_profit'] ?? 0);
    $avgMargin = (float)($summary['avg_margin'] ?? 0);
    $countReseller = (int)($summary['count_reseller'] ?? count($items));
    $sumSukses = (int)($summary['total_trx_sukses'] ?? 0);
    $sumGagal = (int)($summary['total_trx_gagal'] ?? 0);
@endphp

<!-- Card Filter Pencarian & Periode -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3 p-lg-4">
        <form method="GET" action="{{ route('admin.reseller-profit') }}" id="filterForm">
            <div class="row g-2 align-items-end">
                <!-- Filter Tanggal Awal -->
                <div class="col-6 col-md-3 col-lg-2">
                    <label class="form-label small fw-semibold text-muted mb-1">
                        <i class="bx bx-calendar me-1"></i>Tanggal Awal
                    </label>
                    <input type="date" name="tgl_awal" id="tgl_awal" class="form-control form-control-sm" value="{{ $tgl_awal }}">
                </div>

                <!-- Filter Tanggal Akhir -->
                <div class="col-6 col-md-3 col-lg-2">
                    <label class="form-label small fw-semibold text-muted mb-1">
                        <i class="bx bx-calendar me-1"></i>Tanggal Akhir
                    </label>
                    <input type="date" name="tgl_akhir" id="tgl_akhir" class="form-control form-control-sm" value="{{ $tgl_akhir }}">
                </div>

                <!-- Filter Per Member (Input Text + Autocomplete Datalist) -->
                <div class="col-12 col-md-4 col-lg-3">
                    <label class="form-label small fw-semibold text-muted mb-1 d-flex justify-content-between align-items-center">
                        <span><i class="bx bx-user me-1"></i>Filter Per Member</span>
                        @if(!empty($kode_reseller))
                            <a href="javascript:void(0)" onclick="clearMemberFilter()" class="text-danger text-decoration-none fw-normal" style="font-size: 0.72rem;">
                                <i class="bx bx-x me-1"></i>Reset
                            </a>
                        @endif
                    </label>
                    <div class="input-group input-group-sm">
                        <input type="text" 
                               name="kode_reseller" 
                               id="kode_reseller" 
                               list="resellerOptions" 
                               class="form-control form-control-sm" 
                               placeholder="Ketik Kode / Nama Member..." 
                               value="{{ $kode_reseller }}" 
                               autocomplete="off">
                        @if(!empty($kode_reseller))
                            <button type="button" class="btn btn-outline-secondary" onclick="clearMemberFilter()" title="Hapus Filter Member">
                                <i class="bx bx-x"></i>
                            </button>
                        @endif
                    </div>
                    <datalist id="resellerOptions">
                        @foreach($allResellers as $r)
                            @php $rKode = $r['kode'] ?? ''; $rNama = $r['nama'] ?? ''; @endphp
                            <option value="{{ $rKode }}">{{ $rKode }} - {{ $rNama }}</option>
                        @endforeach
                    </datalist>
                    @if(!empty($kode_reseller))
                        @php
                            $selectedReseller = collect($allResellers)->first(function($item) use ($kode_reseller) {
                                return strcasecmp($item['kode'] ?? '', $kode_reseller) === 0 
                                    || strcasecmp($item['nama'] ?? '', $kode_reseller) === 0
                                    || str_contains(strtolower($item['kode'] ?? ''), strtolower($kode_reseller))
                                    || str_contains(strtolower($item['nama'] ?? ''), strtolower($kode_reseller));
                            });
                        @endphp
                        @if($selectedReseller)
                            <div class="form-text small text-primary mt-1 text-truncate" style="font-size: 0.72rem;">
                                <i class="bx bx-check-circle me-1"></i><strong>{{ $selectedReseller['kode'] }}</strong>: {{ $selectedReseller['nama'] }}
                            </div>
                        @endif
                    @endif
                </div>

                <!-- Filter Status Profit -->
                <div class="col-6 col-md-3 col-lg-2">
                    <label class="form-label small fw-semibold text-muted mb-1">
                        <i class="bx bx-filter-alt me-1"></i>Status Profit
                    </label>
                    <select name="profit_filter" class="form-select form-select-sm">
                        <option value="all" {{ $profit_filter === 'all' ? 'selected' : '' }}>Semua Status</option>
                        <option value="profit" {{ $profit_filter === 'profit' ? 'selected' : '' }}>Hanya Profit (Untung)</option>
                        <option value="minus" {{ $profit_filter === 'minus' ? 'selected' : '' }}>Hanya Rugi (Minus)</option>
                    </select>
                </div>

                <!-- Cari Nama / Kode -->
                <div class="col-6 col-md-3 col-lg-2">
                    <label class="form-label small fw-semibold text-muted mb-1">
                        <i class="bx bx-search me-1"></i>Cari Keyword
                    </label>
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Nama / Kode..." value="{{ $search }}">
                </div>

                <!-- Tombol Submit & Reset -->
                <div class="col-12 col-md-2 col-lg-1 d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm w-100" title="Terapkan Filter">
                        <i class="bx bx-filter"></i>
                    </button>
                    <a href="{{ route('admin.reseller-profit') }}" class="btn btn-outline-secondary btn-sm" title="Reset Filter">
                        <i class="bx bx-reset"></i>
                    </a>
                </div>
            </div>

            <!-- Shortcut Tombol Rentang Tanggal Cepat -->
            <div class="d-flex flex-wrap gap-1 mt-3 pt-2 border-top align-items-center">
                <span class="small text-muted me-2">Pilih Cepat:</span>
                <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" onclick="setQuickDate('today')">Hari Ini</button>
                <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" onclick="setQuickDate('yesterday')">Kemarin</button>
                <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" onclick="setQuickDate('last7')">7 Hari Terakhir</button>
                <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" onclick="setQuickDate('thisMonth')">Bulan Ini</button>
                <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" onclick="setQuickDate('lastMonth')">Bulan Lalu</button>

                <div class="ms-auto">
                    <a href="{{ route('admin.reseller-profit.export', request()->all()) }}" class="btn btn-sm btn-outline-success">
                        <i class="bx bx-download me-1"></i> Ekspor CSV / Excel
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Kartu Ringkasan Metrik Akumulasi Filter -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3">
                <div class="text-muted small fw-semibold text-uppercase mb-1">Total Pemakaian (Omset)</div>
                <h5 class="mb-1 fw-bold text-dark">Rp {{ number_format($sumOmset, 0, ',', '.') }}</h5>
                <span class="text-muted small">{{ number_format($sumSukses, 0, ',', '.') }} Transaksi Sukses</span>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3">
                <div class="text-muted small fw-semibold text-uppercase mb-1">Total Modal Server (HPP)</div>
                <h5 class="mb-1 fw-semibold text-secondary">Rp {{ number_format($sumModal, 0, ',', '.') }}</h5>
                <span class="text-muted small">Biaya Biller / Modul</span>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3">
                <div class="text-muted small fw-semibold text-uppercase mb-1">Total Profit Bersih</div>
                <h5 class="mb-1 fw-bold {{ $sumProfit >= 0 ? 'text-success' : 'text-danger' }}">
                    Rp {{ number_format($sumProfit, 0, ',', '.') }}
                </h5>
                <span class="badge {{ $sumProfit >= 0 ? 'bg-label-success' : 'bg-label-danger' }}">
                    Rata-rata Margin {{ number_format($avgMargin, 2) }}%
                </span>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3">
                <div class="text-muted small fw-semibold text-uppercase mb-1">Total Agen Terdata</div>
                <h5 class="mb-1 fw-bold text-dark">{{ $countReseller }} Agen</h5>
                <span class="text-muted small">Periode: {{ date('d M Y', strtotime($tgl_awal)) }} - {{ date('d M Y', strtotime($tgl_akhir)) }}</span>
            </div>
        </div>
    </div>
</div>

<!-- Tabel Utama: Performa & Profit Per Member -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent py-3 border-bottom d-flex align-items-center justify-content-between">
        <div>
            <h6 class="mb-0 fw-bold text-dark">
                <i class="bx bx-table text-primary me-2"></i>Daftar Analisis Performa & Profit Agen
            </h6>
            <small class="text-muted">Hasil perhitungan keuntungan per reseller berdasarkan filter yang dipilih</small>
        </div>
        <span class="badge bg-label-primary">{{ count($items) }} Agen Ditemukan</span>
    </div>

    <div class="table-responsive text-nowrap">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>Reseller</th>
                    <th class="text-end">Sisa Saldo</th>
                    <th class="text-center">Total Trx</th>
                    <th class="text-end">Pemakaian / Omset</th>
                    <th class="text-end">Modal Server</th>
                    <th class="text-end">Profit Bersih</th>
                    <th class="text-center">Margin</th>
                    <th class="text-center">Status</th>
                    <th class="text-center" style="width: 100px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $index => $item)
                    @php
                        $omset = (float)($item['omset'] ?? 0);
                        $modal = (float)($item['modal'] ?? 0);
                        $profit = (float)($item['profit'] ?? 0);
                        $margin = (float)($item['margin'] ?? 0);
                        $isProfit = $profit >= 0;
                        $trxSukses = (int)($item['trx_sukses'] ?? 0);
                        $trxGagal = (int)($item['trx_gagal'] ?? 0);
                    @endphp
                    <tr>
                        <td class="text-muted small">{{ $loop->iteration }}</td>
                        <td>
                            <div class="fw-bold text-dark">{{ $item['nama'] ?? '-' }}</div>
                            <div class="small text-muted font-monospace">
                                <span>{{ $item['kode'] ?? '-' }}</span>
                                <span class="badge bg-label-secondary py-0 px-1 ms-1">Level {{ $item['kode_level'] ?? '-' }}</span>
                            </div>
                        </td>
                        <td class="text-end font-monospace">
                            Rp {{ number_format((float)($item['saldo'] ?? 0), 0, ',', '.') }}
                        </td>
                        <td class="text-center">
                            <span class="fw-semibold text-dark">{{ $item['total_trx'] ?? 0 }}</span>
                            <div class="small text-muted">
                                <span class="text-success">{{ $trxSukses }} <i class="bx bx-check"></i></span>
                                @if($trxGagal > 0)
                                    &bull; <span class="text-danger">{{ $trxGagal }} <i class="bx bx-x"></i></span>
                                @endif
                            </div>
                        </td>
                        <td class="text-end font-monospace fw-semibold text-dark">
                            Rp {{ number_format($omset, 0, ',', '.') }}
                        </td>
                        <td class="text-end font-monospace text-secondary">
                            Rp {{ number_format($modal, 0, ',', '.') }}
                        </td>
                        <td class="text-end font-monospace fw-bold {{ $isProfit ? 'text-success' : 'text-danger' }}">
                            Rp {{ number_format($profit, 0, ',', '.') }}
                        </td>
                        <td class="text-center">
                            <span class="badge {{ $isProfit ? 'bg-label-success' : 'bg-label-danger' }}">
                                {{ number_format($margin, 2) }}%
                            </span>
                        </td>
                        <td class="text-center">
                            @if($isProfit)
                                <span class="badge bg-label-success">
                                    <i class="bx bx-trending-up me-1"></i> Untung
                                </span>
                            @else
                                <span class="badge bg-label-danger">
                                    <i class="bx bx-trending-down me-1"></i> Rugi
                                </span>
                            @endif
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-outline-primary btn-sm py-1 px-2" onclick="showResellerDetail('{{ $item['kode'] }}', '{{ addslashes($item['nama']) }}')">
                                <i class="bx bx-show me-1"></i> Rincian
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center py-5 text-muted">
                            <i class="bx bx-folder-open fs-1 text-muted d-block mb-2"></i>
                            <h6 class="text-muted mb-1">Tidak Ada Data Transaksi</h6>
                            <p class="small text-muted mb-0">Tidak ditemukan aktivitas transaksi untuk filter tanggal dan kriteria yang dipilih.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Rincian Performa & Transaksi Member -->
<div class="modal fade" id="modalResellerDetail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom py-3">
                <div>
                    <h5 class="modal-title fw-bold text-dark mb-0" id="modalMemberTitle">Rincian Performa Member</h5>
                    <small class="text-muted" id="modalMemberSubtitle">Memuat data...</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="modalMemberBody">
                <div class="text-center py-5 text-muted" id="modalLoadingSpinner">
                    <div class="spinner-border text-primary mb-2" role="status"></div>
                    <div>Mengambil rincian data transaksi...</div>
                </div>

                <div id="modalDetailContent" style="display: none;">
                    <!-- Ringkasan Kartu Atas Member -->
                    <div class="row g-3 mb-4" id="modalSummaryCards"></div>

                    <!-- Tab Navigasi: Top Produk vs Riwayat Transaksi -->
                    <ul class="nav nav-tabs mb-3" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabTopProducts" type="button">
                                <i class="bx bx-cube me-1"></i> Top Produk Ditransaksikan
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabRecentTrx" type="button">
                                <i class="bx bx-history me-1"></i> Riwayat 30 Transaksi Terakhir
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content border-0 p-0">
                        <!-- Tab 1: Top Produk -->
                        <div class="tab-pane fade show active" id="tabTopProducts">
                            <div class="table-responsive border rounded">
                                <table class="table table-sm table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Kode Produk</th>
                                            <th class="text-center">Jml Trx</th>
                                            <th class="text-end">Omset (Rp)</th>
                                            <th class="text-end">Modal Server (Rp)</th>
                                            <th class="text-end">Profit Server (Rp)</th>
                                            <th class="text-center">Margin</th>
                                        </tr>
                                    </thead>
                                    <tbody id="modalTopProductsBody"></tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Tab 2: Riwayat Transaksi -->
                        <div class="tab-pane fade" id="tabRecentTrx">
                            <div class="table-responsive border rounded" style="max-height: 400px; overflow-y: auto;">
                                <table class="table table-sm table-hover align-middle mb-0">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <th>Waktu Entri</th>
                                            <th>Produk & Tujuan</th>
                                            <th class="text-end">Harga Jual</th>
                                            <th class="text-end">Harga Beli</th>
                                            <th class="text-end">Profit</th>
                                            <th class="text-center">Status</th>
                                            <th>SN / Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody id="modalRecentTrxBody"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('extra-scripts')
<script>
function setQuickDate(type) {
    const today = new Date();
    let startDate = new Date();
    let endDate = new Date();

    if (type === 'today') {
        startDate = today;
        endDate = today;
    } else if (type === 'yesterday') {
        startDate.setDate(today.getDate() - 1);
        endDate.setDate(today.getDate() - 1);
    } else if (type === 'last7') {
        startDate.setDate(today.getDate() - 6);
        endDate = today;
    } else if (type === 'thisMonth') {
        startDate = new Date(today.getFullYear(), today.getMonth(), 1);
        endDate = today;
    } else if (type === 'lastMonth') {
        startDate = new Date(today.getFullYear(), today.getMonth() - 1, 1);
        endDate = new Date(today.getFullYear(), today.getMonth(), 0);
    }

    const formatYMD = d => {
        const year = d.getFullYear();
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    };

    document.getElementById('tgl_awal').value = formatYMD(startDate);
    document.getElementById('tgl_akhir').value = formatYMD(endDate);
    document.getElementById('filterForm').submit();
}

function showResellerDetail(kode, nama) {
    const modalEl = document.getElementById('modalResellerDetail');
    const modal = new bootstrap.Modal(modalEl);

    document.getElementById('modalMemberTitle').innerText = `${nama} (${kode})`;
    document.getElementById('modalMemberSubtitle').innerText = `Periode: ${document.getElementById('tgl_awal').value} s/d ${document.getElementById('tgl_akhir').value}`;

    document.getElementById('modalLoadingSpinner').style.display = 'block';
    document.getElementById('modalDetailContent').style.display = 'none';

    modal.show();

    const tglAwal = document.getElementById('tgl_awal').value;
    const tglAkhir = document.getElementById('tgl_akhir').value;

    fetch(`{{ url('/admin/reseller-profit/detail') }}/${encodeURIComponent(kode)}?tgl_awal=${tglAwal}&tgl_akhir=${tglAkhir}`)
        .then(response => response.json())
        .then(res => {
            document.getElementById('modalLoadingSpinner').style.display = 'none';

            if (!res.ok) {
                alert(res.desc || 'Gagal memuat rincian agen');
                modal.hide();
                return;
            }

            const data = res.result;
            const summary = data.summary || {};
            const reseller = data.reseller || {};
            const topProducts = data.top_products || [];
            const recentTrx = data.recent_transactions || [];

            // Render Summary Cards
            const isProfit = (summary.profit || 0) >= 0;
            const summaryHtml = `
                <div class="col-6 col-md-3">
                    <div class="p-3 bg-light rounded border">
                        <div class="text-muted small">Saldo Saat Ini</div>
                        <div class="fw-bold text-dark fs-6">Rp ${new Intl.NumberFormat('id-ID').format(reseller.saldo || 0)}</div>
                        <div class="small text-muted">Level: ${reseller.kode_level || '-'}</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-3 bg-light rounded border">
                        <div class="text-muted small">Total Pemakaian</div>
                        <div class="fw-bold text-dark fs-6">Rp ${new Intl.NumberFormat('id-ID').format(summary.omset || 0)}</div>
                        <div class="small text-muted">${summary.trx_sukses || 0} Trx Sukses</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-3 bg-light rounded border">
                        <div class="text-muted small">Modal Server</div>
                        <div class="fw-semibold text-secondary fs-6">Rp ${new Intl.NumberFormat('id-ID').format(summary.modal || 0)}</div>
                        <div class="small text-muted">HPP Biller</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-3 ${isProfit ? 'bg-label-success' : 'bg-label-danger'} rounded border">
                        <div class="small fw-semibold">Profit Bersih</div>
                        <div class="fw-bold fs-6">Rp ${new Intl.NumberFormat('id-ID').format(summary.profit || 0)}</div>
                        <div class="small">Margin ${summary.margin || 0}%</div>
                    </div>
                </div>
            `;
            document.getElementById('modalSummaryCards').innerHTML = summaryHtml;

            // Render Top Products
            let prodHtml = '';
            if (topProducts.length === 0) {
                prodHtml = `<tr><td colspan="6" class="text-center py-3 text-muted small">Tidak ada data produk pada periode ini.</td></tr>`;
            } else {
                topProducts.forEach(p => {
                    const pProfit = (p.profit || 0);
                    const isPProfit = pProfit >= 0;
                    prodHtml += `
                        <tr>
                            <td><span class="fw-bold font-monospace">${p.kode_produk}</span></td>
                            <td class="text-center font-monospace">${p.total_trx}</td>
                            <td class="text-end font-monospace">Rp ${new Intl.NumberFormat('id-ID').format(p.omset || 0)}</td>
                            <td class="text-end font-monospace text-secondary">Rp ${new Intl.NumberFormat('id-ID').format(p.modal || 0)}</td>
                            <td class="text-end font-monospace fw-bold ${isPProfit ? 'text-success' : 'text-danger'}">
                                Rp ${new Intl.NumberFormat('id-ID').format(pProfit)}
                            </td>
                            <td class="text-center">
                                <span class="badge ${isPProfit ? 'bg-label-success' : 'bg-label-danger'}">${p.margin || 0}%</span>
                            </td>
                        </tr>
                    `;
                });
            }
            document.getElementById('modalTopProductsBody').innerHTML = prodHtml;

            // Render Recent Transactions
            let trxHtml = '';
            if (recentTrx.length === 0) {
                trxHtml = `<tr><td colspan="7" class="text-center py-3 text-muted small">Tidak ada transaksi tercatat.</td></tr>`;
            } else {
                recentTrx.forEach(t => {
                    const st = t.status_text || t.status || '';
                    let badgeClass = 'bg-label-secondary';
                    if (st === 'Sukses') badgeClass = 'bg-label-success';
                    else if (st === 'Proses') badgeClass = 'bg-label-warning';
                    else badgeClass = 'bg-label-danger';

                    const tProfit = (t.profit || 0);
                    const isTProfit = tProfit >= 0;

                    trxHtml += `
                        <tr>
                            <td class="small text-muted font-monospace">${t.tgl_entri || '-'}</td>
                            <td>
                                <div class="fw-bold font-monospace">${t.kode_produk || '-'}</div>
                                <div class="small text-muted font-monospace">${t.tujuan || '-'}</div>
                            </td>
                            <td class="text-end font-monospace">Rp ${new Intl.NumberFormat('id-ID').format(t.harga || 0)}</td>
                            <td class="text-end font-monospace text-secondary">Rp ${new Intl.NumberFormat('id-ID').format(t.harga_beli || 0)}</td>
                            <td class="text-end font-monospace fw-bold ${isTProfit ? 'text-success' : 'text-danger'}">
                                Rp ${new Intl.NumberFormat('id-ID').format(tProfit)}
                            </td>
                            <td class="text-center">
                                <span class="badge ${badgeClass}">${st}</span>
                            </td>
                            <td class="small font-monospace text-truncate" style="max-width: 180px;" title="${t.sn || '-'}">${t.sn || '-'}</td>
                        </tr>
                    `;
                });
            }
            document.getElementById('modalRecentTrxBody').innerHTML = trxHtml;

            document.getElementById('modalDetailContent').style.display = 'block';
        })
        .catch(err => {
            document.getElementById('modalLoadingSpinner').style.display = 'none';
            alert('Terjadi kesalahan saat mengambil rincian data.');
            modal.hide();
        });
}

function clearMemberFilter() {
    const el = document.getElementById('kode_reseller');
    if (el) el.value = '';
    document.getElementById('filterForm').submit();
}

document.addEventListener('DOMContentLoaded', function() {
    const memberInput = document.getElementById('kode_reseller');
    if (memberInput) {
        memberInput.addEventListener('change', function() {
            if (this.value.trim() !== '') {
                document.getElementById('filterForm').submit();
            }
        });
    }
});
</script>
@endsection
