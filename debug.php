<?php
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/';
require __DIR__ . '/app/Helpers/app_helper.php';
$envFile = __DIR__ . '/.env';
if (is_file($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
        [$key, $value] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim(trim($value), "\"'");
    }
}
$GLOBALS['config'] = require __DIR__ . '/config/app.php';
spl_autoload_register(static function (string $class): void {
    foreach (['Core', 'Controllers', 'Models'] as $folder) {
        $file = __DIR__ . '/app/' . $folder . '/' . $class . '.php';
        if (is_file($file)) { require $file; return; }
    }
});
require __DIR__ . '/app/Helpers/format_helper.php';
$pelanggan = new Pelanggan();
print_r($pelanggan->paginate('', '', 1, 10));
