@extends('installer.layout', ['step' => 3])

@section('title', 'Langkah 3: Pembuatan Akun Super Admin')

@section('installer_content')
<div class="space-y-6">

    <div>
        <h2 class="text-lg font-bold text-slate-900">Buat Akun Super Administrator</h2>
        <p class="text-xs text-slate-500 mt-1">
            Akun ini akan memiliki hak akses penuh untuk mengelola target domain, plotting tim, verifikasi pendaftaran, dan laporan.
        </p>
    </div>

    <form method="POST" action="{{ route('install.admin.post') }}" class="space-y-4">
        @csrf

        <!-- Nama Admin -->
        <div>
            <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Nama Lengkap Super Admin *</label>
            <input type="text" name="name" id="name" value="{{ old('name', 'Super Administrator') }}" required
                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                placeholder="Nama Anda">
        </div>

        <!-- Username Admin -->
        <div>
            <label for="username" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Username Super Admin *</label>
            <input type="text" name="username" id="username" value="{{ old('username', 'admin') }}" required
                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm font-mono focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                placeholder="admin">
            <span class="text-[11px] text-slate-400 mt-0.5 block">Dapat digunakan untuk login ke sistem</span>
        </div>

        <!-- Email Admin -->
        <div>
            <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Alamat Email Super Admin *</label>
            <input type="email" name="email" id="email" value="{{ old('email', 'admin@blogwalker.local') }}" required
                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                placeholder="admin@domainanda.com">
        </div>

        <!-- Password -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Password *</label>
                <input type="password" name="password" id="password" required minlength="8"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                    placeholder="Minimal 8 karakter">
            </div>

            <div>
                <label for="password_confirmation" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Konfirmasi Password *</label>
                <input type="password" name="password_confirmation" id="password_confirmation" required minlength="8"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                    placeholder="Ulangi password">
            </div>
        </div>

        <!-- Actions -->
        <div class="pt-5 border-t border-slate-100 flex items-center justify-between">
            <a href="{{ route('install.database') }}" class="text-xs text-slate-500 hover:text-slate-700 font-semibold">
                &larr; Kembali
            </a>
            <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-100 transition cursor-pointer">
                Simpan & Selesaikan Instalasi &rarr;
            </button>
        </div>
    </form>

</div>
@endsection
