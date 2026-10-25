<#
    build-release.ps1
    Membuat paket rilis Webreport yang sudah diobfuscate dengan YAK Pro.

    Pemakaian (dari folder project):
        powershell -ExecutionPolicy Bypass -File tools\build-release.ps1

    Hasil:
        dist\webreport\                      folder siap kirim
        dist\webreport-release-<tanggal>.zip arsip folder di atas

    Yang TIDAK ikut dikirim: .git, tools, tests, .env, database.sqlite, log, cache, sesi.
    Yang diobfuscate: app\, routes\, bootstrap\ (view Blade tetap apa adanya).
#>
param(
    [string]$Php = "",
    [switch]$NoZip
)

$ErrorActionPreference = 'Stop'
$ProgressPreference = 'SilentlyContinue'

$root  = Split-Path -Parent $PSScriptRoot
$dist  = Join-Path $root 'dist'
$stage = Join-Path $dist 'webreport'
$work  = Join-Path $dist '_obf_work'
$yak   = Join-Path $PSScriptRoot 'yakpro-po'

# ── PHP yang dipakai ──────────────────────────────────────────────
if (-not $Php) {
    $portable = Join-Path $root 'php\php.exe'
    if (Test-Path $portable) { $Php = $portable }
    else { $Php = 'php' }
}
if (-not (Test-Path (Join-Path $yak 'yakpro-po.php'))) {
    throw "YAK Pro belum terpasang di $yak. Lihat tools\README-RELEASE.md."
}
if (-not (Test-Path (Join-Path $yak 'PHP-Parser'))) {
    throw "PHP-Parser belum ada di $yak\PHP-Parser. Lihat tools\README-RELEASE.md."
}

Write-Host "==> Menyalin project ke $stage"
if (Test-Path $dist) { cmd /c "rmdir /s /q `"$dist`"" }
New-Item -ItemType Directory -Path $stage -Force | Out-Null

$excludeDirs = @('.git', 'dist', 'tools', 'node_modules', 'tests', '.phpunit.cache', '.idea', '.vscode',
                 (Join-Path $root 'public\storage'))
$excludeFiles = @('.env', '.env.backup', '.env.production', '*.log', 'database.sqlite',
                  'STRUK *.jpeg', 'phpunit.xml', '.phpunit.result.cache')
robocopy $root $stage /E /NFL /NDL /NJH /NJS /NP /XD @excludeDirs /XF @excludeFiles | Out-Null
if ($LASTEXITCODE -ge 8) { throw "robocopy gagal (kode $LASTEXITCODE)" }

# Bersihkan data runtime
foreach ($p in @('storage\logs', 'storage\framework\cache\data', 'storage\framework\sessions', 'storage\framework\views')) {
    $full = Join-Path $stage $p
    if (Test-Path $full) {
        Get-ChildItem $full -Force | Where-Object { $_.Name -ne '.gitignore' } | Remove-Item -Recurse -Force
    }
}
Get-ChildItem (Join-Path $stage 'bootstrap\cache') -Filter *.php -ErrorAction SilentlyContinue | Remove-Item -Force

# ── Kumpulkan nama variabel yang TIDAK boleh diacak ───────────────
# 1) variabel yang dipakai lewat compact('a','b') (nama dipakai sebagai string)
# 2) parameter route {nama}; Laravel mengisi parameter controller berdasarkan nama
$ignore = New-Object 'System.Collections.Generic.HashSet[string]'
Get-ChildItem (Join-Path $stage 'app'), (Join-Path $stage 'routes'), (Join-Path $stage 'bootstrap') -Recurse -Filter *.php | ForEach-Object {
    $code = [System.IO.File]::ReadAllText($_.FullName)
    foreach ($m in [regex]::Matches($code, 'compact\s*\((.*?)\)', 'Singleline')) {
        foreach ($s in [regex]::Matches($m.Groups[1].Value, "['""](\w+)['""]")) { [void]$ignore.Add($s.Groups[1].Value) }
    }
}
$routeFile = Join-Path $stage 'routes\web.php'
if (Test-Path $routeFile) {
    foreach ($m in [regex]::Matches([System.IO.File]::ReadAllText($routeFile), '\{(\w+)\??\}')) { [void]$ignore.Add($m.Groups[1].Value) }
}
# Nama bawaan Laravel/PHP yang sering dipakai tidak langsung
foreach ($n in @('request', 'app', 'errors', 'slot', 'attributes', 'component', 'data')) { [void]$ignore.Add($n) }
$ignoreList = ($ignore | Sort-Object | ForEach-Object { "'$_'" }) -join ','
Write-Host "==> Variabel dikecualikan dari pengacakan: $($ignore.Count)"

# ── Konfigurasi YAK Pro ───────────────────────────────────────────
# Nama class/method/property/fungsi/konstanta/namespace TIDAK diacak karena Laravel
# memakainya lewat string (route, Eloquent, container). Yang diacak: variabel, alur
# kode (if/loop), string, urutan statement; komentar dan indentasi dibuang.
$cnf = @"
<?php
// YAK Pro - Php Obfuscator: Config File
`$conf->t_ignore_pre_defined_classes = 'all';
`$conf->t_ignore_variables           = array($ignoreList);
`$conf->parser_mode                  = 'HOST_VERSION';
`$conf->scramble_mode                = 'identifier';
`$conf->scramble_length              = 6;
`$conf->t_obfuscate_php_extension    = array('php');
`$conf->obfuscate_constant_name      = false;
`$conf->obfuscate_variable_name      = true;
`$conf->obfuscate_function_name      = false;
`$conf->obfuscate_class_name         = false;
`$conf->obfuscate_interface_name     = false;
`$conf->obfuscate_trait_name         = false;
`$conf->obfuscate_class_constant_name= false;
`$conf->obfuscate_property_name      = false;
`$conf->obfuscate_method_name        = false;
`$conf->obfuscate_namespace_name     = false;
`$conf->obfuscate_label_name         = true;
`$conf->obfuscate_if_statement       = true;
`$conf->obfuscate_loop_statement     = true;
`$conf->obfuscate_string_literal     = true;
`$conf->shuffle_stmts                = true;
`$conf->shuffle_stmts_min_chunk_size = 3;
`$conf->shuffle_stmts_chunk_mode     = 'fixed';
`$conf->strip_indentation            = true;
`$conf->abort_on_error               = true;
`$conf->confirm                      = false;
`$conf->silent                       = true;
`$conf->allow_and_overwrite_empty_files = true;
`$conf->user_comment                 = 'Webreport - protected software. Unauthorized copying or modification is prohibited.';
?>
"@
New-Item -ItemType Directory -Path $work -Force | Out-Null
$cnfFile = Join-Path $work 'yakpro-webreport.cnf'
[System.IO.File]::WriteAllText($cnfFile, $cnf)

# ── Obfuscate ─────────────────────────────────────────────────────
# Obfuscate folder app\ (semua Controller, Service, Model, Middleware, Trait)
foreach ($dir in @('app')) {
    $src = Join-Path $stage $dir
    $out = Join-Path $work $dir
    Write-Host "==> Obfuscate $dir\"
    & $Php (Join-Path $yak 'yakpro-po.php') --config-file $cnfFile $src -o $out
    if ($LASTEXITCODE -ne 0) { throw "YAK Pro gagal pada $dir (kode $LASTEXITCODE)" }

    # YAK Pro menaruh hasil di <out>\yakpro-po\obfuscated bila -o dipakai sebagai root konteks
    $obf = Join-Path $out 'yakpro-po\obfuscated'
    if (-not (Test-Path $obf)) { $obf = $out }

    cmd /c "rmdir /s /q `"$src`""
    New-Item -ItemType Directory -Path $src -Force | Out-Null
    Copy-Item -Path (Join-Path $obf '*') -Destination $src -Recurse -Force
}

# ── Pastikan folder runtime Laravel selalu ada di dalam rilis ─────
foreach ($d in @(
    'bootstrap\cache',
    'storage\logs',
    'storage\framework\cache\data',
    'storage\framework\sessions',
    'storage\framework\views',
    'database'
)) {
    $targetDir = Join-Path $stage $d
    New-Item -ItemType Directory -Path $targetDir -Force | Out-Null
    $gi = Join-Path $targetDir '.gitignore'
    if (-not (Test-Path $gi)) {
        [System.IO.File]::WriteAllText($gi, "*`n!.gitignore`n")
    }
}
icacls (Join-Path $stage 'bootstrap\cache') /grant "Users:(OI)(CI)F" /T | Out-Null
icacls (Join-Path $stage 'storage') /grant "Users:(OI)(CI)F" /T | Out-Null

# ── Pre-compile package manifest agar bootstrap/cache tidak butuh write saat startup ─────
Write-Host "==> Pre-compile package manifest"
& $Php (Join-Path $stage 'artisan') package:discover | Out-Null

# ── Verifikasi sintaks semua file hasil obfuscate ─────────────────
Write-Host "==> Cek sintaks hasil obfuscate"
$bad = 0
Get-ChildItem (Join-Path $stage 'app') -Recurse -Filter *.php | ForEach-Object {
    $r = & $Php -l $_.FullName 2>&1
    if ($LASTEXITCODE -ne 0) { $bad++; Write-Host "   GAGAL: $($_.FullName)`n   $r" -ForegroundColor Red }
}
if ($bad -gt 0) { throw "$bad file bermasalah sintaks. Rilis dibatalkan." }

if (Test-Path $work) { cmd /c "rmdir /s /q `"$work`"" }

if (-not $NoZip) {
    $zip = Join-Path $dist ("webreport-release-{0}.zip" -f (Get-Date -Format 'yyyyMMdd'))
    Write-Host "==> Membuat arsip $zip"
    if (Test-Path $zip) { Remove-Item $zip -Force }
    Add-Type -AssemblyName System.IO.Compression.FileSystem
    [System.IO.Compression.ZipFile]::CreateFromDirectory($stage, $zip)
}

Write-Host ""
Write-Host "SELESAI. Paket rilis ada di: $stage" -ForegroundColor Green
