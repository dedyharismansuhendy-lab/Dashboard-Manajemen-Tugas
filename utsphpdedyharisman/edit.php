<?php
/**
 * ============================================================================
 * [Sub-CPMK 6] Implementasi Fitur Update: Form Pre-filling & Data Updating
 * Aplikasi   : TaskFlow - Relational To-Do List Web App
 * Dibuat oleh: Dedy Harisman
 * ============================================================================
 */
session_start();
require_once __DIR__ . '/config/database.php';

if (!$db_connected || !($pdo instanceof PDO)) {
    $_SESSION['flash_message'] = 'Koneksi database tidak tersedia.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: index.php');
    exit;
}

// ----------------------------------------------------------------------------
// 1. HANDLER HTTP POST: Memproses Perubahan Data Tugas (UPDATE Query)
// ----------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id          = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $title       = trim($_POST['title'] ?? '');
    $category_id = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
    $priority    = trim($_POST['priority'] ?? 'Medium');
    $status      = trim($_POST['status'] ?? 'Pending');
    $due_date    = trim($_POST['due_date'] ?? '');
    $description = trim($_POST['description'] ?? '');

    $allowed_priorities = ['Low', 'Medium', 'High'];
    $allowed_statuses   = ['Pending', 'In Progress', 'Completed'];
    $date_obj           = DateTime::createFromFormat('Y-m-d', $due_date);
    $is_valid_date      = $date_obj && $date_obj->format('Y-m-d') === $due_date;

    if (!$id || $id <= 0) {
        $_SESSION['flash_message'] = 'ID tugas tidak valid.';
        $_SESSION['flash_type'] = 'danger';
        header('Location: index.php');
        exit;
    }

    if ($title === '' || mb_strlen($title) > 150 || !$category_id || !$is_valid_date) {
        $_SESSION['flash_message'] = 'Mohon lengkapi seluruh field wajib (Judul, Kategori, dan Tenggat Waktu) dengan benar.';
        $_SESSION['flash_type'] = 'warning';
        header('Location: edit.php?id=' . $id);
        exit;
    }

    if (!in_array($priority, $allowed_priorities, true)) {
        $priority = 'Medium';
    }
    if (!in_array($status, $allowed_statuses, true)) {
        $status = 'Pending';
    }

    // [Sub-CPMK 6] Eksekusi UPDATE menggunakan PDO Prepared Statements
    try {
        $sql = "
            UPDATE tasks
            SET category_id = :category_id,
                title       = :title,
                description = :description,
                priority    = :priority,
                status      = :status,
                due_date    = :due_date
            WHERE id = :id
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':category_id' => $category_id,
            ':title'       => $title,
            ':description' => $description !== '' ? $description : null,
            ':priority'    => $priority,
            ':status'      => $status,
            ':due_date'    => $due_date,
            ':id'          => $id,
        ]);

        $_SESSION['flash_message'] = 'Data tugas "' . $title . '" berhasil diperbarui!';
        $_SESSION['flash_type'] = 'success';
        header('Location: index.php');
        exit;
    } catch (PDOException $e) {
        error_log('[TaskFlow Update Task Error] ' . $e->getMessage());
        $_SESSION['flash_message'] = 'Terjadi kesalahan saat memperbarui data tugas.';
        $_SESSION['flash_type'] = 'danger';
        header('Location: edit.php?id=' . $id);
        exit;
    }
}

// ----------------------------------------------------------------------------
// 2. HANDLER HTTP GET: [Sub-CPMK 6] Mengambil Data Berdasarkan ID untuk Form Pre-filling
// ----------------------------------------------------------------------------
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || $id <= 0) {
    $_SESSION['flash_message'] = 'Parameter ID tugas tidak ditemukan atau tidak valid.';
    $_SESSION['flash_type'] = 'warning';
    header('Location: index.php');
    exit;
}

try {
    // [Sub-CPMK 6] Mengambil satu baris record tugas dari database
    $stmtTask = $pdo->prepare("SELECT * FROM tasks WHERE id = :id LIMIT 1");
    $stmtTask->execute([':id' => $id]);
    $task = $stmtTask->fetch();

    if (!$task) {
        $_SESSION['flash_message'] = 'Data tugas dengan ID #' . $id . ' tidak ditemukan.';
        $_SESSION['flash_type'] = 'warning';
        header('Location: index.php');
        exit;
    }

    // Mengambil daftar kategori untuk dropdown
    $stmtCat = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC");
    $categories = $stmtCat->fetchAll();
} catch (PDOException $e) {
    error_log('[TaskFlow Fetch Edit Error] ' . $e->getMessage());
    $_SESSION['flash_message'] = 'Gagal mengambil data tugas untuk diedit.';
    $_SESSION['flash_type'] = 'danger';
    header('Location: index.php');
    exit;
}

$page_title = 'Edit Tugas #' . (int) $task['id'];
require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                <h5 class="mb-0 fw-bold text-primary">
                    <i class="bi bi-pencil-square me-2"></i>Edit Tugas #<?= (int) $task['id'] ?>
                </h5>
                <a href="index.php" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Kembali
                </a>
            </div>
            <div class="card-body p-4">
                <!-- [Sub-CPMK 6] Form Pre-filling: Seluruh input terisi otomatis sesuai nilai database saat ini -->
                <form action="edit.php?id=<?= (int) $task['id'] ?>" method="POST">
                    <input type="hidden" name="id" value="<?= (int) $task['id'] ?>">

                    <div class="mb-3">
                        <label for="title" class="form-label fw-semibold">Judul Tugas <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" maxlength="150" value="<?= e($task['title']) ?>" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="category_id" class="form-label fw-semibold">Kategori <span class="text-danger">*</span></label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= (int) $cat['id'] ?>" <?= ((int) $task['category_id'] === (int) $cat['id']) ? 'selected' : '' ?>>
                                        <?= e($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="due_date" class="form-label fw-semibold">Tenggat Waktu (Due Date) <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="due_date" name="due_date" value="<?= e($task['due_date']) ?>" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="priority" class="form-label fw-semibold">Prioritas <span class="text-danger">*</span></label>
                            <select class="form-select" id="priority" name="priority" required>
                                <option value="Low" <?= ($task['priority'] === 'Low') ? 'selected' : '' ?>>Low (Rendah)</option>
                                <option value="Medium" <?= ($task['priority'] === 'Medium') ? 'selected' : '' ?>>Medium (Sedang)</option>
                                <option value="High" <?= ($task['priority'] === 'High') ? 'selected' : '' ?>>High (Tinggi)</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="status" class="form-label fw-semibold">Status Pengerjaan <span class="text-danger">*</span></label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="Pending" <?= ($task['status'] === 'Pending') ? 'selected' : '' ?>>Pending</option>
                                <option value="In Progress" <?= ($task['status'] === 'In Progress') ? 'selected' : '' ?>>In Progress</option>
                                <option value="Completed" <?= ($task['status'] === 'Completed') ? 'selected' : '' ?>>Completed</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="description" class="form-label fw-semibold">Deskripsi Tugas</label>
                        <textarea class="form-control" id="description" name="description" rows="4" placeholder="Rincian atau catatan tugas..."><?= e($task['description']) ?></textarea>
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                        <span class="text-muted small">
                            Terakhir diperbarui: <?= e($task['updated_at']) ?>
                        </span>
                        <div class="d-flex gap-2">
                            <a href="index.php" class="btn btn-outline-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary fw-semibold px-4">
                                <i class="bi bi-check2-circle me-1"></i>Simpan Perubahan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

