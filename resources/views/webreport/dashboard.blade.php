@extends('webreport.layout')

@section('title', 'Dashboard')
@section('page-header', 'Dashboard')

@section('extra-styles')
<style>
    /* ── Stats row ─────────────────────────── */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    /* override colour per card */
    .stat-card.green  { }
    .stat-card.amber  { }
    .stat-card.sky    { }

    /* ── Two-column layout ─────────────────── */
    .dash-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 340px;
        gap: 20px;
        align-items: start;
    }

    @media (max-width: 900px) {
        .dash-grid { grid-template-columns: 1fr; }
    }

    /* ── Chart container ───────────────────── */
    .chart-wrap {
        position: relative;
        height: 240px;
    }

    /* ── Profile table ─────────────────────── */
    .profile-table {
        width: 100%;
        border-collapse: collapse;
    }

    .profile-table tr { border-bottom: 1px solid var(--color-border); }
    .profile-table tr:last-child { border-bottom: none; }

    .profile-table td {
        padding: 10px 0;
        font-size: 13.5px;
    }

    .profile-table td:first-child {
        color: var(--color-text-3);
        font-weight: 500;
        width: 45%;
    }

    .profile-table td:last-child {
        color: var(--color-text);
        font-weight: 600;
        text-align: right;
    }

    /* ── Quick link buttons ────────────────── */
    .quick-links {
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin-top: 16px;
    }

    .quick-link {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 14px;
        border: 1px solid var(--color-border);
        border-radius: var(--radius);
        text-decoration: none;
        color: var(--color-text-2);
        font-size: 13px;
        font-weight: 500;
        background: var(--color-bg);
        transition: all 150ms;
    }

    .quick-link:hover {
        background: var(--color-purple-light);
        color: var(--color-purple);
        border-color: var(--color-purple);
    }

    .quick-link svg {
        width: 16px;
        height: 16px;
        flex-shrink: 0;
        color: var(--color-purple);
    }
</style>
@endsection

@section('content')

@if(isset($reseller))

    <!-- ─── STATS ROW ───────────────────────────────────── -->
    <div class="stats-grid">

        <!-- Saldo -->
        <div class="stat-card">
            <div class="stat-label">Saldo Aktif</div>
            <div class="stat-value">Rp {{ number_format($saldo['saldo'] ?? 0, 0, ',', '.') }}</div>
            <div class="stat-sub">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z"/>
                    <path d="M12 6v6l4 2"/>
                </svg>
                Real-time
            </div>
        </div>

        <!-- Total Transaksi 14 Hari -->
        <div class="stat-card sky">
            <div class="stat-label">Total Trx (14 Hari)</div>
            <div class="stat-value">{{ number_format($total_trx_14 ?? 0) }}</div>
            <div class="stat-sub">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                14 hari terakhir
            </div>
        </div>

        <!-- Transaksi Sukses -->
        <div class="stat-card green">
            <div class="stat-label">Trx Sukses (14 Hari)</div>
            <div class="stat-value">{{ number_format($success_trx_14 ?? 0) }}</div>
            <div class="stat-sub">
                @php $rate = ($total_trx_14 > 0) ? round(($success_trx_14 / $total_trx_14) * 100, 1) : 0; @endphp
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
                Success rate {{ $rate }}%
            </div>
        </div>

        <!-- Omzet -->
        <div class="stat-card amber">
            <div class="stat-label">Omzet (14 Hari)</div>
            <div class="stat-value" style="font-size:1.25rem;">
                Rp {{ number_format($omzet_14 ?? 0, 0, ',', '.') }}
            </div>
            <div class="stat-sub">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="12" y1="1" x2="12" y2="23"/>
                    <path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>
                </svg>
                Dari transaksi sukses
            </div>
        </div>

    </div>

    <!-- ─── MAIN CONTENT: CHART + PROFILE ───────────────── -->
    <div class="dash-grid">

        <!-- Chart Card -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                    </svg>
                    Tren Transaksi — 14 Hari Terakhir
                </div>
                <span style="font-size:11px; color:var(--color-text-4); font-weight:600;">
                    {{ now()->subDays(13)->format('d M') }} – {{ now()->format('d M Y') }}
                </span>
            </div>
            <div class="card-body">
                <div class="chart-wrap">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Right Column: Profile + Quick Links -->
        <div style="display:flex; flex-direction:column; gap:16px;">

            <!-- Profile Card -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Profil Reseller
                    </div>
                </div>
                <div class="card-body" style="padding-top:12px; padding-bottom:12px;">
                    <table class="profile-table">
                        <tr>
                            <td>Kode</td>
                            <td>{{ strtoupper($reseller['kode'] ?? '') }}</td>
                        </tr>
                        <tr>
                            <td>Nama</td>
                            <td>{{ $reseller['nama'] ?? $reseller['name'] ?? '-' }}</td>
                        </tr>
                        @php
                            function maskContactInfo($str) {
                                if (!$str || $str === '-') return '-';
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
                        <tr>
                            <td>Email</td>
                            <td>{{ maskContactInfo($reseller['email'] ?? '-') }}</td>
                        </tr>
                        <tr>
                            <td>No. HP / WA</td>
                            <td>{{ maskContactInfo($reseller['nomor_hp'] ?? $reseller['pengirim'] ?? '-') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

        </div><!-- /right column -->
    </div><!-- /dash-grid -->

@else
    <div class="card" style="text-align:center; padding:56px 24px;">
        <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24"
             style="color:var(--color-danger); margin:0 auto 16px; display:block;">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        <h2 style="font-size:1.1rem; font-weight:700; color:var(--color-text); margin-bottom:8px;">Reseller tidak ditemukan</h2>
        <p style="color:var(--color-text-3); font-size:13px;">Kode <strong>{{ $active_reseller_code }}</strong> tidak terdaftar di sistem OtomaX.</p>
    </div>
@endif

@endsection

@section('extra-scripts')
@if(isset($reseller) && !empty($trend_data))
{{-- Chart.js via CDN — minimal bundle, no Vue/React dependency --}}
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

        const purple = '#7c3aed';
        const green  = '#059669';

        const ctx = document.getElementById('trendChart').getContext('2d');

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels,
                datasets: [
                    {
                        label: 'Total Transaksi',
                        data: totalTrx,
                        backgroundColor: 'rgba(124,58,237,0.12)',
                        borderColor: purple,
                        borderWidth: 2,
                        borderRadius: 5,
                        borderSkipped: false,
                        order: 2,
                    },
                    {
                        label: 'Transaksi Sukses',
                        data: suksesTrx,
                        type: 'line',
                        borderColor: green,
                        backgroundColor: 'rgba(5,150,105,0.08)',
                        borderWidth: 2.5,
                        pointBackgroundColor: green,
                        pointRadius: 3,
                        pointHoverRadius: 5,
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
                            font: { family: "'Inter', sans-serif", size: 12 },
                            color: '#6b7280',
                            boxWidth: 12,
                            padding: 16,
                        }
                    },
                    tooltip: {
                        backgroundColor: '#fff',
                        titleColor: '#111827',
                        bodyColor: '#374151',
                        borderColor: '#e5e7eb',
                        borderWidth: 1,
                        padding: 12,
                        titleFont: { family: "'Inter', sans-serif", weight: '700' },
                        bodyFont:  { family: "'Inter', sans-serif" },
                        callbacks: {
                            label: ctx => ` ${ctx.dataset.label}: ${ctx.parsed.y.toLocaleString('id-ID')} trx`
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: {
                            font: { family: "'Inter', sans-serif", size: 11 },
                            color: '#9ca3af',
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f3f4f6', lineWidth: 1 },
                        ticks: {
                            font: { family: "'Inter', sans-serif", size: 11 },
                            color: '#9ca3af',
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
