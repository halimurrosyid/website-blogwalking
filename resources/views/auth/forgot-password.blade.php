@extends('layouts.app')

@section('title', 'Lupa Password')

@section('content')
<div class="min-h-[75vh] flex items-center justify-center py-6">
    <div class="max-w-md w-full bg-white p-8 rounded-2xl shadow-sm border border-slate-200">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-amber-500 text-white font-bold text-2xl mb-3 shadow-md shadow-amber-100">
                🔑
            </div>
            <h1 class="text-2xl font-bold text-slate-900">Lupa Password?</h1>
            <p class="text-sm text-slate-500 mt-1">Masukkan alamat email Anda untuk menerima instruksi tautan reset password</p>
        </div>

        @if (session('status'))
            <div class="mb-5 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-start gap-2.5">
                <svg class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <div>
                    <span class="font-semibold">{{ session('status') }}</span>
                    @if (session('demo_reset_url'))
                        <div class="mt-2 pt-2 border-t border-emerald-200">
                            <span class="block text-slate-600 mb-1">Mode Lokal/Pengujian (Klik langsung tanpa buka email):</span>
                            <a href="{{ session('demo_reset_url') }}" class="inline-flex items-center gap-1 font-bold text-emerald-700 underline break-all hover:text-emerald-900">
                                Buka Formulir Reset Password &rarr;
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
            @csrf

            <div>
                <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">Alamat Email Terdaftar</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 text-sm transition"
                    placeholder="nama@email.com">
                @error('email')
                    <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="w-full py-3 px-4 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-semibold text-sm shadow-md shadow-amber-100 hover:shadow-lg transition cursor-pointer">
                Kirim Link Reset Password
            </button>

            <div class="text-center pt-2">
                <a href="{{ route('login') }}" class="text-xs font-bold text-slate-600 hover:text-slate-900 hover:underline inline-flex items-center gap-1">
                    &larr; Kembali ke Halaman Masuk
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
