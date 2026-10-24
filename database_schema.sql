-- ============================================================================
-- SKRIP BASIS DATA: pemetaan_usaha_pariwisata
-- Sistem Informasi Geografis Pemetaan dan Klasterisasi Usaha Pariwisata
-- Kota Palembang Menggunakan Algoritma DBSCAN
-- Karakteristik: MySQL / MariaDB (XAMPP), 3NF Normalized, Zero Emoji
-- ============================================================================

CREATE DATABASE IF NOT EXISTS `pemetaan_usaha_pariwisata`
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE `pemetaan_usaha_pariwisata`;

-- ----------------------------------------------------------------------------
-- 1. TABEL: users
-- Kredensial autentikasi administrator sistem berbasis username murni
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
    `id_user` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `nama_lengkap` VARCHAR(100) NOT NULL,
    `role` ENUM('super_admin', 'operator') NOT NULL DEFAULT 'super_admin',
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 2. TABEL: kbli
-- Master kategori klasifikasi baku usaha pariwisata resmi sesuai OSS-RBA
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `kbli` (
    `id_kbli` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `kode_kbli` VARCHAR(10) NOT NULL UNIQUE,
    `judul_kbli` VARCHAR(150) NOT NULL,
    `deskripsi` TEXT NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 3. TABEL: kecamatan
-- Master 18 wilayah administratif kecamatan Kota Palembang
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `kecamatan` (
    `id_kecamatan` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `nama_kecamatan` VARCHAR(100) NOT NULL UNIQUE,
    `geojson_polygon` LONGTEXT NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 4. TABEL: usaha_pariwisata
-- Data operasional entitas usaha pariwisata (2.809 baris data DPMPTSP)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `usaha_pariwisata` (
    `id_usaha` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `id_kbli` INT UNSIGNED NOT NULL,
    `id_kecamatan` INT UNSIGNED NOT NULL,
    `nama_usaha` VARCHAR(255) NOT NULL,
    `alamat` TEXT NULL,
    `latitude` DECIMAL(10, 8) NULL,
    `longitude` DECIMAL(11, 8) NULL,
    `status_spasial` ENUM('verified', 'unverified') NOT NULL DEFAULT 'unverified',
    `sumber_data` VARCHAR(100) NOT NULL DEFAULT 'DPMPTSP Kota Palembang',
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_usaha_kbli` FOREIGN KEY (`id_kbli`) REFERENCES `kbli` (`id_kbli`) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT `fk_usaha_kecamatan` FOREIGN KEY (`id_kecamatan`) REFERENCES `kecamatan` (`id_kecamatan`) ON UPDATE CASCADE ON DELETE RESTRICT,
    INDEX `idx_spasial` (`latitude`, `longitude`, `status_spasial`),
    INDEX `idx_kbli` (`id_kbli`),
    INDEX `idx_kecamatan` (`id_kecamatan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 5. TABEL: konfigurasi_klaster
-- Riwayat versi parameter resmi DBSCAN yang ditetapkan oleh administrator
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `konfigurasi_klaster` (
    `id_konfigurasi` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `id_user` INT UNSIGNED NOT NULL,
    `nama_konfigurasi` VARCHAR(150) NOT NULL,
    `parameter_eps` DECIMAL(5, 3) NOT NULL,
    `parameter_minpts` INT NOT NULL,
    `total_cluster` INT NOT NULL DEFAULT 0,
    `total_noise` INT NOT NULL DEFAULT 0,
    `execution_time_ms` INT UNSIGNED NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_konfigurasi_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 6. TABEL: anggota_klaster
-- Tabel relasi asosiatif pemetaan titik usaha ke klaster hasil kalkulasi DBSCAN
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `anggota_klaster` (
    `id_anggota` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `id_konfigurasi` INT UNSIGNED NOT NULL,
    `id_usaha` INT UNSIGNED NOT NULL,
    `cluster_label` INT NOT NULL,
    `tipe_titik` ENUM('core', 'border', 'noise') NOT NULL,
    CONSTRAINT `fk_anggota_konfigurasi` FOREIGN KEY (`id_konfigurasi`) REFERENCES `konfigurasi_klaster` (`id_konfigurasi`) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT `fk_anggota_usaha` FOREIGN KEY (`id_usaha`) REFERENCES `usaha_pariwisata` (`id_usaha`) ON UPDATE CASCADE ON DELETE CASCADE,
    UNIQUE KEY `uk_konfig_usaha` (`id_konfigurasi`, `id_usaha`),
    INDEX `idx_cluster_label` (`id_konfigurasi`, `cluster_label`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
