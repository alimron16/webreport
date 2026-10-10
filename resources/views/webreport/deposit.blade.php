@extends('webreport.layout')

@section('title', 'Isi Saldo & Mutasi')
@section('page-header', 'Isi Saldo & Mutasi')

@section('content')

    {{-- Modal Success from Tiket Request --}}
    @if(session('tiket_success'))
        @php
            $sukses = session('tiket_success');
            $sukses_bank = $sukses['bank'] ?? '';
            $sukses_rek  = $sukses['rekening'] ?? '';
            $sukses_an   = $sukses['atas_nama'] ?? '';

            if (empty($sukses_rek)) {
                foreach($banks as $b) {
                    if (strcasecmp($b['bank'], $sukses_bank) === 0) {
                        $sukses_rek = $b['rekening'];
                        $sukses_an = $b['atas_nama'];
                        break;
                    }
                }
            }
        @endphp
        <div class="modal fade show d-block" id="successModal" tabindex="-1" style="background: rgba(15, 23, 42, 0.5); z-index: 1060;">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header border-0 bg-label-success p-4 pb-3 text-center d-flex flex-column justify-content-center">
                        <div class="avatar avatar-lg mx-auto mb-2 bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                            <i class="bx bx-check fs-2"></i>
                        </div>
                        <h5 class="modal-title fw-bold text-success mb-1">Tiket Berhasil Dibuat!</h5>
                        <p class="text-muted small mb-0">Silakan transfer TEPAT SESUAI NOMINAL berikut agar saldo masuk otomatis.</p>
                    </div>
                    <div class="modal-body p-4 text-center">
                        <div class="bg-light p-3 rounded mb-4 border">
                            <span class="text-muted small text-uppercase fw-semibold d-block mb-1">Nominal Transfer (Termasuk Kode Unik):</span>
                            <h2 class="text-primary fw-bold mb-3">
                                Rp {{ number_format(session('tiket_success')['nominal'], 0, ',', '.') }}
                            </h2>
                            
                            <hr class="my-3">

                            <span class="text-muted small text-uppercase fw-semibold d-block mb-1">Tujuan Transfer:</span>
                            <div class="fw-bold fs-5 text-dark mb-1">
                                Bank {{ $sukses_bank }}
                            </div>
                            <div class="badge bg-label-primary fs-6 px-3 py-2 mb-1 user-select-all font-monospace">
                                {{ $sukses_rek }}
                            </div>
                            @if(!empty($sukses_an))
                            <div class="text-muted small mt-1">
                                a.n <strong>{{ $sukses_an }}</strong>
                            </div>
                            @endif
                        </div>

                        <button type="button" class="btn btn-primary w-100 py-2" onclick="document.getElementById('successModal').remove()">
                            <i class="bx bx-check me-1"></i> Saya Mengerti
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- ═════════════════════════════════════════════════════════════
         1. PALING ATAS: AMBIL TIKET DEPOSIT (FULL WIDTH)
         ═════════════════════════════════════════════════════════════ -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div class="card-title mb-0 d-flex align-items-center gap-2">
                        <i class="bx bx-wallet-alt text-primary fs-5"></i>
                        <span class="fw-bold text-dark">Ambil Tiket Deposit</span>
                    </div>
                    <span class="badge bg-label-primary">Auto Cek</span>
                </div>
                
                <div class="card-body">
                    <form action="{{ route('webreport.deposit.request') }}" method="POST">
                        @csrf
                        <div class="row g-3 align-items-end">
                            <div class="col-12 col-md-5">
                                <label class="form-label fw-semibold text-dark">Nominal Deposit</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted fw-bold">Rp</span>
                                    <input type="number" name="nominal" class="form-control" required min="10000" placeholder="100000">
                                </div>
                                <div class="form-text text-muted">Minimal transfer Rp 10.000 (Otomatis ditambah kode unik).</div>
                            </div>
                            
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold text-dark">Pilih Bank Tujuan</label>
                                @if(empty($banks))
                                    <div class="alert alert-danger py-2 px-3 small mb-0">
                                        <i class="bx bx-error-circle me-1"></i> Belum ada bank aktif. Hubungi CS.
                                    </div>
                                @else
                                    <select name="bank" class="form-select" required>
                                        <option value="">-- Pilih Rekening Bank Tujuan --</option>
                                        @foreach($banks as $index => $b)
                                            <option value="{{ $index }}">
                                                Bank {{ $b['bank'] }} - {{ $b['rekening'] }} (a.n {{ $b['atas_nama'] }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="form-text text-muted">Pilih rekening penerima deposit.</div>
                                @endif
                            </div>

                            <div class="col-12 col-md-3">
                                <button type="submit" class="btn btn-primary w-100 py-2 d-flex align-items-center justify-content-center gap-2" {{ empty($banks) ? 'disabled' : '' }}>
                                    <i class="bx bx-plus-circle fs-5"></i>
                                    <span class="fw-semibold">Minta Tiket Deposit</span>
                                </button>
                                <div class="form-text text-transparent d-none d-md-block">&nbsp;</div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- ═════════════════════════════════════════════════════════════
         2. DI BAWAHNYA: TIKET BELUM DIBAYAR (JIKA ADA PENDING)
         ═════════════════════════════════════════════════════════════ -->
    @if(count($pending_tiket) > 0)
        <div class="row g-4 mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header border-bottom py-3 d-flex align-items-center justify-content-between">
                        <div class="card-title mb-0 d-flex align-items-center gap-2">
                            <i class="bx bx-time-five text-warning fs-5"></i>
                            <span class="fw-bold text-dark">Tiket Belum Dibayar</span>
                        </div>
                        <span class="badge" style="background-color: #fef3c7; color: #92400e; font-weight: 600; border: 1px solid #fde68a;">
                            {{ count($pending_tiket) }} Menunggu Pembayaran
                        </span>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            @foreach($pending_tiket as $p)
                                @php
                                    $p_rek = $p['rekening'] ?? '';
                                    $p_an = $p['atas_nama'] ?? '';
                                    if (empty($p_rek)) {
                                        foreach($banks as $b) {
                                            if (strcasecmp($b['bank'], $p['bank'] ?? '') === 0) {
                                                $p_rek = $b['rekening'];
                                                $p_an = $b['atas_nama'];
                                                break;
                                            }
                                        }
                                    }
                                @endphp
                                <div class="list-group-item p-3 p-md-4">
                                    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                                        <div class="d-flex flex-column gap-2">
                                            <div class="d-flex flex-wrap align-items-center gap-2">
                                                <span class="badge bg-label-primary px-2.5 py-1.5 fw-bold">Bank {{ $p['bank'] }}</span>
                                                <span class="fw-bold text-dark font-monospace fs-6 user-select-all">{{ $p_rek }}</span>
                                                @if(!empty($p_an))
                                                    <span class="text-muted small">a.n <strong class="text-dark">{{ $p_an }}</strong></span>
                                                @endif
                                            </div>
                                            
                                            <div class="d-flex align-items-baseline gap-2">
                                                <h3 class="text-primary fw-bold mb-0">Rp {{ number_format($p['jumlah'], 0, ',', '.') }}</h3>
                                                <span class="badge bg-label-secondary small fw-normal">Wajib tepat sesuai nominal</span>
                                            </div>

                                            <div class="text-muted small d-flex flex-wrap align-items-center gap-3">
                                                <span><i class="bx bx-calendar me-1"></i> Dibuat: {{ \Carbon\Carbon::parse($p['waktu'])->format('d/m/Y H:i') }} WIB</span>
                                                <span><i class="bx bx-info-circle me-1 text-primary"></i> Otomatis masuk setelah transfer</span>
                                            </div>
                                        </div>

                                        <div class="d-flex align-items-center gap-2 align-self-end align-self-md-center">
                                            <form action="{{ route('webreport.deposit.cancel') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan tiket ini?');">
                                                @csrf
                                                <input type="hidden" name="kode" value="{{ $p['kode'] }}">
                                                <button type="submit" class="btn btn-outline-danger btn-sm px-3 py-2">
                                                    <i class="bx bx-x me-1"></i> Batalkan
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- ═════════════════════════════════════════════════════════════
         BAGIAN BAWAH: RIWAYAT MUTASI SALDO (LEBAR PENUH / FULL WIDTH)
         ═════════════════════════════════════════════════════════════ -->
    <div class="row g-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div class="card-title mb-0">
                        <i class="bx bx-transfer-alt text-primary fs-5"></i>
                        <span>Riwayat Mutasi Saldo</span>
                    </div>
                    <span class="badge bg-label-secondary">{{ count($mutasi) }} Catatan</span>
                </div>
                
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th style="width: 18%;">Tanggal & Jam</th>
                                <th>Keterangan</th>
                                <th class="text-end" style="width: 18%;">Jumlah</th>
                                <th class="text-end" style="width: 20%;">Saldo Akhir</th>
                            </tr>
                        </thead>
                        <tbody id="mutasi-tbody">
                            @forelse($mutasi as $m)
                                <tr>
                                    <td class="text-muted small">
                                        {{ \Carbon\Carbon::parse($m['tanggal'])->format('d/m/Y H:i') }} WIB
                                    </td>
                                    <td>
                                        <span class="fw-semibold text-dark">{{ $m['keterangan'] }}</span>
                                    </td>
                                    <td class="text-end">
                                        @if($m['jumlah'] >= 0)
                                            <span class="badge bg-label-success">+ Rp {{ number_format($m['jumlah'], 0, ',', '.') }}</span>
                                        @else
                                            <span class="badge bg-label-danger">- Rp {{ number_format(abs($m['jumlah']), 0, ',', '.') }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end fw-bold text-dark font-monospace">
                                        Rp {{ number_format($m['saldo_akhir'], 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-5">
                                        <i class="bx bx-receipt fs-1 text-muted d-block mb-2"></i>
                                        Belum ada riwayat mutasi saldo.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if(count($mutasi) > 0)
                <div class="card-footer d-flex align-items-center justify-content-between py-3 border-top">
                    <small id="page-info" class="text-muted"></small>
                    <div class="d-flex gap-2">
                        <button id="btn-prev" class="btn btn-outline-secondary btn-sm">
                            <i class="bx bx-chevron-left"></i> Sebelumnya
                        </button>
                        <button id="btn-next" class="btn btn-outline-secondary btn-sm">
                            Selanjutnya <i class="bx bx-chevron-right"></i>
                        </button>
                    </div>
                </div>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const tbody = document.getElementById('mutasi-tbody');
                        const rows = Array.from(tbody.querySelectorAll('tr'));
                        const btnPrev = document.getElementById('btn-prev');
                        const btnNext = document.getElementById('btn-next');
                        const pageInfo = document.getElementById('page-info');
                        
                        let currentPage = 1;
                        const rowsPerPage = 10;
                        const totalPages = Math.ceil(rows.length / rowsPerPage);
                        
                        function renderTable() {
                            const start = (currentPage - 1) * rowsPerPage;
                            const end = start + rowsPerPage;
                            
                            rows.forEach((row, index) => {
                                row.style.display = (index >= start && index < end) ? '' : 'none';
                            });
                            
                            pageInfo.textContent = `Menampilkan ${start + 1} - ${Math.min(end, rows.length)} dari ${rows.length} Mutasi`;
                            
                            btnPrev.disabled = currentPage === 1;
                            btnNext.disabled = currentPage === totalPages;
                        }
                        
                        btnPrev.addEventListener('click', () => {
                            if (currentPage > 1) { currentPage--; renderTable(); }
                        });
                        
                        btnNext.addEventListener('click', () => {
                            if (currentPage < totalPages) { currentPage++; renderTable(); }
                        });
                        
                        if(rows.length > 0) renderTable();
                    });
                </script>
                @endif
            </div>

        </div>
    </div>

@endsection
