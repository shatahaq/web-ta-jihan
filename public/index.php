<?php

declare(strict_types=1);

define('APP_START', microtime(true));

require dirname(__DIR__) . '/app/Helpers/app_helper.php';

$envFile = dirname(__DIR__) . '/.env';
if (is_file($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
        [$key, $value] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim(trim($value), "\"'");
    }
}

$GLOBALS['config'] = require dirname(__DIR__) . '/config/app.php';
date_default_timezone_set((string) config('timezone'));

spl_autoload_register(static function (string $class): void {
    foreach (['Core', 'Controllers', 'Models'] as $folder) {
        $file = dirname(__DIR__) . '/app/' . $folder . '/' . $class . '.php';
        if (is_file($file)) { require $file; return; }
    }
});
require dirname(__DIR__) . '/app/Helpers/format_helper.php';

Session::start();
$GLOBALS['old'] = Session::consumeFlash('old', []);
$GLOBALS['errors'] = Session::consumeFlash('errors', []);

set_exception_handler(static function (Throwable $exception): void {
    error_log((string) $exception);
    http_response_code(500);
    require root_path('app/Views/errors/500.php');
});

$router = new Router();
require dirname(__DIR__) . '/routes/web.php';
require dirname(__DIR__) . '/routes/api.php';
$router->dispatch();
