@extends('admin.layout')

@section('title', 'Branding Portal')
@section('page-header', 'Branding Portal')

@section('content')
<div style="max-width: 600px; margin: 0 auto;">
    <div class="card" style="border-top: 4px solid var(--color-purple);">
        <div class="card-header" style="padding-bottom: 12px;">
            <div class="card-title">
                <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Pengaturan Tampilan & Branding Portal
            </div>
        </div>
        <div class="card-body">
            <p style="font-size: 13px; color: var(--color-text-2); margin-bottom: 20px; line-height: 1.5;">
                Perbarui nama, deskripsi, dan logo yang akan ditampilkan pada halaman login, register, dan navigasi portal reseller Anda.
            </p>

            <form action="{{ route('admin.branding.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div style="margin-bottom: 16px;">
                    <label class="form-label" for="site_title" style="font-weight: 600; display: block; margin-bottom: 6px;">Nama Portal / Perusahaan</label>
                    <input type="text" name="site_title" id="site_title" class="form-control" value="{{ \Illuminate\Support\Facades\Cache::get('site_title', 'OtomaX Portal') }}" required style="height: auto; padding: 8px 12px;">
                </div>
                
                <div style="margin-bottom: 16px;">
                    <label class="form-label" for="site_subtitle" style="font-weight: 600; display: block; margin-bottom: 6px;">Deskripsi / Slogan Portal</label>
                    <input type="text" name="site_subtitle" id="site_subtitle" class="form-control" value="{{ \Illuminate\Support\Facades\Cache::get('site_subtitle', 'Webreport Reseller Network') }}" required style="height: auto; padding: 8px 12px;">
                </div>
                
                <div style="margin-bottom: 20px;">
                    <label class="form-label" for="site_logo" style="font-weight: 600; display: block; margin-bottom: 6px;">Unggah File Logo Baru</label>
                    <input type="file" name="site_logo" id="site_logo" class="form-control" accept=".png,.jpg,.jpeg,.svg" style="padding-top: 8px; padding-bottom: 8px; height: auto;">
                    <span style="font-size: 11.5px; color: var(--color-text-4); margin-top: 6px; display: block;">Menerima format file gambar: .png, .jpg, .jpeg, .svg (Maksimal 2MB)</span>
                    
                    @if(\Illuminate\Support\Facades\Cache::get('site_logo_url'))
                        <div style="margin-top: 14px; padding: 10px; background: var(--color-bg); border: 1px solid var(--color-border); border-radius: 6px; display: flex; align-items: center; gap: 12px;">
                            <span style="font-size: 12px; font-weight: 600; color: var(--color-text-2);">Logo Aktif Saat Ini:</span>
                            <div style="background: white; padding: 4px 8px; border-radius: 4px; border: 1px solid var(--color-border); display: inline-flex;">
                                <img src="{{ \Illuminate\Support\Facades\Cache::get('site_logo_url') }}" alt="Logo" style="height: 28px; max-width: 140px; object-fit: contain;">
                            </div>
                        </div>
                    @endif
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 10px 16px; font-weight: 600;">
                    Simpan Perubahan Branding
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
