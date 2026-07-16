@extends('webreport.layout')

@section('title', 'History Transaksi')
@section('page-header', 'History Transaksi')

@section('extra-styles')
<style>
    .filter-card {
        background: var(--color-white);
        border: 1px solid var(--color-border);
        border-radius: var(--radius);
        padding: 16px 20px;
        margin-bottom: 20px;
        box-shadow: var(--shadow-sm);
    }

    .filter-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        gap: 12px;
        align-items: flex-end;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    /* Modal */
    .modal-backdrop {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.45);
        z-index: 1000;
        justify-content: center;
        align-items: center;
        padding: 16px;
    }

    .modal-box {
        background: var(--color-white);
        border: 1px solid var(--color-border);
        border-radius: 12px;
        width: 100%;
        max-width: 480px;
        box-shadow: 0 20px 50px rgba(0,0,0,.15);
        animation: modalIn .2s ease;
    }

    @keyframes modalIn {
        from { transform: scale(.96) translateY(8px); opacity: 0; }
        to   { transform: scale(1)   translateY(0);   opacity: 1; }
    }

    .modal-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 20px;
        border-bottom: 1px solid var(--color-border);
    }

    .modal-head h3 {
        font-family: 'Outfit', sans-serif;
        font-size: 15px;
        font-weight: 700;
        color: var(--color-text);
    }

    .modal-close {
        background: none;
        border: none;
        font-size: 20px;
        color: var(--color-text-4);
        cursor: pointer;
        line-height: 1;
        padding: 2px 6px;
        border-radius: 4px;
    }

    .modal-close:hover { background: var(--color-bg); color: var(--color-text); }

    .modal-body { padding: 16px 20px; }

    .detail-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        padding: 9px 0;
        border-bottom: 1px solid var(--color-border);
        font-size: 13px;
    }

    .detail-row:last-child { border-bottom: none; }
    .detail-label { color: var(--color-text-3); font-weight: 500; flex-shrink: 0; }
    .detail-value { color: var(--color-text); font-weight: 600; text-align: right; }

    .detail-keterangan {
        display: flex;
        flex-direction: column;
        gap: 6px;
        padding: 9px 0;
        font-size: 13px;
    }

    .keterangan-box {
        background: var(--color-bg);
        border: 1px solid var(--color-border);
        border-radius: 6px;
        padding: 10px 12px;
        font-size: 12.5px;
        color: var(--color-text-2);
        line-height: 1.5;
    }

    .code-chip {
        display: inline-block;
        background: var(--color-purple-light);
        color: var(--color-purple);
        border-radius: 4px;
        padding: 1px 6px;
        font-size: 11.5px;
        font-weight: 700;
        letter-spacing: 0.02em;
    }

    .empty-state {
        text-align: center;
        padding: 56px 24px;
        color: var(--color-text-4);
    }

    .empty-state svg { margin: 0 auto 12px; display: block; }
    .empty-state p { font-size: 13.5px; }
</style>
@endsection

@section('content')

<!-- Filter Card -->
<div class="filter-card">
    <form action="{{ route('webreport.transactions') }}" method="GET" class="filter-grid">
        <div class="filter-group">
            <label class="form-label" for="start_date">Dari Tanggal</label>
            <input type="date" name="start_date" id="start_date" class="form-control"
                   value="{{ $start_date }}">
        </div>

        <div class="filter-group">
            <label class="form-label" for="end_date">Sampai Tanggal</label>
            <input type="date" name="end_date" id="end_date" class="form-control"
                   value="{{ $end_date }}">
        </div>

        <div class="filter-group">
            <label class="form-label" for="tujuan">No. Tujuan</label>
            <input type="text" name="tujuan" id="tujuan" class="form-control"
                   value="{{ $tujuan }}" placeholder="Cari no tujuan...">
        </div>

        <div class="filter-group">
            <label class="form-label" for="produk">Kode Produk</label>
            <input type="text" name="produk" id="produk" class="form-control"
                   value="{{ $produk }}" placeholder="Cari produk...">
        </div>

        <div class="filter-group">
            <label class="form-label" for="ref_id">Ref ID</label>
            <input type="text" name="ref_id" id="ref_id" class="form-control"
                   value="{{ $ref_id }}" placeholder="Cari Ref ID...">
        </div>

        <div class="filter-group">
            <label class="form-label" for="status">Status</label>
            <select name="status" id="status" class="form-control">
                <option value="">Semua Status</option>
                <option value="Sukses"  {{ $status === 'Sukses'  ? 'selected' : '' }}>Sukses</option>
                <option value="Proses"  {{ $status === 'Proses'  ? 'selected' : '' }}>Proses</option>
                <option value="Gagal"   {{ $status === 'Gagal'   ? 'selected' : '' }}>Gagal</option>
            </select>
        </div>

        <div class="filter-group">
            <label class="form-label" for="limit">Tampilkan</label>
            <select name="limit" id="limit" class="form-control">
                <option value="10"  {{ $limit == 10  ? 'selected' : '' }}>10 baris</option>
                <option value="20"  {{ $limit == 20  ? 'selected' : '' }}>20 baris</option>
                <option value="50"  {{ $limit == 50  ? 'selected' : '' }}>50 baris</option>
                <option value="99999" {{ $limit == 99999 ? 'selected' : '' }}>Semua</option>
            </select>
        </div>

        <div class="filter-group">
            <label class="form-label">&nbsp;</label>
            <button type="submit" class="btn btn-primary">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                Filter
            </button>
        </div>
    </form>
</div>

<!-- Table Card -->
<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
        <div class="card-title">
            <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            Riwayat Transaksi
            <span style="font-size:12px; color:var(--color-text-4); font-weight: 500; margin-left: 8px;">
                ({{ count($transactions) }} transaksi)
            </span>
        </div>
        @if(count($transactions) > 0)
            <a href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}" class="btn btn-secondary btn-sm" style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; background: #107c41; color: white; border-color: #107c41; font-weight: 600; text-decoration: none; border-radius: 6px; transition: background 150ms;">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Ekspor Excel
            </a>
        @endif
    </div>

    <div class="table-wrapper" style="border:none; border-radius:0 0 var(--radius) var(--radius);">
        @if(count($transactions) > 0)
            <table class="ak-table">
                <thead>
                    <tr>
                        <th>Trx ID</th>
                        <th>Tgl Status</th>
                        <th>Tgl Entri</th>
                        <th>Produk</th>
                        <th>No. Tujuan</th>
                        <th>Status</th>
                        <th>SN</th>
                        <th>Nama Reseller</th>
                        <th>Kode Reseller</th>
                        <th>Harga</th>
                        <th>Saldo Awal</th>
                        <th>Saldo Akhir</th>
                        <th>Ref ID</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transactions as $trx)
                        <tr>
                            <td style="font-weight:700; color:var(--color-purple); font-size:12.5px;">
                                #{{ $trx['trxid'] }}
                            </td>
                            <td style="color:var(--color-text-3); font-size:12px; white-space:nowrap;">
                                {{ $trx['tgl_status'] ?? '-' }}
                            </td>
                            <td style="color:var(--color-text-3); font-size:12px; white-space:nowrap;">
                                {{ $trx['tgl_entri'] }}
                            </td>
                            <td><span class="code-chip">{{ $trx['kode_produk'] }}</span></td>
                            <td style="font-weight:500;">{{ $trx['tujuan'] }}</td>
                            <td>
                                @if(strtolower($trx['status']) === 'sukses')
                                    <span class="badge badge-success">Sukses</span>
                                @elseif(strtolower($trx['status']) === 'proses' || strtolower($trx['status']) === 'pending')
                                    <span class="badge badge-warning">Proses</span>
                                @else
                                    <span class="badge badge-danger">Gagal</span>
                                @endif
                            </td>
                            <td style="font-weight:600; color:var(--color-text-2);">{{ $trx['sn'] ?? '-' }}</td>
                            <td style="color:var(--color-text-2);">{{ $reseller_name ?? '-' }}</td>
                            <td style="color:var(--color-text-2);">{{ $trx['kode_reseller'] ?? $active_reseller_code }}</td>
                            <td style="font-weight:700;">Rp {{ number_format($trx['harga'], 0, ',', '.') }}</td>
                            <td style="color:var(--color-text-2);">Rp {{ number_format($trx['saldo_awal'] ?? 0, 0, ',', '.') }}</td>
                            <td style="color:var(--color-text-2);">Rp {{ number_format($trx['saldo_akhir'] ?? 0, 0, ',', '.') }}</td>
                            <td style="color:var(--color-text-2); font-size:12.5px;">
                                {{ $trx['ref_id'] ?: '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty-state">
                <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <p>Tidak ada transaksi untuk filter yang dipilih.</p>
            </div>
        @endif
    </div>
</div>

<!-- Detail Modal -->
<div class="modal-backdrop" id="detailModal" onclick="closeModal()">
    <div class="modal-box" onclick="event.stopPropagation()">
        <div class="modal-head">
            <h3 id="modalTitle">Detail Transaksi</h3>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body" id="modalBody">
            <div style="text-align:center; padding:32px; color:var(--color-text-4);">Memuat data...</div>
        </div>
    </div>
</div>

@endsection

@section('extra-scripts')
<script>
    function viewDetail(trxid) {
        const modal = document.getElementById('detailModal');
        const body  = document.getElementById('modalBody');
        const title = document.getElementById('modalTitle');

        body.innerHTML = `<div style="text-align:center;padding:32px;color:var(--color-text-4);">Memuat data...</div>`;
        modal.style.display = 'flex';

        fetch(`{{ url('/transactions') }}/${trxid}`)
            .then(r => r.json())
            .then(data => {
                if (data.status === 'error') {
                    body.innerHTML = `<div style="color:var(--color-danger);text-align:center;padding:32px;">${data.message}</div>`;
                    return;
                }

                title.innerText = `Detail — Ref ID: ${data.ref_id || '#' + data.trxid}`;

                let statusBadge = '';
                const st = (data.status || '').toLowerCase();
                if (st === 'sukses')          statusBadge = '<span class="badge badge-success">Sukses</span>';
                else if (st === 'proses' || st === 'pending') statusBadge = '<span class="badge badge-warning">Proses</span>';
                else statusBadge = `<span class="badge badge-danger">${data.status || 'Gagal'}</span>`;

                body.innerHTML = `
                    <div class="detail-row">
                        <span class="detail-label">Ref ID</span>
                        <span class="detail-value" style="color:var(--color-purple);">${data.ref_id || '-'}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Waktu</span>
                        <span class="detail-value">${data.tgl_entri}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Produk</span>
                        <span class="detail-value"><span class="code-chip">${data.kode_produk}</span></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">No. Tujuan</span>
                        <span class="detail-value">${data.tujuan}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">SN (Serial Number)</span>
                        <span class="detail-value" style="font-weight: 700;">${data.sn || '-'}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Harga</span>
                        <span class="detail-value" style="font-size:1rem;">Rp ${parseInt(data.harga).toLocaleString('id-ID')}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Status</span>
                        <span class="detail-value">${statusBadge}</span>
                    </div>
                    <div class="detail-keterangan">
                        <span class="detail-label">Keterangan</span>
                        <div class="keterangan-box">${data.keterangan || '-'}</div>
                    </div>
                `;
            })
            .catch(() => {
                body.innerHTML = `<div style="color:var(--color-danger);text-align:center;padding:32px;">Gagal memuat data.</div>`;
            });
    }

    function closeModal() {
        document.getElementById('detailModal').style.display = 'none';
    }

    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });
</script>
@endsection
