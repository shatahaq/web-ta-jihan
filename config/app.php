<?php

declare(strict_types=1);

return [
    'name' => env('APP_NAME', 'Sistem Informasi Tirtanadi'),
    'env' => env('APP_ENV', 'development'),
    'debug' => filter_var(env('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOL),
    'url' => rtrim(env('APP_URL', 'http://localhost/web-ta/public'), '/'),
    'timezone' => env('APP_TIMEZONE', 'Asia/Jakarta'),
    'session_name' => env('SESSION_NAME', 'tirtanadi_session'),
    // Batas terpusat: tepat 60 hari masuk kategori "Nonaktif > 60 Hari".
    'nonaktif_limit_hari' => 60,
    'upload_max_bytes' => 5 * 1024 * 1024,
];
