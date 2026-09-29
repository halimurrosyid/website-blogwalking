# Panduan & Catatan Khusus AI (AI System Context & Standing Rules)

Dokumen ini ditujukan untuk seluruh asisten kecerdasan buatan (AI Assistants seperti Claude, ChatGPT, Cursor, Windsurf, GitHub Copilot, Gemini, DeepSeek, Antigravity, dll.) yang membaca codebase ini di masa depan.

---

## 📌 Ringkasan Penting Sistem (Quick Reference)

| Komponen | Penjelasan & Aturan Wajib |
| :--- | :--- |
| **Framework & Versi** | Laravel 11 / 12, PHP 8.3 |
| **Target Hosting** | cPanel Shared Hosting (TIDAK ADA akses terminal/SSH untuk user awam) |
| **Prinsip UI** | Menggunakan **Tailwind CSS & Alpine.js via CDN**. Dilarang mewajibkan `npm run build` di server produksi |
| **Role User** | Hanya ada 2 role: `'admin'` dan `'blogwalker'`. Jangan gunakan `'worker'` sebagai nama role |
| **Aturan Kuota Domain** | Maksimal 5 URL per root domain. Pengecekan multi-level TLD ada di `app/Services/DomainService.php` |
| **Sistem Periode** | Periode bulanan (`periods`). Super Admin mengevaluasi target minimal dan melakukan rollover via GUI |
| **Pendaftaran Mandiri** | Calon tim daftar via `/register` (upload foto KTP & buku tabungan) &rarr; butuh approval Super Admin |
| **Web Installer** | Wizard instalasi GUI di `/install` (seperti WordPress). Terkunci jika ada `storage/installed` |
| **Menu Sistem Admin** | Di `/admin/system`, menyediakan tombol GUI untuk clear cache, migrate DB, storage link, ganti URL domain, dan backup SQL |

---

## 🔒 Standar Keamanan yang Harus Dipertahankan (Do Not Break)

1. **Upload Berkas:**
   * Selalu gunakan [`app/Services/ImageUploadService.php`](file:///g:/My%20Drive/Coding%20Kantor/Aplikasi%20Blogwalker/app/Services/ImageUploadService.php).
   * Validasi wajib memeriksa binary header (`getimagesize()`), MIME type whitelist (`image/jpeg`, `image/png`, `image/webp`), dan selalu di-encode ulang menjadi format WebP untuk membuang payload polyglot/shell script.
   * Path disimpan relatif di database (contoh: `documents/ktp_123.webp`).
2. **Proteksi File Sensitif (.htaccess):**
   * File `.htaccess` di root dan `public/.htaccess` memblokir akses ke `.env`, `.git`, `.sql`, `.sqlite`, dan file sensitif lainnya.
   * Folder `storage/app/public/.htaccess` mematikan engine PHP (`php_flag engine off`) untuk mencegah eksekusi file berbahaya.
3. **Pencegahan Formula Injection (CSV):**
   * Saat mengekspor laporan review atau pembayaran ke CSV di controller, selalu gunakan metode sanitasi untuk karakter berbahaya (`=`, `+`, `-`, `@`, `\t`, `\r`) agar tidak dapat dieksekusi di Microsoft Excel.
4. **Pencegahan SSRF:**
   * Validasi domain pada `DomainService.php` memblokir skema berbahaya dan IP internal / localhost / loopback.

---

## 🧪 Aturan Pengujian Otomatis (Automated Testing Rules)

Semua pengujian berada di folder `tests/Feature/`. Setiap kali menambah atau mengubah fitur, jalankan:
```bash
php artisan test --compact
```
Atau uji spesifik file yang diubah:
```bash
php artisan test --filter=NamaTestClass --compact
```

### Jebakan Pengujian (Known Gotchas):
1. **Model Period:** Kolom `month` (1-12) dan `year` (contoh: 2026) adalah NOT NULL pada skema database. Jangan lupa menyertakan kedua kolom ini saat membuat data period baru.
2. **Assignments Target Keywords:** Kolom `target_keywords` bertipe teks (string). Jika user memasukkan daftar keyword yang dipisahkan koma, simpan sebagai string mentah atau gabungkan dengan koma.
3. **EnsureInstalled Middleware:** Middleware ini secara otomatis dibypass saat `app()->environment('testing')` agar tidak mengganggu pengujian fungsionalitas lain. Jika ingin menguji middleware installer secara spesifik, gunakan `config(['app.enforce_installed_middleware' => true])`.

---

## 🎨 Format Kode (Code Formatting)

Sebelum memfinalisasi perubahan, selalu jalankan Laravel Pint untuk memastikan format kode konsisten:
```bash
php vendor/bin/pint --format agent
```
*(Jangan gunakan opsi `--test`, cukup gunakan `--format agent`).*
