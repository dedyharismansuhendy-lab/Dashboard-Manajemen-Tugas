<?php
/**
 * ============================================================================
 * [Sub-CPMK 4] Implementasi Fitur Create: Handler Penyimpanan Tugas Baru
 * Aplikasi   : TaskFlow - Relational To-Do List Web App
 * Dibuat oleh: Dedy Harisman
 * ============================================================================
 */
session_start();
require_once __DIR__ . '/config/database.php';

// Hanya izinkan metode HTTP POST (Pola Post/Redirect/Get - PRG)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

if (!$db_connected || !($pdo instanceof PDO)) {
    $_SESSION['flash_message'] = 'Gagal menyimpan tugas karena koneksi database tidak tersedia.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: index.php');
    exit;
}

// [Sub-CPMK 4] Sanitasi dan pengambilan input dari form
$title       = trim($_POST['title'] ?? '');
$category_id = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
$priority    = trim($_POST['priority'] ?? 'Medium');
$due_date    = trim($_POST['due_date'] ?? '');
$description = trim($_POST['description'] ?? '');

// [Sub-CPMK 4] Validasi server-side untuk memastikan field wajib valid
$allowed_priorities = ['Low', 'Medium', 'High'];
$date_obj = DateTime::createFromFormat('Y-m-d', $due_date);
$is_valid_date = $date_obj && $date_obj->format('Y-m-d') === $due_date;

if ($title === '' || mb_strlen($title) > 150) {
    $_SESSION['flash_message'] = 'Judul tugas wajib diisi dan maksimal 150 karakter.';
    $_SESSION['flash_type'] = 'warning';
    header('Location: index.php');
    exit;
}

if (!$category_id || $category_id <= 0) {
    $_SESSION['flash_message'] = 'Silakan pilih kategori tugas yang valid.';
    $_SESSION['flash_type'] = 'warning';
    header('Location: index.php');
    exit;
}

if (!in_array($priority, $allowed_priorities, true)) {
    $priority = 'Medium';
}

if (!$is_valid_date) {
    $_SESSION['flash_message'] = 'Format tanggal tenggat waktu (Due Date) tidak valid.';
    $_SESSION['flash_type'] = 'warning';
    header('Location: index.php');
    exit;
}

// [Sub-CPMK 4] Menyimpan data menggunakan PDO Prepared Statements (Mencegah SQL Injection)
try {
    $sql = "
        INSERT INTO tasks (category_id, title, description, priority, status, due_date)
        VALUES (:category_id, :title, :description, :priority, 'Pending', :due_date)
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':category_id' => $category_id,
        ':title'       => $title,
        ':description' => $description !== '' ? $description : null,
        ':priority'    => $priority,
        ':due_date'    => $due_date,
    ]);

    $_SESSION['flash_message'] = 'Tugas baru "' . $title . '" berhasil ditambahkan!';
    $_SESSION['flash_type'] = 'success';
} catch (PDOException $e) {
    error_log('[TaskFlow Create Task Error] ' . $e->getMessage());
    $_SESSION['flash_message'] = 'Gagal menyimpan tugas ke database. Pastikan kategori yang dipilih terdaftar.';
    $_SESSION['flash_type'] = 'danger';
}

header('Location: index.php');
exit;

