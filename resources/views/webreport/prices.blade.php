@extends('webreport.layout')

@section('title', !empty($is_public) ? 'Daftar Harga Produk - ' . \Illuminate\Support\Facades\Cache::get('site_title', 'OtomaX') : 'Daftar Harga')
@section('page-header', !empty($is_public) ? 'Daftar Harga Resmi Produk' : 'Daftar Harga Produk')

@section('content')

<!-- Toolbar: Filter & Search -->
<div class="card mb-4">
    <div class="card-body p-3">
        <div class="row g-3 align-items-center">
            
            <!-- Sort Filters -->
            <div class="col-12 col-md-6">
                <div class="btn-group w-100" role="group">
                    <button type="button" class="btn btn-sm {{ $filter === 'semua' ? 'btn-primary' : 'btn-outline-primary' }}" onclick="applyFilter('semua')">
                        Semua
                    </button>
                    <button type="button" class="btn btn-sm {{ $filter === 'termurah' ? 'btn-primary' : 'btn-outline-primary' }}" onclick="applyFilter('termurah')">
                        Termurah
                    </button>
                    <button type="button" class="btn btn-sm {{ $filter === 'terlaris' ? 'btn-primary' : 'btn-outline-primary' }}" onclick="applyFilter('terlaris')">
                        Terlaris
                    </button>
                </div>
            </div>

            <!-- Quick Search -->
            <div class="col-12 col-md-6">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light text-muted"><i class="bx bx-search"></i></span>
                    <input type="text" id="productSearch" class="form-control" placeholder="Cari kode atau nama produk..." onkeyup="searchProducts()">
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Products Table (grouped by operator) -->
<div id="pricesContent">
    @php 
        $processedPrices = collect($prices)->map(function($item) {
            if (isset($item['catatan']) && stripos($item['catatan'], 'PROMO') !== false) {
                $item['operator'] = '🔥 PROMO SPESIAL';
            }
            return $item;
        });
        
        $groupedPrices = $processedPrices->groupBy('operator')->sortByDesc(function ($items, $key) {
            return stripos($key, 'PROMO') !== false ? 1 : 0;
        });
    @endphp

    @if($groupedPrices->count() > 0)
        @foreach($groupedPrices as $opName => $products)
            <div class="op-section mb-4" id="section-{{ Str::slug($opName) }}">
                <div class="card">
                    <div class="card-header py-3 bg-light d-flex align-items-center justify-content-between">
                        <div class="card-title mb-0 fs-6">
                            <i class="bx bx-layer text-primary"></i>
                            <span>{{ strtoupper($opName) }}</span>
                        </div>
                        <span class="badge bg-label-primary">{{ $products->count() }} Produk</span>
                    </div>

                    <div class="table-responsive text-nowrap">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 150px;">Kode Produk</th>
                                    <th>Nama Produk</th>
                                    <th class="text-end" style="width: 180px;">Harga Reseller</th>
                                    <th class="text-center" style="width: 120px;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($products as $item)
                                    <tr data-code="{{ strtolower($item['kode']) }}"
                                        data-name="{{ strtolower($item['nama']) }}">
                                        <td>
                                            <span class="badge bg-label-primary font-monospace">{{ $item['kode'] }}</span>
                                        </td>
                                        <td>
                                            <span class="fw-semibold text-dark">{{ $item['nama'] }}</span>
                                            @if(isset($item['catatan']) && stripos($item['catatan'], 'PROMO') !== false)
                                                <span class="badge bg-label-success ms-1">PROMO</span>
                                            @endif
                                        </td>
                                        <td class="text-end fw-bold text-primary">
                                            Rp {{ number_format($item['harga'], 0, ',', '.') }}
                                        </td>
                                        <td class="text-center">
                                            @if(($item['gangguan'] ?? 0) == 1)
                                                <span class="badge bg-label-danger">Gangguan</span>
                                            @elseif(($item['status'] ?? 1) == 0 || strtolower($item['status'] ?? '') === 'nonaktif')
                                                <span class="badge bg-label-danger">Nonaktif</span>
                                            @else
                                                <span class="badge bg-label-success">Aktif</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endforeach
    @else
        <div class="card text-center p-5">
            <i class="bx bx-box text-muted fs-1 d-block mb-2"></i>
            <h5 class="text-muted mb-1">Tidak Ada Produk</h5>
            <p class="text-muted small">Tidak ada produk yang tersedia untuk kategori atau filter ini.</p>
        </div>
    @endif
</div>

@endsection

@section('extra-scripts')
<script>
    function applyFilter(name) {
        const url = new URL(window.location.href);
        if (name && name !== 'semua') url.searchParams.set('filter', name);
        else url.searchParams.delete('filter');
        window.location.href = url.toString();
    }

    function applyLevel(level) {
        const url = new URL(window.location.href);
        if (level && level.trim() !== '') {
            url.searchParams.set('level', level.trim());
        } else {
            url.searchParams.delete('level');
        }
        window.location.href = url.toString();
    }

    function searchProducts() {
        const q = document.getElementById('productSearch').value.toLowerCase().trim();

        document.querySelectorAll('.op-section').forEach(sec => {
            let visible = 0;
            sec.querySelectorAll('table tbody tr').forEach(row => {
                const match = (row.dataset.code + row.dataset.name).includes(q);
                row.style.display = match ? '' : 'none';
                if (match) visible++;
            });
            sec.style.display = visible === 0 ? 'none' : '';
        });
    }

    function copyCurrentUrl() {
        const textBtn = document.getElementById('copyUrlText');
        navigator.clipboard.writeText(window.location.href).then(() => {
            textBtn.innerText = 'Tautan Berhasil Disalin!';
            setTimeout(() => {
                textBtn.innerText = 'Bagikan Halaman';
            }, 2000);
        }).catch(() => {
            textBtn.innerText = 'Tautan Berhasil Disalin!';
            setTimeout(() => {
                textBtn.innerText = 'Bagikan Halaman';
            }, 2000);
        });
    }
</script>
@endsection
