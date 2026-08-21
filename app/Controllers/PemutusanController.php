<?php

declare(strict_types=1);

final class PemutusanController extends Controller
{
    private Pemutusan $pemutusan;
    public function __construct() { $this->pemutusan = new Pemutusan(); }

    public function index(): void
    {
        Auth::requireLogin(); $search = trim((string) ($_GET['q'] ?? '')); $page = max(1, (int) ($_GET['page'] ?? 1));
        $this->view('pemutusan/index', ['title' => 'Data Pemutusan', 'data' => $this->pemutusan->paginate($search, $page), 'search' => $search]);
    }

    public function create(): void
    {
        Auth::requireAdmin(); $this->view('pemutusan/form', ['title' => 'Tambah Pemutusan', 'item' => null, 'action' => url('/pemutusan')]);
    }

    public function store(): void
    {
        Auth::requireAdmin(); $this->csrfOrFail(); $data = $this->input(); $this->validateInput($data, '/pemutusan/create');
        if (!(new Pelanggan())->find($data['npa'])) { Session::flash('errors', ['npa' => 'Pelanggan tidak ditemukan.']); Session::flash('old', $data); redirect('/pemutusan/create'); }
        $this->pemutusan->create($data); ActivityLog::log('Tambah', 'Pemutusan', $data['npa'], 'Menambahkan pemutusan: ' . $data['jenis_tindakan'] . ' untuk NPA ' . $data['npa']); Session::flash('toast', ['type' => 'success', 'message' => 'Data pemutusan berhasil disimpan.']); redirect('/pemutusan');
    }

    public function edit(string $id): void
    {
        Auth::requireAdmin(); $item = $this->pemutusan->find((int) $id); if (!$item) { http_response_code(404); require root_path('app/Views/errors/404.php'); return; }
        $this->view('pemutusan/form', ['title' => 'Ubah Pemutusan', 'item' => $item, 'action' => url('/pemutusan/' . $id)]);
    }

    public function update(string $id): void
    {
        Auth::requireAdmin(); $this->csrfOrFail(); $data = $this->input(); $this->validateInput($data, '/pemutusan/' . $id . '/edit');
        $this->pemutusan->update((int) $id, $data); ActivityLog::log('Ubah', 'Pemutusan', $data['npa'] . ' (#' . $id . ')', 'Memperbarui pemutusan: ' . $data['jenis_tindakan'] . ' — ' . $data['status_pemutusan']); Session::flash('toast', ['type' => 'success', 'message' => 'Data pemutusan berhasil diperbarui.']); redirect('/pemutusan');
    }

    private function input(): array
    {
        return ['npa' => trim((string) ($_POST['npa'] ?? '')), 'tgl_pemutusan' => (string) ($_POST['tgl_pemutusan'] ?? ''), 'status_pemutusan' => (string) ($_POST['status_pemutusan'] ?? ''),
            'jenis_tindakan' => (string) ($_POST['jenis_tindakan'] ?? ''), 'biaya_tindakan' => (float) ($_POST['biaya_tindakan'] ?? 0), 'keterangan' => trim((string) ($_POST['keterangan'] ?? '')) ?: null];
    }
    private function validateInput(array $data, string $back): void
    {
        $this->validate($data, static fn(Validator $v) => $v->required('npa', 'NPA wajib diisi.')->date('tgl_pemutusan', 'Tanggal pemutusan tidak valid.', false)
            ->in('status_pemutusan', ['Belum Diputus','Sudah Diputus','Selesai'], 'Status pemutusan tidak valid.')
            ->in('jenis_tindakan', ['Angkat Meter','Potong Pipa Dinas','Tutup Lubang Bor'], 'Jenis tindakan tidak valid.')
            ->numeric('biaya_tindakan', 'Biaya tindakan harus berupa angka.'), $back);
    }
}
