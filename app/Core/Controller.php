<?php

declare(strict_types=1);

abstract class Controller
{
    protected function view(string $view, array $viewData = [], string $layout = 'app'): void
    {
        $viewFile = root_path('app/Views/' . $view . '.php');
        if (!is_file($viewFile)) {
            throw new RuntimeException('Tampilan tidak ditemukan.');
        }

        extract($viewData, EXTR_SKIP);
        $contentView = $viewFile;
        require root_path('app/Views/layouts/' . $layout . '.php');
    }

    protected function csrfOrFail(bool $api = false): void
    {
        if (Auth::verifyCsrf()) return;
        if ($api) self::json(['message' => 'Token keamanan tidak valid. Muat ulang halaman dan coba kembali.'], 419);
        Session::flash('toast', ['type' => 'error', 'message' => 'Sesi formulir telah berakhir. Silakan coba kembali.']);
        redirect($_SERVER['HTTP_REFERER'] ?? '/dashboard');
    }

    protected function validate(array $data, callable $rules, string $back): array
    {
        $validator = new Validator($data);
        $rules($validator);
        if (!$validator->fails()) return $data;
        Session::flash('errors', $validator->errors());
        Session::flash('old', $data);
        redirect($back);
    }

    public static function json(array $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
