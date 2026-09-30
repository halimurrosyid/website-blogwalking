@extends('layouts.app')

@section('title', 'Pemeliharaan Sistem & Hosting')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-5 border-b border-slate-200">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Pemeliharaan Sistem & Hosting</h1>
            <p class="text-sm text-slate-500 mt-1">
                Kelola pembaruan database, cache, storage link, dan backup langsung dari browser tanpa perlu menyentuh terminal/SSH hosting.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                Hosting Ready (100% GUI)
            </span>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <!-- 4 Tombol Aksi Mandiri (Zero Terminal Action Grid) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">

        <!-- 1. Bersihkan Cache -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between space-y-4 hover:border-slate-300 transition">
            <div>
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold mb-3">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </div>
                <h3 class="text-sm font-bold text-slate-900">Bersihkan Seluruh Cache</h3>
                <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                    Hapus cache view, konfigurasi, dan rute. Gunakan tombol ini setelah mengunggah perubahan file di hosting.
                </p>
            </div>
            <form method="POST" action="{{ route('admin.system.clear-cache') }}">
                @csrf
                <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-xs transition cursor-pointer flex items-center justify-center gap-1.5">
                    <span>Bersihkan Cache</span>
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
            </form>
        </div>

        <!-- 2. Pembaruan Struktur Database -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between space-y-4 hover:border-slate-300 transition">
            <div>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold mb-3">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2 1 3 3 3h10c2 0 3-1 3-3V7c0-2-1-3-3-3H7c-2 0-3 1-3 3z"/>
                    </svg>
                </div>
                <h3 class="text-sm font-bold text-slate-900">Update Struktur Database</h3>
                <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                    Jalankan migrasi tabel baru jika ada fitur tambahan di masa depan, tanpa membuka terminal atau SSH.
                </p>
            </div>
            <form method="POST" action="{{ route('admin.system.run-migrate') }}" onsubmit="return confirm('Jalankan pembaruan tabel database sekarang?')">
                @csrf
                <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition cursor-pointer flex items-center justify-center gap-1.5">
                    <span>Jalankan Migrasi</span>
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
            </form>
        </div>

        <!-- 3. Perbaiki Storage Link -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between space-y-4 hover:border-slate-300 transition">
            <div>
                <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold mb-3">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                    </svg>
                </div>
                <h3 class="text-sm font-bold text-slate-900">Perbaiki Storage Link</h3>
                <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                    Hubungkan folder upload agar screenshot bukti komentar dan foto KTP pendaftar dapat ditampilkan dengan benar.
                </p>
            </div>
            <form method="POST" action="{{ route('admin.system.fix-storage-link') }}">
                @csrf
                <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs shadow-xs transition cursor-pointer flex items-center justify-center gap-1.5">
                    <span>Perbarui Link Storage</span>
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
            </form>
        </div>

        <!-- 4. Backup Database SQL & Paket Lengkap -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between space-y-4 hover:border-slate-300 transition">
            <div>
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold mb-3">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                    </svg>
                </div>
                <h3 class="text-sm font-bold text-slate-900">Backup & Migrasi Data</h3>
                <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                    Unduh cadangan database SQL atau paket lengkap ZIP (termasuk foto bukti & KTP) langsung ke perangkat Anda.
                </p>
            </div>
            <div class="space-y-2">
                <a href="{{ route('admin.system.backup') }}" class="w-full py-2 px-3 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shadow-xs transition cursor-pointer flex items-center justify-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span>Unduh Database (.SQL)</span>
                </a>
                @if($zipSupported)
                <a href="{{ route('admin.system.backup-full') }}" class="w-full py-2 px-3 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs shadow-xs transition cursor-pointer flex items-center justify-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                    <span>Paket Lengkap (.ZIP)</span>
                </a>
                @endif
            </div>
        </div>

    </div>

    <!-- Pusat Migrasi, Backup & Restore Data -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4 M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Pusat Migrasi, Backup & Restore Data
                </h2>
                <p class="text-xs text-slate-500 mt-1">
                    Gunakan fitur ini untuk mencadangkan seluruh data website atau memulihkan data saat Anda berpindah ke domain baru / hosting baru tanpa risiko kehilangan data.
                </p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold {{ $zipSupported ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600' }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $zipSupported ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                    {{ $zipSupported ? 'Format ZIP & SQL Siap' : 'Format SQL Siap' }}
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 divide-y lg:divide-y-0 lg:divide-x divide-slate-100">
            <!-- 1. Ekspor Cadangan (Download Backup) -->
            <div class="p-6 space-y-4">
                <div class="flex items-center gap-2">
                    <span class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-700 font-bold text-xs flex items-center justify-center">1</span>
                    <h3 class="text-sm font-bold text-slate-900">Download Cadangan (Backup)</h3>
                </div>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Pilih tipe cadangan yang ingin Anda simpan ke komputer Anda:
                </p>

                <div class="space-y-3 pt-2">
                    <!-- Option A: Database SQL -->
                    <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <div class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-mono font-bold bg-amber-100 text-amber-800">.SQL</span>
                                <span>Cadangan Database Saja</span>
                            </div>
                            <p class="text-[11px] text-slate-500 mt-1">
                                Berisi data akun pengguna, target link, log komentar, target periode, dan komisi. Ukuran file sangat ringan.
                            </p>
                        </div>
                        <a href="{{ route('admin.system.backup') }}" class="px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shadow-xs transition cursor-pointer flex items-center justify-center gap-1.5 shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            <span>Unduh SQL</span>
                        </a>
                    </div>

                    <!-- Option B: Full Package ZIP -->
                    @if($zipSupported)
                    <div class="p-4 rounded-xl border border-emerald-200 bg-emerald-50/30 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <div class="text-xs font-bold text-emerald-900 flex items-center gap-1.5">
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-mono font-bold bg-emerald-600 text-white">.ZIP</span>
                                <span>Paket Lengkap Migrasi (DB + File Upload)</span>
                            </div>
                            <p class="text-[11px] text-emerald-700/80 mt-1">
                                Sangat direkomendasikan untuk pindah hosting/domain. Berisi database SQL + seluruh foto KTP, buku rekening, dan screenshot bukti komentar.
                            </p>
                        </div>
                        <a href="{{ route('admin.system.backup-full') }}" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition cursor-pointer flex items-center justify-center gap-1.5 shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            <span>Unduh ZIP</span>
                        </a>
                    </div>
                    @endif
                </div>
            </div>

            <!-- 2. Impor / Pulihkan Cadangan (Restore) -->
            <div class="p-6 space-y-4">
                <div class="flex items-center gap-2">
                    <span class="w-7 h-7 rounded-lg bg-blue-100 text-blue-700 font-bold text-xs flex items-center justify-center">2</span>
                    <h3 class="text-sm font-bold text-slate-900">Pulihkan Cadangan (Restore)</h3>
                </div>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Unggah file cadangan <code class="font-mono text-slate-700">.sql</code> atau paket <code class="font-mono text-slate-700">.zip</code> untuk memulihkan seluruh data secara instan:
                </p>

                <form method="POST" action="{{ route('admin.system.restore') }}" enctype="multipart/form-data" onsubmit="return confirm('PERINGATAN: Memulihkan cadangan akan menimpa data yang ada saat ini dengan data dari file cadangan. Apakah Anda yakin ingin melanjutkan proses pemulihan?')" class="space-y-4 pt-2">
                    @csrf
                    <div>
                        <label for="backup_file" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">
                            Pilih File Cadangan (.sql atau .zip) *
                        </label>
                        <input type="file" name="backup_file" id="backup_file" accept=".sql,.zip" required
                            class="w-full text-xs text-slate-600 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 file:cursor-pointer border border-slate-300 rounded-xl p-2 bg-white">
                        <span class="text-[11px] text-slate-400 mt-1 block">Maksimal ukuran file: 100 MB. Jika Anda mengunggah .zip, foto bukti dan database akan dipulihkan sekaligus.</span>
                    </div>

                    <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-[11px] flex items-start gap-2">
                        <span class="shrink-0 text-sm">⚠️</span>
                        <div>
                            <strong>Catatan Keamanan:</strong> Seluruh tabel database akan disinkronkan sesuai data dalam file cadangan. Pastikan file cadangan berasal dari sistem Blogwalker yang valid.
                        </div>
                    </div>

                    <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-xs transition cursor-pointer flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l4-4m0 0l4 4m-4-4v12"/>
                        </svg>
                        <span>Mulai Pemulihan Data (Restore)</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Panduan Migrasi ke Domain Baru -->
        <div class="bg-slate-50/70 p-6 border-t border-slate-100">
            <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Panduan Praktis Migrasi Ketika Ganti Domain Baru
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-xs">
                <div class="p-3 bg-white rounded-xl border border-slate-200">
                    <span class="font-bold text-emerald-700 block mb-1">Langkah 1: Di Domain Lama</span>
                    <p class="text-slate-500 text-[11px]">
                        Klik tombol <strong>Unduh ZIP (Paket Lengkap)</strong> di atas untuk mengamankan seluruh database dan foto bukti ke komputer Anda.
                    </p>
                </div>
                <div class="p-3 bg-white rounded-xl border border-slate-200">
                    <span class="font-bold text-emerald-700 block mb-1">Langkah 2: Di Domain Baru</span>
                    <p class="text-slate-500 text-[11px]">
                        Deploy website di domain baru Anda, masuk ke menu <strong>Sistem</strong>, lalu unggah file ZIP tadi pada formulir <strong>Pulihkan Cadangan</strong>.
                    </p>
                </div>
                <div class="p-3 bg-white rounded-xl border border-slate-200">
                    <span class="font-bold text-emerald-700 block mb-1">Langkah 3: Perbarui URL Domain</span>
                    <p class="text-slate-500 text-[11px]">
                        Isi nama domain baru Anda pada kotak <strong>Pengaturan URL Domain Website</strong> di bawah ini lalu klik Simpan. Selesai!
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Pengaturan Domain Aplikasi (Ketika Ganti Domain) -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-4 border-b border-slate-100">
            <div>
                <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                    </svg>
                    Pengaturan URL Domain Website (Ganti Domain)
                </h2>
                <p class="text-xs text-slate-500 mt-1">
                    Jika kelak Anda mengganti nama domain di hosting, Anda cukup memperbarui URL domain di bawah ini. Seluruh link gambar bukti dan sistem akan otomatis menyesuaikan diri.
                </p>
            </div>
            <span class="text-xs font-mono px-3 py-1 bg-slate-100 text-slate-700 rounded-lg shrink-0">
                Aktif: {{ $appUrl }}
            </span>
        </div>

        <form method="POST" action="{{ route('admin.system.update-domain') }}" class="pt-5 grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
            @csrf
            <div class="sm:col-span-2">
                <label for="app_url" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">
                    URL Domain Website Baru *
                </label>
                <input type="url" name="app_url" id="app_url" value="{{ old('app_url', $appUrl) }}" required
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm font-mono focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                    placeholder="https://domainbaruanda.com">
                <span class="text-[11px] text-slate-400 mt-1 block">Gunakan awalan https:// (atau http:// jika belum ada SSL)</span>
            </div>

            <div>
                <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition cursor-pointer flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Simpan URL Domain Baru</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Informasi Detail Status Server & Direktori -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Status Server -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4 flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/></svg>
                Spesifikasi & Lingkungan Server Hosting
            </h2>
            <div class="divide-y divide-slate-100 text-xs">
                <div class="py-2.5 flex justify-between">
                    <span class="text-slate-500 font-medium">Versi PHP Server</span>
                    <span class="font-mono font-bold text-slate-800">{{ $serverInfo['php_version'] }}</span>
                </div>
                <div class="py-2.5 flex justify-between">
                    <span class="text-slate-500 font-medium">Versi Framework</span>
                    <span class="font-mono font-bold text-slate-800">Laravel {{ $serverInfo['laravel_version'] }}</span>
                </div>
                <div class="py-2.5 flex justify-between">
                    <span class="text-slate-500 font-medium">Web Server</span>
                    <span class="font-mono text-slate-800">{{ $serverInfo['server_software'] }}</span>
                </div>
                <div class="py-2.5 flex justify-between">
                    <span class="text-slate-500 font-medium">Batas Memori PHP (Memory Limit)</span>
                    <span class="font-mono text-slate-800">{{ $serverInfo['memory_limit'] }}</span>
                </div>
                <div class="py-2.5 flex justify-between">
                    <span class="text-slate-500 font-medium">Batas Maksimal Upload Gambar</span>
                    <span class="font-mono text-slate-800">{{ $serverInfo['upload_max_filesize'] }} (POST: {{ $serverInfo['post_max_size'] }})</span>
                </div>
                <div class="py-2.5 flex justify-between">
                    <span class="text-slate-500 font-medium">Max Execution Time</span>
                    <span class="font-mono text-slate-800">{{ $serverInfo['max_execution_time'] }}</span>
                </div>
            </div>
        </div>

        <!-- Status Database & Storage -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4 flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2 1 3 3 3h10c2 0 3-1 3-3V7c0-2-1-3-3-3H7c-2 0-3 1-3 3z"/></svg>
                Status Database & Storage File
            </h2>
            <div class="divide-y divide-slate-100 text-xs">
                <div class="py-2.5 flex justify-between items-center">
                    <span class="text-slate-500 font-medium">Tipe Database Aktif</span>
                    <span class="px-2 py-0.5 rounded text-[11px] font-bold uppercase bg-slate-100 text-slate-700">
                        {{ $dbDriver }}
                    </span>
                </div>
                <div class="py-2.5 flex justify-between items-center">
                    <span class="text-slate-500 font-medium">Jumlah Tabel Terpasang</span>
                    <span class="font-mono font-bold text-slate-800">{{ $dbTablesCount }} Tabel</span>
                </div>
                <div class="py-2.5 flex justify-between items-center">
                    <span class="text-slate-500 font-medium">Status Folder Upload (public/storage)</span>
                    @if($publicStorageExists)
                        <span class="inline-flex items-center gap-1 text-emerald-600 font-bold">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            Terhubung (Aktif)
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 text-rose-600 font-bold">
                            <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                            Belum Terhubung
                        </span>
                    @endif
                </div>
                <div class="py-2.5 flex justify-between items-center">
                    <span class="text-slate-500 font-medium">Status Installer Sistem</span>
                    <span class="inline-flex items-center gap-1 text-emerald-600 font-bold">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        Terkunci Aman (storage/installed)
                    </span>
                </div>
                <div class="py-2.5 flex justify-between items-center">
                    <span class="text-slate-500 font-medium">Akses Terminal / SSH Diperlukan</span>
                    <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-100 text-emerald-800">
                        TIDAK DIPERLUKAN (0% Terminal)
                    </span>
                </div>
            </div>

            <!-- Tombol Beralih / Konfigurasi Ulang Database MySQL -->
            <div class="mt-5 pt-4 border-t border-slate-100">
                @if($dbDriver === 'sqlite')
                    <div class="p-3.5 rounded-xl bg-amber-50 border border-amber-200 mb-3">
                        <div class="flex items-start gap-2.5">
                            <span class="text-amber-600 text-base">ℹ️</span>
                            <div class="text-xs text-amber-900 leading-relaxed">
                                <span class="font-bold">Aplikasi saat ini berjalan menggunakan database internal SQLite.</span><br>
                                Data tersimpan di file lokal <code class="font-mono bg-amber-100 px-1 py-0.5 rounded text-[11px]">database/database.sqlite</code> (karena itu Anda bisa langsung login tanpa setup). Jika Anda ingin beralih ke database <strong>MySQL Hostinger</strong> Anda, klik tombol di bawah untuk membuka Wizard Pengaturan Database.
                            </div>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.system.reset-installer') }}" onsubmit="return confirm('Apakah Anda ingin membuka wizard instalasi untuk mengonfigurasi database MySQL? Sesi login akan keluar dan Anda akan diarahkan ke panduan instalasi database.')">
                    @csrf
                    <button type="submit" class="w-full py-2.5 px-4 rounded-xl {{ $dbDriver === 'sqlite' ? 'bg-amber-600 hover:bg-amber-700 text-white' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' }} font-bold text-xs shadow-xs transition cursor-pointer flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2 1 3 3 3h10c2 0 3-1 3-3V7c0-2-1-3-3-3H7c-2 0-3 1-3 3z"/>
                        </svg>
                        <span>{{ $dbDriver === 'sqlite' ? 'Beralih ke Database MySQL Hostinger (Wizard)' : 'Konfigurasi Ulang Database MySQL' }}</span>
                    </button>
                </form>
            </div>
        </div>

    </div>

</div>
@endsection
