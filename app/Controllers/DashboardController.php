<?php

declare(strict_types=1);

final class DashboardController extends Controller
{
    public function index(): void
    {
        Auth::requireLogin();
        $pelanggan = new Pelanggan();
        $this->view('dashboard/index', [
            'title' => 'Dashboard',
            'stats' => $pelanggan->stats(),
            'daftarUlangTerbaru' => (new DaftarUlang())->recent(),
            'pemutusanTerbaru' => (new Pemutusan())->recent(),
            'pendingCount' => (new DaftarUlang())->pendingCount(),
        ]);
    }
}
