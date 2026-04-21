<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$vendorAutoload = dirname(__DIR__) . '/vendor/autoload.php';

if (is_file($vendorAutoload)) {
    require $vendorAutoload;
}

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';

    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $relativePath = str_replace('\\', '/', $relativeClass) . '.php';
    $candidates = [
        __DIR__ . '/' . $relativePath,
        __DIR__ . '/' . lcfirst($relativePath),
    ];

    if (str_contains($relativePath, '/')) {
        [$head, $tail] = explode('/', $relativePath, 2);
        $candidates[] = __DIR__ . '/' . strtolower($head) . '/' . $tail;
    }

    foreach (array_unique($candidates) as $file) {
        if (is_file($file)) {
            require $file;
            return;
        }
    }
});

require __DIR__ . '/helpers.php';

$config = require __DIR__ . '/config/config.php';

App\Core\DB::init($config['db']);
App\Core\Auth::init($config['app']);

$router = new App\Core\Router($config['app']);

require __DIR__ . '/routes.php';
