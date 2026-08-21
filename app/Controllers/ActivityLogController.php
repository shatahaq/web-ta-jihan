<?php

declare(strict_types=1);

final class ActivityLogController extends Controller
{
    public function index(): void
    {
        Auth::requirePimpinan();

        $search = trim((string) ($_GET['q'] ?? ''));
        $modul = (string) ($_GET['modul'] ?? '');
        $page = max(1, (int) ($_GET['page'] ?? 1));

        $log = new ActivityLog();
        $data = $log->paginate($search, $modul, $page);

        $this->view('activity_log/index', [
            'title' => 'Riwayat Aktivitas',
            'data' => $data,
            'search' => $search,
            'modul' => $modul,
        ]);
    }
}
