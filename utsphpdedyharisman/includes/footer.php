<?php
/**
 * ============================================================================
 * Template Footer & Indikator Status Database
 * Aplikasi   : TaskFlow - Relational To-Do List Web App
 * Dibuat oleh: Dedy Harisman
 * ============================================================================
 */
?>
</main>

<!-- Footer Utama -->
<footer class="bg-white border-top py-4 mt-auto shadow-sm">
    <div class="container">
        <div class="row align-items-center gy-2">
            <div class="col-md-6 text-center text-md-start">
                <p class="mb-0 text-muted small">
                    <strong>TaskFlow</strong> &mdash; Relational To-Do List Web App (PHP 8.2 Native &amp; MySQL PDO)<br>
                    Dibuat oleh <strong>Dedy Harisman</strong>
                </p>
            </div>
            <!-- [Sub-CPMK 3] Indikator Status Koneksi Database pada Footer -->
            <div class="col-md-6 text-center text-md-end">
                <?php if (!empty($db_connected)): ?>
                    <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle px-3 py-2">
                        <i class="bi bi-check-circle-fill me-1"></i>Status DB: Database Connected (PDO MySQL)
                    </span>
                <?php else: ?>
                    <span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle px-3 py-2">
                        <i class="bi bi-x-circle-fill me-1"></i>Status DB: Terputus / Error Koneksi
                    </span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap 5.3 JS Bundle CDN -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>

