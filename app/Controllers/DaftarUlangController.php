<?php

declare(strict_types=1);

final class DaftarUlangController extends Controller
{
    private DaftarUlang $daftarUlang;
    public function __construct() { $this->daftarUlang = new DaftarUlang(); }

    public function index(): void
    {
        Auth::requireLogin(); $status = (string) ($_GET['status'] ?? ''); $page = max(1, (int) ($_GET['page'] ?? 1));
        $this->view('daftar_ulang/index', ['title' => 'Daftar Ulang', 'data' => $this->daftarUlang->paginate($status, $page), 'status' => $status]);
    }

    public function create(): void
    {
        Auth::requireAdmin();
        $npa = trim((string) ($_GET['npa'] ?? ''));
        $customer = $npa ? (new Pelanggan())->find($npa) : null;
        $this->view('daftar_ulang/form', ['title' => 'Pengajuan Daftar Ulang', 'pelanggan' => $customer]);
    }

    public function store(): void
    {
        Auth::requireAdmin(); $this->csrfOrFail();
        $npa = trim((string) ($_POST['npa'] ?? ''));
        $biaya = $_POST['biaya_daftar_ulang'] ?? '';
        $data = ['npa' => $npa, 'biaya_daftar_ulang' => $biaya, 'keterangan' => trim((string) ($_POST['keterangan'] ?? '')) ?: null];
        $this->validate($data, static fn(Validator $v) => $v->required('npa', 'NPA wajib diisi.')->numeric('biaya_daftar_ulang', 'Biaya daftar ulang harus berupa angka.'), '/daftar-ulang/create');
        $customer = (new Pelanggan())->find($npa);
        if (!$customer) { Session::flash('errors', ['npa' => 'Data pelanggan tidak ditemukan.']); Session::flash('old', $data); redirect('/daftar-ulang/create'); }
        if (kategoriStatus($customer)['key'] !== 'nonaktif_lama') { Session::flash('toast', ['type' => 'warning', 'message' => 'Pengajuan daftar ulang hanya tersedia untuk pelanggan nonaktif sesuai batas hari.']); redirect('/pelanggan/' . rawurlencode($npa)); }
        $latest = $this->daftarUlang->latest($npa);
        if ($latest && $latest['status_verifikasi'] === 'Pending') { Session::flash('toast', ['type' => 'warning', 'message' => 'Pelanggan masih memiliki pengajuan yang menunggu verifikasi.']); redirect('/daftar-ulang/' . rawurlencode($latest['no_registrasi'])); }

        try {
            $data['no_registrasi'] = $this->registrationNumber();
            $data['biaya_daftar_ulang'] = (float) $data['biaya_daftar_ulang'];
            $data['bukti_lunas'] = $this->uploadProof();
            $this->daftarUlang->create($data);
            ActivityLog::log('Tambah', 'Daftar Ulang', $data['no_registrasi'], 'Mengajukan daftar ulang untuk NPA ' . $npa);
            Session::flash('toast', ['type' => 'success', 'message' => 'Pengajuan daftar ulang berhasil dikirim dan menunggu verifikasi.']); redirect('/daftar-ulang/' . rawurlencode($data['no_registrasi']));
        } catch (RuntimeException $e) { Session::flash('toast', ['type' => 'error', 'message' => $e->getMessage()]); Session::flash('old', $data); redirect('/daftar-ulang/create?npa=' . rawurlencode($npa)); }
        catch (Throwable $e) { error_log((string) $e); Session::flash('toast', ['type' => 'error', 'message' => 'Pengajuan gagal disimpan.']); redirect('/daftar-ulang/create?npa=' . rawurlencode($npa)); }
    }

    public function show(string $registration): void
    {
        Auth::requireLogin(); $item = $this->daftarUlang->find($registration);
        if (!$item) { http_response_code(404); require root_path('app/Views/errors/404.php'); return; }
        $this->view('daftar_ulang/show', ['title' => 'Detail Daftar Ulang', 'item' => $item]);
    }

    public function approve(string $registration): void
    {
        Auth::requireAdmin(); $this->csrfOrFail();
        $activate = ($_POST['aktifkan_pelanggan'] ?? '1') === '1';
        if (!$this->daftarUlang->verify($registration, 'Disetujui', (int) Auth::id(), $activate)) {
            Session::flash('toast', ['type' => 'warning', 'message' => 'Pengajuan tidak dapat diverifikasi. Status mungkin sudah berubah.']);
        } else {
            ActivityLog::log('Ubah', 'Daftar Ulang', $registration, 'Menyetujui pengajuan daftar ulang' . ($activate ? ' dan mengaktifkan pelanggan' : ''));
            Session::flash('toast', ['type' => 'success', 'message' => 'Pengajuan disetujui' . ($activate ? ' dan status pelanggan telah diaktifkan.' : '.')]);
        }
        redirect('/daftar-ulang/' . rawurlencode($registration));
    }

    public function reject(string $registration): void
    {
        Auth::requireAdmin(); $this->csrfOrFail();
        $ok = $this->daftarUlang->verify($registration, 'Ditolak', (int) Auth::id());
        if ($ok) ActivityLog::log('Ubah', 'Daftar Ulang', $registration, 'Menolak pengajuan daftar ulang');
        Session::flash('toast', $ok ? ['type' => 'success', 'message' => 'Pengajuan daftar ulang ditolak.'] : ['type' => 'warning', 'message' => 'Pengajuan tidak dapat diverifikasi.']);
        redirect('/daftar-ulang/' . rawurlencode($registration));
    }

    public function bukti(string $registration): void
    {
        Auth::requireLogin(); $item = $this->daftarUlang->find($registration);
        $file = $item['bukti_lunas'] ?? null;
        $path = $file ? root_path('storage/uploads/bukti_lunas/' . basename($file)) : '';
        if (!$file || !is_file($path)) { http_response_code(404); require root_path('app/Views/errors/404.php'); return; }
        $mime = mime_content_type($path) ?: 'application/octet-stream';
        header('Content-Type: ' . $mime); header('Content-Length: ' . (string) filesize($path)); header('Content-Disposition: inline; filename="bukti-lunas-' . e($registration) . '.' . pathinfo($path, PATHINFO_EXTENSION) . '"');
        readfile($path); exit;
    }

    private function registrationNumber(): string
    {
        do { $number = 'DU-' . date('Y') . '-' . strtoupper(bin2hex(random_bytes(3))); } while ($this->daftarUlang->find($number));
        return $number;
    }

    private function uploadProof(): ?string
    {
        if (!isset($_FILES['bukti_lunas']) || $_FILES['bukti_lunas']['error'] === UPLOAD_ERR_NO_FILE) return null;
        $file = $_FILES['bukti_lunas'];
        if ($file['error'] !== UPLOAD_ERR_OK) throw new RuntimeException('Bukti lunas gagal diunggah.');
        if ((int) $file['size'] > (int) config('upload_max_bytes')) throw new RuntimeException('Ukuran file maksimal 5 MB.');
        if (!is_uploaded_file($file['tmp_name'])) throw new RuntimeException('Berkas unggahan tidak valid.');
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'application/pdf' => 'pdf'];
        if (!isset($extensions[$mime])) throw new RuntimeException('Format file tidak didukung. Gunakan JPG, PNG, atau PDF.');
        $filename = bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
        $target = root_path('storage/uploads/bukti_lunas/' . $filename);
        if (!move_uploaded_file($file['tmp_name'], $target)) throw new RuntimeException('Bukti lunas gagal disimpan.');
        return $filename;
    }
}
