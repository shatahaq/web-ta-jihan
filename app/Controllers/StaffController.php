<?php

declare(strict_types=1);

final class StaffController extends Controller
{
    private User $user;

    public function __construct()
    {
        $this->user = new User();
    }

    public function index(): void
    {
        Auth::requirePimpinan();
        $staff = $this->user->allStaff(Auth::id() ?? 0);
        $this->view('staff/index', ['title' => 'Kelola Staff', 'staff' => $staff]);
    }

    public function create(): void
    {
        Auth::requirePimpinan();
        $this->view('staff/form', ['title' => 'Tambah Staff Baru']);
    }

    public function store(): void
    {
        Auth::requirePimpinan();
        $this->csrfOrFail();

        $data = [
            'nama_lengkap' => trim((string) ($_POST['nama_lengkap'] ?? '')),
            'username' => trim((string) ($_POST['username'] ?? '')),
            'password' => (string) ($_POST['password'] ?? ''),
            'password_confirmation' => (string) ($_POST['password_confirmation'] ?? ''),
        ];

        $this->validate($data, static function (Validator $v) use ($data): void {
            $v->required('nama_lengkap', 'Nama lengkap wajib diisi.')
              ->max('nama_lengkap', 100, 'Nama maksimal 100 karakter.')
              ->required('username', 'Username wajib diisi.')
              ->max('username', 50, 'Username maksimal 50 karakter.')
              ->required('password', 'Password wajib diisi.');
        }, '/staff/create');

        // Check password length
        if (strlen($data['password']) < 6) {
            Session::flash('errors', ['password' => 'Password minimal 6 karakter.']);
            Session::flash('old', $data);
            redirect('/staff/create');
        }

        // Check password confirmation
        if ($data['password'] !== $data['password_confirmation']) {
            Session::flash('errors', ['password_confirmation' => 'Konfirmasi password tidak cocok.']);
            Session::flash('old', $data);
            redirect('/staff/create');
        }

        // Check username uniqueness
        if ($this->user->usernameExists($data['username'])) {
            Session::flash('errors', ['username' => 'Username sudah digunakan.']);
            Session::flash('old', $data);
            redirect('/staff/create');
        }

        try {
            $this->user->createStaff($data);
            ActivityLog::log('Tambah', 'User', $data['username'], 'Menambahkan staff baru: ' . $data['nama_lengkap'] . ' (' . $data['username'] . ')');
            Session::flash('toast', ['type' => 'success', 'message' => 'Staff baru berhasil ditambahkan.']);
            redirect('/staff');
        } catch (Throwable $e) {
            error_log((string) $e);
            Session::flash('toast', ['type' => 'error', 'message' => 'Gagal menambahkan staff baru.']);
            Session::flash('old', $data);
            redirect('/staff/create');
        }
    }

    public function destroy(string $id): void
    {
        Auth::requirePimpinan();
        $this->csrfOrFail();

        $userId = (int) $id;

        // Can't delete yourself
        if ($userId === Auth::id()) {
            Session::flash('toast', ['type' => 'error', 'message' => 'Anda tidak bisa menghapus akun Anda sendiri.']);
            redirect('/staff');
        }

        $staff = $this->user->findById($userId);
        if (!$staff || $staff['role'] !== 'Admin') {
            Session::flash('toast', ['type' => 'error', 'message' => 'Data staff tidak ditemukan.']);
            redirect('/staff');
        }

        try {
            $this->user->deleteStaff($userId);
            ActivityLog::log('Hapus', 'User', $staff['username'], 'Menghapus staff: ' . $staff['nama_lengkap'] . ' (' . $staff['username'] . ')');
            Session::flash('toast', ['type' => 'success', 'message' => 'Staff berhasil dihapus.']);
        } catch (Throwable $e) {
            error_log((string) $e);
            Session::flash('toast', ['type' => 'error', 'message' => 'Staff tidak dapat dihapus karena memiliki riwayat aktivitas.']);
        }
        redirect('/staff');
    }
}
