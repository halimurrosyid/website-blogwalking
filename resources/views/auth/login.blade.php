@extends('layouts.app')

@section('title', 'Masuk ke Akun')

@section('content')
<div class="min-h-[75vh] flex items-center justify-center py-6">
    <div class="max-w-md w-full bg-white p-8 rounded-2xl shadow-sm border border-slate-200" x-data="{
        fillCredentials(login, pass) {
            document.getElementById('login').value = login;
            document.getElementById('password').value = pass;
        }
    }">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-emerald-600 text-white font-bold text-2xl mb-3 shadow-md shadow-emerald-100">
                BW
            </div>
            <h1 class="text-2xl font-bold text-slate-900">Masuk ke Sistem</h1>
            <p class="text-sm text-slate-500 mt-1">Silakan masuk dengan akun Admin atau Blogwalker Anda</p>
        </div>

        <form method="POST" action="{{ route('login.post') }}" class="space-y-5">
            @csrf

            <div>
                <label for="login" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">Email atau Username</label>
                <input type="text" name="login" id="login" value="{{ old('login') }}" required autofocus
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm transition"
                    placeholder="nama@email.com atau username">
            </div>

            <div>
                <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">Password</label>
                <input type="password" name="password" id="password" required
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm transition"
                    placeholder="••••••••">
            </div>

            <div class="flex items-center justify-between text-sm">
                <label class="flex items-center text-slate-600 cursor-pointer">
                    <input type="checkbox" name="remember" class="w-4 h-4 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500">
                    <span class="ml-2 text-xs">Ingat saya</span>
                </label>
            </div>

            <button type="submit" class="w-full py-3 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm shadow-md shadow-emerald-100 hover:shadow-lg transition cursor-pointer">
                Masuk Sekarang
            </button>

            <div class="text-center pt-2">
                <span class="text-xs text-slate-500">Ingin bergabung ke tim? </span>
                <a href="{{ route('register') }}" class="text-xs font-bold text-emerald-600 hover:underline">
                    Daftar Sebagai Blogwalker Baru
                </a>
            </div>
        </form>

        <!-- Quick Demo Accounts (Sangat membantu untuk tes langsung) -->
        <div class="mt-8 pt-6 border-t border-slate-100 text-center">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-3">Akun Uji Coba Cepat (Klik untuk Isi):</p>
            <div class="grid grid-cols-2 gap-3">
                <button type="button" @click="fillCredentials('admin@blogwalker.local', 'password123')"
                    class="p-2.5 rounded-xl bg-slate-50 hover:bg-emerald-50 border border-slate-200 hover:border-emerald-300 text-left transition cursor-pointer">
                    <span class="block text-xs font-bold text-slate-800">👑 Admin</span>
                    <span class="block text-[11px] text-slate-500 truncate">admin@blogwalker.local</span>
                </button>
                <button type="button" @click="fillCredentials('blogwalker@blogwalker.local', 'password123')"
                    class="p-2.5 rounded-xl bg-slate-50 hover:bg-emerald-50 border border-slate-200 hover:border-emerald-300 text-left transition cursor-pointer">
                    <span class="block text-xs font-bold text-slate-800">✍️ Blogwalker (Semua Domain)</span>
                    <span class="block text-[11px] text-slate-500 truncate">blogwalker@blogwalker.local</span>
                </button>
            </div>
            <p class="text-[11px] text-slate-400 mt-3">Password default: <code class="bg-slate-100 px-1 py-0.5 rounded text-slate-600 font-mono">password123</code></p>
        </div>
    </div>
</div>
@endsection
