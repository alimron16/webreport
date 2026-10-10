@extends('webreport.layout')

@section('title', 'Format Transaksi & API')
@section('page-header', 'Format Transaksi & API')

@section('content')
<div class="row g-4">
    
    <!-- Endpoint Info -->
    <div class="col-12">
        <div class="card mb-0">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div class="card-title mb-0">
                    <i class="bx bx-broadcast text-primary fs-4"></i>
                    <span>IP Center / Endpoint URL</span>
                </div>
                <span class="badge bg-label-primary">Center</span>
            </div>
            <div class="card-body">
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bx bx-globe"></i></span>
                    <input type="text" class="form-control font-monospace" value="{{ \Illuminate\Support\Facades\Cache::get('api_endpoint', 'http://103.166.91.114:5558/') }}" readonly id="endpointUrl">
                    <button class="btn btn-outline-primary" type="button" onclick="copyText('endpointUrl', this)">
                        <i class="bx bx-copy me-1"></i> Salin
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Parsing dengan Signature -->
    <div class="col-12 col-lg-6">
        <div class="card h-100 mb-0">
            <div class="card-header">
                <div class="card-title mb-0">
                    <i class="bx bx-shield-quarter text-primary fs-4"></i>
                    <span>Parsing dengan Signature (MD5)</span>
                </div>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <label class="form-label text-primary"><i class="bx bx-cart me-1"></i> Format Transaksi</label>
                    <div class="p-3 bg-dark text-light rounded font-monospace small user-select-all" style="word-break: break-all;">
                        trx?product=[product]&qty=[qty]&dest=[tujuan]&refID=[trxid]&memberID=[memberid]&sign=[sign]
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="form-label text-primary"><i class="bx bx-wallet me-1"></i> Cek Saldo</label>
                    <div class="p-3 bg-dark text-light rounded font-monospace small user-select-all" style="word-break: break-all;">
                        balance?memberID=[memberID]&sign=[sign]
                    </div>
                </div>

                <div>
                    <label class="form-label text-primary"><i class="bx bx-plus-circle me-1"></i> Pengambilan Tiket Deposit</label>
                    <div class="p-3 bg-dark text-light rounded font-monospace small user-select-all" style="word-break: break-all;">
                        saldo?memberid=[memberid]&sign=[sign]&cmd=ticket&amount=nominal
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Parsing tanpa Signature -->
    <div class="col-12 col-lg-6">
        <div class="card h-100 mb-0">
            <div class="card-header">
                <div class="card-title mb-0">
                    <i class="bx bx-lock-open text-primary fs-4"></i>
                    <span>Parsing tanpa Signature (PIN / Pass)</span>
                </div>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <label class="form-label text-primary"><i class="bx bx-cart me-1"></i> Format Transaksi</label>
                    <div class="p-3 bg-dark text-light rounded font-monospace small user-select-all" style="word-break: break-all;">
                        trx?product=[product]&qty=[qty]&dest=[tujuan]&refID=[trxid]&memberID=[memberid]&pin=[pin]&password=[password]
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="form-label text-primary"><i class="bx bx-wallet me-1"></i> Cek Saldo</label>
                    <div class="p-3 bg-dark text-light rounded font-monospace small user-select-all" style="word-break: break-all;">
                        balance?memberID=[memberID]&pin=[pin]&password=[password]
                    </div>
                </div>

                <div>
                    <label class="form-label text-primary"><i class="bx bx-plus-circle me-1"></i> Pengambilan Tiket Deposit</label>
                    <div class="p-3 bg-dark text-light rounded font-monospace small user-select-all" style="word-break: break-all;">
                        saldo?memberid=[memberid]&pin=[pin]&password=[password]&cmd=ticket&amount=nominal
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Regex Balasan Jawaban Transaksi -->
    <div class="col-12">
        <div class="card mb-0">
            <div class="card-header">
                <div class="card-title mb-0">
                    <i class="bx bx-code-curly text-primary fs-4"></i>
                    <span>Regex Penangkapan Balasan Jawaban Server (Parser)</span>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <div class="p-3 bg-light rounded border h-100 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="badge bg-label-success">Sukses</span>
                                    <button class="btn btn-outline-secondary py-0 px-2" style="font-size: 11px; height: 24px;" type="button" onclick="copyRegex('regexSuccess', this)">
                                        <i class="bx bx-copy me-1"></i> Salin
                                    </button>
                                </div>
                                <div id="regexSuccess" class="font-monospace small text-dark user-select-all" style="word-break: break-all;">{{ \Illuminate\Support\Facades\Cache::get('api_regex_success', '(?i).*SUKSES.*|.*BERHASIL.*|.*SN[:\s=]+(?<sn>[^\s,]+).*') }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="p-3 bg-light rounded border h-100 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="badge bg-label-danger">Gagal</span>
                                    <button class="btn btn-outline-secondary py-0 px-2" style="font-size: 11px; height: 24px;" type="button" onclick="copyRegex('regexFailed', this)">
                                        <i class="bx bx-copy me-1"></i> Salin
                                    </button>
                                </div>
                                <div id="regexFailed" class="font-monospace small text-dark user-select-all" style="word-break: break-all;">{{ \Illuminate\Support\Facades\Cache::get('api_regex_failed', '(?i).*GAGAL.*|.*SALDO TIDAK CUKUP.*|.*NOMOR SALAH.*') }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="p-3 bg-light rounded border h-100 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="badge bg-label-warning">Menunggu / Proses</span>
                                    <button class="btn btn-outline-secondary py-0 px-2" style="font-size: 11px; height: 24px;" type="button" onclick="copyRegex('regexPending', this)">
                                        <i class="bx bx-copy me-1"></i> Salin
                                    </button>
                                </div>
                                <div id="regexPending" class="font-monospace small text-dark user-select-all" style="word-break: break-all;">{{ \Illuminate\Support\Facades\Cache::get('api_regex_pending', '(?i).*DIPROSES.*|.*MENUNGGU.*|.*PENDING.*') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection

@section('extra-scripts')
<script>
    function copyText(id, btn) {
        const inp = document.getElementById(id);
        inp.select();
        navigator.clipboard.writeText(inp.value).then(() => {
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="bx bx-check me-1"></i> Tersalin!';
            btn.classList.replace('btn-outline-primary', 'btn-primary');
            setTimeout(() => {
                btn.innerHTML = originalHtml;
                btn.classList.replace('btn-primary', 'btn-outline-primary');
            }, 1800);
        });
    }

    function copyRegex(id, btn) {
        const el = document.getElementById(id);
        const text = el.innerText.trim();
        navigator.clipboard.writeText(text).then(() => {
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="bx bx-check me-1"></i> Tersalin!';
            btn.classList.replace('btn-outline-secondary', 'btn-secondary');
            setTimeout(() => {
                btn.innerHTML = originalHtml;
                btn.classList.replace('btn-secondary', 'btn-outline-secondary');
            }, 1800);
        });
    }
</script>
@endsection
