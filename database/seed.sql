USE tirtanadi;

-- Semua password di bawah telah dibuat dengan password_hash() PHP.
INSERT INTO tb_user (id_user, username, password, nama_lengkap, role) VALUES
(1, 'admin', '$2y$10$UNfpQJhb6uR58OKIppb/C.donauV8zlysLB6.AF39wLat6NcaeCxy', 'Admin Hublang', 'Admin'),
(2, 'admin.denai', '$2y$10$UNfpQJhb6uR58OKIppb/C.donauV8zlysLB6.AF39wLat6NcaeCxy', 'Nadya Lahfah', 'Admin'),
(3, 'pimpinan', '$2y$10$WUo/agn9cv2.nVNP8r9uP.7BE7zzOfScz1zxa7Mbv0Fxqbmj6EFrW', 'Pimpinan Cabang', 'Pimpinan'),
(4, 'pimpinan.operasional', '$2y$10$WUo/agn9cv2.nVNP8r9uP.7BE7zzOfScz1zxa7Mbv0Fxqbmj6EFrW', 'Rizky Wijaya', 'Pimpinan'),
(5, 'pimpinan.layanan', '$2y$10$WUo/agn9cv2.nVNP8r9uP.7BE7zzOfScz1zxa7Mbv0Fxqbmj6EFrW', 'Sari Handayani', 'Pimpinan')
ON DUPLICATE KEY UPDATE username = VALUES(username);

INSERT INTO tb_pelanggan (npa, nama_pelanggan, alamat, no_telepon, status, tgl_nonaktif) VALUES
('1201000001', 'Budi Santoso', 'Jl. Pelajar No. 12, Medan Denai', '081260000001', 'Aktif', NULL),
('1201000002', 'Maya Sari', 'Jl. Tangguk Bongkar X, Medan Denai', '081260000002', 'Aktif', NULL),
('1201000003', 'Andi Pratama', 'Jl. Pancasila No. 34, Medan Denai', '081260000003', 'Nonaktif', DATE_SUB(CURDATE(), INTERVAL 21 DAY)),
('1201000004', 'Dewi Lestari', 'Jl. Denai No. 87, Medan Denai', '081260000004', 'Nonaktif', DATE_SUB(CURDATE(), INTERVAL 75 DAY)),
('1201000005', 'Rudi Hartono', 'Jl. Seksama No. 9, Medan Denai', '081260000005', 'Nonaktif', DATE_SUB(CURDATE(), INTERVAL 90 DAY)),
('1201000006', 'Intan Permata', 'Jl. Panglima Denai No. 45, Medan Denai', '081260000006', 'Aktif', NULL),
('1201000007', 'Fajar Ramadhan', 'Jl. Menteng VII, Medan Denai', '081260000007', 'Putus', DATE_SUB(CURDATE(), INTERVAL 66 DAY)),
('1201000008', 'Nina Kurnia', 'Jl. AR Hakim No. 111, Medan Denai', '081260000008', 'Nonaktif', DATE_SUB(CURDATE(), INTERVAL 12 DAY)),
('1201000009', 'Agus Salim', 'Jl. Pasar Merah, Medan Denai', '081260000009', 'Aktif', NULL),
('1201000010', 'Citra Amelia', 'Jl. Garu II, Medan Denai', '081260000010', 'Nonaktif', DATE_SUB(CURDATE(), INTERVAL 61 DAY))
ON DUPLICATE KEY UPDATE nama_pelanggan = VALUES(nama_pelanggan);

INSERT INTO tb_tagihan (npa, periode, meter_awal, meter_akhir, total_tagihan, status_bayar, tgl_bayar) VALUES
('1201000001', DATE_FORMAT(CURDATE(), '%Y-%m-01'), 120, 135, 145000.00, 'Belum Lunas', NULL),
('1201000001', DATE_SUB(DATE_FORMAT(CURDATE(), '%Y-%m-01'), INTERVAL 1 MONTH), 105, 120, 140000.00, 'Lunas', DATE_SUB(CURDATE(), INTERVAL 24 DAY)),
('1201000002', DATE_FORMAT(CURDATE(), '%Y-%m-01'), 62, 71, 112000.00, 'Lunas', CURDATE()),
('1201000006', DATE_FORMAT(CURDATE(), '%Y-%m-01'), 202, 223, 181000.00, 'Belum Lunas', NULL),
('1201000009', DATE_FORMAT(CURDATE(), '%Y-%m-01'), 51, 58, 98000.00, 'Belum Lunas', NULL)
ON DUPLICATE KEY UPDATE total_tagihan = VALUES(total_tagihan);

INSERT INTO tb_pemutusan (npa, tgl_pemutusan, status_pemutusan, jenis_tindakan, biaya_tindakan, keterangan) VALUES
('1201000003', DATE_SUB(CURDATE(), INTERVAL 18 DAY), 'Sudah Diputus', 'Angkat Meter', 150000.00, 'Tunggakan telah melewati masa pemberitahuan.'),
('1201000004', DATE_SUB(CURDATE(), INTERVAL 70 DAY), 'Selesai', 'Potong Pipa Dinas', 275000.00, 'Tindakan selesai di lapangan.'),
('1201000005', DATE_SUB(CURDATE(), INTERVAL 85 DAY), 'Selesai', 'Tutup Lubang Bor', 200000.00, 'Penutupan sambungan sesuai prosedur.'),
('1201000008', DATE_SUB(CURDATE(), INTERVAL 8 DAY), 'Belum Diputus', 'Angkat Meter', 150000.00, 'Menunggu jadwal petugas.'),
('1201000010', DATE_SUB(CURDATE(), INTERVAL 56 DAY), 'Selesai', 'Angkat Meter', 150000.00, 'Tindakan pemutusan selesai.')
ON DUPLICATE KEY UPDATE biaya_tindakan = VALUES(biaya_tindakan);

INSERT INTO tb_daftar_ulang (no_registrasi, npa, tgl_permohonan, biaya_daftar_ulang, bukti_lunas, keterangan, status_verifikasi, tgl_verifikasi, id_user) VALUES
('DU-2026-0001', '1201000004', DATE_SUB(NOW(), INTERVAL 7 DAY), 350000.00, NULL, 'Permohonan sambungan kembali.', 'Pending', NULL, NULL),
('DU-2026-0002', '1201000005', DATE_SUB(NOW(), INTERVAL 20 DAY), 400000.00, NULL, 'Dokumen telah dilengkapi.', 'Disetujui', DATE_SUB(NOW(), INTERVAL 18 DAY), 1),
('DU-2026-0003', '1201000010', DATE_SUB(NOW(), INTERVAL 14 DAY), 350000.00, NULL, 'Mohon verifikasi ulang.', 'Ditolak', DATE_SUB(NOW(), INTERVAL 12 DAY), 2)
ON DUPLICATE KEY UPDATE biaya_daftar_ulang = VALUES(biaya_daftar_ulang);
-- Dummy Data Pelanggan (Aktif & Putus)

-- Pelanggan Putus (Dari Laporan Pemutusan)
INSERT IGNORE INTO tb_pelanggan (npa, nama_pelanggan, alamat, status, tgl_nonaktif) VALUES
('0306150016', 'YARNIAR SINAGA, DRS', 'MA SELATAN Gg SEHATI 4', 'Putus', '2025-07-31'),
('0305220012', 'H MOHD SALEH', 'MA SELATAN Gg PENGHULU 18-A', 'Putus', '2025-07-31'),
('0305280010', 'YULIANI SIREGAR', 'MA SELATAN Gg MANGUN 3-A', 'Putus', '2025-07-31'),
('0305410005', 'ASIAH', 'A RAHMAN HAKIM Gg SEKAWAN/HM SALEH 380-F/11-B', 'Putus', '2025-07-31'),
('0309320029', 'DRS WESLY HUTABARAT MSC', 'RAJAWALI I 13-QQ', 'Putus', '2025-10-30'),
('0325660038', 'JAFAAR', 'MENTENG II Gg PEMBANGUNAN 15-B', 'Putus', '2025-10-30');

-- Data Pemutusan
INSERT IGNORE INTO tb_pemutusan (npa, tgl_pemutusan, status_pemutusan, jenis_tindakan, biaya_tindakan, keterangan) VALUES
('0306150016', '2025-07-31', 'Selesai', 'Potong Pipa Dinas', 50000, 'TRA 6 BULAN, PD'),
('0305220012', '2025-07-31', 'Selesai', 'Potong Pipa Dinas', 50000, 'TRA 6 BULAN, DINAS DI DOP METER HILANG'),
('0305280010', '2025-07-31', 'Selesai', 'Potong Pipa Dinas', 50000, 'TRA 6 BULAN, PD METER HILANG'),
('0305410005', '2025-07-31', 'Selesai', 'Angkat Meter', 50000, 'TRA 10 BULAN, AM ST: 0006.95 MELINDO'),
('0309320029', '2025-10-30', 'Selesai', 'Potong Pipa Dinas', 50000, 'TRA 6 BULAN, PD'),
('0325660038', '2025-10-30', 'Selesai', 'Angkat Meter', 50000, 'TRA 6 BULAN, AM ST: 6717 LINFLOW');

-- Pelanggan Aktif (Dari Daftar Saldo Tunggakan Rekening Air Aktif)
INSERT IGNORE INTO tb_pelanggan (npa, nama_pelanggan, alamat, status) VALUES
('0301010177', 'JOHNY', 'ASIA BLOK-G 13/12-A', 'N.2', 'Aktif'),
('0301030001', 'AMAT ALI', 'AKIK 47-A', 'RT.5', 'Aktif'),
('0301050046', 'SELAMAT', 'AKIK 191-E/55-D', 'RT.5', 'Aktif'),
('0301050067', 'JENNY', 'SUTRISNO DALAM 222-F/432-', 'N.3', 'Aktif');

-- Data Tagihan Dummy (Untuk Pelanggan Aktif yang Memiliki Tunggakan 08-2025)
INSERT IGNORE INTO tb_tagihan (npa, periode, meter_awal, meter_akhir, total_tagihan, status_bayar) VALUES
('0301010177', '2025-08-01', 1000, 1020, 241915.60, 'Belum Lunas'),
('0301030001', '2025-08-01', 1500, 1505, 7000.00, 'Belum Lunas'),
('0301050046', '2025-08-01', 2000, 2010, 7000.00, 'Belum Lunas'),
('0301050067', '2025-08-01', 2500, 2520, 6000.00, 'Belum Lunas');
