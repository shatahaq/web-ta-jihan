<?php

declare(strict_types=1);

final class LaporanController extends Controller
{
    public function index(): void
    {
        Auth::requireLogin(); $filters = $this->filters();
        $this->view('laporan/index', ['title' => 'Laporan Pelanggan', 'rows' => (new Pelanggan())->report($filters), 'filters' => $filters]);
    }

    public function print(): void
    {
        Auth::requireLogin(); $filters = $this->filters();
        $this->view('laporan/print', ['title' => 'Cetak Laporan', 'rows' => (new Pelanggan())->report($filters), 'filters' => $filters], 'print');
    }

    private function filters(): array
    {
        return ['periode' => trim((string) ($_GET['periode'] ?? '')), 'status' => (string) ($_GET['status'] ?? ''), 'jenis' => (string) ($_GET['jenis'] ?? ''), 'verifikasi' => (string) ($_GET['verifikasi'] ?? '')];
    }
}
