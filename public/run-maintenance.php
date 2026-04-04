<?php
/**
 * Mirëmbajtje pa SSH/terminal: migrime + pastrim cache skemë (p.sh. pas deploy në arontrade.net).
 *
 * 1. Në .env në server vendos (ose përdor të njëjtin si clear-cache.php):
 *    CACHE_CLEAR_KEY=ni_fjale_e_gjate_dhe_e_fshehte
 * 2. Hap në shfletues (vetëm ti):
 *    https://www.arontrade.net/run-maintenance.php?key=ni_fjale_e_gjate_dhe_e_fshehte
 * 3. Pas përdorimit, MUND ta fshish këtë skedar nga public për siguri (ose lëre me çelës të fortë).
 */

header('Content-Type: text/plain; charset=utf-8');

$key = $_GET['key'] ?? '';
$base = __DIR__;
$envFile = is_file($base.'/.env') ? $base.'/.env' : dirname($base).'/.env';
$expected = 'NdryshojeKeteFjalen';
if (is_file($envFile)) {
    $env = file_get_contents($envFile);
    if (preg_match('/CACHE_CLEAR_KEY\s*=\s*([^\s"\']+|"[^"]*"|\'[^\']*\')/', $env, $m)) {
        $expected = trim(trim($m[1], '"\''));
    }
}
if ($key === '' || $expected === 'NdryshojeKeteFjalen' || $key !== $expected) {
    http_response_code(403);
    exit('Forbidden.');
}

if (! is_file($base.'/../vendor/autoload.php')) {
    http_response_code(500);
    exit("Gabim: nuk u gjet vendor/. Ekzekuto composer install në hosting (ose ngarko vendor me FTP).\n");
}

require $base.'/../vendor/autoload.php';

/** @var \Illuminate\Foundation\Application $app */
$app = require_once $base.'/../bootstrap/app.php';

/** @var \Illuminate\Contracts\Console\Kernel $kernel */
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Artisan;

$lines = ["=== AronTrade maintenance ===", ''];

$run = function (string $cmd, array $params = []) use (&$lines) {
    $lines[] = "→ php artisan $cmd ".json_encode($params);
    try {
        $code = Artisan::call($cmd, $params);
        $out = trim(Artisan::output());
        if ($out !== '') {
            $lines[] = $out;
        }
        $lines[] = ($code === 0 ? 'OK (exit 0)' : "Exit code: $code")."\n";
    } catch (\Throwable $e) {
        $lines[] = 'GABIM: '.$e->getMessage()."\n";
    }
};

$run('migrate', ['--force' => true]);

// Pastron config/cache/routes/views (e njëvlefshme me «schema:clear» në versione të vjetra Laravel)
$run('optimize:clear');

$lines[] = '=== U krye. Mund të fshish run-maintenance.php nga public nëse do. ===';

echo implode("\n", $lines);
