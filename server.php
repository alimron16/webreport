<?php

@ini_set('expose_php', '0');
@header_remove('X-Powered-By');

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/'
);

// Tolak karakter null-byte atau percobaan path traversal (.. atau backslash)
if (str_contains($uri, "\0") || str_contains($uri, '..') || str_contains($uri, '\\')) {
    http_response_code(400);
    exit('Bad Request');
}

$publicRoot = realpath(__DIR__ . '/public');
$publicFile = __DIR__ . '/public' . $uri;
$realPublicFile = realpath($publicFile);

// Pastikan file benar-benar berada di dalam direktori public/ (cegah Directory Traversal / LFI)
if (
    $uri !== '/'
    && $publicRoot !== false
    && $realPublicFile !== false
    && str_starts_with($realPublicFile, $publicRoot . DIRECTORY_SEPARATOR)
    && is_file($realPublicFile)
) {
    $mimes = [
        'png'   => 'image/png',
        'jpg'   => 'image/jpeg',
        'jpeg'  => 'image/jpeg',
        'gif'   => 'image/gif',
        'webp'  => 'image/webp',
        'svg'   => 'image/svg+xml',
        'ico'   => 'image/x-icon',
        'css'   => 'text/css; charset=utf-8',
        'js'    => 'application/javascript; charset=utf-8',
        'json'  => 'application/json',
        'woff'  => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf'   => 'font/ttf',
    ];
    $ext = strtolower(pathinfo($realPublicFile, PATHINFO_EXTENSION));

    // Hanya izinkan ekstensi aset statis yang masuk whitelist (jangan pernah kirim .php, .env, .sqlite, .log)
    if (isset($mimes[$ext])) {
        if (realpath(getcwd()) !== $publicRoot) {
            header('Content-Type: ' . $mimes[$ext]);
            header('Content-Length: ' . filesize($realPublicFile));
            header('X-Content-Type-Options: nosniff');
            header('Cache-Control: public, max-age=86400');
            readfile($realPublicFile);
            exit;
        }
        return false;
    }

    // Jika mencoba mengakses file non-statis secara langsung (selain index.php), blokir jika bukan php
    if ($ext !== 'php') {
        http_response_code(403);
        exit('Forbidden');
    }
}

require_once __DIR__ . '/public/index.php';
