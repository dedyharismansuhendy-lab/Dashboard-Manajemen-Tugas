# Product Requirements Document (PRD): TaskFlow To-Do List

## 1. Ringkasan Proyek
- **Nama Aplikasi:** TaskFlow (Relational To-Do List & Task Manager)
- **Nama Database:** `24424018`
- **Tech Stack:** PHP 8.2 Native, MySQL / MariaDB, HTML5, Bootstrap 5 (CDN), Vanilla JavaScript.
- **Target Deployment:** Localhost (XAMPP / Laragon) tanpa Docker, dan InfinityFree (Shared Cloud Hosting Gratis).
- **Tujuan Akademis:** Memenuhi seluruh indikator penilaian Sub-CPMK 1 hingga Sub-CPMK 7 mata kuliah Pemrograman Web Database.

## 2. Pemetaan Kebutuhan Fungsional terhadap Sub-CPMK

### Sub-CPMK 1: Arsitektur Web Database & Environment Setup
- Aplikasi dapat langsung dijalankan di Localhost menggunakan XAMPP/Laragon tanpa memerlukan Docker.
- Struktur folder harus datar/kompatibel agar seluruh isi aplikasi dapat langsung disalin ke direktori `C:\xampp\htdocs\` maupun diunggah ke `htdocs/` pada layanan InfinityFree tanpa perlu mengubah path *routing*.
- Menyertakan penjelasan alur *Client-Server* (HTTP Request -> Web Server Apache -> PHP Interpreter -> PDO Driver -> MySQL Server -> HTML Response) di dalam `TUTOR.md`.

### Sub-CPMK 2: Perancangan Skema Database & Normalisasi (ERD)
Database bernama **`24424018`**. Untuk memenuhi syarat normalisasi (1NF, 2NF, 3NF) dan relasi tabel, database wajib memiliki 2 tabel berelasi (*One-to-Many*):
1. **Tabel `categories`**
   - `id` (INT, AUTO_INCREMENT, PRIMARY KEY)
   - `name` (VARCHAR(50), NOT NULL, UNIQUE)
   - `color_badge` (VARCHAR(20), DEFAULT 'primary')
   - `created_at` (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)
2. **Tabel `tasks`**
   - `id` (INT, AUTO_INCREMENT, PRIMARY KEY)
   - `category_id` (INT, NOT NULL, FOREIGN KEY mengacu pada `categories(id)` ON DELETE RESTRICT ON UPDATE CASCADE)
   - `title` (VARCHAR(150), NOT NULL)
   - `description` (TEXT, NULL)
   - `priority` (ENUM('Low', 'Medium', 'High'), DEFAULT 'Medium')
   - `status` (ENUM('Pending', 'In Progress', 'Completed'), DEFAULT 'Pending')
   - `due_date` (DATE, NOT NULL)
   - `created_at` (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)
   - `updated_at` (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP)

### Sub-CPMK 3: Konektivitas Database & Error Handling
- File `config/database.php` wajib menggunakan **PDO (`PDO::ERRMODE_EXCEPTION`)** dengan nama database `24424018`.
- Menampilkan indikator visual status koneksi database pada *footer* atau *header* halaman ("Database Connected" / pesan error ramah pengguna jika gagal tanpa membocorkan password di layar produksi).

### Sub-CPMK 4: Implementasi Fitur Create (Data Insertion & Form Handling)
- Form input untuk menambah Tugas Baru (`title`, `category_id` dari dropdown dinamis tabel `categories`, `priority`, `due_date`, `description`).
- Form ringkas/modal untuk menambah Kategori Baru.
- Validasi *server-side* (memastikan field wajib tidak kosong) dan sanitasi input.
- Menyimpan data menggunakan *PDO Prepared Statements* (`INSERT INTO ...`).

### Sub-CPMK 5: Implementasi Fitur Read (Data Retrieval & Dynamic Table)
- Menampilkan data tugas dalam tabel HTML responsif menggunakan query `INNER JOIN` antara `tasks` dan `categories`.
- Data diurutkan secara logis: tugas yang belum selesai (`Pending` & `In Progress`) tampil di atas, diurutkan berdasarkan `due_date ASC` (terdekat) dan `priority DESC`.
- Menyediakan fitur filter berdasarkan Kategori atau Status melalui parameter URL (`GET`).

### Sub-CPMK 6: Implementasi Fitur Update (Data Updating & Form Pre-filling)
- Halaman khusus `edit.php?id={id}` yang mengambil satu baris record dari database (`SELECT * FROM tasks WHERE id = :id`).
- **Form Pre-filling:** Seluruh field input teks, textarea, tanggal, serta opsi `<select>` (kategori, prioritas, status) wajib terisi otomatis sesuai nilai saat ini di database.
- Tombol aksi cepat pada tabel utama untuk langsung mengubah status menjadi `Completed`.

### Sub-CPMK 7: Implementasi Fitur Delete (Data Deletion & Confirmation Alerts)
- Tombol Hapus pada setiap baris tugas yang memicu dialog konfirmasi pengguna (`JavaScript confirm('Apakah Anda yakin ingin menghapus tugas ini secara permanen?')`).
- Jika dikonfirmasi, sistem mengeksekusi query `DELETE FROM tasks WHERE id = :id` menggunakan *Prepared Statement*.
- Menampilkan *Flash Message* (Alert Bootstrap) di halaman utama yang mengonfirmasi bahwa data berhasil dihapus.

## 3. Kebutuhan Non-Fungsional & Batasan Hosting Gratis (InfinityFree)
- **Kompatibilitas Localhost & InfinityFree:** Konfigurasi di `config/database.php` diatur langsung untuk Localhost (`127.0.0.1`, database `24424018`, user `root`, password kosong `""`) dan mudah diubah ketika diunggah ke InfinityFree.
- **Keamanan:** Semua output teks dari database ke HTML wajib dibungkus dengan `htmlspecialchars($string, ENT_QUOTES, 'UTF-8')` untuk mencegah XSS.

berikan footer dibuat oleh dedy harisman
