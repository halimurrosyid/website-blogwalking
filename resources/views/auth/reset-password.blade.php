@extends('layouts.app')

@section('title', 'Perbarui Password')

@section('content')
<div class="min-h-[75vh] flex items-center justify-center py-6">
    <div class="max-w-md w-full bg-white p-8 rounded-2xl shadow-sm border border-slate-200">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-emerald-600 text-white font-bold text-2xl mb-3 shadow-md shadow-emerald-100">
                🔒
            </div>
            <h1 class="text-2xl font-bold text-slate-900">Perbarui Password</h1>
            <p class="text-sm text-slate-500 mt-1">Masukkan kata sandi baru untuk akun Blogwalker Anda</p>
        </div>

        <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
            @csrf

            <input type="hidden" name="token" value="{{ $token }}">

            <div>
                <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">Alamat Email</label>
                <input type="email" name="email" id="email" value="{{ old('email', $email) }}" required autofocus
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm transition"
                    placeholder="nama@email.com">
                @error('email')
                    <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">Password Baru</label>
                <div x-data="{ showPass: false }" class="relative">
                    <input :type="showPass ? 'text' : 'password'" name="password" id="password" required
                        class="w-full px-4 pr-11 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm transition"
                        placeholder="Minimal 8 karakter">
                    <button type="button" @click="showPass = !showPass" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none cursor-pointer" tabindex="-1">
                        <svg x-show="showPass" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg x-show="!showPass" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                        </svg>
                    </button>
                </div>
                @error('password')
                    <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">Konfirmasi Password Baru</label>
                <div x-data="{ showPass: false }" class="relative">
                    <input :type="showPass ? 'text' : 'password'" name="password_confirmation" id="password_confirmation" required
                        class="w-full px-4 pr-11 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm transition"
                        placeholder="Ulangi password baru">
                    <button type="button" @click="showPass = !showPass" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none cursor-pointer" tabindex="-1">
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

            <button type="submit" class="w-full py-3 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm shadow-md shadow-emerald-100 hover:shadow-lg transition cursor-pointer">
                Simpan Password Baru
            </button>

            <div class="text-center pt-2">
                <a href="{{ route('login') }}" class="text-xs font-bold text-slate-600 hover:text-slate-900 hover:underline">
                    &larr; Batalkan & Kembali ke Halaman Masuk
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
