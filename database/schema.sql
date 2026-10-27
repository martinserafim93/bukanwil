-- ==========================================================
-- BUKANWIL — Skema Database MySQL 8.0 / MariaDB
-- Buku Tamu Kanwil Kemenag Kaltara
-- Kanwil Kemenag Kaltara
-- Versi: 1.0.0 | 2026-10-18
-- ==========================================================

SET NAMES utf8mb4;
SET time_zone = '+08:00'; -- WITA (UTC+8)

-- ----------------------------------------------------------
-- Tabel: tamu
-- Menyimpan setiap entri buku tamu dari form publik.
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tamu` (
    `id`               BIGINT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `nama`             VARCHAR(255)        NOT NULL,
    `jenis_kelamin`    ENUM('Laki-laki','Perempuan') NOT NULL,
    `asal_pengunjung`  ENUM('perorangan','instansi','lembaga') NOT NULL,
    -- Wajib di level aplikasi bila asal_pengunjung IN ('instansi','lembaga')
    `nama_instansi`    VARCHAR(255)        NULL     DEFAULT NULL,
    `alamat`           TEXT                NOT NULL,
    `no_hp`            VARCHAR(20)         NOT NULL,
    `keperluan`        TEXT                NOT NULL,
    -- Path relatif dari public/: contoh uploads/foto/a3f8b2c1.jpg
    `foto`             VARCHAR(500)        NULL     DEFAULT NULL,
    -- 0 = belum dibaca admin; 1 = sudah dibaca
    `is_read`          TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
    -- 0 = aktif di meja kerja; 1 = diarsipkan (riwayat lampau)
    `is_archived`      TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
    `created_at`       DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
    -- NULL = data sah; NOT NULL = dihapus ke tong sampah (soft delete)
    `deleted_at`       DATETIME            NULL     DEFAULT NULL,

    PRIMARY KEY (`id`),
    INDEX `idx_tamu_created_at`    (`created_at`),
    INDEX `idx_tamu_is_read`       (`is_read`),
    INDEX `idx_tamu_is_archived`   (`is_archived`),
    INDEX `idx_tamu_deleted_at`    (`deleted_at`),
    INDEX `idx_tamu_jenis_kelamin` (`jenis_kelamin`),
    INDEX `idx_tamu_asal`          (`asal_pengunjung`)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Entri buku tamu PTSP';


-- ----------------------------------------------------------
-- Tabel: admin
-- User yang dapat mengakses CMS.
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admin` (
    `id`         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `nama`       VARCHAR(255)  NOT NULL,
    `username`   VARCHAR(100)  NOT NULL,
    -- Disimpan sebagai bcrypt hash (password_hash PHP)
    `password`   VARCHAR(255)  NOT NULL,
    `role`       ENUM('superadmin','admin') NOT NULL DEFAULT 'admin',
    `created_at` DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP
                               ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_admin_username` (`username`)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Akun admin CMS';


-- ----------------------------------------------------------
-- Tabel: pengaturan
-- Key-value store untuk konfigurasi tampilan aplikasi.
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `pengaturan` (
    `id`    INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `key`   VARCHAR(100) NOT NULL,
    `value` TEXT         NOT NULL,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_pengaturan_key` (`key`)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Konfigurasi tampilan aplikasi';


-- ==========================================================
-- MIGRASI untuk database yang SUDAH berjalan
-- Jalankan manual (mis. phpMyAdmin) hanya bila tabel `tamu` sudah ada
-- dari versi sebelumnya. Instalasi baru: cukup jalankan DDL di atas.
--
-- Perubahan 1: nilai enum `asal_pengunjung` ditambah 'lembaga'.
-- Aman dijalankan berulang (idempoten) — nilai lama tidak berubah,
-- hanya daftar nilai yang diizinkan bertambah.
-- ----------------------------------------------------------
-- ALTER TABLE `tamu`
--     MODIFY `asal_pengunjung` ENUM('perorangan','instansi','lembaga') NOT NULL;
--
-- Perubahan 2 (2026-10-18): hapus kolom lat dan lng (fitur koordinat dihapus).
-- Aman dijalankan berulang — bila kolom sudah tidak ada, perintah gagal tanpa
-- merusak tabel. Jalankan satu per satu bila ingin menangani error dengan aman.
-- ----------------------------------------------------------
-- ALTER TABLE `tamu` DROP COLUMN `lat`;
-- ALTER TABLE `tamu` DROP COLUMN `lng`;
-- ==========================================================


-- ==========================================================
-- SEED DATA
-- ==========================================================

-- Seed pengaturan default
INSERT INTO `pengaturan` (`key`, `value`) VALUES
    ('judul_aplikasi',      'BUKANWIL'),
    ('deskripsi_aplikasi',  'Buku Tamu Kanwil Kemenag Kaltara'),
    ('nama_instansi_ptsp',  'Kantor Wilayah Kementerian Agama Provinsi Kalimantan Utara'),
    ('teks_header',         'Selamat datang. Silakan isi buku tamu sebelum melanjutkan pelayanan.')
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);

-- Seed akun superadmin awal
-- Username: superadmin | Password: Admin@2026 (WAJIB diganti saat login pertama)
-- Hash bcrypt cost-12: password_hash('Admin@2026', PASSWORD_BCRYPT, ['cost'=>12])
INSERT INTO `admin` (`nama`, `username`, `password`, `role`) VALUES
    (
        'Super Administrator',
        'superadmin',
        '$2y$12$fKjjLr/bP9PzVGfcJNoTgOTFro.mRnDJlqyd6n7MC9VzM2nvBoeiW',
        'superadmin'
    )
ON DUPLICATE KEY UPDATE `id` = `id`;
