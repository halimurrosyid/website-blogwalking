@extends('installer.layout', ['step' => 2])

@section('title', 'Langkah 2: Konfigurasi Database')

@section('installer_content')
<div class="space-y-6" x-data="{
    connectionType: 'mysql',
    loading: false
}">

    <div>
        <h2 class="text-lg font-bold text-slate-900">Pengaturan Database (MySQL / MariaDB)</h2>
        <p class="text-xs text-slate-500 mt-1">
            Masukkan kredensial database yang telah Anda buat di cPanel Hosting Anda.
        </p>
    </div>

    <!-- cPanel Helper Box -->
    <div class="p-4 rounded-xl bg-blue-50 border border-blue-200 text-blue-900 text-xs flex items-start gap-2.5">
        <svg class="w-5 h-5 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <div class="space-y-1 leading-relaxed">
            <strong class="font-bold">Tips Pengguna cPanel:</strong>
            <p>1. Buka cPanel &rarr; masuk ke menu <strong>MySQL Databases</strong>.</p>
            <p>2. Buat database baru (misal: <code class="font-mono bg-blue-100 px-1 py-0.5 rounded">usercpanel_blogwalker</code>) dan buat user baru.</p>
            <p>3. Tambahkan user ke database tersebut dan centang <strong>ALL PRIVILEGES</strong>.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('install.database.post') }}" @submit="loading = true" class="space-y-4">
        @csrf

        <!-- Connection Type Selection -->
        <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Tipe Database</label>
            <div class="grid grid-cols-2 gap-3">
                <label class="flex items-center gap-2 p-3 rounded-xl border cursor-pointer transition"
                    :class="connectionType === 'mysql' ? 'border-emerald-500 bg-emerald-50/40 text-emerald-900 font-bold' : 'border-slate-200 bg-white text-slate-600'">
                    <input type="radio" name="db_connection" value="mysql" x-model="connectionType" class="text-emerald-600">
                    <span class="text-xs">MySQL / MariaDB (cPanel)</span>
                </label>
                <label class="flex items-center gap-2 p-3 rounded-xl border cursor-pointer transition"
                    :class="connectionType === 'sqlite' ? 'border-emerald-500 bg-emerald-50/40 text-emerald-900 font-bold' : 'border-slate-200 bg-white text-slate-600'">
                    <input type="radio" name="db_connection" value="sqlite" x-model="connectionType" class="text-emerald-600">
                    <span class="text-xs">SQLite (Lokal / Simple)</span>
                </label>
            </div>
        </div>

        <!-- MySQL Fields -->
        <template x-if="connectionType === 'mysql'">
            <div class="space-y-4 pt-2">
                <div class="grid grid-cols-3 gap-3">
                    <div class="col-span-2">
                        <label for="db_host" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Database Host *</label>
                        <input type="text" name="db_host" id="db_host" value="{{ old('db_host', $defaultHost) }}" required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm font-mono focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            placeholder="127.0.0.1 atau localhost">
                    </div>
                    <div>
                        <label for="db_port" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Port *</label>
                        <input type="text" name="db_port" id="db_port" value="{{ old('db_port', $defaultPort) }}" required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm font-mono focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            placeholder="3306">
                    </div>
                </div>

                <div>
                    <label for="db_database" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Nama Database *</label>
                    <input type="text" name="db_database" id="db_database" value="{{ old('db_database', $defaultDatabase) }}" required
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm font-mono focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                        placeholder="Contoh: usercpanel_blogwalker">
                </div>

                <div>
                    <label for="db_username" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Username Database *</label>
                    <input type="text" name="db_username" id="db_username" value="{{ old('db_username', $defaultUsername) }}" required
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm font-mono focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                        placeholder="Contoh: usercpanel_bwuser">
                </div>

                <div>
                    <label for="db_password" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Password Database</label>
                    <input type="password" name="db_password" id="db_password"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm font-mono focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                        placeholder="Password user database MySQL Anda">
                </div>
            </div>
        </template>

        <!-- SQLite Fields -->
        <template x-if="connectionType === 'sqlite'">
            <div class="pt-2">
                <label for="db_database_sqlite" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Nama File Database SQLite</label>
                <input type="text" name="db_database" id="db_database_sqlite" value="database.sqlite" required
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm font-mono focus:ring-2 focus:ring-emerald-500 focus:outline-none">
            </div>
        </template>

        <!-- Actions -->
        <div class="pt-5 border-t border-slate-100 flex items-center justify-between">
            <a href="{{ route('install.index') }}" class="text-xs text-slate-500 hover:text-slate-700 font-semibold">
                &larr; Kembali
            </a>
            <button type="submit" :disabled="loading" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-100 transition cursor-pointer disabled:opacity-50">
                <span x-show="!loading">Tes Koneksi & Migrasi Tabel &rarr;</span>
                <span x-show="loading" x-cloak class="flex items-center gap-1.5">
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    Menghubungkan & Memigrasi Database...
                </span>
            </button>
        </div>
    </form>

</div>
@endsection
