# Checklist Pengujian Manual

Gunakan seed data dan lakukan setiap pengujian berikut sebelum deployment.

## Autentikasi dan akses

- [ ] Login `admin` / `Admin123!` berhasil dan session ID berubah.
- [ ] Login `pimpinan` / `Pimpinan123!` berhasil.
- [ ] Username atau password yang salah menampilkan pesan generik.
- [ ] Logout menghapus session dan halaman privat kembali ke login.
- [ ] Pimpinan mendapat 403 saat membuka atau mengirim POST/PUT/DELETE endpoint Admin.
- [ ] Request POST tanpa token CSRF ditolak.

## Pelanggan dan tagihan

- [ ] Admin menambah, memperbarui, mencari, dan menghapus pelanggan tanpa riwayat.
- [ ] NPA duplikat ditolak.
- [ ] Pelanggan dengan tagihan/riwayat tidak dapat dihapus.
- [ ] Filter dan pagination pelanggan bekerja untuk NPA, nama, dan alamat.
- [ ] Tagihan dapat ditambah dan diubah; kombinasi NPA + periode duplikat ditolak.
- [ ] Nominal dan total tunggakan tampil dalam format Rupiah.

## Pemutusan, pencarian, dan daftar ulang

- [ ] Admin menambah dan mengubah pemutusan dengan semua jenis tindakan.
- [ ] Pencarian NPA menampilkan kondisi Aktif, Nonaktif < batas, dan Nonaktif > batas.
- [ ] NPA tidak ditemukan menampilkan empty state tanpa reload penuh.
- [ ] Pengajuan daftar ulang hanya bisa dibuat pada pelanggan kategori Nonaktif > batas.
- [ ] File JPG/PNG/PDF ≤5 MB diterima; MIME lain dan file besar ditolak.
- [ ] Admin dapat menyetujui/menolak sekali; verifikator dan waktu tercatat.
- [ ] Persetujuan dengan opsi aktif mengubah pelanggan menjadi Aktif.

## Laporan dan keamanan

- [ ] Filter laporan menghasilkan baris yang sesuai dan reset berfungsi.
- [ ] Cetak laporan tidak menampilkan navigasi atau tombol.
- [ ] Input HTML pada data pelanggan tampil sebagai teks, bukan dieksekusi.
- [ ] Query pencarian dengan karakter SQL khusus tidak menyebabkan error atau data bocor.
- [ ] Folder `storage/uploads` tidak dapat diakses langsung dari web root.
