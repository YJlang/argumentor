<?php

declare(strict_types=1);

// PHP 내장 서버(php -S)에서 실제 정적 파일은 그대로 서빙
if (PHP_SAPI === 'cli-server') {
    $file = __DIR__ . (string) parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (is_file($file)) {
        return false;
    }
}

require __DIR__ . '/../vendor/autoload.php';

use App\Support\Env;
use App\Support\Router;

Env::load(__DIR__ . '/../.env');

$debug = Env::get('APP_DEBUG', 'false') === 'true';
error_reporting(E_ALL);
ini_set('display_errors', $debug ? '1' : '0');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$router = new Router();
require __DIR__ . '/../src/routes.php';

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
