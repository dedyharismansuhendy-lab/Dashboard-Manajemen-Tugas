-- ============================================================================
-- [Sub-CPMK 2] Perancangan Skema Database & Normalisasi (ERD - 3NF)
-- Aplikasi   : TaskFlow - Relational To-Do List & Task Manager
-- Database   : 24424018
-- Dibuat oleh: Dedy Harisman
-- ============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Buat dan gunakan database `24424018` secara otomatis untuk Localhost (XAMPP)
-- Catatan: Jika mengimpor ke InfinityFree, Anda dapat menghapus/mengomentari 2 baris
-- CREATE DATABASE dan USE di bawah ini setelah memilih database di phpMyAdmin hosting.
CREATE DATABASE IF NOT EXISTS `24424018`
    DEFAULT CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
USE `24424018`;

-- ----------------------------------------------------------------------------
-- 1. Tabel `categories` (Tabel Master / Parent - 1NF, 2NF, 3NF)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL UNIQUE,
    `color_badge` VARCHAR(20) NOT NULL DEFAULT 'primary',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 2. Tabel `tasks` (Tabel Transaksi / Child dengan Relasi Many-to-One)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tasks` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `category_id` INT NOT NULL,
    `title` VARCHAR(150) NOT NULL,
    `description` TEXT NULL,
    `priority` ENUM('Low', 'Medium', 'High') NOT NULL DEFAULT 'Medium',
    `status` ENUM('Pending', 'In Progress', 'Completed') NOT NULL DEFAULT 'Pending',
    `due_date` DATE NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_tasks_category`
        FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ----------------------------------------------------------------------------
-- Seed Data Awal: Tabel `categories`
-- ----------------------------------------------------------------------------
INSERT IGNORE INTO `categories` (`id`, `name`, `color_badge`) VALUES
(1, 'Akademik & Kuliah', 'primary'),
(2, 'Proyek Web & Coding', 'info'),
(3, 'Pribadi & Rutinitas', 'success'),
(4, 'Organisasi & Kampus', 'warning'),
(5, 'Mendesak / Penting', 'danger');

-- ----------------------------------------------------------------------------
-- Seed Data Awal: Tabel `tasks`
-- ----------------------------------------------------------------------------
INSERT IGNORE INTO `tasks` (`id`, `category_id`, `title`, `description`, `priority`, `status`, `due_date`) VALUES
(1, 1, 'Menyelesaikan Proyek UTS Pemrograman Web Database', 'Membangun aplikasi TaskFlow dengan PHP 8.2 Native, PDO Prepared Statements, dan MySQL berelasi sesuai Sub-CPMK 1-7.', 'High', 'In Progress', CURDATE()),
(2, 2, 'Merancang ERD dan Normalisasi Tabel (3NF)', 'Membuat skema tabel categories dan tasks pada database 24424018 dengan Foreign Key ON DELETE RESTRICT ON UPDATE CASCADE.', 'High', 'Completed', DATE_SUB(CURDATE(), INTERVAL 1 DAY)),
(3, 2, 'Uji Coba Aplikasi di XAMPP Localhost & InfinityFree', 'Memastikan koneksi database PDO 24424018 berjalan lancar di XAMPP Localhost serta siap di-deploy ke hosting InfinityFree.', 'Medium', 'Pending', DATE_ADD(CURDATE(), INTERVAL 2 DAY)),
(4, 3, 'Review Materi Keamanan Web (SQL Injection & XSS)', 'Memastikan seluruh query memakai PDO Prepared Statements dan output dibungkus htmlspecialchars().', 'Low', 'Pending', DATE_ADD(CURDATE(), INTERVAL 4 DAY));
