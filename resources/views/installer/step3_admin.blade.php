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
                <div x-data="{ showPass: false }" class="relative">
                    <input :type="showPass ? 'text' : 'password'" name="password" id="password" required minlength="8"
                        class="w-full px-3.5 pr-10 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                        placeholder="Minimal 8 karakter">
                    <button type="button" @click="showPass = !showPass" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none cursor-pointer" tabindex="-1" title="Lihat/Sembunyikan Password">
                        <svg x-show="showPass" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg x-show="!showPass" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div>
                <label for="password_confirmation" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Konfirmasi Password *</label>
                <div x-data="{ showPass: false }" class="relative">
                    <input :type="showPass ? 'text' : 'password'" name="password_confirmation" id="password_confirmation" required minlength="8"
                        class="w-full px-3.5 pr-10 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                        placeholder="Ulangi password">
                    <button type="button" @click="showPass = !showPass" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none cursor-pointer" tabindex="-1" title="Lihat/Sembunyikan Password">
                        <svg x-show="showPass" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg x-show="!showPass" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                        </svg>
                    </button>
                </div>
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
