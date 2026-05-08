<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

$_ENV['APP_BASE_PATH'] = dirname(__DIR__).'/backend';
$_ENV['COMPOSER_VENDOR_DIR'] = dirname(__DIR__).'/vendor';
putenv('COMPOSER_VENDOR_DIR='.$_ENV['COMPOSER_VENDOR_DIR']);
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['PHP_SELF'] = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = __FILE__;

if (file_exists($maintenance = $_ENV['APP_BASE_PATH'].'/storage/framework/maintenance.php')) {
    require $maintenance;
}

require dirname(__DIR__).'/vendor/autoload.php';

$app = require_once $_ENV['APP_BASE_PATH'].'/bootstrap/app.php';

$kernel = $app->make(Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);
