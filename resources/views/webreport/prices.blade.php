@extends('webreport.layout')

@section('title', 'Daftar Harga')
@section('page-header', 'Daftar Harga Produk')

@section('extra-styles')
<style>
    .filter-tabs {
        display: flex;
        gap: 8px;
        margin-bottom: 16px;
        flex-wrap: wrap;
    }

    .filter-tab {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 16px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        border: 1px solid var(--color-border);
        background: var(--color-white);
        color: var(--color-text-3);
        transition: all 150ms;
    }

    .filter-tab:hover {
        background: var(--color-bg);
        color: var(--color-text);
        border-color: var(--color-border-dark);
    }

    .filter-tab.active {
        background: var(--color-purple);
        color: #fff;
        border-color: var(--color-purple);
    }

    .search-wrap {
        margin-bottom: 20px;
        position: relative;
    }

    .search-wrap svg {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--color-text-4);
        pointer-events: none;
    }

    .search-wrap input {
        padding-left: 36px !important;
    }

    .op-section { margin-bottom: 24px; }

    .op-heading {
        display: flex;
        align-items: center;
        gap: 8px;
        font-family: 'Outfit', sans-serif;
        font-size: 13px;
        font-weight: 700;
        color: var(--color-text-3);
        text-transform: uppercase;
        letter-spacing: 0.08em;
        margin-bottom: 10px;
    }

    .op-heading::after {
        content: '';
        flex: 1;
        height: 1px;
        background: var(--color-border);
    }

    .price-tag {
        font-weight: 700;
        color: var(--color-text);
    }

    .empty-state {
        text-align: center;
        padding: 56px 24px;
        color: var(--color-text-4);
    }

    .empty-state svg { margin: 0 auto 12px; display: block; }
</style>
@endsection

@section('content')

<!-- Sort Filter Tabs -->
<div class="filter-tabs">
    <button class="filter-tab {{ $filter === 'semua'    ? 'active' : '' }}" onclick="applyFilter('semua')">
        Semua Produk
    </button>
    <button class="filter-tab {{ $filter === 'termurah' ? 'active' : '' }}" onclick="applyFilter('termurah')">
        Termurah
    </button>
    <button class="filter-tab {{ $filter === 'terlaris' ? 'active' : '' }}" onclick="applyFilter('terlaris')">
        Terlaris
    </button>
</div>

<!-- Quick Search -->
<div class="search-wrap">
    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
    </svg>
    <input type="text" id="productSearch" class="form-control"
           placeholder="Cari kode atau nama produk..." onkeyup="searchProducts()">
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
        
        // Sort PROMO SPESIAL to be on top
        $groupedPrices = $processedPrices->groupBy('operator')->sortByDesc(function ($items, $key) {
            return stripos($key, 'PROMO') !== false ? 1 : 0;
        });
    @endphp

    @if($groupedPrices->count() > 0)
        @foreach($groupedPrices as $opName => $products)
            <div class="op-section" id="section-{{ Str::slug($opName) }}">
                <div class="op-heading">
                    {{ strtoupper($opName) }}
                    <span style="font-size:11px; color:var(--color-text-4); font-weight:600; text-transform:none; letter-spacing:0;">
                        ({{ $products->count() }} produk)
                    </span>
                </div>

                <div class="card" style="padding:0; overflow:hidden;">
                    <div class="table-wrapper" style="border:none; border-radius:inherit;">
                        <table class="ak-table">
                            <thead>
                                <tr>
                                    <th style="width:140px;">Kode</th>
                                    <th>Nama Produk</th>
                                    <th style="width:180px;">Harga Reseller</th>
                                    <th style="width:90px;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($products as $item)
                                    <tr data-code="{{ strtolower($item['kode']) }}"
                                        data-name="{{ strtolower($item['nama']) }}">
                                        <td>
                                            <span class="code-chip" style="display:inline-block;background:var(--color-purple-light);color:var(--color-purple);border-radius:4px;padding:1px 7px;font-size:11.5px;font-weight:700;">
                                                {{ $item['kode'] }}
                                            </span>
                                        </td>
                                        <td style="font-weight:500;">
                                            {{ $item['nama'] }}
                                            @if(isset($item['catatan']) && stripos($item['catatan'], 'PROMO') !== false)
                                                <span class="badge badge-success" style="font-size: 10px; padding: 2px 4px; margin-left: 4px; background: #e8f5e9; color: #2e7d32;">PROMO</span>
                                            @endif
                                        </td>
                                        <td class="price-tag">Rp {{ number_format($item['harga'], 0, ',', '.') }}</td>
                                        <td>
                                            @if(($item['gangguan'] ?? 0) == 1)
                                                <span class="badge badge-danger">Gangguan</span>
                                            @elseif(($item['status'] ?? 1) == 0 || strtolower($item['status'] ?? '') === 'nonaktif')
                                                <span class="badge badge-danger">Nonaktif</span>
                                            @else
                                                <span class="badge badge-success">Aktif</span>
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
        <div class="card empty-state">
            <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/>
            </svg>
            <p>Tidak ada produk tersedia.</p>
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
</script>
@endsection
