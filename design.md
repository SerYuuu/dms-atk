# design.md — Technical Design & Architecture Specification

**Nama Proyek:** Sistem Manajemen Dokumen (DMS) UMKM Toko ATK  
**Stack Utama:** Laravel 10 (PHP), Blade Templates, MySQL, Tesseract OCR  

---

## 1. System Overview & Tech Stack

* **Backend & Frontend Framework:** Laravel 10 (Monolith architecture menggunakan Blade Templates).
* **CSS Framework / Styling:** **Tailwind CSS** (bergaya *clean professional*, warna dasar *slate/gray* netral yang bersih, aksen natural seperti *emerald* atau *indigo* tipis, tanpa efek *glow/neon* berlebihan).
* **Database Management System:** MySQL 8.0 / MariaDB.
* **OCR & Parsing Engine:** Tesseract OCR (dijalankan via Symfony Process / PHP exec dengan OpenCV image preprocessing untuk ekstraksi teks dokumen).
* **File Storage:** Local Private Storage (`storage/app/private/documents/`).
* **Export Engine:** `maatwebsite/excel` (XLSX) & `barryvdh/laravel-dompdf` (PDF).

---

## 2. Database Schema (ERD Specification)

```sql
-- 1. Users Table
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'pemilik') NOT NULL DEFAULT 'admin',
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 2. Categories Table (Master Kategori Dokumen)
CREATE TABLE categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL, -- Contoh: Faktur Supplier, Surat Kontrak, Dokumen Pajak, Katalog Harga
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 3. Documents Table (Pusat Arsip Seluruh Dokumen Operasional)
CREATE TABLE documents (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL, -- Judul/Nama Dokumen
    document_number VARCHAR(100) NULL, -- Nomor Invoice / Nomor Kontrak / No Surat
    entity_name VARCHAR(255) NULL, -- Nama Supplier / Instansi / Klien terkait
    document_date DATE NULL, -- Tanggal dokumen diterbitkan
    due_date DATE NULL, -- Tanggal jatuh tempo (untuk tagihan/kontrak)
    total_amount DECIMAL(15, 2) NULL DEFAULT 0.00, -- Nominal uang (jika ada transaksi/tagihan)
    category_id BIGINT UNSIGNED NOT NULL,
    importance_level ENUM('penting', 'biasa', 'rahasia') DEFAULT 'biasa',
    status ENUM('draft', 'tersimpan', 'archived') NOT NULL DEFAULT 'draft',
    payment_status ENUM('n/a', 'belum_lunas', 'lunas') DEFAULT 'n/a', -- Khusus dokumen finansial
    file_path VARCHAR(255) NOT NULL,
    file_mime_type VARCHAR(100) NOT NULL,
    file_size INT NOT NULL, -- dalam bytes
    ocr_text TEXT NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_search (title, document_number, entity_name),
    INDEX idx_dates (document_date, due_date),
    INDEX idx_category (category_id)
);

-- 4. Document Items Table (Opsional: Rincian item jika dokumen berupa faktur/nota)
CREATE TABLE document_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    document_id BIGINT UNSIGNED NOT NULL,
    item_name VARCHAR(255) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    unit_price DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    subtotal DECIMAL(15, 2) GENERATED ALWAYS AS (quantity * unit_price) STORED,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE CASCADE
);

-- 5. Audit Logs Table
CREATE TABLE audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    action VARCHAR(100) NOT NULL, -- login, logout, upload, verify, save, edit, delete, export
    document_id BIGINT UNSIGNED NULL,
    description TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE SET NULL
);
```

---

## 3. Directory & File Structure (Laravel 10 Blade)

```text
app/
├── Http/
│   ├── Controllers/
│   │   ├── AuthController.php
│   │   ├── DashboardController.php
│   │   ├── DocumentController.php
│   │   ├── CategoryController.php
│   │   ├── ReportController.php
│   │   └── AuditLogController.php
│   ├── Middleware/
│   │   ├── RoleMiddleware.php (Check Admin / Pemilik)
│   │   └── EnsureDocumentDraft.php
│   └── Requests/
│       ├── StoreDocumentRequest.php
│       └── UpdateDocumentRequest.php
├── Models/
│   ├── User.php
│   ├── Category.php
│   ├── Document.php
│   ├── DocumentItem.php
│   └── AuditLog.php
├── Services/
│   ├── OcrEngineService.php
│   ├── ImagePreprocessingService.php
│   ├── AuditLogger.php
│   └── ReminderService.php
resources/
├── views/
│   ├── layouts/
│   │   └── app.blade.php (Template utama, sidebar, navbar bersih, tombol logout)
│   ├── auth/
│   │   └── login.blade.php
│   ├── dashboard/
│   │   └── index.blade.php (Ringkasan dokumen & widget pengingat jatuh tempo)
│   ├── documents/
│   │   ├── index.blade.php (List, Pencarian, & Filter Kategori)
│   │   ├── create.blade.php (Form Upload Berkas)
│   │   ├── verify.blade.php (Side-by-side Preview & OCR Form Koreksi Metadata)
│   │   └── show.blade.php (Detail Dokumen & File Preview)
│   ├── categories/
│   │   └── index.blade.php
│   ├── reports/
│   │   └── index.blade.php
│   └── audit_logs/
│       └── index.blade.php
```

---

## 4. Routing & Controller Matrix (Web Routes)

| Method | Route URI | Middleware / Role | Controller Action | Deskripsi |
|---|---|---|---|---|
| `GET` | `/login` | Guest | `AuthController@showLoginForm` | Halaman login |
| `POST` | `/login` | Guest | `AuthController@login` | Proses autentikasi |
| `POST` | `/logout` | Auth | `AuthController@logout` | Proses keluar aplikasi & hapus sesi |
| `GET` | `/dashboard` | Auth | `DashboardController@index` | Statistik, ringkasan & reminder jatuh tempo |
| `GET` | `/documents` | Auth | `DocumentController@index` | Daftar & pencarian dokumen operasional |
| `GET` | `/documents/upload` | Auth | `DocumentController@create` | Form upload berkas baru |
| `POST` | `/documents/upload` | Auth | `DocumentController@store` | Proses upload & jalankan OCR |
| `GET` | `/documents/{id}/verify` | Auth | `DocumentController@verify` | Halaman koreksi hasil OCR & pilih kategori |
| `PUT` | `/documents/{id}` | Auth | `DocumentController@updateDraft` | Simpan permanen hasil verifikasi |
| `GET` | `/documents/{id}` | Auth | `DocumentController@show` | Lihat detail & preview dokumen |
| `GET` | `/documents/{id}/file` | Auth | `DocumentController@streamFile` | Secure file download/stream |
| `GET` | `/documents/{id}/edit` | **Pemilik** | `DocumentController@edit` | Form edit dokumen tersimpan |
| `PUT` | `/documents/{id}/update` | **Pemilik** | `DocumentController@update` | Update data dokumen |
| `DELETE` | `/documents/{id}` | **Pemilik** | `DocumentController@destroy` | Hapus dokumen dan file fisik |
| `GET` | `/reports` | Auth | `ReportController@index` | Rekapitulasi & ekspor PDF/Excel |
| `GET` | `/audit-logs` | **Pemilik** | `AuditLogController@index` | Riwayat aktivitas sistem |