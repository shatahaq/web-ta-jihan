<?php

declare(strict_types=1);

final class AuthController extends Controller
{
    public function login(): void
    {
        if (Auth::check()) redirect('/dashboard');
        $this->view('auth/login', ['title' => 'Masuk'], 'guest');
    }

    public function authenticate(): void
    {
        $this->csrfOrFail();
        $data = ['username' => trim((string) ($_POST['username'] ?? '')), 'password' => (string) ($_POST['password'] ?? '')];
        $this->validate($data, static fn(Validator $v) => $v->required('username', 'Username wajib diisi.')->required('password', 'Password wajib diisi.'), '/login');

        $user = (new User())->findByUsername($data['username']);
        if (!$user || !password_verify($data['password'], $user['password'])) {
            Session::flash('old', ['username' => $data['username']]);
            Session::flash('toast', ['type' => 'error', 'message' => 'Username atau password tidak sesuai.']);
            redirect('/login');
        }

        Auth::login($user);
        Session::flash('toast', ['type' => 'success', 'message' => 'Selamat datang, ' . $user['nama_lengkap'] . '.']);
        redirect('/dashboard');
    }

    public function logout(): void
    {
        $this->csrfOrFail();
        Auth::logout();
        Session::start();
        Session::flash('toast', ['type' => 'success', 'message' => 'Anda telah keluar dari sistem.']);
        redirect('/login');
    }
}
