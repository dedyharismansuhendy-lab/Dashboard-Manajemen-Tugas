<?php
/**
 * ============================================================================
 * [Sub-CPMK 7] Implementasi Fitur Delete: Penghapusan Data Permanen
 * Aplikasi   : TaskFlow - Relational To-Do List Web App
 * Dibuat oleh: Dedy Harisman
 * ============================================================================
 */
session_start();
require_once __DIR__ . '/config/database.php';

// Mendukung eksekusi via POST (utama) maupun GET terproteksi
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT)
   ?: filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    $_SESSION['flash_message'] = 'ID tugas yang akan dihapus tidak valid.';
    $_SESSION['flash_type'] = 'warning';
    header('Location: index.php');
    exit;
}

if (!$db_connected || !($pdo instanceof PDO)) {
    $_SESSION['flash_message'] = 'Gagal menghapus tugas karena koneksi database terputus.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: index.php');
    exit;
}

// [Sub-CPMK 7] Eksekusi DELETE FROM tasks WHERE id = :id menggunakan PDO Prepared Statement
try {
    $stmtInfo = $pdo->prepare("SELECT title FROM tasks WHERE id = :id LIMIT 1");
    $stmtInfo->execute([':id' => $id]);
    $task = $stmtInfo->fetch();

    if (!$task) {
        $_SESSION['flash_message'] = 'Tugas tidak ditemukan atau sudah dihapus sebelumnya.';
        $_SESSION['flash_type'] = 'warning';
        header('Location: index.php');
        exit;
    }

    $stmtDelete = $pdo->prepare("DELETE FROM tasks WHERE id = :id");
    $stmtDelete->execute([':id' => $id]);

    // [Sub-CPMK 7] Menyimpan Flash Message konfirmasi bahwa data berhasil dihapus
    $_SESSION['flash_message'] = 'Tugas "' . $task['title'] . '" telah berhasil dihapus secara permanen.';
    $_SESSION['flash_type'] = 'success';
} catch (PDOException $e) {
    error_log('[TaskFlow Delete Task Error] ' . $e->getMessage());
    $_SESSION['flash_message'] = 'Terjadi kesalahan saat menghapus data tugas.';
    $_SESSION['flash_type'] = 'danger';
}

header('Location: index.php');
exit;

