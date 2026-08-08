-- Sistem Informasi Status Pemutusan dan Daftar Ulang Pelanggan Nonaktif
-- Tirtanadi Cabang Medan Denai | MySQL 8.x
CREATE DATABASE IF NOT EXISTS tirtanadi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE tirtanadi;

CREATE TABLE IF NOT EXISTS tb_user (
    id_user INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    role ENUM('Admin', 'Pimpinan') NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tb_pelanggan (
    npa VARCHAR(20) PRIMARY KEY,
    nama_pelanggan VARCHAR(100) NOT NULL,
    alamat TEXT NOT NULL,
    no_telepon VARCHAR(20) NULL,
    golongan VARCHAR(10) NULL,
    status ENUM('Aktif', 'Nonaktif', 'Putus') NOT NULL DEFAULT 'Aktif',
    tgl_nonaktif DATE NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_pelanggan_status (status),
    INDEX idx_pelanggan_nama (nama_pelanggan),
    INDEX idx_pelanggan_nonaktif (tgl_nonaktif)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tb_tagihan (
    id_tagihan INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    npa VARCHAR(20) NOT NULL,
    periode DATE NOT NULL,
    meter_awal INT UNSIGNED NULL,
    meter_akhir INT UNSIGNED NULL,
    total_tagihan DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    status_bayar ENUM('Lunas', 'Belum Lunas') NOT NULL DEFAULT 'Belum Lunas',
    tgl_bayar DATE NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_tagihan_npa_periode (npa, periode),
    INDEX idx_tagihan_status (status_bayar),
    CONSTRAINT fk_tagihan_pelanggan FOREIGN KEY (npa) REFERENCES tb_pelanggan(npa)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tb_pemutusan (
    id_pemutusan INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    npa VARCHAR(20) NOT NULL,
    tgl_pemutusan DATE NOT NULL,
    status_pemutusan ENUM('Belum Diputus', 'Sudah Diputus', 'Selesai') NOT NULL DEFAULT 'Belum Diputus',
    jenis_tindakan ENUM('Angkat Meter', 'Potong Pipa Dinas', 'Tutup Lubang Bor') NOT NULL,
    biaya_tindakan DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    keterangan TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_pemutusan_npa_tgl (npa, tgl_pemutusan),
    CONSTRAINT fk_pemutusan_pelanggan FOREIGN KEY (npa) REFERENCES tb_pelanggan(npa)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tb_daftar_ulang (
    no_registrasi VARCHAR(20) PRIMARY KEY,
    npa VARCHAR(20) NOT NULL,
    tgl_permohonan DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    biaya_daftar_ulang DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    bukti_lunas VARCHAR(255) NULL,
    keterangan TEXT NULL,
    status_verifikasi ENUM('Pending', 'Disetujui', 'Ditolak') NOT NULL DEFAULT 'Pending',
    tgl_verifikasi DATETIME NULL,
    id_user INT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_daftar_ulang_npa_status (npa, status_verifikasi),
    INDEX idx_daftar_ulang_tanggal (tgl_permohonan),
    CONSTRAINT fk_daftar_ulang_pelanggan FOREIGN KEY (npa) REFERENCES tb_pelanggan(npa)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_daftar_ulang_user FOREIGN KEY (id_user) REFERENCES tb_user(id_user)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
