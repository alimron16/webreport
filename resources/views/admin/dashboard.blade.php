@extends('admin.layout')

@section('title', 'Daftar Registrasi')
@section('page-header', 'Registrasi Reseller')

@section('extra-styles')
<style>
    .filter-tabs {
        display: flex;
        gap: 8px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }

    .filter-tab {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        border: 1px solid var(--color-border);
        background: var(--color-white);
        color: var(--color-text-3);
        transition: all 150ms;
        text-decoration: none;
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

    .filter-tab span {
        background: rgba(0,0,0,0.1);
        padding: 1px 6px;
        border-radius: 99px;
        font-size: 11px;
    }

    .filter-tab.active span {
        background: rgba(255,255,255,0.2);
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

<!-- Filter Tabs -->
<div class="filter-tabs">
    <a href="{{ route('admin.dashboard', ['status' => 'pending']) }}" class="filter-tab {{ $status === 'pending' ? 'active' : '' }}">
        Menunggu Persetujuan <span>{{ $counts['pending'] }}</span>
    </a>
    <a href="{{ route('admin.dashboard', ['status' => 'approved']) }}" class="filter-tab {{ $status === 'approved' ? 'active' : '' }}">
        Disetujui <span>{{ $counts['approved'] }}</span>
    </a>
    <a href="{{ route('admin.dashboard', ['status' => 'rejected']) }}" class="filter-tab {{ $status === 'rejected' ? 'active' : '' }}">
        Ditolak <span>{{ $counts['rejected'] }}</span>
    </a>
    <a href="{{ route('admin.dashboard', ['status' => 'all']) }}" class="filter-tab {{ $status === 'all' ? 'active' : '' }}">
        Semua Data <span>{{ $counts['all'] }}</span>
    </a>
</div>

<div class="card" style="margin-bottom: 20px;">
    <div class="card-header">
        <div class="card-title">
            <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            Permohonan Registrasi Reseller
        </div>
    </div>

    <div class="table-wrapper" style="border:none; border-radius:0 0 var(--radius) var(--radius);">
        @if(count($registrations) > 0)
            <table class="ak-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Nama Outlet</th>
                        <th>WhatsApp</th>
                        <th>Status</th>
                        <th>Kode</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($registrations as $reg)
                        <tr>
                            <td style="color:var(--color-text-3); font-size:12px;">
                                {{ $reg->created_at->format('d M y') }}
                            </td>
                            <td style="font-weight:600; color:var(--color-text);">
                                {{ $reg->nama }}
                            </td>
                            <td>{{ $reg->no_hp }}</td>
                            <td>
                                <span class="badge badge-{{ $reg->statusColor() }}">
                                    {{ $reg->statusLabel() }}
                                </span>
                            </td>
                            <td style="font-weight:700; color:var(--color-purple);">
                                {{ $reg->kode_reseller ?? '-' }}
                            </td>
                            <td>
                                <a href="{{ route('admin.registrations.detail', $reg->id) }}" class="btn btn-secondary btn-sm">
                                    Tinjau
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty-state">
                <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <p>Tidak ada permohonan registrasi dengan status ini.</p>
            </div>
        @endif
    </div>
</div>

<div style="margin-top: 15px;">
    {{ $registrations->links() }}
</div>

@endsection
