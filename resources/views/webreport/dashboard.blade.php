@extends('webreport.layout')

@section('title', 'Dashboard')
@section('page-header', 'Dashboard Ringkasan')

@section('content')

@if(isset($reseller))

    <!-- ─── SNEAT STATS ROW ───────────────────────────────────── -->
    <div class="row g-3 mb-4">

        <!-- Saldo Aktif -->
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100 mb-0">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="avatar flex-shrink-0">
                            <span class="avatar-initial rounded bg-label-primary p-2">
                                <i class="bx bx-wallet fs-4"></i>
                            </span>
                        </div>
                        <span class="badge bg-label-primary">Deposit</span>
                    </div>
                    <span class="d-block text-muted fw-semibold mb-1" style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px;">Saldo Aktif</span>
                    <h4 class="card-title mb-1 text-primary fw-bold">Rp {{ number_format($saldo['saldo'] ?? 0, 0, ',', '.') }}</h4>
                    <small class="text-success fw-semibold d-inline-flex align-items-center">
                        <i class="bx bx-check-circle me-1"></i> Real-time Database
                    </small>
                </div>
            </div>
        </div>

        <!-- Total Trx (14 Hari) -->
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100 mb-0">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="avatar flex-shrink-0">
                            <span class="avatar-initial rounded bg-label-info p-2">
                                <i class="bx bx-receipt fs-4"></i>
                            </span>
                        </div>
                        <span class="badge bg-label-info">14 Hari</span>
                    </div>
                    <span class="d-block text-muted fw-semibold mb-1" style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px;">Total Transaksi</span>
                    <h4 class="card-title mb-1 fw-bold">{{ number_format($total_trx_14 ?? 0) }} <span class="fs-6 fw-normal text-muted">Trx</span></h4>
                    <small class="text-muted fw-semibold">
                        Aktivitas transaksi 14 hari
                    </small>
                </div>
            </div>
        </div>

        <!-- Trx Sukses -->
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100 mb-0">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="avatar flex-shrink-0">
                            <span class="avatar-initial rounded bg-label-success p-2">
                                <i class="bx bx-check-double fs-4"></i>
                            </span>
                        </div>
                        @php $rate = ($total_trx_14 > 0) ? round(($success_trx_14 / $total_trx_14) * 100, 1) : 0; @endphp
                        <span class="badge bg-label-success">{{ $rate }}% Sukses</span>
                    </div>
                    <span class="d-block text-muted fw-semibold mb-1" style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px;">Transaksi Sukses</span>
                    <h4 class="card-title mb-1 text-success fw-bold">{{ number_format($success_trx_14 ?? 0) }} <span class="fs-6 fw-normal text-muted">Trx</span></h4>
                    <small class="text-success fw-semibold d-inline-flex align-items-center">
                        <i class="bx bx-trending-up me-1"></i> Success rate {{ $rate }}%
                    </small>
                </div>
            </div>
        </div>

        <!-- Omzet Transaksi -->
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100 mb-0">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="avatar flex-shrink-0">
                            <span class="avatar-initial rounded bg-label-warning p-2">
                                <i class="bx bx-line-chart fs-4"></i>
                            </span>
                        </div>
                        <span class="badge bg-label-warning">Omzet</span>
                    </div>
                    <span class="d-block text-muted fw-semibold mb-1" style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px;">Omzet (14 Hari)</span>
                    <h4 class="card-title mb-1 fw-bold">Rp {{ number_format($omzet_14 ?? 0, 0, ',', '.') }}</h4>
                    <small class="text-muted fw-semibold">
                        Dari transaksi sukses
                    </small>
                </div>
            </div>
        </div>

    </div>

    <!-- ─── SNEAT MAIN GRID: CHART + PROFILE ───────────────── -->
    <div class="row g-4">

        <!-- Chart Card -->
        <div class="col-12 col-lg-8">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div class="card-title mb-0">
                        <i class="bx bx-bar-chart-alt-2 fs-4 text-primary"></i>
                        <span>Tren Transaksi (14 Hari Terakhir)</span>
                    </div>
                    <span class="badge bg-label-secondary">
                        {{ now()->subDays(13)->format('d M') }} – {{ now()->format('d M Y') }}
                    </span>
                </div>
                <div class="card-body">
                    <div style="position: relative; height: 280px; width: 100%;">
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Profile & Quick Links -->
        <div class="col-12 col-lg-4">
            
            <!-- Reseller Profile Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <div class="card-title mb-0">
                        <i class="bx bx-user-circle fs-4 text-primary"></i>
                        <span>Profil Reseller</span>
                    </div>
                    <span class="badge bg-label-primary">{{ strtoupper($reseller['kode'] ?? 'RS') }}</span>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-4 border-0">
                            <span class="text-muted" style="font-size: 0.85rem;">Kode Reseller</span>
                            <span class="fw-semibold text-primary">{{ strtoupper($reseller['kode'] ?? '') }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-4 border-0">
                            <span class="text-muted" style="font-size: 0.85rem;">Nama Outlet</span>
                            <span class="fw-semibold">{{ $reseller['nama'] ?? $reseller['name'] ?? '-' }}</span>
                        </li>
                        @php
                            if (!function_exists('maskContactInfo')) {
                                function maskContactInfo($str) {
                                    if (!$str || $str === '-') return '-';
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
                        <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-4 border-0">
                            <span class="text-muted" style="font-size: 0.85rem;">Email</span>
                            <span class="fw-semibold">{{ maskContactInfo($reseller['email'] ?? '-') }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-4 border-0">
                            <span class="text-muted" style="font-size: 0.85rem;">No. WhatsApp</span>
                            <span class="fw-semibold">{{ maskContactInfo($reseller['nomor_hp'] ?? $reseller['pengirim'] ?? '-') }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Quick Action Cards -->
            <div class="card">
                <div class="card-header py-3">
                    <div class="card-title mb-0" style="font-size: 0.95rem;">
                        <i class="bx bx-grid-alt fs-5 text-primary"></i>
                        <span>Aksi Cepat</span>
                    </div>
                </div>
                <div class="card-body d-flex flex-column gap-2 pt-2">
                    <a href="{{ route('webreport.deposit') }}" class="btn btn-primary w-100 justify-content-start py-2">
                        <i class="bx bx-plus-circle fs-5 me-1"></i>
                        <span>Ambil Tiket Deposit</span>
                    </a>
                    <a href="{{ route('webreport.transactions') }}" class="btn btn-outline-primary w-100 justify-content-start py-2">
                        <i class="bx bx-history fs-5 me-1"></i>
                        <span>Riwayat Transaksi</span>
                    </a>
                    <a href="{{ route('webreport.prices') }}" class="btn btn-outline-primary w-100 justify-content-start py-2">
                        <i class="bx bx-purchase-tag fs-5 me-1"></i>
                        <span>Cek Daftar Harga Realtime</span>
                    </a>
                </div>
            </div>

        </div>

    </div>

@else
    <div class="card text-center p-5">
        <i class="bx bx-error-circle text-danger mb-3" style="font-size: 3rem;"></i>
        <h4 class="mb-2">Reseller Tidak Ditemukan</h4>
        <p class="text-muted">Kode reseller <strong>{{ $active_reseller_code }}</strong> tidak terdaftar dalam database OtomaX.</p>
    </div>
@endif

@endsection

@section('extra-scripts')
@if(isset($reseller) && !empty($trend_data))
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
    (function() {
        const raw = @json($trend_data);

        const labels    = raw.map(d => {
            const dt = new Date(d.tgl);
            return dt.toLocaleDateString('id-ID', { day: '2-digit', month: 'short' });
        });
        const totalTrx  = raw.map(d => d.total_trx);
        const suksesTrx = raw.map(d => d.trx_sukses);

        const saudagarBlue = '#0B6FB6';
        const saudagarGreen = '#10b981';

        const ctx = document.getElementById('trendChart').getContext('2d');

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels,
                datasets: [
                    {
                        label: 'Total Transaksi',
                        data: totalTrx,
                        backgroundColor: 'rgba(11, 111, 182, 0.16)',
                        borderColor: saudagarBlue,
                        borderWidth: 2,
                        borderRadius: 6,
                        borderSkipped: false,
                        order: 2,
                    },
                    {
                        label: 'Transaksi Sukses',
                        data: suksesTrx,
                        type: 'line',
                        borderColor: saudagarGreen,
                        backgroundColor: 'rgba(16, 185, 129, 0.12)',
                        borderWidth: 2.5,
                        pointBackgroundColor: saudagarGreen,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        tension: 0.35,
                        fill: true,
                        order: 1,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: { family: "'Public Sans', sans-serif", size: 12, weight: '500' },
                            color: '#697a8d',
                            boxWidth: 12,
                            padding: 16,
                            usePointStyle: true,
                        }
                    },
                    tooltip: {
                        backgroundColor: '#ffffff',
                        titleColor: '#566a7f',
                        bodyColor: '#697a8d',
                        borderColor: '#d9dee3',
                        borderWidth: 1,
                        padding: 12,
                        boxPadding: 4,
                        titleFont: { family: "'Public Sans', sans-serif", weight: '600' },
                        bodyFont:  { family: "'Public Sans', sans-serif" },
                        callbacks: {
                            label: ctx => ` ${ctx.dataset.label}: ${ctx.parsed.y.toLocaleString('id-ID')} trx`
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: {
                            font: { family: "'Public Sans', sans-serif", size: 11 },
                            color: '#a1acb8',
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f0f2f4', lineWidth: 1 },
                        ticks: {
                            font: { family: "'Public Sans', sans-serif", size: 11 },
                            color: '#a1acb8',
                            precision: 0,
                        }
                    }
                }
            }
        });
    })();
</script>
@endif
@endsection
