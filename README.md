# Sistem Informasi Tirtanadi Medan Denai

Aplikasi PHP native untuk memantau status pemutusan dan proses daftar ulang pelanggan nonaktif Perumda Tirtanadi Cabang Medan Denai. Antarmuka menggunakan Bahasa Indonesia, Tailwind CSS CDN, JavaScript ES6, dan MySQL.

## Teknologi

- PHP 8.1+ (dibangun dan diperiksa dengan PHP 8.3)
- MySQL 8.x / MariaDB kompatibel
- PDO MySQL dengan native prepared statements
- Tailwind CSS melalui CDN (tanpa Node.js, Composer, atau framework backend)
- HTML5, CSS, dan Fetch API

## Fitur

- Login, logout, session regeneration, serta role `Admin` dan `Pimpinan`
- Dashboard dengan statistik database, status pelanggan, pemutusan dan pengajuan terbaru
- CRUD pelanggan, pencarian/filter/pagination NPA/nama/alamat
- Daftar dan pengelolaan tagihan
- Riwayat tindakan pemutusan
- Pencarian NPA AJAX dengan klasifikasi status terpusat
- Pengajuan daftar ulang, unggah bukti lunas aman, persetujuan/penolakan, dan audit verifikator
- Laporan filter dan halaman cetak A4 landscape
- Endpoint JSON untuk pencarian dan integrasi AJAX

## Struktur

```text
app/
  Controllers/      HTTP request dan aturan bisnis modul
  Core/             Router, PDO, session, auth, controller, validator
  Helpers/          escape, format Rupiah/tanggal, status nonaktif
  Models/           Query database terparameterisasi
  Views/            layout dan halaman terpisah
config/             konfigurasi aplikasi dan database
database/           schema.sql dan seed.sql
public/             document root: index.php, .htaccess, asset
storage/uploads/    bukti lunas, berada di luar public/
tests/checklist.md  skenario uji manual
```

## Instalasi lokal (XAMPP/LAMP)

1. Salin konfigurasi dan sesuaikan kredensial:

   ```bash
   cp .env.example .env
   ```

   Set `APP_URL` sesuai lokasi proyek, misalnya `http://localhost/web-ta/public` bila mengakses melalui subfolder XAMPP, atau `http://tirtanadi.test` jika `public/` diarahkan sebagai virtual-host document root.

2. Buat database dan struktur tabel:

   ```bash
   mysql -u root -p < database/schema.sql
   mysql -u root -p tirtanadi < database/seed.sql
   ```

   Bila nama database berbeda, ubah `CREATE DATABASE`, `USE` pada berkas SQL, dan `DB_DATABASE` pada `.env` secara konsisten.

3. Pastikan PHP dapat menulis direktori berikut:

   ```bash
   chmod -R 775 storage/uploads storage/logs
   ```

4. Konfigurasikan Apache agar document root menunjuk ke folder `public/`. Berkas `public/.htaccess` sudah meneruskan route aplikasi ke `index.php`; aktifkan `mod_rewrite` dan izinkan `AllowOverride All` bila diperlukan.

   Untuk uji cepat tanpa Apache:

   ```bash
   php -S localhost:8000 -t public public/index.php
   ```

## Akun demo

| Role | Username | Password |
|---|---|---|
| Admin | `admin` | `Admin123!` |
| Pimpinan | `pimpinan` | `Pimpinan123!` |

Password di `seed.sql` adalah hasil `password_hash()` dan diverifikasi oleh `password_verify()`; bukan plaintext atau MD5.

## Status pelanggan

Kolom database hanya menyimpan `Aktif`, `Nonaktif`, atau `Putus`. Kategori ditentukan saat data dibaca menggunakan `kategoriStatus()` dan konfigurasi `nonaktif_limit_hari` pada `config/app.php`.

Kebijakan boundary terdokumentasi di helper: pelanggan tepat pada hari ke-60 diproses sebagai **Nonaktif > 60 Hari**. Ubah satu nilai konfigurasi untuk menyesuaikan ketentuan, tanpa menyebarkan angka batas di seluruh kode.

## Database dan relasi

```text
tb_pelanggan 1 ── N tb_tagihan
tb_pelanggan 1 ── N tb_pemutusan
tb_pelanggan 1 ── N tb_daftar_ulang N ── 1 tb_user
```

`schema.sql` memakai InnoDB, utf8mb4, foreign key, index pencarian, `DECIMAL(15,2)` untuk uang, serta unique constraint `(npa, periode)` pada tagihan.

## Hak akses

| Kemampuan | Admin | Pimpinan |
|---|---:|---:|
| Dashboard, detail, pencarian, tagihan, laporan, cetak | Ya | Ya |
| Tambah/ubah/hapus pelanggan | Ya | Tidak |
| Tambah/ubah tagihan dan pemutusan | Ya | Tidak |
| Ajukan dan verifikasi daftar ulang | Ya | Tidak |

Pembatasan diterapkan pada controller dan endpoint server; tombol UI hanya merupakan pelengkap.

## API JSON

Semua endpoint memerlukan session aktif. Endpoint yang mengubah data memerlukan role Admin dan token CSRF pada field `_token` atau header `X-CSRF-Token`.

| Method | Endpoint | Fungsi |
|---|---|---|
| GET | `/api/pelanggan/search?npa=` | Pencarian singkat NPA/nama |
| GET | `/api/pelanggan/{npa}` | Detail pelanggan |
| GET | `/api/pelanggan/{npa}/status` | Status gabungan untuk pencarian NPA |
| GET | `/api/pelanggan/{npa}/tagihan` | Riwayat tagihan |
| GET | `/api/pelanggan/{npa}/pemutusan` | Riwayat pemutusan |
| GET | `/api/pelanggan/{npa}/daftar-ulang` | Riwayat daftar ulang |
| POST/PUT/DELETE | `/api/pelanggan[/{npa}]` | CRUD pelanggan |
| POST/PUT | `/api/pemutusan[/{id}]` | Tambah/ubah pemutusan |
| POST | `/api/daftar-ulang` | Buat pengajuan |
| POST | `/api/daftar-ulang/{no}/approve` | Setujui pengajuan |
| POST | `/api/daftar-ulang/{no}/reject` | Tolak pengajuan |

## Keamanan

- Semua query menggunakan PDO prepared statement dengan emulasi prepare dimatikan.
- Password menggunakan `password_hash()`/`password_verify()`.
- Cookie session HttpOnly, SameSite Lax, dan session ID diregenerasi setelah login.
- CSRF dicek pada setiap mutasi state.
- Semua output pengguna melalui `e()`/`htmlspecialchars()`.
- Bukti lunas diperiksa memakai MIME dari server (`finfo`), dibatasi 5 MB, diberi nama acak, dan disimpan di luar `public/`.
- Error detail dicatat ke server; pengguna menerima pesan umum/halaman 403, 404, atau 500.

## Troubleshooting

- **Koneksi database gagal:** pastikan MySQL berjalan dan parameter `DB_*` di `.env` benar.
- **Route 404 di Apache:** aktifkan `mod_rewrite`, `AllowOverride All`, lalu pastikan document root adalah `public/`.
- **Unggah gagal:** berikan izin tulis untuk `storage/uploads/bukti_lunas` dan cek batas `upload_max_filesize` PHP (minimal 5M).
- **Asset/link mengarah salah:** sesuaikan `APP_URL`, tanpa slash `/` di akhir.

Lihat [tests/checklist.md](tests/checklist.md) untuk checklist QA sebelum digunakan.
