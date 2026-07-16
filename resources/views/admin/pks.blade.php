@extends('admin.layout')

@section('title', 'Template PKS')
@section('page-header', 'Template PKS (Perjanjian Kerja Sama)')

@section('content')
<div style="max-width: 600px; margin: 0 auto;">
    <div class="card" style="border-top: 4px solid var(--color-purple);">
        <div class="card-header" style="padding-bottom: 12px;">
            <div class="card-title">
                <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Unggah Dokumen Perjanjian Kerja Sama (PKS)
            </div>
        </div>
        <div class="card-body">
            <p style="font-size: 13px; color: var(--color-text-2); margin-bottom: 20px; line-height: 1.5;">
                Gunakan halaman ini untuk memperbarui dokumen PKS (Perjanjian Kerja Sama) yang wajib disetujui dan diunduh oleh calon reseller baru saat melakukan proses pendaftaran.
            </p>

            @php
                $activePksName = \Illuminate\Support\Facades\Cache::get('pks_template_name');
            @endphp

            @if($activePksName)
                <div style="background: var(--color-purple-light); border: 1px solid var(--color-border); border-radius: 8px; padding: 14px; margin-bottom: 20px; font-size: 13px; word-break: break-all; display: flex; align-items: center; gap: 10px;">
                    <svg width="22" height="22" fill="none" stroke="var(--color-purple)" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <div>
                        <div style="font-weight: 700; color: var(--color-purple); margin-bottom: 2px;">File PKS Aktif Saat Ini:</div>
                        <div style="color: var(--color-text-2); font-weight: 500;">{{ $activePksName }}</div>
                    </div>
                </div>
            @else
                <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 14px; margin-bottom: 20px; font-size: 13px; color: #b45309; display: flex; align-items: center; gap: 10px;">
                    <svg width="22" height="22" fill="none" stroke="#d97706" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    <div>
                        <div style="font-weight: 700; margin-bottom: 2px;">File PKS Belum Tersedia:</div>
                        <div>Calon reseller akan mengunduh dokumen registrasi default bawaan sistem HTML.</div>
                    </div>
                </div>
            @endif

            <form action="{{ route('admin.pks.upload') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div style="margin-bottom: 20px;">
                    <label class="form-label" for="pks_file" style="font-weight: 600; display: block; margin-bottom: 8px;">Pilih File Baru</label>
                    <input type="file" name="pks_file" id="pks_file" class="form-control" accept=".pdf,.doc,.docx,.zip" required style="padding-top: 8px; padding-bottom: 8px; height: auto;">
                    <span style="font-size: 11.5px; color: var(--color-text-4); margin-top: 6px; display: block;">Menerima format file: .pdf, .doc, .docx, .zip (Maksimal 5MB)</span>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 10px 16px; font-weight: 600;">
                    Simpan & Terapkan File PKS Baru
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
