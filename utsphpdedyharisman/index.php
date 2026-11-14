<?php
/**
 * ============================================================================
 * [Sub-CPMK 4 & 5] Halaman Utama: Form Create & Tabel Read Dinamis (JOIN Query)
 * Aplikasi   : TaskFlow - Relational To-Do List Web App
 * Dibuat oleh: Dedy Harisman
 * ============================================================================
 */
session_start();
require_once __DIR__ . '/config/database.php';

$page_title = 'Dashboard Manajemen Tugas';

// Variabel penampung data
$categories = [];
$tasks = [];
$stats = [
    'total'       => 0,
    'pending'     => 0,
    'in_progress' => 0,
    'completed'   => 0,
];

// [Sub-CPMK 5] Mengambil parameter filter dari URL (GET)
$filter_category = isset($_GET['category_id']) && $_GET['category_id'] !== '' ? (int) $_GET['category_id'] : null;
$filter_status   = isset($_GET['status']) && $_GET['status'] !== '' ? trim($_GET['status']) : '';
$allowed_statuses = ['Pending', 'In Progress', 'Completed'];
if (!in_array($filter_status, $allowed_statuses, true)) {
    $filter_status = '';
}

if ($db_connected && $pdo instanceof PDO) {
    try {
        // 1. Mengambil semua kategori untuk dropdown form Create dan Filter
        $stmtCat = $pdo->query("SELECT id, name, color_badge FROM categories ORDER BY name ASC");
        $categories = $stmtCat->fetchAll();

        // 2. Mengambil ringkasan statistik jumlah tugas
        $stmtStats = $pdo->query("
            SELECT 
                COUNT(*) AS total,
                SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) AS pending,
                SUM(CASE WHEN status = 'In Progress' THEN 1 ELSE 0 END) AS in_progress,
                SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) AS completed
            FROM tasks
        ");
        $rowStats = $stmtStats->fetch();
        if ($rowStats) {
            $stats['total']       = (int) ($rowStats['total'] ?? 0);
            $stats['pending']     = (int) ($rowStats['pending'] ?? 0);
            $stats['in_progress'] = (int) ($rowStats['in_progress'] ?? 0);
            $stats['completed']   = (int) ($rowStats['completed'] ?? 0);
        }

        // 3. [Sub-CPMK 5] Query INNER JOIN antara `tasks` dan `categories`
        //    dengan filter dinamis dan pengurutan logis:
        //    - Tugas yang belum selesai (Pending & In Progress) tampil di atas
        //    - Diurutkan berdasarkan due_date ASC (tenggat terdekat)
        //    - Diurutkan berdasarkan priority DESC (High -> Medium -> Low)
        $sql = "
            SELECT 
                t.id,
                t.category_id,
                t.title,
                t.description,
                t.priority,
                t.status,
                t.due_date,
                t.created_at,
                t.updated_at,
                c.name AS category_name,
                c.color_badge AS category_color
            FROM tasks t
            INNER JOIN categories c ON t.category_id = c.id
            WHERE 1=1
        ";
        $params = [];

        if ($filter_category !== null && $filter_category > 0) {
            $sql .= " AND t.category_id = :category_id";
            $params[':category_id'] = $filter_category;
        }

        if ($filter_status !== '') {
            $sql .= " AND t.status = :status";
            $params[':status'] = $filter_status;
        }

        $sql .= "
            ORDER BY 
                CASE WHEN t.status = 'Completed' THEN 1 ELSE 0 END ASC,
                t.due_date ASC,
                FIELD(t.priority, 'High', 'Medium', 'Low') ASC,
                t.id DESC
        ";

        $stmtTasks = $pdo->prepare($sql);
        $stmtTasks->execute($params);
        $tasks = $stmtTasks->fetchAll();
    } catch (PDOException $e) {
        error_log('[TaskFlow Read Error] ' . $e->getMessage());
        $_SESSION['flash_message'] = 'Terjadi kesalahan saat memuat data tugas. Pastikan tabel database telah diimpor dari database/schema.sql.';
        $_SESSION['flash_type'] = 'danger';
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- Ringkasan Statistik Tugas -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card shadow-sm h-100 border-start border-4 border-primary">
            <div class="card-body py-3">
                <div class="text-muted small fw-semibold text-uppercase">Total Tugas</div>
                <div class="fs-3 fw-bold text-primary"><?= (int) $stats['total'] ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card shadow-sm h-100 border-start border-4 border-secondary">
            <div class="card-body py-3">
                <div class="text-muted small fw-semibold text-uppercase">Pending</div>
                <div class="fs-3 fw-bold text-secondary"><?= (int) $stats['pending'] ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card shadow-sm h-100 border-start border-4 border-warning">
            <div class="card-body py-3">
                <div class="text-muted small fw-semibold text-uppercase">In Progress</div>
                <div class="fs-3 fw-bold text-warning-emphasis"><?= (int) $stats['in_progress'] ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card shadow-sm h-100 border-start border-4 border-success">
            <div class="card-body py-3">
                <div class="text-muted small fw-semibold text-uppercase">Completed</div>
                <div class="fs-3 fw-bold text-success"><?= (int) $stats['completed'] ?></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- ================================================================== -->
    <!-- KOLOM KIRI: [Sub-CPMK 4] Form Tambah Tugas & Kategori Baru         -->
    <!-- ================================================================== -->
    <div class="col-lg-4">
        <!-- Card Form Tambah Tugas Baru -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                <h5 class="mb-0 fw-bold text-primary">
                    <i class="bi bi-plus-circle-fill me-2"></i>Tambah Tugas Baru
                </h5>
                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalAddCategory">
                    <i class="bi bi-folder-plus me-1"></i>+ Kategori
                </button>
            </div>
            <div class="card-body">
                <form action="create_task.php" method="POST">
                    <div class="mb-3">
                        <label for="title" class="form-label fw-semibold">Judul Tugas <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" maxlength="150" placeholder="Contoh: Menyelesaikan Laporan Basis Data" required>
                    </div>

                    <div class="mb-3">
                        <label for="category_id" class="form-label fw-semibold">Kategori <span class="text-danger">*</span></label>
                        <select class="form-select" id="category_id" name="category_id" required>
                            <option value="" disabled selected>-- Pilih Kategori --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= (int) $cat['id'] ?>">
                                    <?= e($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (empty($categories)): ?>
                            <div class="form-text text-danger">
                                Belum ada kategori. Silakan tambah kategori terlebih dahulu.
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-sm-6">
                            <label for="priority" class="form-label fw-semibold">Prioritas <span class="text-danger">*</span></label>
                            <select class="form-select" id="priority" name="priority" required>
                                <option value="Low">Low (Rendah)</option>
                                <option value="Medium" selected>Medium (Sedang)</option>
                                <option value="High">High (Tinggi)</option>
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <label for="due_date" class="form-label fw-semibold">Tenggat Waktu <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="due_date" name="due_date" value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label fw-semibold">Deskripsi Tugas</label>
                        <textarea class="form-control" id="description" name="description" rows="3" placeholder="Catatan atau rincian pengerjaan tugas (opsional)..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 fw-semibold" <?= empty($categories) ? 'disabled' : '' ?>>
                        <i class="bi bi-save me-1"></i>Simpan Tugas
                    </button>
                </form>
            </div>
        </div>

        <!-- Card Daftar Kategori Tersedia -->
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                <h6 class="mb-0 fw-bold text-secondary">
                    <i class="bi bi-tags-fill me-2"></i>Daftar Kategori (Tabel Master)
                </h6>
                <button type="button" class="btn btn-sm btn-light border" data-bs-toggle="modal" data-bs-target="#modalAddCategory">
                    <i class="bi bi-plus-lg"></i> Baru
                </button>
            </div>
            <div class="card-body">
                <?php if (empty($categories)): ?>
                    <p class="text-muted small mb-0">Belum ada kategori terdaftar.</p>
                <?php else: ?>
                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach ($categories as $cat): ?>
                            <a href="index.php?category_id=<?= (int) $cat['id'] ?>" class="text-decoration-none">
                                <span class="badge bg-<?= e($cat['color_badge']) ?> px-3 py-2">
                                    <i class="bi bi-tag-fill me-1"></i><?= e($cat['name']) ?>
                                </span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ================================================================== -->
    <!-- KOLOM KANAN: [Sub-CPMK 5, 6, 7] Filter & Tabel Dinamis Tugas       -->
    <!-- ================================================================== -->
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3 border-bottom">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-table me-2 text-primary"></i>Daftar Tugas Terjadwal
                    </h5>
                    <span class="badge bg-light text-dark border">
                        Menampilkan <?= count($tasks) ?> tugas
                    </span>
                </div>
            </div>

            <div class="card-body">
                <!-- [Sub-CPMK 5] Form Filter Berdasarkan Kategori & Status via Parameter GET -->
                <form action="index.php" method="GET" class="row g-2 align-items-end mb-4 p-3 bg-light rounded-3 border">
                    <div class="col-sm-5">
                        <label for="filter_category" class="form-label small fw-semibold mb-1">Filter Kategori</label>
                        <select name="category_id" id="filter_category" class="form-select form-select-sm">
                            <option value="">Semua Kategori</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= (int) $cat['id'] ?>" <?= ($filter_category === (int) $cat['id']) ? 'selected' : '' ?>>
                                    <?= e($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-sm-4">
                        <label for="filter_status" class="form-label small fw-semibold mb-1">Filter Status</label>
                        <select name="status" id="filter_status" class="form-select form-select-sm">
                            <option value="">Semua Status</option>
                            <option value="Pending" <?= ($filter_status === 'Pending') ? 'selected' : '' ?>>Pending</option>
                            <option value="In Progress" <?= ($filter_status === 'In Progress') ? 'selected' : '' ?>>In Progress</option>
                            <option value="Completed" <?= ($filter_status === 'Completed') ? 'selected' : '' ?>>Completed</option>
                        </select>
                    </div>
                    <div class="col-sm-3 d-flex gap-1">
                        <button type="submit" class="btn btn-sm btn-primary flex-grow-1">
                            <i class="bi bi-funnel-fill me-1"></i>Filter
                        </button>
                        <?php if ($filter_category !== null || $filter_status !== ''): ?>
                            <a href="index.php" class="btn btn-sm btn-outline-secondary" title="Reset Filter">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </form>

                <!-- [Sub-CPMK 5] Tabel HTML Responsif Menampilkan Tugas + Kategori (INNER JOIN) -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" style="width: 35%;">Tugas &amp; Deskripsi</th>
                                <th scope="col">Kategori</th>
                                <th scope="col">Prioritas</th>
                                <th scope="col">Tenggat</th>
                                <th scope="col">Status</th>
                                <th scope="col" class="text-end" style="min-width: 135px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($tasks)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                                        Tidak ada data tugas yang ditemukan. Silakan tambah tugas baru atau sesuaikan filter.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($tasks as $task): ?>
                                    <?php
                                    $is_completed = ($task['status'] === 'Completed');
                                    $is_overdue   = (!$is_completed && strtotime($task['due_date']) < strtotime(date('Y-m-d')));

                                    // Mapping warna badge prioritas
                                    $priority_badge = match ($task['priority']) {
                                        'High'   => 'danger',
                                        'Medium' => 'warning text-dark',
                                        'Low'    => 'info text-dark',
                                        default  => 'secondary',
                                    };

                                    // Mapping warna badge status
                                    $status_badge = match ($task['status']) {
                                        'Completed'   => 'success',
                                        'In Progress' => 'primary',
                                        'Pending'     => 'secondary',
                                        default       => 'secondary',
                                    };
                                    ?>
                                    <tr class="<?= $is_completed ? 'table-light' : '' ?>">
                                        <td>
                                            <div class="fw-semibold <?= $is_completed ? 'task-completed-title' : 'text-dark' ?>">
                                                <?= e($task['title']) ?>
                                            </div>
                                            <?php if (!empty($task['description'])): ?>
                                                <div class="small text-muted mt-1">
                                                    <?= nl2br(e($task['description'])) ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= e($task['category_color']) ?>">
                                                <?= e($task['category_name']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $priority_badge ?>">
                                                <?= e($task['priority']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="small fw-semibold <?= $is_overdue ? 'text-danger' : 'text-body' ?>">
                                                <i class="bi bi-calendar-event me-1"></i><?= e(date('d M Y', strtotime($task['due_date']))) ?>
                                            </div>
                                            <?php if ($is_overdue): ?>
                                                <span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle mt-1">
                                                    Terlambat
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $status_badge ?>">
                                                <?= e($task['status']) ?>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-inline-flex gap-1">
                                                <!-- [Sub-CPMK 6] Tombol Aksi Cepat Toggle Status Completed -->
                                                <form action="toggle_status.php" method="POST" class="d-inline">
                                                    <input type="hidden" name="id" value="<?= (int) $task['id'] ?>">
                                                    <?php if (!$is_completed): ?>
                                                        <button type="submit" class="btn btn-sm btn-outline-success" title="Tandai Selesai (Completed)">
                                                            <i class="bi bi-check-lg"></i>
                                                        </button>
                                                    <?php else: ?>
                                                        <button type="submit" class="btn btn-sm btn-outline-secondary" title="Kembalikan ke Pending">
                                                            <i class="bi bi-arrow-counterclockwise"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </form>

                                                <!-- [Sub-CPMK 6] Tombol Menuju Halaman Edit (Form Pre-filling) -->
                                                <a href="edit.php?id=<?= (int) $task['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit Tugas">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>

                                                <!-- [Sub-CPMK 7] Tombol Hapus dengan Konfirmasi JavaScript -->
                                                <form action="delete.php" method="POST" class="d-inline">
                                                    <input type="hidden" name="id" value="<?= (int) $task['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus Tugas" onclick="return confirm('Apakah Anda yakin ingin menghapus tugas ini secara permanen?');">
                                                        <i class="bi bi-trash3-fill"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ====================================================================== -->
<!-- [Sub-CPMK 4] Modal Form Tambah Kategori Baru                           -->
<!-- ====================================================================== -->
<div class="modal fade" id="modalAddCategory" tabindex="-1" aria-labelledby="modalAddCategoryLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="create_category.php" method="POST">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalAddCategoryLabel">
                        <i class="bi bi-folder-plus me-2"></i>Tambah Kategori Baru
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="category_name" class="form-label fw-semibold">Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="category_name" name="name" maxlength="50" placeholder="Contoh: Praktikum Basis Data" required>
                    </div>
                    <div class="mb-2">
                        <label for="color_badge" class="form-label fw-semibold">Warna Badge Bootstrap</label>
                        <select class="form-select" id="color_badge" name="color_badge">
                            <option value="primary" selected>Biru (Primary)</option>
                            <option value="success">Hijau (Success)</option>
                            <option value="info">Biru Muda (Info)</option>
                            <option value="warning">Kuning (Warning)</option>
                            <option value="danger">Merah (Danger)</option>
                            <option value="secondary">Abu-abu (Secondary)</option>
                            <option value="dark">Hitam (Dark)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>Simpan Kategori
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

