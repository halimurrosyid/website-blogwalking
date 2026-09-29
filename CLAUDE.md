# AI Development Guidelines & System Architecture (CLAUDE.md)
> Dokumen ini adalah panduan resmi untuk semua asisten AI (Claude, Copilot, Cursor, ChatGPT, Antigravity, dll.) yang bekerja pada repositori Aplikasi Blogwalker Management System.

---

## 1. Ikhtisar Aplikasi & Model Bisnis (Core Business Domain)
Aplikasi ini adalah **Sistem Manajemen Tim Blogwalker (Link Building)** berbasis Laravel:
* **Peran (Roles):**
  * `admin`: Super Administrator (pemilik sistem / supervisor).
  * `blogwalker`: Anggota tim yang bertugas mencari blog & berkomentar menanamkan backlink.
* **Aturan Kuota Domain:**
  * Maksimal **5 URL** komentar per satu root domain (misal: 1 domain hanya boleh dikomentari maksimal 5 URL berbeda). Jika sudah mencapai limit, domain terkunci (`is_locked = true`).
  * Root domain diekstrak menggunakan [`app/Services/DomainService.php`](file:///g:/My%20Drive/Coding%20Kantor/Aplikasi%20Blogwalker/app/Services/DomainService.php) yang mendukung Multi-level TLD (.co.id, .sch.id, .ac.id, .gov.uk, dll).
* **Plotting & Penugasan Bulanan (Monthly Periods):**
  * Setiap blogwalker mendapatkan penugasan (`assignments`) berupa: TLD yang diizinkan (misal `.co.id`, `.web.id`), keyword target, URL backlink klien, dan target minimal bulanan (default: 100 komentar).
  * Setiap periode bulan (misal: Oktober 2026), kinerja dievaluasi. Jika blogwalker tidak mencapai target minimal, status menjadi `disqualified` dan tidak otomatis diikutsertakan di periode bulan berikutnya.
  * Super Admin dapat mengunci periode dan melakukan **Rollover** ke periode baru melalui GUI (`/admin/periods`).
* **Registrasi & Approval:**
  * Calon blogwalker dapat mendaftar mandiri via `/register` dengan menyertakan Nama, Username, Email, Password, No WhatsApp/HP, Bank Konvensional (BCA, Mandiri, BNI, BRI, dll), No Rekening, Foto KTP, dan Foto Buku Tabungan/M-Banking.
  * Akun baru berstatus `pending` dan **tidak bisa login** sampai di-approve Super Admin di `/admin/registrations`. Saat di-approve, Super Admin langsung mengatur plotting targetnya.

---

## 2. Arsitektur Shared Hosting (Prinsip "Nol Terminal / Zero Terminal")
Aplikasi ini dirancang khusus untuk berjalan di **cPanel Shared Hosting**:
1. **Tidak Ada Build Tools Node.js di Server Produksi:**
   * Tampilan UI dibangun menggunakan **Tailwind CSS & Alpine.js via CDN**.
   * **JANGAN PERNAH** menambahkan dependensi yang mengharuskan user menjalankan `npm run build` di server hosting.
2. **Web-Based Installation Wizard (GUI Installer):**
   * Diatur oleh middleware [`EnsureInstalled`](file:///g:/My%20Drive/Coding%20Kantor/Aplikasi%20Blogwalker/app/Http/Middleware/EnsureInstalled.php).
   * Jika file `storage/installed` belum ada, akses web otomatis diarahkan ke wizard `/install` (seperti WordPress).
   * Setelah instalasi selesai, file `storage/installed` dibuat dan wizard otomatis terkunci permanen.
3. **Menu Pemeliharaan Sistem Mandiri (`/admin/system`):**
   * Super Admin dapat membersihkan cache (`optimize:clear`), menjalankan migrasi (`migrate --force`), memperbaiki storage link, mengunduh backup database (`.sql`), dan mengganti URL domain langsung dari browser tanpa terminal/SSH.

---

## 3. Aturan & Konvensi Kode (Coding Conventions)
* **PHP:** PHP 8.3. Selalu gunakan *type-hints* parameter dan *explicit return types* pada semua method.
* **Role Naming:** Selalu gunakan string `'blogwalker'` untuk anggota tim (bukan `'worker'`).
* **Route Naming:**
  * Prefix Admin: `admin.*` (Middleware: `['auth', 'admin']`)
  * Prefix Blogwalker: `blogwalker.*` (Middleware: `['auth', 'blogwalker']`)
* **Penyimpanan Berkas Gambar:**
  * Selalu gunakan [`app/Services/ImageUploadService.php`](file:///g:/My%20Drive/Coding%20Kantor/Aplikasi%20Blogwalker/app/Services/ImageUploadService.php).
  * Gambar otomatis disanitasi, divalidasi binary header (`getimagesize()`), dan dikonversi ke format **WebP** kualitas 80% untuk hemat disk space dan mencegah polyglot file execution.
  * Seluruh path di database disimpan secara **relatif** (contoh: `documents/ktp_123.webp`, `submissions/ss_456.webp`), jangan simpan domain absolut.

---

## 4. Perangkap Penting Saat Testing (Testing Gotchas)
* Database testing menggunakan **SQLite in-memory** (`:memory:`).
* **Period Constraints:** Model `Period` mewajibkan kolom `month` (angka 1-12) dan `year` (angka 4 digit). Jika membuat factory atau mock `Period`, selalu sertakan `month` dan `year`.
* **Assignments Target Keywords:** Kolom `target_keywords` di tabel `assignments` bertipe `text` (string). Jangan menginput tipe `array` langsung ke Eloquent tanpa casting.
* **Middleware Testing:** `EnsureInstalled` membypass installer saat testing (`app()->environment('testing')`) kecuali `config(['app.enforce_installed_middleware' => true])` diaktifkan secara sengaja untuk mengetes installer.

---

## 5. Menjalankan Format & Pengujian
Sebelum menyerahkan pekerjaan:
```bash
# 1. Format kode sesuai standar Laravel Pint
php vendor/bin/pint --format agent

# 2. Jalankan test suite otomatis
php artisan test --compact
```
Semua test wajib berstatus **PASSED** (hijau) tanpa ada regresi.
