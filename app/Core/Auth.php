<?php

declare(strict_types=1);

final class Auth
{
    public static function check(): bool
    {
        return is_array(Session::get('user'));
    }

    public static function user(): ?array
    {
        return Session::get('user');
    }

    public static function id(): ?int
    {
        return self::user()['id_user'] ?? null;
    }

    public static function isAdmin(): bool
    {
        return self::check() && self::user()['role'] === 'Admin';
    }

    public static function login(array $user): void
    {
        session_regenerate_id(true);
        Session::put('user', [
            'id_user' => (int) $user['id_user'],
            'username' => $user['username'],
            'nama_lengkap' => $user['nama_lengkap'],
            'role' => $user['role'],
        ]);
        self::token();
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], (bool) $params['secure'], (bool) $params['httponly']);
        }
        session_destroy();
    }

    public static function token(): string
    {
        $token = Session::get('_token');
        if (!is_string($token) || $token === '') {
            $token = bin2hex(random_bytes(32));
            Session::put('_token', $token);
        }

        return $token;
    }

    public static function verifyCsrf(): bool
    {
        $token = $_POST['_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        return is_string($token) && hash_equals(self::token(), $token);
    }

    public static function requireLogin(bool $api = false): void
    {
        if (self::check()) {
            return;
        }

        if ($api) {
            Controller::json(['message' => 'Sesi Anda telah berakhir.'], 401);
        }
        redirect('/login');
    }

    public static function requireAdmin(bool $api = false): void
    {
        self::requireLogin($api);
        if (self::isAdmin()) {
            return;
        }

        if ($api) {
            Controller::json(['message' => 'Anda tidak memiliki izin untuk melakukan tindakan ini.'], 403);
        }
        http_response_code(403);
        require root_path('app/Views/errors/403.php');
        exit;
    }
}
