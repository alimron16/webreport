@extends('webreport.layout')

@section('title', 'Tautan Kadaluarsa - ' . \Illuminate\Support\Facades\Cache::get('site_title', 'OtomaX'))
@section('page-header', 'Tautan Tidak Aktif')

@section('content')
<div class="row justify-content-center my-4">
    <div class="col-12 col-md-8 col-lg-6">
        <div class="card text-center p-4 p-sm-5">
            <div class="avatar avatar-xl mx-auto mb-3 bg-label-danger rounded-circle d-flex align-items-center justify-content-center" style="width: 72px; height: 72px;">
                <i class="bx bx-time-five fs-1 text-danger"></i>
            </div>

            <h4 class="fw-bold mb-2">Tautan Telah Kadaluarsa</h4>
            <p class="text-muted small mb-4">
                Mohon maaf, tautan daftar harga <strong>{{ $link['label'] ?? 'ini' }}</strong> telah melewati masa berlaku pada 
                <span class="text-danger fw-semibold">{{ \Carbon\Carbon::parse($link['expires_at'])->translatedFormat('d F Y, H:i') }} WIB</span>.
            </p>

            <div class="bg-light p-3 rounded border text-start mb-4">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">Target Level:</span>
                    <span class="fw-semibold text-dark">{{ !empty($link['level']) ? 'Level ' . strtoupper($link['level']) : 'Harga Umum' }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted small">Status:</span>
                    <span class="badge bg-label-danger">Masa Berlaku Berakhir</span>
                </div>
            </div>

            <div class="d-flex flex-wrap gap-2 justify-content-center">
                <a href="{{ route('webreport.public-prices') }}" class="btn btn-primary">
                    <i class="bx bx-purchase-tag me-1"></i> Buka Daftar Harga Umum
                </a>
                <a href="{{ route('webreport.login') }}" class="btn btn-outline-secondary">
                    <i class="bx bx-log-in me-1"></i> Login Reseller
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
