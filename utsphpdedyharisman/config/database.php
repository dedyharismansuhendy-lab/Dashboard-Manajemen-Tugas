<?php
/**
 * ============================================================================
 * [Sub-CPMK 3] Konektivitas Database & Error Handling menggunakan PDO
 * Aplikasi   : TaskFlow - Relational To-Do List Web App
 * Database   : 24424018
 * Dibuat oleh: Dedy Harisman
 * ============================================================================
 */

// ----------------------------------------------------------------------------
// A. KONFIGURASI LOCALHOST (XAMPP / LARAGON / WAMP) TANPA DOCKER
// ----------------------------------------------------------------------------
$db_host = '127.0.0.1';   // Gunakan '127.0.0.1' atau 'localhost'
$db_name = '24424018';    // Nama database sesuai NIM: 24424018
$db_user = 'root';        // Username default MySQL XAMPP
$db_pass = '';            // Password default MySQL XAMPP (kosong)

// ----------------------------------------------------------------------------
// B. KONFIGURASI KHUSUS DEPLOYMENT INFINITYFREE (SHARED CLOUD HOSTING)
// ----------------------------------------------------------------------------
// PANDUAN DEPLOY KE INFINITYFREE:
// Saat mengunggah ke folder `htdocs/` di InfinityFree, hapus tanda komentar (//)
// pada 4 baris di bawah ini dan sesuaikan dengan kredensial MySQL Details di
// Control Panel (VistaPanel) akun InfinityFree Anda:
//
// $db_host = 'sqlXXX.infinityfree.com';      // Contoh: sql210.infinityfree.com
// $db_name = 'if0_XXXXXXX_24424018';         // Nama database di panel InfinityFree
// $db_user = 'if0_XXXXXXX';                  // Username MySQL InfinityFree Anda
// $db_pass = 'password_hosting_anda';        // Password akun/MySQL InfinityFree Anda
// ----------------------------------------------------------------------------

$pdo = null;
$db_connected = false;
$db_error_message = '';

// [Sub-CPMK 3] Membuka koneksi PDO dengan blok try-catch & PDO::ERRMODE_EXCEPTION
try {
    $dsn = "mysql:host={$db_host};dbname={$db_name};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Lempar exception jika terjadi error SQL
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Hasil query dalam bentuk associative array
        PDO::ATTR_EMULATE_PREPARES   => false,                  // Gunakan native prepared statements MySQL
    ];

    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
    $db_connected = true;
} catch (PDOException $e) {
    $db_connected = false;
    // Menyimpan pesan error yang aman untuk pengguna tanpa membocorkan kredensial/password sensitif
    $db_error_message = 'Gagal terhubung ke database "24424018". Pastikan MySQL di XAMPP sudah aktif dan file database/schema.sql sudah di-import.';
    // Catat detail teknis hanya ke error log internal server
    error_log('[TaskFlow Database Error] ' . $e->getMessage());
}
