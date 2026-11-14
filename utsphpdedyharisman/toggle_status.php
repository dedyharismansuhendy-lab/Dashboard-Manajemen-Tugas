<?php
/**
 * ============================================================================
 * [Sub-CPMK 6] Handler Aksi Cepat: Toggle Status Tugas (Completed / Pending)
 * Aplikasi   : TaskFlow - Relational To-Do List Web App
 * Dibuat oleh: Dedy Harisman
 * ============================================================================
 */
session_start();
require_once __DIR__ . '/config/database.php';

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT)
   ?: filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id <= 0 || !$db_connected || !($pdo instanceof PDO)) {
    $_SESSION['flash_message'] = 'Permintaan perubahan status tidak valid.';
    $_SESSION['flash_type'] = 'warning';
    header('Location: index.php');
    exit;
}

try {
    // Cek status tugas saat ini
    $stmtCheck = $pdo->prepare("SELECT id, title, status FROM tasks WHERE id = :id LIMIT 1");
    $stmtCheck->execute([':id' => $id]);
    $task = $stmtCheck->fetch();

    if (!$task) {
        $_SESSION['flash_message'] = 'Tugas tidak ditemukan.';
        $_SESSION['flash_type'] = 'warning';
        header('Location: index.php');
        exit;
    }

    // Jika belum Completed -> ubah ke Completed; jika sudah Completed -> kembalikan ke Pending
    $new_status = ($task['status'] === 'Completed') ? 'Pending' : 'Completed';

    $stmtUpdate = $pdo->prepare("UPDATE tasks SET status = :status WHERE id = :id");
    $stmtUpdate->execute([
        ':status' => $new_status,
        ':id'     => $id,
    ]);

    $_SESSION['flash_message'] = 'Status tugas "' . $task['title'] . '" berhasil diubah menjadi ' . $new_status . '.';
    $_SESSION['flash_type'] = 'success';
} catch (PDOException $e) {
    error_log('[TaskFlow Toggle Status Error] ' . $e->getMessage());
    $_SESSION['flash_message'] = 'Gagal memperbarui status tugas.';
    $_SESSION['flash_type'] = 'danger';
}

header('Location: index.php');
exit;

