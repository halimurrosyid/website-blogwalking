# 🌐 Blogwalker Pro — Link Building & Blogwalking Team Management Platform

<p align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="300" alt="Laravel Logo">
</p>

<p align="center">
  <span style="font-weight: bold; font-size: 1.1em;">Platform Manajemen Tim Blogwalker & Kampanye SEO Backlink Terpadu</span><br>
  Dirancang khusus untuk <strong>Shared Hosting (cPanel)</strong> dengan antarmuka <strong>100% Bebas Terminal (Zero Terminal / Web Installer)</strong>.
</p>

---

## 📌 Daftar Isi
1. [Tentang Sistem](#-tentang-sistem)
2. [Fitur Utama](#-fitur-utama)
   - [Portal Blogwalker (Tim Penulis Komentar)](#1-portal-blogwalker-tim-penulis-komentar)
   - [Portal Super Administrator](#2-portal-super-administrator)
   - [Web-Based Installation Wizard (GUI Installer)](#3-web-based-installation-wizard-gui-installer)
   - [Pemeliharaan Sistem Mandiri (Zero Terminal)](#4-pemeliharaan-sistem-mandiri-zero-terminal)
3. [Standar Keamanan Tingkat Tinggi (Security Hardening)](#-standar-keamanan-tingkat-tinggi-security-hardening)
4. [Panduan Instalasi di Hosting cPanel (100% GUI)](#-panduan-instalasi-di-hosting-cpanel-100-gui)
5. [Pengembangan Lokal (Developers Guide)](#-pengembangan-lokal-developers-guide)
6. [Tech Stack](#-tech-stack)

---

## 💡 Tentang Sistem

Dalam strategi SEO Off-Page, tim *blogwalking* bertugas mencari blog atau artikel relevan dan meninggalkan komentar berkualitas yang menyematkan backlink klien. Pengelolaan manual seringkali menimbulkan kendala:
* Dua atau lebih anggota tim mengomentari domain yang sama melebihi batas yang wajar (indikasi spam).
* Anggota tim membuang waktu berkomentar di domain yang ternyata sudah penuh kuotanya.
* Admin kewalahan merekap bukti screenshot, mencocokkan nomor rekening bank, dan menghitung komisi/gaji bulanan.
* Ketidaksesuaian target negara (TLD) dan kata kunci (*keyword*) yang ditanamkan.

**Blogwalker Pro** menyelesaikan seluruh masalah di atas melalui sistem terpusat berbasis web yang cepat, responsif, dan otomatis.

---

## 🚀 Fitur Utama

### 1. Portal Blogwalker (Tim Penulis Komentar)
* **Live Domain Checker (Pencegah Buang Waktu):**
  * Kotak pencarian AJAX cepat untuk memeriksa domain target (misal: `kontraktor.co.id`).
  * Sistem mendeteksi sisa slot kuota (contoh: *Tersedia: 2/5 URL* atau *Penuh: 5/5 URL*) dengan dukungan TLD bertingkat (*Multi-level TLD* seperti `.co.id`, `.sch.id`, `.web.id`, `.gov.uk`).
* **Form Submit Cepat dengan Fitur `Ctrl + V` dari Clipboard:**
  * Cukup ambil screenshot di Windows (`Win + Shift + S`) lalu tekan **`Ctrl + V`** di halaman form.
  * Preview gambar otomatis muncul tanpa perlu simpan file ke laptop.
  * Auto-kompresi gambar ke format **WebP** kualitas 80% sebelum diunggah (ukuran berkurang hingga ~90%, menghemat kuota dan inode hosting).
* **Antrean Target URL (Claim & Skip):**
  * Blogwalker dapat mengklaim URL dari pool antrean target yang disediakan oleh admin atau mencari URL mandiri.
* **Dashboard Penugasan & Saldo:**
  * Memantau TLD yang ditugaskan, kata kunci SEO, URL backlink klien, status verifikasi (Pending / Disetujui / Ditolak), target bulanan, dan total gaji yang siap dibayar.

---

### 2. Portal Super Administrator
* **Dashboard Metrik Real-Time:**
  * Statistik total submit, submission menunggu verifikasi, saldo belum dibayar, efisiensi tim, dan ringkasan kuota domain.
* **Antrean Verifikasi (Review Queue):**
  * Tinjau URL komentar dan perbesar screenshot bukti dengan modal *lightbox zoom*.
  * Aksi 1-klik: **Setujui (Approve)** atau **Tolak (Reject)** dengan input alasan penolakan (kuota domain otomatis dikembalikan jika ditolak).
  * Fitur **Bulk Approve** untuk menyetujui puluhan submission sekaligus.
* **Verifikasi Registrasi Blogwalker (KTP & Buku Rekening):**
  * Pelamar mendaftar mandiri via `/register` dengan data lengkap: Nama, Username, Email, Password, No WhatsApp/HP, Bank Konvensional (BCA, Mandiri, BNI, BRI, CIMB, Permata, dll), No Rekening, dan Atas Nama.
  * Upload foto KTP dan foto buku rekening/m-banking.
  * Super Admin dapat me-review dokumen via zoom lightbox, menyetujui akun pelamar sekaligus mengatur plotting TLD, keyword, dan targetnya, atau menolaknya dengan alasan jelas.
* **Manajemen Periode Bulanan & Evaluasi Target:**
  * Pengaturan target minimal bulanan (misal: 100 URL per bulan).
  * Evaluasi otomatis: blogwalker yang tidak mencapai target di akhir bulan berstatus *Disqualified* dan tidak diikutsertakan di periode bulan baru.
  * Fitur **Rollover Periode Baru** via tombol GUI.
* **Master Domain & Reset Kuota:**
  * Memantau daftar seluruh domain yang pernah dikomentari tim.
  * Tombol **Reset Domain** (individual atau massal) untuk membuka kembali kuota domain menjadi 0/5 jika kampanye SEO diulang.
* **Rekap Gaji / Payroll & Export CSV:**
  * Filter komisi berdasarkan rentang tanggal dan nama blogwalker.
  * Tombol tandai sudah dibayar (*Mark as Paid*) dan ekspor laporan ke format CSV.

---

### 3. Web-Based Installation Wizard (GUI Installer)
Aplikasi ini dilengkapi wizard instalasi interaktif layaknya **WordPress**:
* **Langkah 1 (Cek Server):** Pengecekan otomatis versi PHP (>= 8.2), ekstensi PHP (PDO, cURL, Mbstring, Fileinfo, GD, OpenSSL), dan hak akses tulis direktori.
* **Langkah 2 (Setup Database):** Form pengisian host, nama database, user, dan password MySQL (dengan tips pembuatan database cPanel). Sistem otomatis menguji koneksi, menulis file konfigurasi `.env`, dan mengeksekusi migrasi tabel.
* **Langkah 3 (Akun Super Admin):** Pembuatan akun Super Administrator pertama.
* **Langkah 4 (Selesai & Terkunci):** Installer otomatis membuat file gembok `storage/installed` sehingga halaman instalasi terkunci permanen demi keamanan server.

---

### 4. Pemeliharaan Sistem Mandiri (Zero Terminal)
Tersedia menu khusus **Sistem** (`/admin/system`) pada panel Super Admin untuk pengoperasian tanpa SSH/terminal:
* 🚀 **Bersihkan Seluruh Cache:** 1-klik untuk membersihkan cache view, config, dan route setelah upload perubahan file.
* 🔄 **Update Struktur Database (Migrate):** 1-klik untuk menjalankan migrasi tabel baru jika ada fitur tambahan di masa depan.
* 📁 **Perbaiki Storage Link:** 1-klik untuk menghubungkan direktori upload bukti foto dan KTP.
* 🌐 **Pengaturan URL Domain Website (Ganti Domain):** Ubah nama domain web langsung dari form GUI jika berpindah domain.
* 💾 **Download Backup SQL:** 1-klik untuk mengunduh salinan database lengkap dalam format `.sql` ke komputer tanpa membuka phpMyAdmin.

---

## 🔒 Standar Keamanan Tingkat Tinggi (Security Hardening)

1. **Anti-Polyglot / Web Shell Upload Hardening:**
   * Validasi binary header file gambar menggunakan `getimagesize()`.
   * Whitelist ketat MIME type (`image/jpeg`, `image/png`, `image/webp`).
   * Seluruh gambar di-encode ulang menjadi format WebP menggunakan library GD di server untuk membuang payload script PHP/polyglot yang disisipkan penyerang.
   * Direktori `storage/app/public/.htaccess` mematikan modul eksekusi PHP (`php_flag engine off`).
2. **Perlindungan Berkas Server (`.htaccess`):**
   * Pemblokiran akses langsung terhadap file rahasia: `.env`, `.git`, `.sqlite`, `.sql`, `.log`, `composer.json`, dan `artisan`.
3. **Proteksi CSV Formula Injection (CWE-1236):**
   * Sanitasi otomatis pada seluruh ekspor data CSV untuk karakter bahaya (`=`, `+`, `-`, `@`, `\t`, `\r`) agar terhindar dari eksekusi macro jahat di spreadsheet/Excel.
4. **Anti-SSRF (Server-Side Request Forgery):**
   * Validasi strict scheme `http/https` dan pemblokiran IP internal / localhost / loopback (`127.0.0.1`, `10.x.x.x`, `192.168.x.x`).
5. **Rate Limiting & Brute Force Defense:**
   * Pembatasan akses login/register (`throttle:10,1`), live domain checker (`throttle:60,1`), dan submit komentar (`throttle:20,1`).
6. **HTTP Security Headers:**
   * Dilengkapi middleware header keamanan: `X-Frame-Options: SAMEORIGIN`, `X-Content-Type-Options: nosniff`, `X-XSS-Protection`, `Referrer-Policy`, dan `Permissions-Policy`.

---

## 🌐 Panduan Instalasi di Hosting cPanel (100% GUI)

### Langkah 1: Buat Database di cPanel
1. Buka cPanel akun hosting Anda.
2. Masuk ke menu **MySQL Databases**.
3. Buat database baru (contoh: `u1234_blogwalker`).
4. Buat user database baru dan catat password-nya.
5. Di bagian **Add User to Database**, hubungkan user ke database tersebut dan centang opsi **ALL PRIVILEGES**.

### Langkah 2: Unggah Berkas Web
1. Buka menu **File Manager** di cPanel.
2. Masuk ke folder root domain/subdomain Anda (misal `public_html`).
3. Unggah file zip proyek ini lalu klik **Extract**.
4. Pastikan file `storage/installed` **tidak ada** (agar sistem mendeteksi instalasi baru).

### Langkah 3: Jalankan Web Installer di Browser
1. Buka domain Anda di browser: `https://domainanda.com`.
2. Halaman **Installation Wizard** akan otomatis tampil:
   * **Langkah 1:** Periksa syarat server (semua bercentang hijau). Klik *Lanjut ke Konfigurasi Database*.
   * **Langkah 2:** Masukkan Database Host (`127.0.0.1` atau `localhost`), Nama Database, Username, dan Password yang telah dibuat di cPanel. Klik *Tes Koneksi & Migrasi Tabel*.
   * **Langkah 3:** Masukkan Nama, Username, Email, dan Password untuk akun Super Administrator Anda. Klik *Simpan & Selesaikan Instalasi*.
   * **Langkah 4:** Instalasi selesai! Klik tombol *Masuk ke Halaman Login*.

---

## 💻 Pengembangan Lokal (Developers Guide)

Bagi pengembang yang ingin menjalankan pengujian atau menambah fitur di komputer lokal:

```bash
# 1. Clone repositori
git clone https://github.com/halimurrosyid/website-blogwalking.git
cd website-blogwalking

# 2. Install dependensi PHP
composer install

# 3. Setup environment
cp .env.example .env
php artisan key:generate

# 4. Migrasi database lokal
php artisan migrate

# 5. Jalankan server lokal
php artisan serve
```

### Menjalankan Pengujian Otomatis (*Automated Feature Tests*)
```bash
php artisan test --compact
```
*(Seluruh rangkaian pengujian meliputi registrasi, validasi berkas identitas, plotting target, proteksi domain, kuota 5 URL, rollover periode, pemeliharaan sistem, dan installer web).*

### Format Kode (Laravel Pint)
```bash
php vendor/bin/pint --format agent
```

---

## 🛠 Tech Stack

* **Backend:** PHP 8.3 & Laravel 11 / 12
* **Database:** MySQL / MariaDB (cPanel) & SQLite (Local / Testing)
* **Frontend:** Tailwind CSS (CDN) & Alpine.js (CDN) &mdash; *Tanpa dependensi Node.js di server produksi*
* **Image Processing:** PHP GD Extension (WebP Compression & Re-encoding)
* **Code Formatter:** Laravel Pint
* **Testing:** PHPUnit Framework

---

## 📄 Lisensi
Hak Cipta &copy; 2026 Blogwalker Pro. Open-source di bawah lisensi [MIT License](LICENSE).
