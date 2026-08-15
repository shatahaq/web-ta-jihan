<?php

declare(strict_types=1);

final class ApiController extends Controller
{
    public function customerSearch(): void
    {
        Auth::requireLogin(true); $term = trim((string) ($_GET['npa'] ?? $_GET['q'] ?? ''));
        self::json(['data' => $term === '' ? [] : (new Pelanggan())->customerSearch($term)]);
    }

    public function customer(string $npa): void
    {
        Auth::requireLogin(true); $customer = (new Pelanggan())->find($npa);
        if (!$customer) self::json(['message' => 'Data pelanggan tidak ditemukan.'], 404);
        self::json(['data' => $customer, 'kategori' => kategoriStatus($customer)]);
    }

    public function status(string $npa): void
    {
        Auth::requireLogin(true); $customer = (new Pelanggan())->find($npa);
        if (!$customer) self::json(['message' => 'Data pelanggan tidak ditemukan.'], 404);
        $tagihan = new Tagihan(); $pemutusan = new Pemutusan(); $daftarUlang = new DaftarUlang();
        self::json(['data' => ['pelanggan' => $customer, 'kategori' => kategoriStatus($customer), 'tagihan' => $tagihan->summary($npa), 'pemutusan' => $pemutusan->latest($npa), 'daftar_ulang' => $daftarUlang->latest($npa)]]);
    }

    public function tagihan(string $npa): void { Auth::requireLogin(true); self::json(['data' => (new Tagihan())->byPelanggan($npa)]); }
    public function pemutusan(string $npa): void { Auth::requireLogin(true); self::json(['data' => (new Pemutusan())->byPelanggan($npa)]); }
    public function daftarUlang(string $npa): void { Auth::requireLogin(true); self::json(['data' => (new DaftarUlang())->byPelanggan($npa)]); }

    public function createPelanggan(): void
    {
        Auth::requireAdmin(true); $this->csrfOrFail(true);
        $data = ['npa' => trim((string) ($_POST['npa'] ?? '')), 'nama_pelanggan' => trim((string) ($_POST['nama_pelanggan'] ?? '')), 'alamat' => trim((string) ($_POST['alamat'] ?? '')), 'no_telepon' => trim((string) ($_POST['no_telepon'] ?? '')), 'status' => (string) ($_POST['status'] ?? 'Aktif'), 'tgl_nonaktif' => ($_POST['tgl_nonaktif'] ?? '') ?: null];
        $v = new Validator($data); $v->required('npa', 'NPA wajib diisi.')->max('npa',20,'NPA maksimal 20 karakter.')->required('nama_pelanggan','Nama pelanggan wajib diisi.')->required('alamat','Alamat wajib diisi.')->in('status',['Aktif','Nonaktif','Putus'],'Status tidak valid.');
        if ($v->fails()) self::json(['message' => 'Data belum lengkap.', 'errors' => $v->errors()], 422);
        if ((new Pelanggan())->find($data['npa'])) self::json(['message' => 'NPA sudah terdaftar.'], 422);
        (new Pelanggan())->create($data); self::json(['message' => 'Data pelanggan berhasil disimpan.'], 201);
    }

    public function updatePelanggan(string $npa): void
    {
        Auth::requireAdmin(true); $this->csrfOrFail(true); $customer = new Pelanggan(); if (!$customer->find($npa)) self::json(['message' => 'Data pelanggan tidak ditemukan.'], 404);
        parse_str(file_get_contents('php://input'), $input); $data = ['nama_pelanggan' => trim((string) ($input['nama_pelanggan'] ?? '')), 'alamat' => trim((string) ($input['alamat'] ?? '')), 'no_telepon' => trim((string) ($input['no_telepon'] ?? '')), 'status' => (string) ($input['status'] ?? 'Aktif'), 'tgl_nonaktif' => ($input['tgl_nonaktif'] ?? '') ?: null];
        if ($data['status'] === 'Aktif') $data['tgl_nonaktif'] = null; $customer->update($npa, $data); self::json(['message' => 'Data pelanggan berhasil diperbarui.']);
    }

    public function deletePelanggan(string $npa): void
    {
        Auth::requireAdmin(true); $this->csrfOrFail(true);
        try { (new Pelanggan())->delete($npa); self::json(['message' => 'Data pelanggan berhasil dihapus.']); }
        catch (Throwable $e) { error_log((string) $e); self::json(['message' => 'Pelanggan memiliki riwayat transaksi dan tidak dapat dihapus.'], 422); }
    }

    public function createPemutusan(): void
    {
        Auth::requireAdmin(true); $this->csrfOrFail(true);
        $data = $this->pemutusanInput($_POST);
        $v = new Validator($data); $v->required('npa', 'NPA wajib diisi.')->date('tgl_pemutusan', 'Tanggal pemutusan tidak valid.', false)
            ->in('status_pemutusan', ['Belum Diputus','Sudah Diputus','Selesai'], 'Status pemutusan tidak valid.')
            ->in('jenis_tindakan', ['Angkat Meter','Potong Pipa Dinas','Tutup Lubang Bor'], 'Jenis tindakan tidak valid.')
            ->numeric('biaya_tindakan', 'Biaya tindakan harus berupa angka.');
        if ($v->fails()) self::json(['message' => 'Data belum lengkap.', 'errors' => $v->errors()], 422);
        if (!(new Pelanggan())->find($data['npa'])) self::json(['message' => 'Data pelanggan tidak ditemukan.'], 404);
        (new Pemutusan())->create($data); self::json(['message' => 'Data pemutusan berhasil disimpan.'], 201);
    }

    public function updatePemutusan(string $id): void
    {
        Auth::requireAdmin(true); $this->csrfOrFail(true); $input = $this->requestInput();
        $model = new Pemutusan(); if (!$model->find((int) $id)) self::json(['message' => 'Data pemutusan tidak ditemukan.'], 404);
        $data = $this->pemutusanInput($input);
        $v = new Validator($data); $v->date('tgl_pemutusan', 'Tanggal pemutusan tidak valid.', false)->in('status_pemutusan', ['Belum Diputus','Sudah Diputus','Selesai'], 'Status pemutusan tidak valid.')->in('jenis_tindakan', ['Angkat Meter','Potong Pipa Dinas','Tutup Lubang Bor'], 'Jenis tindakan tidak valid.')->numeric('biaya_tindakan', 'Biaya tindakan harus berupa angka.');
        if ($v->fails()) self::json(['message' => 'Data tidak valid.', 'errors' => $v->errors()], 422);
        $model->update((int) $id, $data); self::json(['message' => 'Data pemutusan berhasil diperbarui.']);
    }

    public function createDaftarUlang(): void
    {
        Auth::requireAdmin(true); $this->csrfOrFail(true);
        $input = $_POST; $npa = trim((string) ($input['npa'] ?? ''));
        $data = ['npa' => $npa, 'biaya_daftar_ulang' => $input['biaya_daftar_ulang'] ?? '', 'keterangan' => trim((string) ($input['keterangan'] ?? '')) ?: null];
        $v = new Validator($data); $v->required('npa','NPA wajib diisi.')->numeric('biaya_daftar_ulang','Biaya daftar ulang harus berupa angka.');
        if ($v->fails()) self::json(['message' => 'Data belum lengkap.', 'errors' => $v->errors()], 422);
        $customer = (new Pelanggan())->find($npa); if (!$customer) self::json(['message' => 'Data pelanggan tidak ditemukan.'], 404);
        if (kategoriStatus($customer)['key'] !== 'nonaktif_lama') self::json(['message' => 'Pelanggan belum memenuhi ketentuan daftar ulang.'], 422);
        $model = new DaftarUlang(); $latest = $model->latest($npa); if ($latest && $latest['status_verifikasi'] === 'Pending') self::json(['message' => 'Masih ada pengajuan yang menunggu verifikasi.'], 422);
        do { $registration = 'DU-' . date('Y') . '-' . strtoupper(bin2hex(random_bytes(3))); } while ($model->find($registration));
        $data['no_registrasi'] = $registration; $data['biaya_daftar_ulang'] = (float) $data['biaya_daftar_ulang']; $data['bukti_lunas'] = $this->apiUploadProof();
        $model->create($data); self::json(['message' => 'Pengajuan daftar ulang berhasil dikirim.', 'data' => ['no_registrasi' => $data['no_registrasi']]], 201);
    }

    public function approveDaftarUlang(string $registration): void
    {
        Auth::requireAdmin(true); $this->csrfOrFail(true);
        $active = ($_POST['aktifkan_pelanggan'] ?? '1') === '1';
        if (!(new DaftarUlang())->verify($registration, 'Disetujui', (int) Auth::id(), $active)) self::json(['message' => 'Pengajuan tidak dapat diverifikasi.'], 422);
        self::json(['message' => 'Pengajuan berhasil disetujui.']);
    }

    public function rejectDaftarUlang(string $registration): void
    {
        Auth::requireAdmin(true); $this->csrfOrFail(true);
        if (!(new DaftarUlang())->verify($registration, 'Ditolak', (int) Auth::id())) self::json(['message' => 'Pengajuan tidak dapat diverifikasi.'], 422);
        self::json(['message' => 'Pengajuan berhasil ditolak.']);
    }

    private function requestInput(): array
    {
        $input = $_POST;
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') parse_str((string) file_get_contents('php://input'), $input);
        return $input;
    }

    private function pemutusanInput(array $input): array
    {
        return ['npa' => trim((string) ($input['npa'] ?? '')), 'tgl_pemutusan' => (string) ($input['tgl_pemutusan'] ?? ''), 'status_pemutusan' => (string) ($input['status_pemutusan'] ?? ''), 'jenis_tindakan' => (string) ($input['jenis_tindakan'] ?? ''), 'biaya_tindakan' => (float) ($input['biaya_tindakan'] ?? 0), 'keterangan' => trim((string) ($input['keterangan'] ?? '')) ?: null];
    }

    private function apiUploadProof(): ?string
    {
        if (!isset($_FILES['bukti_lunas']) || $_FILES['bukti_lunas']['error'] === UPLOAD_ERR_NO_FILE) return null;
        $file = $_FILES['bukti_lunas'];
        if ($file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) self::json(['message' => 'Bukti lunas gagal diunggah.'], 422);
        if ((int) $file['size'] > (int) config('upload_max_bytes')) self::json(['message' => 'Ukuran file maksimal 5 MB.'], 422);
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']); $extensions = ['image/jpeg'=>'jpg','image/png'=>'png','application/pdf'=>'pdf'];
        if (!isset($extensions[$mime])) self::json(['message' => 'Format file tidak didukung.'], 422);
        $name = bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
        if (!move_uploaded_file($file['tmp_name'], root_path('storage/uploads/bukti_lunas/' . $name))) self::json(['message' => 'Bukti lunas gagal disimpan.'], 500);
        return $name;
    }
}
