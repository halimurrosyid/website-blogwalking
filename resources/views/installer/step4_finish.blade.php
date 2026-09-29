@extends('installer.layout', ['step' => 4])

@section('title', 'Langkah 4: Instalasi Selesai')

@section('installer_content')
<div class="text-center py-6 space-y-6">

    <div class="w-20 h-20 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto shadow-inner ring-8 ring-emerald-50">
        <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
        </svg>
    </div>

    <div>
        <h2 class="text-2xl font-black text-slate-900 tracking-tight">Selamat! Instalasi Berhasil</h2>
        <p class="text-sm text-slate-500 mt-2 max-w-md mx-auto">
            Aplikasi Blogwalker Management System telah siap digunakan sepenuhnya di server web hosting Anda.
        </p>
    </div>

    <!-- Ringkasan Status Sistem -->
    <div class="bg-slate-50 border border-slate-200/80 rounded-2xl p-5 text-left space-y-3.5 max-w-lg mx-auto">
        <div class="text-xs font-bold uppercase tracking-wider text-slate-400">Ringkasan Sistem yang Disiapkan:</div>

        <div class="flex items-center gap-3 text-xs text-slate-700">
            <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold text-[10px] shrink-0">✓</span>
            <span>Konfigurasi database & file lingkungan (<strong>.env</strong>) terpasang.</span>
        </div>

        <div class="flex items-center gap-3 text-xs text-slate-700">
            <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold text-[10px] shrink-0">✓</span>
            <span>Seluruh struktur tabel database & relasi berhasil dimigrasikan.</span>
        </div>

        <div class="flex items-center gap-3 text-xs text-slate-700">
            <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold text-[10px] shrink-0">✓</span>
            <span>Periode bulan berjalan & pengaturan dasar sistem otomatis aktif.</span>
        </div>

        <div class="flex items-center gap-3 text-xs text-slate-700">
            <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold text-[10px] shrink-0">✓</span>
            <span>Akun Super Administrator telah dibuat dan siap login.</span>
        </div>

        <div class="flex items-center gap-3 text-xs text-slate-700">
            <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold text-[10px] shrink-0">✓</span>
            <span>Installer otomatis <strong>terkunci secara permanen</strong> (file <code>storage/installed</code> dibuat demi keamanan).</span>
        </div>
    </div>

    <!-- Tips Keamanan & Operasional -->
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-left max-w-lg mx-auto">
        <div class="flex items-start gap-2.5">
            <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div class="text-xs text-amber-800 space-y-1">
                <div class="font-bold">Tips Pengoperasian Hosting:</div>
                <p>
                    Pastikan Document Root domain/subdomain di cPanel mengarah ke folder <strong>public</strong>. Simpan informasi login Super Admin Anda dengan aman.
                </p>
            </div>
        </div>
    </div>

    <div class="pt-4">
        <a href="{{ route('login') }}" class="inline-flex items-center justify-center gap-2 px-8 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm shadow-lg shadow-emerald-200 transition transform hover:-translate-y-0.5">
            <span>Masuk ke Halaman Login</span>
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
            </svg>
        </a>
    </div>

</div>
@endsection
