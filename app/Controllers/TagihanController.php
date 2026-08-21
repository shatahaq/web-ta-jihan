<?php

declare(strict_types=1);

final class TagihanController extends Controller
{
    public function index(): void
    {
        Auth::requireLogin(); $search = trim((string) ($_GET['q'] ?? '')); $status = (string) ($_GET['status'] ?? ''); $page = max(1, (int) ($_GET['page'] ?? 1));
        $this->view('tagihan/index', ['title' => 'Data Tagihan', 'data' => (new Tagihan())->paginate($search, $status, $page), 'search' => $search, 'status' => $status]);
    }

    public function create(): void
    {
        Auth::requireAdmin(); $this->view('tagihan/form', ['title' => 'Tambah Tagihan', 'pelanggan' => (new Pelanggan())->customerSearch('', 100)]);
    }

    public function store(): void
    {
        Auth::requireAdmin(); $this->csrfOrFail();
        $data = $this->input(); $this->validate($data, static fn(Validator $v) => $v->required('npa', 'NPA wajib diisi.')->date('periode', 'Periode tidak valid.', false)->numeric('total_tagihan', 'Total tagihan harus berupa angka.')->in('status_bayar', ['Lunas','Belum Lunas'], 'Status bayar tidak valid.'), '/tagihan/create');
        if (!(new Pelanggan())->find($data['npa'])) { Session::flash('errors', ['npa' => 'Pelanggan tidak ditemukan.']); Session::flash('old', $data); redirect('/tagihan/create'); }
        try { (new Tagihan())->create($data); ActivityLog::log('Tambah', 'Tagihan', $data['npa'], 'Menambahkan tagihan periode ' . $data['periode'] . ' untuk NPA ' . $data['npa']); Session::flash('toast', ['type' => 'success', 'message' => 'Tagihan berhasil ditambahkan.']); redirect('/tagihan'); }
        catch (Throwable $e) { error_log((string) $e); Session::flash('toast', ['type' => 'error', 'message' => 'Tagihan gagal disimpan. Pastikan periode belum ada.']); redirect('/tagihan/create'); }
    }

    public function edit(string $id): void
    {
        Auth::requireAdmin();
        $tagihan = (new Tagihan())->find((int) $id);
        if (!$tagihan) { http_response_code(404); require root_path('app/Views/errors/404.php'); return; }
        $this->view('tagihan/form', ['title' => 'Ubah Tagihan', 'pelanggan' => (new Pelanggan())->customerSearch('', 100), 'tagihan' => $tagihan, 'action' => url('/tagihan/' . $id)]);
    }

    public function update(string $id): void
    {
        Auth::requireAdmin(); $this->csrfOrFail();
        $existing = (new Tagihan())->find((int) $id);
        if (!$existing) { http_response_code(404); require root_path('app/Views/errors/404.php'); return; }
        $data = $this->input();
        $this->validate($data, static fn(Validator $v) => $v->date('periode', 'Periode tidak valid.', false)->numeric('total_tagihan', 'Total tagihan harus berupa angka.')->in('status_bayar', ['Lunas','Belum Lunas'], 'Status bayar tidak valid.'), '/tagihan/' . $id . '/edit');
        try { (new Tagihan())->update((int) $id, $data); ActivityLog::log('Ubah', 'Tagihan', ($data['npa'] ?? $existing['npa']) . ' (#' . $id . ')', 'Memperbarui tagihan periode ' . $data['periode'] . ' — ' . $data['status_bayar']); Session::flash('toast', ['type' => 'success', 'message' => 'Tagihan berhasil diperbarui.']); redirect('/tagihan'); }
        catch (Throwable $e) { error_log((string) $e); Session::flash('toast', ['type' => 'error', 'message' => 'Tagihan gagal diperbarui. Pastikan periode belum digunakan.']); redirect('/tagihan/' . $id . '/edit'); }
    }

    private function input(): array
    {
        $status = (string) ($_POST['status_bayar'] ?? 'Belum Lunas');
        return ['npa' => trim((string) ($_POST['npa'] ?? '')), 'periode' => (string) ($_POST['periode'] ?? ''),
            'meter_awal' => ($_POST['meter_awal'] ?? '') === '' ? null : (int) $_POST['meter_awal'], 'meter_akhir' => ($_POST['meter_akhir'] ?? '') === '' ? null : (int) $_POST['meter_akhir'],
            'total_tagihan' => (float) ($_POST['total_tagihan'] ?? 0), 'status_bayar' => $status, 'tgl_bayar' => $status === 'Lunas' ? (($_POST['tgl_bayar'] ?? '') ?: date('Y-m-d')) : null];
    }
}
