<?php
/**
 * Përdore këtë skedar si index.php kur Document Root është public_html
 * (dhe nuk mund ta ndryshosh në public_html/public).
 *
 * Hapat në server:
 * 1. Kopjo të gjithë përmbajtjen e public/ në public_html/ (index.php, .htaccess, build/, images/, etj.)
 * 2. Zëvendëso public_html/index.php me këtë skedar (ruaje si index.php).
 */

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

$base = __DIR__;

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = $base.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require $base.'/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once $base.'/bootstrap/app.php';

$app->handleRequest(Request::capture());
