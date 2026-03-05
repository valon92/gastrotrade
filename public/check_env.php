<?php
/**
 * Skriptë diagnostikuese – fshi pas përdorimit.
 * Vendose në public_html/public/check_env.php dhe hap: https://www.arontrade.net/check_env.php
 */

header('Content-Type: text/plain; charset=utf-8');

$publicDir = __DIR__;
$rootDir = dirname($publicDir);

echo "=== Kontroll i .env dhe cache ===\n\n";
echo "Rrënja e projektit (duhet të jetë ku është .env): " . $rootDir . "\n\n";

// 1. A ekziston .env?
$envPath = $rootDir . '/.env';
$envExists = file_exists($envPath);
echo "1. Skedari .env ekziston: " . ($envExists ? 'PO' : 'JO') . "\n";
if (!$envExists) {
    echo "   Rruga e kontrolluar: " . $envPath . "\n";
}

// 2. Përmbajtja e .env (vetëm rreshtat që fillojnë me APP_)
$appKeySet = false;
if ($envExists) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    echo "\n2. Rreshtat APP_* nga .env:\n";
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') continue;
        if (strpos($line, 'APP_') === 0) {
            if (stripos($line, 'APP_KEY=') === 0) {
                $val = trim(substr($line, 8));
                $appKeySet = $val !== '' && strlen($val) > 20;
                echo "   " . ($appKeySet ? 'APP_KEY=*** (e vendosur)' : $line) . "\n";
            } else {
                echo "   " . $line . "\n";
            }
        }
    }
    echo "\n   APP_KEY i plotë (base64): " . ($appKeySet ? 'PO' : 'JO') . "\n";
}

// 3. Fshi config cache nëse ekziston
$configCachePath = $rootDir . '/bootstrap/cache/config.php';
$cacheExists = file_exists($configCachePath);
echo "\n3. Cache config (bootstrap/cache/config.php) ekziston: " . ($cacheExists ? 'PO' : 'JO') . "\n";

if ($cacheExists) {
    if (@unlink($configCachePath)) {
        echo "   E FSHIRA – Laravel do të lexojë përsëri nga .env\n";
    } else {
        echo "   NUK u fshi (kontrollo lejet e folderit bootstrap/cache)\n";
    }
}

echo "\n=== Përfundim ===\n";
if ($appKeySet && !$cacheExists) {
    echo "Gjithçka duket në rregull. Rifresko faqen kryesore.\n";
} elseif ($appKeySet && $cacheExists) {
    echo "Cache u fshi. Rifresko faqen kryesore.\n";
} else {
    echo "Vendos APP_KEY=base64:... në public_html/.env dhe ruaje, pastaj rifresko këtë skedar.\n";
}

echo "\n>>> FSHI këtë skedar (check_env.php) pasi të funksionojë faqja.\n";
