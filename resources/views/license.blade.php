<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aktivasi Lisensi - Webreport</title>
    <meta name="description" content="Aktivasi lisensi aplikasi Webreport. Masukkan license key untuk mengaktifkan aplikasi.">
    <meta name="robots" content="noindex, nofollow">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-1: hsl(232, 47%, 8%);
            --bg-2: hsl(256, 52%, 14%);
            --accent: hsl(252, 92%, 68%);
            --accent-2: hsl(190, 95%, 55%);
            --ok: hsl(152, 70%, 48%);
            --bad: hsl(352, 85%, 62%);
            --text: hsl(220, 30%, 96%);
            --muted: hsl(226, 18%, 68%);
            --glass: hsla(232, 40%, 16%, 0.62);
            --border: hsla(0, 0%, 100%, 0.1);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            font-family: 'Outfit', system-ui, sans-serif;
            color: var(--text);
            background:
                radial-gradient(900px 500px at 12% 8%, hsla(252, 92%, 60%, 0.28), transparent 60%),
                radial-gradient(800px 500px at 92% 92%, hsla(190, 95%, 50%, 0.2), transparent 60%),
                linear-gradient(135deg, var(--bg-1), var(--bg-2));
            overflow-x: hidden;
        }
        .orb {
            position: fixed; border-radius: 50%; filter: blur(70px); opacity: .45;
            animation: float 14s ease-in-out infinite; pointer-events: none;
        }
        .orb.a { width: 280px; height: 280px; background: var(--accent); top: -80px; left: -60px; }
        .orb.b { width: 240px; height: 240px; background: var(--accent-2); bottom: -80px; right: -40px; animation-delay: -6s; }
        @keyframes float { 0%,100% { transform: translateY(0) } 50% { transform: translateY(26px) } }

        .card {
            position: relative; z-index: 1;
            width: 100%; max-width: 480px;
            padding: 36px 32px;
            background: var(--glass);
            border: 1px solid var(--border);
            border-radius: 24px;
            backdrop-filter: blur(22px);
            -webkit-backdrop-filter: blur(22px);
            box-shadow: 0 30px 80px rgba(0,0,0,.45);
            animation: rise .7s cubic-bezier(.2,.8,.2,1) both;
        }
        @keyframes rise { from { opacity: 0; transform: translateY(24px) scale(.98) } to { opacity: 1; transform: none } }

        .icon {
            width: 64px; height: 64px; border-radius: 18px;
            display: grid; place-items: center; margin-bottom: 20px;
            background: linear-gradient(135deg, var(--accent), var(--accent-2));
            box-shadow: 0 10px 30px hsla(252, 92%, 60%, .45);
        }
        .icon svg { width: 32px; height: 32px; stroke: #fff; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
        h1 { font-size: 26px; font-weight: 700; letter-spacing: -.3px; margin-bottom: 6px; }
        .sub { color: var(--muted); font-size: 14.5px; line-height: 1.55; margin-bottom: 22px; }

        .badge {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 7px 14px; border-radius: 999px; font-size: 13px; font-weight: 600; margin-bottom: 18px;
        }
        .badge::before { content: ''; width: 8px; height: 8px; border-radius: 50%; background: currentColor; box-shadow: 0 0 10px currentColor; }
        .badge.ok  { color: var(--ok);  background: hsla(152, 70%, 48%, .12); border: 1px solid hsla(152, 70%, 48%, .35); }
        .badge.bad { color: var(--bad); background: hsla(352, 85%, 62%, .12); border: 1px solid hsla(352, 85%, 62%, .35); }

        .alert { padding: 12px 14px; border-radius: 12px; font-size: 14px; margin-bottom: 16px; line-height: 1.5; }
        .alert.error   { background: hsla(352, 85%, 62%, .14); border: 1px solid hsla(352, 85%, 62%, .4); color: hsl(352, 100%, 85%); }
        .alert.success { background: hsla(152, 70%, 48%, .14); border: 1px solid hsla(152, 70%, 48%, .4); color: hsl(152, 80%, 82%); }

        .rows { display: grid; gap: 10px; margin-bottom: 20px; }
        .row {
            display: flex; justify-content: space-between; align-items: center; gap: 12px;
            padding: 12px 14px; border-radius: 12px;
            background: hsla(0, 0%, 100%, .04); border: 1px solid var(--border);
            font-size: 13.5px;
        }
        .row span:first-child { color: var(--muted); flex-shrink: 0; }
        .row .val { font-family: 'JetBrains Mono', monospace; font-size: 13px; word-break: break-all; text-align: right; }
        .copy {
            background: none; border: 0; color: var(--accent-2); cursor: pointer;
            font-family: inherit; font-size: 12.5px; font-weight: 600; margin-left: 8px;
        }
        .copy:hover { text-decoration: underline; }

        label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 8px; color: var(--muted); }
        input[type=text] {
            width: 100%; padding: 14px 16px; border-radius: 14px;
            background: hsla(0, 0%, 0%, .28); border: 1px solid var(--border); color: var(--text);
            font-family: 'JetBrains Mono', monospace; font-size: 15px; letter-spacing: .5px;
            transition: border-color .2s, box-shadow .2s;
        }
        input[type=text]:focus {
            outline: none; border-color: var(--accent);
            box-shadow: 0 0 0 4px hsla(252, 92%, 68%, .22);
        }
        .btn {
            width: 100%; margin-top: 14px; padding: 14px; border: 0; border-radius: 14px; cursor: pointer;
            font-family: inherit; font-size: 15.5px; font-weight: 700; color: #fff;
            background: linear-gradient(135deg, var(--accent), var(--accent-2));
            box-shadow: 0 12px 28px hsla(252, 92%, 60%, .4);
            transition: transform .15s, box-shadow .15s, filter .15s;
        }
        .btn:hover { transform: translateY(-2px); filter: brightness(1.08); box-shadow: 0 16px 34px hsla(252, 92%, 60%, .5); }
        .btn:active { transform: translateY(0); }
        .btn.ghost { background: hsla(0, 0%, 100%, .08); box-shadow: none; border: 1px solid var(--border); }
        .hint { margin-top: 18px; font-size: 12.5px; color: var(--muted); line-height: 1.55; text-align: center; }
        a.link { text-decoration: none; display: block; }
    </style>
</head>
<body>
    <div class="orb a"></div>
    <div class="orb b"></div>

    <main class="card" id="license-card">
        <div class="icon">
            <svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        </div>

        <h1>Aktivasi Lisensi</h1>
        <p class="sub">
            @if($info['valid'])
                Aplikasi sudah berlisensi dan siap digunakan.
            @else
                Aplikasi terkunci. Masukkan license key dari penyedia untuk mengaktifkan.
            @endif
        </p>

        <div class="badge {{ $info['valid'] ? 'ok' : 'bad' }}" id="license-status">
            {{ $info['valid'] ? 'Lisensi Aktif' : 'Lisensi Tidak Aktif' }}
        </div>

        @if(session('success'))
            <div class="alert success" id="license-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert error" id="license-error">{{ session('error') }}</div>
        @elseif($errors->any())
            <div class="alert error" id="license-error">{{ $errors->first() }}</div>
        @elseif(!$info['valid'] && !empty($info['message']))
            <div class="alert error" id="license-error">{{ $info['message'] }}</div>
        @endif

        <div class="rows">
            @if($info['has_key'])
                <div class="row"><span>License Key</span><span class="val">{{ $info['key_mask'] }}</span></div>
            @endif
            @if($info['valid'] && $info['expiry'])
                <div class="row"><span>Berlaku hingga</span><span class="val">{{ $info['expiry'] }}</span></div>
            @endif
            <div class="row">
                <span>HWID</span>
                <span class="val"><span id="hwid-text">{{ $info['hwid'] }}</span><button type="button" class="copy" id="btn-copy-hwid">Salin</button></span>
            </div>
        </div>

        @if(!$info['valid'] || $isAdmin)
            <form method="POST" action="{{ route('license.activate') }}" id="license-form" autocomplete="off">
                @csrf
                <label for="license_key">{{ $info['valid'] ? 'Ganti License Key' : 'License Key' }}</label>
                <input type="text" id="license_key" name="license_key" value="{{ old('license_key') }}" placeholder="XXXX-XXXX-XXXX-XXXX" required spellcheck="false">
                <button type="submit" class="btn" id="btn-activate">{{ $info['valid'] ? 'Perbarui Lisensi' : 'Aktifkan Sekarang' }}</button>
            </form>
        @endif

        @if($info['valid'])
            <a href="{{ url('/') }}" class="link"><button type="button" class="btn ghost" id="btn-open-app">Buka Aplikasi</button></a>
        @endif

        <p class="hint">Berikan HWID di atas ke penyedia aplikasi bila diminta untuk mengikat lisensi ke server ini.</p>
    </main>

    <script>
        document.getElementById('btn-copy-hwid').addEventListener('click', function () {
            var btn = this;
            navigator.clipboard.writeText(document.getElementById('hwid-text').innerText).then(function () {
                btn.innerText = 'Tersalin';
                setTimeout(function () { btn.innerText = 'Salin'; }, 1600);
            });
        });
    </script>
</body>
</html>
