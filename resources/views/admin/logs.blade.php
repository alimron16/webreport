@extends('admin.layout')
@section('page-header', 'System Logs')
@section('content')

<style>
    .log-container {
        background: #1e1e1e;
        color: #d4d4d4;
        font-family: 'Consolas', 'Courier New', monospace;
        font-size: 13px;
        padding: 16px;
        border-radius: var(--radius);
        overflow-x: auto;
        overflow-y: auto;
        max-height: 600px;
        line-height: 1.5;
        box-shadow: inset 0 2px 4px rgba(0,0,0,0.5);
        white-space: pre-wrap;
        word-wrap: break-word;
    }

    .log-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
    }

    .log-title {
        font-weight: 600;
        color: var(--color-text);
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .log-title svg {
        width: 18px;
        height: 18px;
        color: var(--color-purple);
    }
</style>

<div class="card">
    <div class="card-body">
        <div class="log-header">
            <div class="log-title">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                laravel.log
            </div>
            <form action="{{ route('admin.logs.clear') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus semua log? Tindakan ini tidak bisa dibatalkan.');">
                @csrf
                <button type="submit" class="btn btn-danger btn-sm">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Clear Logs
                </button>
            </form>
        </div>
        
        <div class="log-container" id="logContainer">
@if(empty($logs))
Tidak ada log tersedia.
@else
{{ $logs }}
@endif
        </div>
    </div>
</div>

@endsection

@section('extra-scripts')
<script>
    // Auto-scroll to bottom of logs on load
    document.addEventListener("DOMContentLoaded", function() {
        const logContainer = document.getElementById("logContainer");
        logContainer.scrollTop = logContainer.scrollHeight;
    });
</script>
@endsection
