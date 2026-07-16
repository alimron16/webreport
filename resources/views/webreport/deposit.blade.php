@extends('webreport.layout')

@section('title', 'Isi Saldo & Mutasi')
@section('page-header', 'Isi Saldo & Mutasi')

@section('extra-styles')
<style>
    .grid-container {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .stg-card {
        background: var(--color-white);
        border: 1px solid var(--color-border);
        border-radius: var(--radius);
        overflow: hidden;
        box-shadow: var(--shadow-sm);
    }

    .stg-card-hdr {
        padding: 16px 20px;
        border-bottom: 1px solid var(--color-border);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .stg-card-hdr h3 {
        font-family: 'Outfit', sans-serif;
        font-size: 15px;
        font-weight: 700;
        color: var(--color-text);
        margin: 0;
    }

    .icon-box {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: var(--color-purple-light);
        color: var(--color-purple);
    }

    .icon-box.green {
        background: #ecfdf5;
        color: #10b981;
    }

    .card-body {
        padding: 20px;
    }

    .form-group {
        margin-bottom: 16px;
    }

    .form-group label {
        display: block;
        font-size: 11px;
        font-weight: 600;
        color: var(--color-text-3);
        margin-bottom: 6px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .hint-text {
        font-size: 11.5px;
        color: var(--color-text-4);
        margin-top: 6px;
    }

    .table-wrapper {
        overflow-x: auto;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th {
        background: var(--color-bg);
        padding: 12px 16px;
        font-size: 11px;
        font-weight: 600;
        color: var(--color-text-3);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 1px solid var(--color-border);
        text-align: left;
    }
    th.text-right { text-align: right; }

    td {
        padding: 14px 16px;
        border-bottom: 1px solid var(--color-border);
        font-size: 13px;
        color: var(--color-text-2);
        vertical-align: middle;
    }

    tr:last-child td {
        border-bottom: none;
    }

    tr:hover td {
        background: #f9fafb;
    }

    .td-right { text-align: right; }

    .text-green { color: #10b981; font-weight: 600; }
    .text-red { color: #ef4444; font-weight: 600; }
    /* Modal Popup untuk Tiket Sukses */
    .modal-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.45);
        z-index: 1000;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 16px;
    }

    .modal-box {
        background: var(--color-white);
        border-radius: 12px;
        width: 100%;
        max-width: 420px;
        box-shadow: 0 20px 50px rgba(0,0,0,.15);
        animation: modalIn .3s cubic-bezier(0.16, 1, 0.3, 1);
        overflow: hidden;
    }

    @keyframes modalIn {
        from { transform: scale(0.95) translateY(10px); opacity: 0; }
        to   { transform: scale(1)   translateY(0);   opacity: 1; }
    }

    .modal-header {
        background: #f0fdf4;
        padding: 20px 24px;
        border-bottom: 1px solid #bbf7d0;
        text-align: center;
    }
    
    .modal-header .icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 48px;
        height: 48px;
        background: #dcfce7;
        color: #16a34a;
        border-radius: 50%;
        margin-bottom: 12px;
    }

    .modal-header h3 {
        font-family: 'Outfit', sans-serif;
        color: #166534;
        margin: 0 0 6px 0;
        font-size: 18px;
        font-weight: 700;
    }

    .modal-header p {
        color: #15803d;
        font-size: 13px;
        margin: 0;
    }

    .modal-body {
        padding: 24px;
        text-align: center;
    }

    .modal-tiket-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 24px;
    }

    .modal-lbl {
        font-size: 11px;
        text-transform: uppercase;
        font-weight: 600;
        color: #64748b;
        margin-bottom: 4px;
    }

    .modal-val {
        font-family: 'Outfit', sans-serif;
        font-size: 28px;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 16px;
    }

    .modal-val-sm {
        font-size: 15px;
        font-weight: 600;
        color: #334155;
        line-height: 1.4;
    }

    .btn-close-modal {
        display: block;
        width: 100%;
        background: #10b981;
        color: #fff;
        text-align: center;
        padding: 12px;
        border-radius: 8px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: background 0.2s;
    }
    .btn-close-modal:hover { background: #059669; }

    /* Pending Ticket Card */
    .pending-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        margin-bottom: 24px;
    }

    .pending-hdr {
        background: #f8fafc;
        padding: 12px 16px;
        border-bottom: 1px solid #e2e8f0;
        border-radius: 8px 8px 0 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .pending-hdr .icon-box {
        color: #f59e0b;
    }

    .pending-hdr h3 {
        font-family: 'Outfit', sans-serif;
        font-size: 14px;
        font-weight: 600;
        color: #334155;
        margin: 0;
    }

    .pending-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px;
        border-bottom: 1px solid #f1f5f9;
    }
    
    .pending-item:last-child {
        border-bottom: none;
    }

    .pending-bank {
        font-size: 13px;
        font-weight: 600;
        color: #475569;
        margin-bottom: 4px;
    }

    .pending-amount {
        font-family: 'Outfit', sans-serif;
        font-size: 18px;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 4px;
    }

    .pending-date {
        font-size: 11px;
        color: #94a3b8;
    }

    .btn-cancel {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        color: #ef4444;
        font-size: 12px;
        padding: 6px 12px;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-cancel:hover {
        background: #fef2f2;
        border-color: #fca5a5;
    }
</style>
@endsection

@section('content')

    {{-- Modal Success from Tiket Request --}}
    @if(session('tiket_success'))
        @php
            $sukses_bank = session('tiket_success')['bank'];
            $sukses_rek = '';
            $sukses_an = '';
            foreach($banks as $b) {
                if ($b['bank'] === $sukses_bank) {
                    $sukses_rek = $b['rekening'];
                    $sukses_an = $b['atas_nama'];
                    break;
                }
            }
        @endphp
        <div class="modal-backdrop" id="successModal">
            <div class="modal-box">
                <div class="modal-header">
                    <div class="icon">
                        <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <h3>Tiket Berhasil Dibuat!</h3>
                    <p>Silakan transfer TEPAT SESUAI NOMINAL ke rekening berikut agar saldo masuk otomatis.</p>
                </div>
                <div class="modal-body">
                    <div class="modal-tiket-box">
                        <div class="modal-lbl">Nominal Transfer:</div>
                        <div class="modal-val">
                            Rp {{ number_format(session('tiket_success')['nominal'], 0, ',', '.') }}
                        </div>
                        
                        <div class="modal-lbl" style="margin-top: 16px;">Tujuan Transfer:</div>
                        <div class="modal-val-sm">
                            Bank {{ $sukses_bank }}<br>
                            {{ $sukses_rek }}<br>
                            <span style="font-weight: normal; color: #64748b;">a.n {{ $sukses_an }}</span>
                        </div>
                    </div>
                    <button class="btn-close-modal" onclick="document.getElementById('successModal').style.display='none'">Saya Mengerti</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Error Alert --}}
    @if(session('error'))
        <div class="alert alert-danger" style="margin-bottom: 20px;">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div class="grid-container">
        
        {{-- Kiri: Form Ambil Tiket --}}
        <div>
            <div class="stg-card">
                <div class="stg-card-hdr">
                    <div class="icon-box">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    </div>
                    <h3>Ambil Tiket Deposit</h3>
                </div>
                
                <div class="card-body">
                    <form action="{{ route('webreport.deposit.request') }}" method="POST">
                        @csrf
                        
                        <div class="form-group">
                            <label>Nominal Deposit</label>
                            <input type="number" name="nominal" class="form-control" required min="10000" placeholder="100000">
                            <div class="hint-text">Min. Rp 10.000</div>
                        </div>
                        
                        <div class="form-group">
                            <label>Pilih Bank Tujuan</label>
                            @if(empty($banks))
                                <div style="font-size: 12px; color: #ef4444; background: #fef2f2; padding: 8px; border: 1px solid #fee2e2; border-radius: 4px;">
                                    Belum ada bank yang tersedia. Hubungi admin.
                                </div>
                            @else
                                <select name="bank" class="form-control" required>
                                    <option value="">-- Pilih Bank --</option>
                                    @foreach($banks as $b)
                                        <option value="{{ $b['bank'] }}">{{ $b['bank'] }} - {{ $b['rekening'] }} ({{ $b['atas_nama'] }})</option>
                                    @endforeach
                                </select>
                            @endif
                        </div>

                        <button type="submit" class="btn btn-primary" style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 8px;" {{ empty($banks) ? 'disabled' : '' }}>
                            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 5l7 7-7 7M5 5l7 7-7 7"/></svg>
                            Minta Tiket
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Kanan / Bawah: Tabel Pending & Mutasi Deposit --}}
        <div>
            @if(count($pending_tiket) > 0)
            <div class="pending-card">
                <div class="pending-hdr">
                    <div class="icon-box">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h3>Tiket Belum Dibayar</h3>
                </div>
                <div>
                    @foreach($pending_tiket as $p)
                        @php
                            $p_rek = '';
                            $p_an = '';
                            foreach($banks as $b) {
                                if ($b['bank'] === $p['bank']) {
                                    $p_rek = $b['rekening'];
                                    $p_an = $b['atas_nama'];
                                    break;
                                }
                            }
                        @endphp
                        <div class="pending-item">
                            <div>
                                <div class="pending-bank">Bank {{ $p['bank'] }} - {{ $p_rek }}</div>
                                <div class="pending-amount">Rp {{ number_format($p['jumlah'], 0, ',', '.') }}</div>
                                <div class="pending-date">
                                    Dibuat: {{ \Carbon\Carbon::parse($p['waktu'])->format('d/m/Y H:i') }}
                                </div>
                            </div>
                            <div>
                                <form action="{{ route('webreport.deposit.cancel') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan tiket ini?');">
                                    @csrf
                                    <input type="hidden" name="kode" value="{{ $p['kode'] }}">
                                    <button type="submit" class="btn-cancel">Batalkan</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="stg-card" style="height: 100%;">
                <div class="stg-card-hdr">
                    <div class="icon-box green">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h3>Riwayat Mutasi Saldo</h3>
                </div>
                
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Keterangan</th>
                                <th class="text-right">Jumlah</th>
                                <th class="text-right">Saldo Akhir</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($mutasi as $m)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($m['tanggal'])->format('d/m/Y H:i') }}</td>
                                    <td><strong style="color:var(--color-text);">{{ $m['keterangan'] }}</strong></td>
                                    <td class="td-right">
                                        @if($m['jumlah'] >= 0)
                                            <span class="text-green">+ Rp {{ number_format($m['jumlah'], 0, ',', '.') }}</span>
                                        @else
                                            <span class="text-red">- Rp {{ number_format(abs($m['jumlah']), 0, ',', '.') }}</span>
                                        @endif
                                    </td>
                                    <td class="td-right saldo-text">Rp {{ number_format($m['saldo_akhir'], 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" style="text-align: center; color: var(--color-text-4); padding: 40px 20px;">
                                        Belum ada riwayat deposit atau mutasi saldo.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

@endsection
