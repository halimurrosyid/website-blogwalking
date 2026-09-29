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

        <!-- 4. Backup Database SQL Langsung Download -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between space-y-4 hover:border-slate-300 transition">
            <div>
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold mb-3">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                    </svg>
                </div>
                <h3 class="text-sm font-bold text-slate-900">Download Backup SQL</h3>
                <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                    Unduh file cadangan database lengkap langsung ke komputer Anda tanpa perlu login ke phpMyAdmin.
                </p>
            </div>
            <a href="{{ route('admin.system.backup') }}" class="w-full py-2.5 px-4 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shadow-xs transition cursor-pointer flex items-center justify-center gap-1.5">
                <span>Unduh File .SQL</span>
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            </a>
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
        </div>

    </div>

</div>
@endsection
