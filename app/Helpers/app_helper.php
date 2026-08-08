<?php

declare(strict_types=1);

function root_path(string $path = ''): string { return dirname(__DIR__, 2) . ($path ? '/' . ltrim($path, '/') : ''); }
function env(string $key, ?string $default = null): ?string { return $_ENV[$key] ?? $_SERVER[$key] ?? $default; }
function config(?string $key = null): mixed { $config = $GLOBALS['config'] ?? []; return $key === null ? $config : ($config[$key] ?? null); }
function e(mixed $value): string { return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function url(string $path = ''): string { return rtrim((string) config('url'), '/') . '/' . ltrim($path, '/'); }
function asset(string $path): string { return url('/assets/' . ltrim($path, '/')); }
function redirect(string $path): never { header('Location: ' . (str_starts_with($path, 'http') ? $path : url($path))); exit; }
function csrf_field(): string { return '<input type="hidden" name="_token" value="' . e(Auth::token()) . '">'; }
function old(string $key, mixed $default = ''): mixed { $data = $GLOBALS['old'] ?? []; return $data[$key] ?? $default; }
function error(string $key): ?string { $errors = $GLOBALS['errors'] ?? []; return $errors[$key] ?? null; }
function is_active(string $path): bool { return str_starts_with(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '', parse_url(url($path), PHP_URL_PATH) ?: ''); }
