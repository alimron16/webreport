@extends('webreport.layout')

@section('title', 'History Transaksi')
@section('page-header', 'History Transaksi')

@section('content')

<!-- Filter Card -->
<div class="card mb-4">
    <div class="card-header py-3 d-flex align-items-center justify-content-between">
        <div class="card-title mb-0" style="font-size: 0.95rem;">
            <i class="bx bx-filter-alt text-primary fs-5"></i>
            <span>Filter & Pencarian Transaksi</span>
        </div>
        <button class="btn btn-sm btn-outline-secondary d-md-none" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse">
            <i class="bx bx-chevron-down"></i>
        </button>
    </div>
    <div class="card-body collapse d-md-block show" id="filterCollapse">
        <form action="{{ route('webreport.transactions') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-12 col-sm-6 col-md-3 col-xl-2">
                <label class="form-label" for="start_date">Dari Tanggal</label>
                <input type="date" name="start_date" id="start_date" class="form-control" value="{{ $start_date }}">
            </div>

            <div class="col-12 col-sm-6 col-md-3 col-xl-2">
                <label class="form-label" for="end_date">Sampai Tanggal</label>
                <input type="date" name="end_date" id="end_date" class="form-control" value="{{ $end_date }}">
            </div>

            <div class="col-12 col-sm-6 col-md-3 col-xl-2">
                <label class="form-label" for="tujuan">No. Tujuan</label>
                <input type="text" name="tujuan" id="tujuan" class="form-control" value="{{ $tujuan }}" placeholder="08123456xxx">
            </div>

            <div class="col-12 col-sm-6 col-md-3 col-xl-2">
                <label class="form-label" for="produk">Kode Produk</label>
                <input type="text" name="produk" id="produk" class="form-control" value="{{ $produk }}" placeholder="T10, S5...">
            </div>

            <div class="col-12 col-sm-6 col-md-3 col-xl-2">
                <label class="form-label" for="ref_id">Ref ID</label>
                <input type="text" name="ref_id" id="ref_id" class="form-control" value="{{ $ref_id }}" placeholder="ID Pembeli...">
            </div>

            <div class="col-6 col-sm-3 col-md-3 col-xl-1">
                <label class="form-label" for="status">Status</label>
                <select name="status" id="status" class="form-select">
                    <option value="">Semua</option>
                    <option value="Sukses" {{ $status === 'Sukses' ? 'selected' : '' }}>Sukses</option>
                    <option value="Proses" {{ $status === 'Proses' ? 'selected' : '' }}>Proses</option>
                    <option value="Gagal"  {{ $status === 'Gagal'  ? 'selected' : '' }}>Gagal</option>
                </select>
            </div>

            <div class="col-6 col-sm-3 col-md-3 col-xl-1">
                <label class="form-label" for="limit">Limit</label>
                <select name="limit" id="limit" class="form-select">
                    <option value="25" {{ (string)$limit === '25' ? 'selected' : '' }}>25</option>
                    <option value="50" {{ (string)$limit === '50' || empty($limit) ? 'selected' : '' }}>50</option>
                    <option value="100" {{ (string)$limit === '100' ? 'selected' : '' }}>100</option>
                    <option value="250" {{ (string)$limit === '250' ? 'selected' : '' }}>250</option>
                    <option value="500" {{ (string)$limit === '500' ? 'selected' : '' }}>500</option>
                    <option value="semua" {{ (string)$limit === 'semua' ? 'selected' : '' }}>Semua</option>
                </select>
            </div>

            <div class="col-12 col-sm-6 col-md-3 col-xl-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">
                    <i class="bx bx-search"></i> Cari
                </button>
                <a href="{{ route('webreport.transactions') }}" class="btn btn-outline-secondary" title="Reset Filter">
                    <i class="bx bx-reset"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Table Card -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="card-title mb-0">
            <i class="bx bx-list-check text-primary fs-4"></i>
            <span>Daftar Transaksi</span>
            <span class="badge bg-label-primary ms-2">{{ count($transactions) }} data</span>
            @if(!empty($limit) && $limit !== 'semua')
                <span class="badge bg-label-secondary ms-1">Limit: {{ $limit }}</span>
            @endif
        </div>
        
        @if(count($transactions) > 0)
        <a href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}" class="btn btn-outline-success btn-sm">
            <i class="bx bx-spreadsheet"></i> Ekspor Excel
        </a>
        @endif
    </div>

    <div class="table-responsive text-nowrap">
        @if(count($transactions) > 0)
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Trx ID</th>
                        <th>Tgl Entri</th>
                        <th>Tgl Status</th>
                        <th>Produk</th>
                        <th>No. Tujuan</th>
                        <th>Status</th>
                        <th>SN</th>
                        <th class="text-end">Harga</th>
                        <th class="text-end">Saldo Awal</th>
                        <th class="text-end">Saldo Akhir</th>
                        <th>Ref ID</th>
                        <th>Keterangan</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transactions as $trx)
                        <tr>
                            <td>
                                <span class="fw-bold text-primary font-monospace">#{{ $trx['trxid'] }}</span>
                            </td>
                            <td class="text-muted small">
                                {{ !empty($trx['tgl_entri']) ? \Carbon\Carbon::parse($trx['tgl_entri'])->format('d/m/Y H:i:s') : '-' }}
                            </td>
                            <td class="text-muted small">
                                {{ !empty($trx['tgl_status']) ? \Carbon\Carbon::parse($trx['tgl_status'])->format('d/m/Y H:i:s') : '-' }}
                            </td>
                            <td>
                                <span class="badge bg-label-primary font-monospace">{{ $trx['kode_produk'] ?? '-' }}</span>
                            </td>
                            <td class="fw-semibold text-dark font-monospace">
                                {{ $trx['tujuan'] ?? '-' }}
                            </td>
                            <td>
                                @if(strtolower($trx['status'] ?? '') === 'sukses')
                                    <span class="badge bg-label-success">
                                        <i class="bx bx-check me-1"></i> Sukses
                                    </span>
                                @elseif(strtolower($trx['status'] ?? '') === 'proses' || strtolower($trx['status'] ?? '') === 'pending')
                                    <span class="badge bg-label-warning">
                                        <i class="bx bx-time me-1"></i> Proses
                                    </span>
                                @else
                                    <span class="badge bg-label-danger">
                                        <i class="bx bx-x me-1"></i> Gagal
                                    </span>
                                @endif
                            </td>
                            <td class="small">
                                <span class="font-monospace text-dark user-select-all">{{ $trx['sn'] ?? '-' }}</span>
                            </td>
                            <td class="text-end fw-bold text-dark">
                                Rp {{ number_format($trx['harga'] ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="text-end text-muted small">
                                Rp {{ number_format($trx['saldo_awal'] ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="text-end text-muted small">
                                Rp {{ number_format($trx['saldo_akhir'] ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="small">
                                <span class="font-monospace text-muted">{{ $trx['ref_id'] ?? '-' }}</span>
                            </td>
                            <td class="small text-truncate" style="max-width: 220px;" title="{{ $trx['keterangan'] ?? '' }}">
                                {{ $trx['keterangan'] ?? '-' }}
                            </td>
                            <td class="text-center text-nowrap">
                                @if(app(\App\Services\ReceiptService::class)->isPrintable($trx))
                                <a href="{{ route('webreport.struk', $trx['trxid'] ?? ($trx['kode'] ?? 0)) }}" target="_blank" class="btn btn-sm btn-outline-primary py-1 px-2" title="Cetak Struk Resmi">
                                    <i class="bx bx-printer me-1"></i>Struk
                                </a>
                                @else
                                <span class="text-muted small">-</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="text-center py-5">
                <i class="bx bx-search-alt text-muted fs-1 d-block mb-2"></i>
                <h5 class="text-muted mb-1">Tidak Ada Transaksi</h5>
                <p class="text-muted small">Tidak ditemukan data transaksi untuk filter tanggal atau pencarian yang dipilih.</p>
            </div>
        @endif
    </div>
</div>


@endsection
