<?php
/**
 * ============================================================================
 * Template Header & Flash Message Alert
 * Aplikasi   : TaskFlow - Relational To-Do List Web App
 * Dibuat oleh: Dedy Harisman
 * ============================================================================
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Helper sanitasi output untuk mencegah Cross-Site Scripting (XSS)
 */
if (!function_exists('e')) {
    function e(?string $string): string
    {
        return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? e($page_title) . ' - TaskFlow' : 'TaskFlow - Relational To-Do List Web App' ?></title>
    <!-- Bootstrap 5.3 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Bootstrap Icons CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: #f4f6f9;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        main {
            flex: 1;
        }
        .navbar-brand {
            font-weight: 700;
            letter-spacing: 0.3px;
        }
        .card {
            border: none;
            border-radius: 0.75rem;
        }
        .table > :not(caption) > * > * {
            vertical-align: middle;
        }
        .task-completed-title {
            text-decoration: line-through;
            color: #6c757d;
        }
    </style>
</head>
<body>

<!-- Navbar Utama -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm mb-4">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="index.php">
            <i class="bi bi-check2-square fs-4"></i>
            <span>TaskFlow</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarTaskFlow" aria-controls="navbarTaskFlow" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarTaskFlow">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link active" href="index.php"><i class="bi bi-list-task me-1"></i>Daftar Tugas</a>
                </li>
            </ul>
            <!-- [Sub-CPMK 3] Indikator Visual Status Koneksi Database di Header -->
            <div class="d-flex align-items-center gap-2">
                <?php if (!empty($db_connected)): ?>
                    <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle px-3 py-2 rounded-pill">
                        <i class="bi bi-database-fill-check me-1"></i>Database Connected
                    </span>
                <?php else: ?>
                    <span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle px-3 py-2 rounded-pill">
                        <i class="bi bi-database-fill-x me-1"></i>Database Disconnected
                    </span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<main class="container pb-5">
    <!-- [Sub-CPMK 3] Pesan Error Ramah Pengguna Jika Koneksi Database Gagal -->
    <?php if (empty($db_connected) && !empty($db_error_message)): ?>
        <div class="alert alert-danger shadow-sm d-flex align-items-center" role="alert">
            <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
            <div>
                <strong>Koneksi Database Bermasalah!</strong><br>
                <?= e($db_error_message) ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- [Sub-CPMK 4, 6, 7] Flash Message Alert (Session Feedback setelah aksi CRUD) -->
    <?php if (isset($_SESSION['flash_message'])): ?>
        <?php
        $flash_type = $_SESSION['flash_type'] ?? 'info';
        $allowed_types = ['success', 'danger', 'warning', 'info'];
        if (!in_array($flash_type, $allowed_types, true)) {
            $flash_type = 'info';
        }
        $icon_map = [
            'success' => 'bi-check-circle-fill',
            'danger'  => 'bi-exclamation-octagon-fill',
            'warning' => 'bi-exclamation-triangle-fill',
            'info'    => 'bi-info-circle-fill',
        ];
        ?>
        <div class="alert alert-<?= e($flash_type) ?> alert-dismissible fade show shadow-sm d-flex align-items-center" role="alert">
            <i class="bi <?= $icon_map[$flash_type] ?> me-2 fs-5"></i>
            <div><?= e($_SESSION['flash_message']) ?></div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
        ?>
    <?php endif; ?>

