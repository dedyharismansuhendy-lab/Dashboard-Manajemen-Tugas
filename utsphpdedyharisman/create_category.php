<?php
/**
 * ============================================================================
 * [Sub-CPMK 4] Implementasi Fitur Create: Handler Penyimpanan Kategori Baru
 * Aplikasi   : TaskFlow - Relational To-Do List Web App
 * Dibuat oleh: Dedy Harisman
 * ============================================================================
 */
session_start();
require_once __DIR__ . '/config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

if (!$db_connected || !($pdo instanceof PDO)) {
    $_SESSION['flash_message'] = 'Gagal menambah kategori karena koneksi database terputus.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: index.php');
    exit;
}

// [Sub-CPMK 4] Sanitasi dan validasi server-side input kategori
$name        = trim($_POST['name'] ?? '');
$color_badge = trim($_POST['color_badge'] ?? 'primary');

$allowed_colors = ['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'dark'];
if (!in_array($color_badge, $allowed_colors, true)) {
    $color_badge = 'primary';
}

if ($name === '' || mb_strlen($name) > 50) {
    $_SESSION['flash_message'] = 'Nama kategori wajib diisi dan maksimal 50 karakter.';
    $_SESSION['flash_type'] = 'warning';
    header('Location: index.php');
    exit;
}

// [Sub-CPMK 4] Menyimpan kategori baru dengan PDO Prepared Statement
try {
    $stmt = $pdo->prepare("INSERT INTO categories (name, color_badge) VALUES (:name, :color_badge)");
    $stmt->execute([
        ':name'        => $name,
        ':color_badge' => $color_badge,
    ]);

    $_SESSION['flash_message'] = 'Kategori "' . $name . '" berhasil ditambahkan!';
    $_SESSION['flash_type'] = 'success';
} catch (PDOException $e) {
    // Kode SQLSTATE 23000 menandakan pelanggaran constraint UNIQUE (nama kategori sudah ada)
    if ($e->getCode() === '23000') {
        $_SESSION['flash_message'] = 'Kategori "' . $name . '" sudah terdaftar. Gunakan nama kategori lain.';
        $_SESSION['flash_type'] = 'warning';
    } else {
        error_log('[TaskFlow Create Category Error] ' . $e->getMessage());
        $_SESSION['flash_message'] = 'Terjadi kesalahan saat menyimpan kategori baru.';
        $_SESSION['flash_type'] = 'danger';
    }
}

header('Location: index.php');
exit;

