<?php

declare(strict_types=1);

final class PelangganController extends Controller
{
    private Pelanggan $pelanggan;

    public function __construct() { $this->pelanggan = new Pelanggan(); }

    public function index(): void
    {
        Auth::requireLogin();
        $search = trim((string) ($_GET['q'] ?? ''));
        $status = (string) ($_GET['status'] ?? '');
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $this->view('pelanggan/index', ['title' => 'Data Pelanggan', 'data' => $this->pelanggan->paginate($search, $status, $page), 'search' => $search, 'status' => $status]);
    }

    public function create(): void
    {
        Auth::requireAdmin();
        $this->view('pelanggan/form', ['title' => 'Tambah Pelanggan', 'pelanggan' => null, 'action' => url('/pelanggan')]);
    }

    public function store(): void
    {
        Auth::requireAdmin(); $this->csrfOrFail();
        $data = $this->validatedInput();
        if ($this->pelanggan->find($data['npa'])) {
            Session::flash('errors', ['npa' => 'NPA sudah terdaftar.']); Session::flash('old', $data); redirect('/pelanggan/create');
        }
        try {
            $this->pelanggan->create($data);
            Session::flash('toast', ['type' => 'success', 'message' => 'Data pelanggan berhasil disimpan.']); redirect('/pelanggan');
        } catch (Throwable $e) { error_log((string) $e); Session::flash('toast', ['type' => 'error', 'message' => 'Data pelanggan gagal disimpan.']); redirect('/pelanggan/create'); }
    }

    public function show(string $npa): void
    {
        Auth::requireLogin();
        $customer = $this->findOr404($npa);
        $this->view('pelanggan/show', ['title' => 'Detail Pelanggan', 'pelanggan' => $customer,
            'tagihan' => (new Tagihan())->byPelanggan($npa), 'pemutusan' => (new Pemutusan())->byPelanggan($npa),
            'daftarUlang' => (new DaftarUlang())->byPelanggan($npa), 'kategori' => kategoriStatus($customer)]);
    }

    public function edit(string $npa): void
    {
        Auth::requireAdmin();
        $this->view('pelanggan/form', ['title' => 'Ubah Pelanggan', 'pelanggan' => $this->findOr404($npa), 'action' => url('/pelanggan/' . rawurlencode($npa))]);
    }

    public function update(string $npa): void
    {
        Auth::requireAdmin(); $this->csrfOrFail(); $this->findOr404($npa);
        $data = $this->validatedInput(false); $this->pelanggan->update($npa, $data);
        Session::flash('toast', ['type' => 'success', 'message' => 'Data pelanggan berhasil diperbarui.']); redirect('/pelanggan/' . rawurlencode($npa));
    }

    public function destroy(string $npa): void
    {
        Auth::requireAdmin(); $this->csrfOrFail();
        try { $this->pelanggan->delete($npa); Session::flash('toast', ['type' => 'success', 'message' => 'Data pelanggan berhasil dihapus.']); }
        catch (Throwable $e) { error_log((string) $e); Session::flash('toast', ['type' => 'error', 'message' => 'Pelanggan tidak dapat dihapus karena memiliki riwayat transaksi.']); }
        redirect('/pelanggan');
    }

    private function validatedInput(bool $withNpa = true): array
    {
        $data = [
            'npa' => trim((string) ($_POST['npa'] ?? '')), 'nama_pelanggan' => trim((string) ($_POST['nama_pelanggan'] ?? '')),
            'alamat' => trim((string) ($_POST['alamat'] ?? '')), 'no_telepon' => trim((string) ($_POST['no_telepon'] ?? '')),
            'golongan' => trim((string) ($_POST['golongan'] ?? '')), 'status' => (string) ($_POST['status'] ?? ''),
            'tgl_nonaktif' => trim((string) ($_POST['tgl_nonaktif'] ?? '')),
        ];
        if ($data['status'] === 'Aktif') $data['tgl_nonaktif'] = null;
        $this->validate($data, static function (Validator $v) use ($withNpa): void {
            if ($withNpa) $v->required('npa', 'NPA wajib diisi.')->max('npa', 20, 'NPA maksimal 20 karakter.');
            $v->required('nama_pelanggan', 'Nama pelanggan wajib diisi.')->max('nama_pelanggan', 100, 'Nama maksimal 100 karakter.')
              ->required('alamat', 'Alamat wajib diisi.')->in('status', ['Aktif','Nonaktif','Putus'], 'Status tidak valid.')->date('tgl_nonaktif', 'Tanggal nonaktif tidak valid.');
        }, $withNpa ? '/pelanggan/create' : ($_SERVER['HTTP_REFERER'] ?? '/pelanggan'));
        if ($data['status'] !== 'Aktif' && !$data['tgl_nonaktif']) { Session::flash('errors', ['tgl_nonaktif' => 'Tanggal nonaktif wajib diisi untuk status ini.']); Session::flash('old', $data); redirect($_SERVER['HTTP_REFERER'] ?? '/pelanggan'); }
        return $data;
    }

    private function findOr404(string $npa): array
    {
        $customer = $this->pelanggan->find($npa);
        if ($customer) return $customer;
        http_response_code(404); require root_path('app/Views/errors/404.php'); exit;
    }
}
