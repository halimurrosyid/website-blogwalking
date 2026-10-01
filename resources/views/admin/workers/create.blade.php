@extends('layouts.app')

@section('title', 'Tambah Blogwalker Baru')

@section('content')
<div class="max-w-2xl mx-auto py-4">

    <!-- Header -->
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Tambah Anggota Blogwalker Baru</h1>
            <p class="text-sm text-slate-500 mt-1">Buat akun untuk blogwalker dan tentukan plotting tugasnya.</p>
        </div>
        <a href="{{ route('admin.workers.index') }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900 px-3 py-2 rounded-lg bg-white border border-slate-200">
            &larr; Batal
        </a>
    </div>

    <!-- Form -->
    <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200 shadow-sm">
        <form method="POST" action="{{ route('admin.workers.store') }}" class="space-y-6">
            @csrf

            <h2 class="text-sm font-bold uppercase tracking-wider text-slate-400 pb-2 border-b border-slate-100">1. Informasi Akun Worker</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">Nama Lengkap <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 text-sm">
                </div>

                <div>
                    <label for="phone" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">No. WhatsApp / HP</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone') }}" placeholder="08xxxxxxxxxx"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 text-sm">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">Email Login <span class="text-rose-500">*</span></label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 text-sm">
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">Password <span class="text-rose-500">*</span></label>
                    <input type="password" name="password" id="password" required minlength="6"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 text-sm" placeholder="Minimal 6 karakter">
                </div>
            </div>

            <div>
                <label for="default_rate" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                    Tarif per Komentar Disetujui (Rupiah) <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <span class="absolute left-4 top-2.5 text-sm font-bold text-slate-400">Rp</span>
                    <input type="number" step="50" name="default_rate" id="default_rate" value="{{ old('default_rate', 700) }}" required
                        class="w-full pl-12 pr-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 text-sm font-bold text-slate-900">
                </div>
                <span class="text-[11px] text-slate-400 mt-1 block">Default: Rp 700 atau sesuaikan dengan tingkat kesulitan TLD.</span>
            </div>

            <h2 class="text-sm font-bold uppercase tracking-wider text-slate-400 pt-4 pb-2 border-b border-slate-100">2. Plotting Tugas Khusus</h2>

            <div>
                <label for="allowed_tlds" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                    Batasan Ekstensi Domain (Opsional)
                </label>
                <x-tld-selector name="allowed_tlds" id="allowed_tlds" :value="old('allowed_tlds', '')" />
            </div>

            <div>
                <label for="target_keywords" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                    Target Kata Kunci (Keywords)
                </label>
                <textarea name="target_keywords" id="target_keywords" rows="2"
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 text-sm"
                    placeholder="Contoh: jasa seo website, optimasi google terpercaya, konsultan seo">{{ old('target_keywords') }}</textarea>
            </div>

            <div>
                <label for="target_backlink_url" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                    URL Target Backlink
                </label>
                <input type="url" name="target_backlink_url" id="target_backlink_url" value="{{ old('target_backlink_url') }}"
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 text-sm font-mono"
                    placeholder="https://website-klien.com/layanan-seo">
            </div>

            <div>
                <label for="custom_instructions" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                    Catatan / Panduan Khusus untuk Worker Ini
                </label>
                <textarea name="custom_instructions" id="custom_instructions" rows="2"
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 text-sm"
                    placeholder="Contoh: Gunakan nama profil orang asli, komentar minimal 2 kalimat yang nyambung dengan isi post.">{{ old('custom_instructions') }}</textarea>
            </div>

            <div class="pt-2">
                <button type="submit" class="w-full py-3.5 px-6 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm shadow-md shadow-emerald-100 hover:shadow-lg transition cursor-pointer">
                    Simpan Worker & Aktifkan Plotting
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
