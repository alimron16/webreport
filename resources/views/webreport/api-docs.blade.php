@extends('webreport.layout')

@section('title', 'Dokumentasi API')
@section('page-header', 'Dokumentasi API')

@section('extra-styles')
<style>
/* ═══════════════════════════════════════════════
   API DOCS PAGE — Design System
   Font: Inter (body 14px), Outfit (headings)
   ═══════════════════════════════════════════════ */

.docs-container {
    max-width: 100%;
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.docs-card {
    background: #fff;
    border: 1px solid var(--color-border);
    border-radius: 10px;
    box-shadow: var(--shadow-sm);
    overflow: hidden;
}

.docs-card-header {
    background: #f8fafc;
    border-bottom: 1px solid var(--color-border);
    padding: 16px 24px;
    font-family: 'Outfit', sans-serif;
    font-size: 16px;
    font-weight: 600;
    color: var(--color-text-1);
    display: flex;
    align-items: center;
    gap: 10px;
}

.docs-card-body {
    padding: 24px;
}

.doc-section {
    margin-bottom: 24px;
}

.doc-section:last-child {
    margin-bottom: 0;
}

.doc-title {
    font-weight: 600;
    color: var(--color-primary);
    margin-bottom: 12px;
    font-size: 15px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.doc-code {
    background: #1e293b;
    color: #e2e8f0;
    padding: 14px 16px;
    border-radius: 8px;
    font-family: 'Consolas', 'Monaco', monospace;
    font-size: 13px;
    line-height: 1.5;
    overflow-x: auto;
    word-break: break-all;
    border: 1px solid #0f172a;
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
}

.doc-code.regex {
    background: #f1f5f9;
    color: #334155;
    border: 1px solid #cbd5e1;
    box-shadow: none;
}

.doc-label {
    display: inline-block;
    padding: 4px 8px;
    background: var(--color-bg);
    border: 1px solid var(--color-border);
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
    color: var(--color-text-3);
    margin-bottom: 6px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}
</style>
@endsection

@section('content')
<div class="docs-container">
    
    <!-- Endpoint Info -->
    <div class="docs-card">
        <div class="docs-card-header">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
            IP CENTER / ENDPOINT
        </div>
        <div class="docs-card-body">
            <div class="doc-code">http://103.166.91.114:5558/</div>
        </div>
    </div>

    <!-- Parsing dengan Signature -->
    <div class="docs-card">
        <div class="docs-card-header">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
            Parsing dengan Signature
        </div>
        <div class="docs-card-body">
            <div class="doc-section">
                <div class="doc-title">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    Untuk Transaksi
                </div>
                <div class="doc-code">trx?product=[product]&qty=[qty]&dest=[tujuan]&refID=[trxid]&memberID=[memberid]&sign=[sign]</div>
            </div>
            
            <div class="doc-section">
                <div class="doc-title">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    Untuk Cek Saldo
                </div>
                <div class="doc-code">balance?memberID=[memberID]&sign=[sign]</div>
            </div>

            <div class="doc-section">
                <div class="doc-title">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    Untuk Pengambilan Tiket
                </div>
                <div class="doc-code">saldo?memberid=[memberid]&sign=[sign]&cmd=ticket&amount=nominal</div>
            </div>
        </div>
    </div>

    <!-- Parsing tanpa Signature -->
    <div class="docs-card">
        <div class="docs-card-header">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/>
            </svg>
            Parsing tanpa Signature
        </div>
        <div class="docs-card-body">
            <div class="doc-section">
                <div class="doc-title">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    Untuk Transaksi
                </div>
                <div class="doc-code">trx?product=[product]&qty=[qty]&dest=[tujuan]&refID=[trxid]&memberID=[memberid]&&pin=[pin]&password=[password]</div>
            </div>
            
            <div class="doc-section">
                <div class="doc-title">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    Untuk Cek Saldo
                </div>
                <div class="doc-code">balance?memberID=[memberID]&pin=[pin]&password=[password]</div>
            </div>

            <div class="doc-section">
                <div class="doc-title">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    Untuk Pengambilan Tiket
                </div>
                <div class="doc-code">saldo?memberid=[memberid]&pin=[pin]&password=[password]&cmd=ticket&amount=nominal</div>
            </div>
        </div>
    </div>

    <!-- Reply Jawaban (Regex) -->
    <div class="docs-card">
        <div class="docs-card-header">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
            </svg>
            Reply Jawaban (Auto Regex)
        </div>
        <div class="docs-card-body">
            
            <!-- Sukses -->
            <div class="doc-section">
                <div class="doc-title">Trx Sukses</div>
                <div class="doc-label">Contoh Reply</div>
                <div class="doc-code" style="margin-bottom:10px;">#131702637 TD15GB.082163500671 SUKSES. SN/Ref: 0214029236000072604211. Saldo 1.434.763.120- 64.795=1.434.698.325 @1607110625</div>
                
                <div class="doc-label">Regex Pattern</div>
                <div class="doc-code regex">#(?&lt;trxidcmp&gt;[\d.,]+)[\s\S]*? (?&lt;kode1&gt;[a-zA-Z]+)[\s\S]*?D(?&lt;kode2&gt;[\d.,]+)[\s\S]*?5(?&lt;kode3&gt;[a-zA-Z]+)[\s\S]*?\.(?&lt;tujuan&gt;\d+) S[\s\S]*?: (?&lt;sn&gt;\d+)\. Sa[\s\S]*? (?&lt;saldoawal&gt;[\d.,]+)[\s\S]*? (?&lt;hargabeli&gt;[\d.,]+)=[\s\S]*?(?&lt;saldo&gt;-?[\d.,]+) @[\s\S]*?(?&lt;tid&gt;[\d.,]+)</div>
            </div>

            <!-- Proses -->
            <div class="doc-section">
                <div class="doc-title">Trx Diproses</div>
                <div class="doc-label">Contoh Reply</div>
                <div class="doc-code" style="margin-bottom:10px;">status=1&amp;message=#131702637 TD15GB.082163500671 akan diproses @110516. Saldo 1.434.763.120 - 64.795 = 1.434.698.325 #1290982407</div>
                
                <div class="doc-label">Regex Pattern</div>
                <div class="doc-code regex">(?&lt;trxidcmp&gt;[\d.,]+) T[\s\S]*?(?&lt;kode1&gt;[a-zA-Z]+)1[\s\S]*?(?&lt;kode2&gt;[\d.,]+)G[\s\S]*?(?&lt;kode3&gt;[a-zA-Z]+)\.[\s\S]*?\.(?&lt;tujuan&gt;\d+) a[\s\S]*?(?&lt;xxx&gt;[\d.,]+)\.[\s\S]*?(?&lt;saldoawal&gt;[\d.,]+) -[\s\S]*? (?&lt;hargabeli&gt;[\d.,]+) =[\s\S]*? (?&lt;saldo&gt;-?[\d.,]+) #[\s\S]*?(?&lt;xxxx&gt;[\d.,]+)</div>
            </div>

            <!-- Gagal Biasa -->
            <div class="doc-section">
                <div class="doc-title">Trx Gagal</div>
                <div class="doc-label">Contoh Reply</div>
                <div class="doc-code" style="margin-bottom:10px;">THH5G.0895623091012 GAGAL. . Saldo 366.945.449 @233915</div>
                
                <div class="doc-label">Regex Pattern</div>
                <div class="doc-code regex">(?&lt;kode1&gt;[a-zA-Z]+)[\s\S]*?(?&lt;kode2&gt;[\d.,]+)[\s\S]*?(?&lt;kode3&gt;[a-zA-Z]+)\.[\s\S]*?\.(?&lt;tujuan&gt;\d+) G[\s\S]*? (?&lt;saldo&gt;-?[\d.,]+) @[\s\S]*?@(?&lt;xxxx&gt;[\d.,]+)</div>
            </div>

            <!-- Gagal Saldo Tidak Cukup -->
            <div class="doc-section">
                <div class="doc-title">Trx Gagal (Saldo Tidak Cukup)</div>
                <div class="doc-label">Contoh Reply</div>
                <div class="doc-code" style="margin-bottom:10px;">status=43&amp;message=DNF30.081280638827 GAGAL, Saldo tidak cukup. Hrg 30.025, Saldo 9.854 Limit 0, masih proses 0 silahkan deposit saldo terlebih dahulu. @100516</div>
                
                <div class="doc-label">Regex Pattern</div>
                <div class="doc-code regex">(?&lt;kode1&gt;[a-zA-Z]+)3[\s\S]*?(?&lt;kode2&gt;[\d.,]+)\.[\s\S]*?\.(?&lt;tujuan&gt;\d+) G[\s\S]*? (?&lt;hargabeli&gt;[\d.,]+),[\s\S]*? (?&lt;saldo&gt;-?[\d.,]+) L[\s\S]*?(?&lt;xxxx&gt;[\d.,]+)</div>
            </div>

            <!-- Trx Sblmnya Blm Selesai -->
            <div class="doc-section">
                <div class="doc-title">Tunggu Trx Sebelumnya Selesai</div>
                <div class="doc-label">Contoh Reply</div>
                <div class="doc-code" style="margin-bottom:10px;">status=1&amp;message=Mhn tunggu trx sblmnya selesai: #131703233 IVFINC7.2001519879127 @111016, status Sedang Diproses. Saldo 1.423.391.284</div>
                
                <div class="doc-label">Regex Pattern</div>
                <div class="doc-code regex">(?&lt;trxidcmp&gt;[\d.,]+) I[\s\S]*?(?&lt;kode1&gt;[a-zA-Z]+)7[\s\S]*?(?&lt;kode2&gt;[\d.,]+)\.[\s\S]*?\.(?&lt;tujuan&gt;\d+) @[\s\S]*?(?&lt;xxxx&gt;[\d.,]+),[\s\S]*? (?&lt;saldo&gt;-?[\d.,]+)</div>
            </div>

            <!-- Tujuan Salah -->
            <div class="doc-section">
                <div class="doc-title">Gagal (Tujuan Salah)</div>
                <div class="doc-label">Contoh Reply</div>
                <div class="doc-code" style="margin-bottom:10px;">Three Data Happy 8GB 3 Hari THH6G3H.089673591571 GAGAL. Nomor tujuan salah. Stok 1.426.793.624 @111016</div>
                
                <div class="doc-label">Regex Pattern</div>
                <div class="doc-code regex">.*\.(?&lt;tujuan&gt;\d+) GAGAL\. Nomor tujuan salah\. Stok (?&lt;saldo&gt;[.\d]+) @.*</div>
            </div>

            <!-- Produk Gangguan -->
            <div class="doc-section">
                <div class="doc-title">Gagal (Produk Sedang Gangguan)</div>
                <div class="doc-label">Contoh Reply</div>
                <div class="doc-code" style="margin-bottom:10px;">status=47&amp;message=1290953473# ISAT DATA 3GB 28HARI NASIONAL MFII330.085335578953 GAGAL. Produk sedang gangguan. Stok 1.483.945.770 @104116</div>
                
                <div class="doc-label">Regex Pattern</div>
                <div class="doc-code regex">.*\.(?&lt;tujuan&gt;\d+) GAGAL\. Produk sedang gangguan\. Stok (?&lt;saldo&gt;[.\d]+) @.*</div>
            </div>
            
        </div>
    </div>
</div>
@endsection
