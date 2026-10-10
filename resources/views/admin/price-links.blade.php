@extends('admin.layout')

@section('title', 'Tautan Daftar Harga Publik - ' . \Illuminate\Support\Facades\Cache::get('site_title', 'OtomaX'))
@section('page-header', 'Tautan Daftar Harga Publik')

@section('content')

<div class="row g-4">

    <!-- 1. Form Generate Tautan Baru (Atas - Full Width) -->
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div class="card-title mb-0 d-flex align-items-center gap-2">
                    <i class="bx bx-plus-circle text-primary fs-4"></i>
                    <span class="fw-bold text-dark">Buat Tautan Baru</span>
                </div>
                <span class="badge bg-label-primary">Publik</span>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    Tautan yang dibuat dapat dibagikan ke calon agen atau media sosial. Pengunjung dapat melihat harga realtime sesuai level tanpa login.
                </p>

                <form action="{{ route('admin.price-links.create') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold text-dark">Label / Nama Tautan <span class="text-danger">*</span></label>
                            <input type="text" name="label" class="form-control" placeholder="Contoh: Promo Agen Telegram VIP" required value="{{ old('label') }}">
                            <div class="form-text text-muted">Judul atau penanda tujuan tautan ini dibuat.</div>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold text-dark">Target Level / Grup Reseller</label>
                            <select name="level" class="form-select">
                                <option value="">Harga Umum / Dasar (Default)</option>
                                @if(!empty($levels))
                                    @foreach($levels as $lvl)
                                        @php 
                                            $lvlKode = is_array($lvl) ? ($lvl['kode'] ?? '') : ($lvl->kode ?? '');
                                            $lvlNama = is_array($lvl) ? ($lvl['nama'] ?? $lvlKode) : ($lvl->nama ?? $lvlKode);
                                        @endphp
                                        @if(!empty($lvlKode))
                                            <option value="{{ $lvlKode }}" {{ old('level') === $lvlKode ? 'selected' : '' }}>
                                                {{ $lvlNama }} (Kode: {{ strtoupper($lvlKode) }})
                                            </option>
                                        @endif
                                    @endforeach
                                @endif
                            </select>
                            <div class="form-text text-muted">Pilih margin level grup OtomaX untuk link ini.</div>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold text-dark">Masa Berlaku (Kadaluarsa) <span class="text-danger">*</span></label>
                            <select name="expiry_type" id="expiryTypeSelect" class="form-select" onchange="toggleCustomDate(this.value)">
                                <option value="forever" {{ old('expiry_type') === 'forever' ? 'selected' : '' }}>Selamanya (Tidak Pernah Expired)</option>
                                <option value="1day" {{ old('expiry_type') === '1day' ? 'selected' : '' }}>1 Hari (24 Jam)</option>
                                <option value="7days" {{ old('expiry_type') === '7days' ? 'selected' : '' }}>7 Hari (1 Minggu)</option>
                                <option value="14days" {{ old('expiry_type') === '14days' ? 'selected' : '' }}>14 Hari (2 Minggu)</option>
                                <option value="30days" {{ old('expiry_type') === '30days' ? 'selected' : '' }}>30 Hari (1 Bulan)</option>
                                <option value="90days" {{ old('expiry_type') === '90days' ? 'selected' : '' }}>90 Hari (3 Bulan)</option>
                                <option value="custom" {{ old('expiry_type') === 'custom' ? 'selected' : '' }}>Kustom Tanggal...</option>
                            </select>
                            <div class="form-text text-muted">Durasi aktif tautan publik.</div>
                        </div>

                        <div class="col-12 col-md-4" id="customDateField" style="{{ old('expiry_type') === 'custom' ? '' : 'display: none;' }}">
                            <label class="form-label fw-semibold text-dark">Pilih Tanggal Berakhir</label>
                            <input type="date" name="custom_date" class="form-control" value="{{ old('custom_date') }}">
                        </div>

                        <div class="col-12 d-flex justify-content-end pt-2">
                            <button type="submit" class="btn btn-primary px-4 py-2">
                                <i class="bx bx-link-alt me-1"></i> Generate & Simpan Tautan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 2. Tabel Daftar Tautan Tersimpan (Bawah - Full Width) -->
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="card-title mb-0 d-flex align-items-center gap-2">
                    <i class="bx bx-list-ul text-primary fs-4"></i>
                    <span class="fw-bold text-dark">Tautan Tersimpan</span>
                    <span class="badge bg-label-primary ms-2">{{ count($links) }} link</span>
                </div>
                <a href="{{ route('webreport.public-prices') }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                    <i class="bx bx-globe me-1"></i> Tautan Dasar ↗
                </a>
            </div>

            <div class="table-responsive text-nowrap">
                @if(!empty($links) && count($links) > 0)
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th style="width: 25%;">Label & Target Level</th>
                                <th style="width: 40%;">Tautan Publik</th>
                                <th style="width: 15%;">Masa Berlaku</th>
                                <th class="text-center" style="width: 10%;">Hits</th>
                                <th class="text-end" style="width: 10%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($links as $link)
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark mb-1">
                                            {{ $link['label'] ?? 'Tautan Harga' }}
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            @if(!empty($link['level']))
                                                <span class="badge bg-label-primary">
                                                    Level {{ strtoupper($link['level']) }}
                                                </span>
                                            @else
                                                <span class="badge bg-label-secondary">
                                                    Harga Umum
                                                </span>
                                            @endif
                                            <small class="text-muted">
                                                {{ \Carbon\Carbon::parse($link['created_at'])->format('d/m/Y') }}
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm" style="max-width: 460px;">
                                            <input type="text" id="url-{{ $link['id'] }}" class="form-control font-monospace small bg-light" value="{{ $link['url'] }}" readonly>
                                            <button type="button" class="btn btn-outline-primary" onclick="copyLink('{{ $link['id'] }}', this)" title="Salin Tautan">
                                                <i class="bx bx-copy"></i>
                                            </button>
                                            <a href="{{ $link['url'] }}" target="_blank" class="btn btn-outline-secondary" title="Buka">
                                                <i class="bx bx-link-external"></i>
                                            </a>
                                        </div>
                                    </td>
                                    <td>
                                        @if(empty($link['expires_at']))
                                            <span class="badge bg-label-success">
                                                <i class="bx bx-check-circle me-1"></i> Selamanya
                                            </span>
                                        @elseif(!empty($link['is_expired']))
                                            <span class="badge bg-label-danger">
                                                <i class="bx bx-x-circle me-1"></i> Kadaluarsa
                                            </span>
                                            <div class="small text-danger mt-1">
                                                {{ \Carbon\Carbon::parse($link['expires_at'])->format('d M Y') }}
                                            </div>
                                        @else
                                            <span class="badge bg-label-success">
                                                <i class="bx bx-time me-1"></i> Aktif
                                            </span>
                                            <div class="small text-muted mt-1">
                                                s/d {{ \Carbon\Carbon::parse($link['expires_at'])->format('d M Y') }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-center fw-bold text-dark">
                                        {{ number_format($link['hits'] ?? 0) }}
                                    </td>
                                    <td class="text-end">
                                        <form action="{{ route('admin.price-links.delete', $link['id']) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus tautan ini?')">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-danger btn-sm px-2 py-1" title="Hapus">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="text-center py-5">
                        <i class="bx bx-link-alt text-muted fs-1 d-block mb-2"></i>
                        <h5 class="text-muted mb-1">Belum Ada Tautan</h5>
                        <p class="text-muted small">Buat tautan harga publik di atas untuk dibagikan tanpa login.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>

@endsection

@section('extra-scripts')
<script>
    function toggleCustomDate(val) {
        const field = document.getElementById('customDateField');
        if (val === 'custom') {
            field.style.display = 'block';
        } else {
            field.style.display = 'none';
        }
    }

    function copyLink(id, btn) {
        const input = document.getElementById('url-' + id);
        input.select();
        input.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(input.value).then(() => {
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="bx bx-check text-success"></i>';
            setTimeout(() => {
                btn.innerHTML = originalHtml;
            }, 1800);
        }).catch(() => {
            document.execCommand('copy');
            btn.innerHTML = '<i class="bx bx-check text-success"></i>';
            setTimeout(() => { btn.innerHTML = '<i class="bx bx-copy"></i>'; }, 1800);
        });
    }
</script>
@endsection
