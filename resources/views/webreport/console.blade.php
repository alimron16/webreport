@extends('webreport.layout')

@section('title', 'Developer API Console')
@section('page-header', 'Developer API Console')

@section('extra-styles')
<style>
    .console-layout {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
    }

    .form-group {
        margin-bottom: 1.25rem;
    }

    .form-group label {
        display: block;
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.5rem;
    }

    .form-control {
        width: 100%;
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid var(--glass-border);
        border-radius: 8px;
        padding: 0.75rem;
        color: #fff;
        outline: none;
        transition: var(--transition);
        font-family: 'Inter', sans-serif;
    }

    .form-control:focus {
        border-color: var(--accent-primary);
        background: rgba(255, 255, 255, 0.05);
    }

    .code-editor {
        font-family: 'Courier New', Courier, monospace;
        font-size: 0.9rem;
        line-height: 1.4;
        background: #0f172a;
        color: #38bdf8;
        border: 1px solid var(--glass-border);
        border-radius: 10px;
        min-height: 250px;
        resize: vertical;
        padding: 1rem;
    }

    .response-container {
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .response-header {
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.5rem;
    }

    .response-view {
        background: #0b0f19;
        border: 1px solid var(--glass-border);
        border-radius: 12px;
        padding: 1.25rem;
        flex-grow: 1;
        overflow: auto;
        max-height: 480px;
        font-family: 'Courier New', Courier, monospace;
        font-size: 0.85rem;
        color: #34d399;
        white-space: pre-wrap;
    }

    @media (max-width: 992px) {
        .console-layout {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('content')

<div class="console-layout">
    <!-- Left Panel: Request Configuration -->
    <div class="card">
        <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.25rem; font-weight: 600; margin-bottom: 1.5rem;">Kirim Request API</h2>
        
        <form action="{{ route('webreport.console.execute') }}" method="POST" id="apiForm">
            @csrf
            
            <div class="form-group">
                <label for="endpoint">Pilih Endpoint OtomaX</label>
                <select name="endpoint" id="endpoint" class="form-control" onchange="loadPayloadTemplate()">
                    <option value="Test" {{ $selected_endpoint === 'Test' ? 'selected' : '' }}>1. Test (Ping)</option>
                    <option value="InsertInbox" {{ $selected_endpoint === 'InsertInbox' ? 'selected' : '' }}>2. InsertInbox</option>
                    <option value="InsertOutbox" {{ $selected_endpoint === 'InsertOutbox' ? 'selected' : '' }}>3. InsertOutbox</option>
                    <option value="GetTrx" {{ $selected_endpoint === 'GetTrx' ? 'selected' : '' }}>4. GetTrx</option>
                    <option value="GetTrxHistory" {{ $selected_endpoint === 'GetTrxHistory' ? 'selected' : '' }}>5. GetTrxHistory</option>
                    <option value="GetRs" {{ $selected_endpoint === 'GetRs' ? 'selected' : '' }}>6. GetRs</option>
                    <option value="GetSaldoRs" {{ $selected_endpoint === 'GetSaldoRs' ? 'selected' : '' }}>7. GetSaldoRs</option>
                    <option value="TesPinRs" {{ $selected_endpoint === 'TesPinRs' ? 'selected' : '' }}>8. TesPinRs</option>
                    <option value="GetFormatRequest" {{ $selected_endpoint === 'GetFormatRequest' ? 'selected' : '' }}>9. GetFormatRequest</option>
                    <option value="GetVersiOtomaX" {{ $selected_endpoint === 'GetVersiOtomaX' ? 'selected' : '' }}>10. GetVersiOtomaX</option>
                    <option value="UpdateStatusOutbox" {{ $selected_endpoint === 'UpdateStatusOutbox' ? 'selected' : '' }}>11. UpdateStatusOutbox</option>
                    <option value="TambahSaldo" {{ $selected_endpoint === 'TambahSaldo' ? 'selected' : '' }}>12. TambahSaldo</option>
                    <option value="GetOutboxCallback" {{ $selected_endpoint === 'GetOutboxCallback' ? 'selected' : '' }}>13. GetOutboxCallback</option>
                    <option value="SetOutboxCallback" {{ $selected_endpoint === 'SetOutboxCallback' ? 'selected' : '' }}>14. SetOutboxCallback</option>
                    <option value="QueryProduk" {{ $selected_endpoint === 'QueryProduk' ? 'selected' : '' }}>15. QueryProduk</option>
                    <option value="QueryOperator" {{ $selected_endpoint === 'QueryOperator' ? 'selected' : '' }}>16. QueryOperator</option>
                    <option value="GetDaftarHargaRs" {{ $selected_endpoint === 'GetDaftarHargaRs' ? 'selected' : '' }}>17. GetDaftarHargaRs</option>
                    <option value="GetSaldoDompet" {{ $selected_endpoint === 'GetSaldoDompet' ? 'selected' : '' }}>18. GetSaldoDompet</option>
                    <option value="GetDataDompet" {{ $selected_endpoint === 'GetDataDompet' ? 'selected' : '' }}>19. GetDataDompet</option>
                    <option value="TambahPoin" {{ $selected_endpoint === 'TambahPoin' ? 'selected' : '' }}>20. TambahPoin</option>
                    <option value="InsertReseller" {{ $selected_endpoint === 'InsertReseller' ? 'selected' : '' }}>21. InsertReseller (Register)</option>
                </select>
            </div>

            <div class="form-group">
                <label for="payload">Request Body (JSON)</label>
                <textarea name="payload" id="payload" class="form-control code-editor">{{ $submitted_data ?? '' }}</textarea>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">Kirim Request</button>
        </form>
    </div>

    <!-- Right Panel: Response Output -->
    <div class="response-container">
        <div class="response-header">Response Payload (JSON)</div>
        <div class="response-view" id="responseView">@if(!empty($raw_response)){{ $raw_response }}@else/* Hasil response API OtomaX akan tampil di sini */@endif</div>
    </div>
</div>

@endsection

@section('extra-scripts')
<script>
    // Templates parameters based on OtomaX API Reference
    const templates = {
        Test: {
            kode: "yusuf"
        },
        InsertInbox: {
            pesan: "tiket.100000.1234",
            kode_reseller: "yusuf",
            pengirim: "0812996000",
            tipe_pengirim: "W"
        },
        InsertOutbox: {
            kode_reseller: "yusuf",
            penerima: "62812996000",
            tipe_penerima: "W",
            pesan: "tes outbox"
        },
        GetTrx: {
            trxid: 4522967
        },
        GetTrxHistory: {
            kode_reseller: "YUSUF",
            tgl_awal: "2026-07-01",
            tgl_akhir: "2026-07-15",
            limit: 2
        },
        GetRs: {
            kode: "yusuf"
        },
        GetSaldoRs: {
            kode: "yusuf"
        },
        TesPinRs: {
            kode: "yusuf",
            pin_hash: "mGLwYsNI0ITdUA1Qmgtclqkhoz+frKs53i7unjrZdXI="
        },
        GetFormatRequest: {
            nama: "tiketdeposit"
        },
        GetVersiOtomaX: {},
        UpdateStatusOutbox: {
            kode: 9047353,
            status: 20
        },
        TambahSaldo: {
            kode_reseller: "yusuf",
            jumlah: 1000,
            saldo: 3000,
            keterangan: "tes tambah",
            kode_dompet: "A"
        },
        GetOutboxCallback: {},
        SetOutboxCallback: {
            url: "http://45.77.173.173:9888/api/senderoto/"
        },
        QueryProduk: {
            kode: "BYROMNI"
        },
        QueryOperator: {
            kode: "PPOB"
        },
        GetDaftarHargaRs: {
            kode_reseller: "yusuf",
            kode_produk: "",
            kode_operator: ""
        },
        GetSaldoDompet: {
            kode_reseller: "yusuf",
            kode_dompet: "A"
        },
        GetDataDompet: {
            kode_dompet: "A"
        },
        TambahPoin: {
            kode_reseller: "yusuf",
            jumlah: 10
        },
        InsertReseller: {
            name: "Arief",
            pengirim: "081211221334",
            tipe_pengirim: "W",
            pin: "987898"
        }
    };

    function loadPayloadTemplate() {
        const endpoint = document.getElementById('endpoint').value;
        const payloadTextarea = document.getElementById('payload');
        
        // If there's no pre-existing submitted payload from controller, load default template
        const templateData = templates[endpoint] || {};
        payloadTextarea.value = JSON.stringify(templateData, null, 2);
    }

    // Initialize template on load if empty
    window.addEventListener('DOMContentLoaded', () => {
        const payloadTextarea = document.getElementById('payload');
        if (!payloadTextarea.value.trim()) {
            loadPayloadTemplate();
        }
    });
</script>
@endsection
