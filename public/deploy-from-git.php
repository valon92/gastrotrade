<?php
/**
 * Deploy nga arontrade_git në public_html (për cPanel kur butoni Deploy nuk klikohet).
 * Hap në shfletues: https://www.arontrade.net/deploy-from-git.php?key=JEKODI_JOT
 * Ndrysho DEPLOY_KEY më poshtë dhe fshije këtë skedar pas përdorimit në production (siguria).
 */

$DEPLOY_KEY = 'NdryshojeKeteFjalen'; // vendos një fjalë/frazë sekrete dhe përdore në URL: ?key=NdryshojeKeteFjalen
if (!isset($_GET['key']) || $_GET['key'] !== $DEPLOY_KEY) {
    http_response_code(403);
    exit('Forbidden.');
}

$source = '/home/aronqbxm/arontrade_git';
$dest   = __DIR__; // public_html (kur document root është public_html) ose public_html/public

if (!is_dir($source)) {
    http_response_code(500);
    exit("Gabim: Source '$source' nuk u gjet.");
}

$dirs  = ['app', 'bootstrap', 'config', 'database', 'resources', 'routes'];
$files = ['artisan', 'composer.json', 'composer.lock', 'package.json', 'package-lock.json', 'vite.config.js', 'tailwind.config.js', 'postcss.config.js'];
$log   = [];

foreach ($dirs as $dir) {
    $src = $source . '/' . $dir;
    $dst = $dest . '/' . $dir;
    if (!is_dir($src)) continue;
    if (copyDir($src, $dst)) {
        $log[] = "OK: $dir/";
    } else {
        $log[] = "Gabim: $dir/";
    }
}

// public/* → public_html (përmbajtja e public, jo folderi public)
$publicSrc = $source . '/public';
$publicDst = $dest;
if (is_dir($publicSrc)) {
    if (copyDir($publicSrc, $publicDst)) {
        $log[] = 'OK: public/ → root';
    } else {
        $log[] = 'Gabim: public/';
    }
}

foreach ($files as $f) {
    $src = $source . '/' . $f;
    $dst = $dest . '/' . $f;
    if (file_exists($src) && copy($src, $dst)) {
        $log[] = "OK: $f";
    }
}

// Fshi cache Laravel
$cacheConfig = $dest . '/bootstrap/cache/config.php';
$cacheRoutes = glob($dest . '/bootstrap/cache/routes*.php');
if (file_exists($cacheConfig)) { @unlink($cacheConfig); $log[] = 'Cache: config.php fshirë'; }
foreach ($cacheRoutes ?: [] as $f) { @unlink($f); }
$log[] = 'Cache: routes fshirë';

header('Content-Type: text/plain; charset=utf-8');
echo "Deploy përfundoi.\n\n" . implode("\n", $log);

function copyDir($src, $dst) {
    if (!is_dir($dst)) mkdir($dst, 0755, true);
    $ok = true;
    $d = opendir($src);
    if (!$d) return false;
    while (($e = readdir($d)) !== false) {
        if ($e === '.' || $e === '..') continue;
        $s = $src . '/' . $e;
        $t = $dst . '/' . $e;
        if (is_dir($s)) {
            $ok = copyDir($s, $t) && $ok;
        } else {
            $ok = @copy($s, $t) && $ok;
        }
    }
    closedir($d);
    return $ok;
}
