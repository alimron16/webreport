@extends('admin.layout')

@section('title', 'Dashboard Utama')
@section('page-header', 'Dashboard Ringkasan Server')

@section('content')

@php
    $today = $stats['today'] ?? [];
    $yesterday = $stats['yesterday'] ?? [];
    $month = $stats['month'] ?? [];
    $server = $stats['server'] ?? [];
    $trendChart = $stats['trend_chart'] ?? [];
    $topResellers = $stats['top_resellers'] ?? [];
    $minusResellers = $stats['minus_resellers'] ?? [];

    $todayOmset = (float)($today['omset'] ?? 0);
    $todayProfit = (float)($today['profit'] ?? 0);
    $todayMargin = (float)($today['margin'] ?? 0);
    $todaySukses = (int)($today['trx_sukses'] ?? 0);
    $todayProses = (int)($today['trx_proses'] ?? 0);
    $todayGagal = (int)($today['trx_gagal'] ?? 0);
    $todayTotalTrx = (int)($today['total_trx'] ?? 0);

    $monthOmset = (float)($month['omset'] ?? 0);
    $monthProfit = (float)($month['profit'] ?? 0);
    $monthModal = (float)($month['modal'] ?? 0);
    $monthMargin = (float)($month['margin'] ?? 0);
    $monthSukses = (int)($month['trx_sukses'] ?? 0);

    $totalSaldo = (float)($server['total_saldo'] ?? 0);
    $totalAgen = (int)($server['total_reseller_aktif'] ?? 0);
@endphp

<!-- Baris Atas: 4 Kartu KPI Utama -->
<div class="row g-3 mb-4">
    <!-- Omset Hari Ini -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-uppercase">Omset Hari Ini</span>
                    <div class="p-2 rounded bg-label-primary text-primary">
                        <i class="bx bx-wallet fs-5"></i>
                    </div>
                </div>
                <h4 class="mb-1 fw-bold text-dark">Rp {{ number_format($todayOmset, 0, ',', '.') }}</h4>
                <div class="d-flex align-items-center text-muted small">
                    <span class="text-truncate">Kemarin: Rp {{ number_format((float)($yesterday['omset'] ?? 0), 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Gross Profit Hari Ini -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-uppercase">Profit Hari Ini</span>
                    <div class="p-2 rounded {{ $todayProfit >= 0 ? 'bg-label-success text-success' : 'bg-label-danger text-danger' }}">
                        <i class="bx {{ $todayProfit >= 0 ? 'bx-trending-up' : 'bx-trending-down' }} fs-5"></i>
                    </div>
                </div>
                <h4 class="mb-1 fw-bold {{ $todayProfit >= 0 ? 'text-success' : 'text-danger' }}">
                    Rp {{ number_format($todayProfit, 0, ',', '.') }}
                </h4>
                <div class="d-flex align-items-center small">
                    <span class="badge {{ $todayProfit >= 0 ? 'bg-label-success' : 'bg-label-danger' }} me-2">
                        Margin {{ number_format($todayMargin, 2) }}%
                    </span>
                    <span class="text-muted">Kemarin: Rp {{ number_format((float)($yesterday['profit'] ?? 0), 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Transaksi Hari Ini -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-uppercase">Transaksi Hari Ini</span>
                    <div class="p-2 rounded bg-label-info text-info">
                        <i class="bx bx-transfer-alt fs-5"></i>
                    </div>
                </div>
                <h4 class="mb-1 fw-bold text-dark">{{ number_format($todayTotalTrx, 0, ',', '.') }} <span class="fs-7 text-muted fw-normal">Trx</span></h4>
                <div class="d-flex gap-2 small text-nowrap">
                    <span class="text-success fw-semibold"><i class="bx bx-check-circle"></i> {{ $todaySukses }}</span>
                    <span class="text-warning fw-semibold"><i class="bx bx-time-five"></i> {{ $todayProses }}</span>
                    <span class="text-danger fw-semibold"><i class="bx bx-x-circle"></i> {{ $todayGagal }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Saldo & Agen Terdaftar -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-uppercase">Total Saldo Agen</span>
                    <div class="p-2 rounded bg-label-secondary text-secondary">
                        <i class="bx bx-group fs-5"></i>
                    </div>
                </div>
                <h4 class="mb-1 fw-bold text-dark">Rp {{ number_format($totalSaldo, 0, ',', '.') }}</h4>
                <div class="d-flex align-items-center text-muted small">
                    <i class="bx bx-user-check text-success me-1"></i>
                    <span>{{ $totalAgen }} Agen Aktif Terdata</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Baris 2: Grafik 7 Hari & Rekap Bulan Ini -->
<div class="row g-3 mb-4">
    <!-- Grafik 7 Hari Terakhir -->
    <div class="col-12 col-xl-8">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-header bg-transparent py-3 border-bottom d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="bx bx-bar-chart-alt-2 text-primary me-2"></i>Tren Performa 7 Hari Terakhir
                    </h6>
                    <small class="text-muted">Perbandingan nilai Omset Transaksi vs Modal Server vs Laba Bersih</small>
                </div>
                <a href="{{ route('admin.reseller-profit') }}" class="btn btn-outline-primary btn-sm">
                    <i class="bx bx-list-check me-1"></i> Laporan Detail
                </a>
            </div>
            <div class="card-body p-3">
                <div style="height: 290px; position: relative;">
                    <canvas id="adminTrendChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Rekap Akumulasi Bulan Ini -->
    <div class="col-12 col-xl-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-header bg-transparent py-3 border-bottom">
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="bx bx-calendar-check text-primary me-2"></i>Akumulasi Bulan Ini
                </h6>
                <small class="text-muted">{{ now()->translatedFormat('F Y') }}</small>
            </div>
            <div class="card-body p-3 d-flex flex-column justify-content-between">
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-muted small">Total Omset Penjualan</span>
                        <span class="fw-bold text-dark">Rp {{ number_format($monthOmset, 0, ',', '.') }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-muted small">Total Modal Server (HPP)</span>
                        <span class="fw-semibold text-secondary">Rp {{ number_format($monthModal, 0, ',', '.') }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-muted small">Total Profit Bersih</span>
                        <span class="fw-bold {{ $monthProfit >= 0 ? 'text-success' : 'text-danger' }}">
                            Rp {{ number_format($monthProfit, 0, ',', '.') }}
                        </span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-muted small">Rata-rata Margin Server</span>
                        <span class="badge {{ $monthMargin >= 0 ? 'bg-label-success' : 'bg-label-danger' }}">
                            {{ number_format($monthMargin, 2) }}%
                        </span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-2">
                        <span class="text-muted small">Total Transaksi Sukses</span>
                        <span class="fw-bold text-dark">{{ number_format($monthSukses, 0, ',', '.') }} Trx</span>
                    </div>
                </div>

                <div class="p-3 rounded bg-light border">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="bx bx-info-circle text-primary fs-5"></i>
                        <span class="fw-semibold small text-dark">Audit Pemakaian Member</span>
                    </div>
                    <p class="small text-muted mb-3">
                        Periksa rincian keuntungan atau kerugian setiap reseller secara spesifik berdasarkan rentang tanggal.
                    </p>
                    <a href="{{ route('admin.reseller-profit') }}" class="btn btn-primary btn-sm w-100">
                        <i class="bx bx-line-chart me-1"></i> Buka Laporan Profit Agen
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Baris 3: Top Agen & Alert Margin Minus -->
<div class="row g-3">
    <!-- Top 5 Reseller Paling Profit -->
    <div class="col-12 col-xl-7">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-header bg-transparent py-3 border-bottom d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="bx bx-award text-warning me-2"></i>Top 5 Agen Paling Profit Bulan Ini
                    </h6>
                    <small class="text-muted">Agen penyumbang margin keuntungan tertinggi</small>
                </div>
                <a href="{{ route('admin.reseller-profit') }}" class="btn btn-link text-primary p-0 small text-decoration-none">
                    Semua Agen <i class="bx bx-chevron-right"></i>
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Reseller</th>
                            <th class="text-center">Trx Sukses</th>
                            <th class="text-end">Omset</th>
                            <th class="text-end">Profit Server</th>
                            <th class="text-center">Margin</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topResellers as $top)
                            @php
                                $tProfit = (float)($top['profit'] ?? 0);
                                $tOmset = (float)($top['omset'] ?? 0);
                                $tMargin = (float)($top['margin'] ?? 0);
                            @endphp
                            <tr>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $top['nama'] ?? '-' }}</div>
                                    <div class="small text-muted font-monospace">{{ $top['kode'] ?? '-' }} &bull; Level {{ $top['kode_level'] ?? '-' }}</div>
                                </td>
                                <td class="text-center font-monospace">{{ number_format($top['trx_sukses'] ?? 0, 0, ',', '.') }}</td>
                                <td class="text-end font-monospace">Rp {{ number_format($tOmset, 0, ',', '.') }}</td>
                                <td class="text-end font-monospace fw-bold text-success">
                                    Rp {{ number_format($tProfit, 0, ',', '.') }}
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-label-success">{{ number_format($tMargin, 2) }}%</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted small">
                                    Belum ada transaksi sukses pada bulan ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Alert Agen Margin Minus / Status Keamanan Margin -->
    <div class="col-12 col-xl-5">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-header bg-transparent py-3 border-bottom">
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="bx bx-shield-quarter text-primary me-2"></i>Peringatan Margin Server
                </h6>
                <small class="text-muted">Deteksi potensi kerugian atau salah setting harga produk</small>
            </div>
            <div class="card-body p-3">
                @if(count($minusResellers) > 0)
                    <div class="alert alert-danger d-flex align-items-start gap-2 mb-3" role="alert">
                        <i class="bx bx-error-circle fs-4 flex-shrink-0 mt-1"></i>
                        <div>
                            <div class="fw-bold">Ditemukan Agen dengan Margin Minus</div>
                            <div class="small">
                                Terdapat {{ count($minusResellers) }} agen dengan total profit negatif pada bulan ini. Hal ini dapat terjadi jika harga jual reseller lebih rendah dibanding harga modal supplier.
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive border rounded mb-3">
                        <table class="table table-sm mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Agen</th>
                                    <th class="text-end">Rugi / Minus</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($minusResellers as $m)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold text-dark">{{ $m['nama'] ?? '-' }}</div>
                                            <div class="small text-muted font-monospace">{{ $m['kode'] ?? '-' }}</div>
                                        </td>
                                        <td class="text-end font-monospace fw-bold text-danger">
                                            Rp {{ number_format((float)($m['profit'] ?? 0), 0, ',', '.') }}
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('admin.reseller-profit', ['kode_reseller' => $m['kode']]) }}" class="btn btn-outline-danger btn-sm py-0 px-2">
                                                Cek Trx
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-4 text-center rounded bg-light border my-2">
                        <div class="p-3 rounded-circle bg-label-success d-inline-flex mb-3">
                            <i class="bx bx-check-circle fs-2 text-success"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-1">Semua Margin Positif</h6>
                        <p class="text-muted small mb-0">
                            Tidak ditemukan agen dengan akumulasi laba minus pada bulan ini. Seluruh produk menghasilkan profit yang sehat.
                        </p>
                    </div>
                @endif

                <div class="mt-3 pt-2 border-top d-flex justify-content-between align-items-center">
                    <span class="small text-muted">Ingin audit detail per tanggal?</span>
                    <a href="{{ route('admin.reseller-profit') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bx bx-calendar me-1"></i> Filter Tanggal
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('extra-scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const chartData = @json($trendChart);

    const labels = chartData.map(item => item.label);
    const omsetData = chartData.map(item => item.omset);
    const modalData = chartData.map(item => item.modal);
    const profitData = chartData.map(item => item.profit);

    const ctx = document.getElementById('adminTrendChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Omset Penjualan (Rp)',
                    data: omsetData,
                    backgroundColor: 'rgba(37, 99, 235, 0.75)',
                    borderColor: '#2563eb',
                    borderWidth: 1,
                    borderRadius: 4,
                    order: 2
                },
                {
                    label: 'Modal Server (Rp)',
                    data: modalData,
                    backgroundColor: 'rgba(100, 116, 139, 0.45)',
                    borderColor: '#64748b',
                    borderWidth: 1,
                    borderRadius: 4,
                    order: 3
                },
                {
                    label: 'Laba Bersih / Profit (Rp)',
                    data: profitData,
                    type: 'line',
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.15)',
                    borderWidth: 3,
                    fill: false,
                    tension: 0.3,
                    pointBackgroundColor: '#10b981',
                    pointRadius: 4,
                    order: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false
            },
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        boxWidth: 12,
                        font: { size: 12 }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += 'Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed.y);
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            if (value >= 1000000) return (value / 1000000).toFixed(1) + ' jt';
                            if (value >= 1000) return (value / 1000).toFixed(0) + ' rb';
                            return value;
                        }
                    }
                }
            }
        }
    });
});
</script>
@endsection
