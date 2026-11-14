# AGENT.md - Panduan Eksekusi & Standar Kode

## 1. Peran & Prinsip Utama
Anda adalah AI Software Engineering Agent yang bertugas mengimplementasikan proyek **TaskFlow** sesuai `PRD.md`. Kode harus bersih, terstruktur, mudah dipelajari oleh mahasiswa untuk kebutuhan praktikum Sub-CPMK 1–7, dan 100% berjalan lancar di Localhost (XAMPP / Laragon) tanpa Docker maupun di InfinityFree dengan nama database **`24424018`**.

## 2. Struktur Direktori Wajib
Gunakan struktur direktori yang datar (flat-friendly) agar seluruh isi folder proyek bisa langsung disalin ke dalam `htdocs` (XAMPP/InfinityFree):

```text
utsphpdedyharisman/
├── config/
│   └── database.php        # Sub-CPMK 3: Koneksi PDO (DB: 24424018) & Error Handling
├── database/
│   └── schema.sql          # Sub-CPMK 2: DDL Database 24424018, Tabel Berelasi & Seed Data
├── includes/
│   ├── header.php          # Template UI atas & Flash Message Alert
│   └── footer.php          # Template UI bawah & Indikator Koneksi DB (Dibuat oleh Dedy Harisman)
├── index.php               # Sub-CPMK 4 & 5: Form Create & Tabel Read Dinamis
├── create_task.php         # Sub-CPMK 4: Handler POST penyimpanan tugas baru
├── create_category.php     # Sub-CPMK 4: Handler POST kategori baru
├── edit.php                # Sub-CPMK 6: Halaman Form Pre-filling & Handler Update
├── toggle_status.php       # Sub-CPMK 6: Handler cepat ubah status selesai
├── delete.php              # Sub-CPMK 7: Handler Delete permanen
├── PRD.md                  # Dokumen Spesifikasi Produk
├── AGENT.md                # Dokumen Instruksi Agent
└── TUTOR.md                # Dokumentasi Arsitektur, ERD, & Panduan Localhost/InfinityFree
```

## 3. Aturan Teknis Penulisan Kode
- **Tanpa Framework / Composer / Docker:** Jangan gunakan Composer, library pihak ketiga, ataupun Docker agar aplikasi langsung berjalan di XAMPP Localhost dan mudah di-upload ke InfinityFree.
- **Koneksi Database (`config/database.php`):**
  - Gunakan `$db_host = '127.0.0.1'`, `$db_name = '24424018'`, `$db_user = 'root'`, dan `$db_pass = ''` sebagai konfigurasi utama Localhost (XAMPP).
  - Sediakan blok komentar yang sangat jelas di `config/database.php` tempat pengguna mengisi `sqlXXX.infinityfree.com`, username `if0_XXXXXXX`, password, dan nama database ketika hendak deploy ke InfinityFree.
- **Komentar Edukatif Berbasis Sub-CPMK:**
  - Berikan komentar penanda pada blok kode krusial, misalnya `// [Sub-CPMK 3] Membuka koneksi PDO dengan Penanganan Error` atau `// [Sub-CPMK 6] Mengambil data berdasarkan ID untuk Form Pre-filling`.
- **Penanganan Session & Header:**
  - Selalu panggil `session_start()` di baris paling atas sebelum output HTML untuk menyimpan notifikasi flash message (`$_SESSION['flash_message']` dan `$_SESSION['flash_type']`).
  - Gunakan pola PRG (Post/Redirect/Get) setelah aksi Create, Update, dan Delete agar form tidak terkirim ganda saat halaman di-refresh.
- **Footer:** Berikan footer dibuat oleh Dedy Harisman.
