<?php
/**
 * Fshin cache-in e Laravel (view, config) pas deploy.
 * Thirret nga GitHub Actions pas ngarkimit FTP.
 * URL: https://www.arontrade.net/clear-cache.php?key=JEKODI
 * Vendos CACHE_CLEAR_KEY në .env në server (dhe në GitHub Secrets nëse e përdor në workflow).
 */
$key = $_GET['key'] ?? '';
$envFile = dirname(__DIR__) . '/.env';
$expected = 'NdryshojeKeteFjalen';
if (is_file($envFile)) {
    $env = file_get_contents($envFile);
    if (preg_match('/CACHE_CLEAR_KEY\s*=\s*(\S+)/', $env, $m)) {
        $expected = trim($m[1], '"\'');
    }
}
if ($key !== $expected || $expected === 'NdryshojeKeteFjalen') {
    http_response_code(403);
    exit('Forbidden');
}
$base = dirname(__DIR__);
$dirs = [
    $base . '/bootstrap/cache',
    $base . '/storage/framework/views',
];
$cleared = [];
foreach ($dirs as $dir) {
    if (!is_dir($dir)) continue;
    $files = glob($dir . '/*');
    foreach ($files ?: [] as $f) {
        if (is_file($f) && basename($f) !== '.gitignore') {
            @unlink($f);
            $cleared[] = $f;
        }
    }
}
// Fshij OPcache që serveri të ngarkojë kodin e ri PHP (middleware, routes) pas deploy
$opcacheReset = false;
if (function_exists('opcache_reset')) {
    $opcacheReset = @opcache_reset();
}
header('Content-Type: application/json');
echo json_encode(['ok' => true, 'cleared' => count($cleared), 'opcache_reset' => $opcacheReset], JSON_PRETTY_PRINT);
