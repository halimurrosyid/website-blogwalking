@extends('layouts.app')

@section('title', 'Edit Blogwalker - ' . $worker->name)

@section('content')
<div class="max-w-2xl mx-auto py-4">

    <!-- Header -->
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Edit Blogwalker & Plotting</h1>
            <p class="text-sm text-slate-500 mt-1">Perbarui profil blogwalker {{ $worker->name }} dan target tugasnya.</p>
        </div>
        <a href="{{ route('admin.workers.index') }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900 px-3 py-2 rounded-lg bg-white border border-slate-200">
            &larr; Kembali
        </a>
    </div>

    <!-- Form -->
    <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200 shadow-sm">
        <form method="POST" action="{{ route('admin.workers.update', $worker) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <h2 class="text-sm font-bold uppercase tracking-wider text-slate-400 pb-2 border-b border-slate-100">1. Informasi Akun Worker</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">Nama Lengkap <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name', $worker->name) }}" required
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 text-sm">
                </div>

                <div>
                    <label for="phone" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">No. WhatsApp / HP</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone', $worker->phone) }}" placeholder="08xxxxxxxxxx"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 text-sm">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">Email Login <span class="text-rose-500">*</span></label>
                    <input type="email" name="email" id="email" value="{{ old('email', $worker->email) }}" required
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 text-sm">
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">Password Baru (Opsional)</label>
                    <input type="password" name="password" id="password" minlength="6"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 text-sm" placeholder="Kosongkan jika tidak diganti">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="default_rate" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                        Tarif per Komentar Disetujui (Rupiah) <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-4 top-2.5 text-sm font-bold text-slate-400">Rp</span>
                        <input type="number" step="50" name="default_rate" id="default_rate" value="{{ old('default_rate', $worker->default_rate) }}" required
                            class="w-full pl-12 pr-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 text-sm font-bold text-slate-900">
                    </div>
                </div>

                <div>
                    <label for="is_active" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">Status Akun</label>
                    <select name="is_active" id="is_active" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 text-sm">
                        <option value="1" {{ old('is_active', $worker->is_active) ? 'selected' : '' }}>Aktif (Bisa Login & Submit)</option>
                        <option value="0" {{ !old('is_active', $worker->is_active) ? 'selected' : '' }}>Nonaktif (Diblokir)</option>
                    </select>
                </div>
            </div>

            <h2 class="text-sm font-bold uppercase tracking-wider text-slate-400 pt-4 pb-2 border-b border-slate-100">2. Plotting Tugas Khusus</h2>

            <div>
                <label for="allowed_tlds" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                    Batasan Ekstensi Domain (Opsional)
                </label>
                <input type="text" name="allowed_tlds" id="allowed_tlds" 
                    value="{{ old('allowed_tlds', is_array($assignment?->allowed_tlds) ? implode(', ', $assignment->allowed_tlds) : '') }}"
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 text-sm font-mono"
                    placeholder="Kosongkan = Bebas Semua Domain">
                <span class="text-[11px] text-emerald-600 font-semibold mt-1 block">💡 Kosongkan agar blogwalker bisa berkomentar di SEMUA domain (.id, .com, dll.).</span>
            </div>

            <div>
                <label for="target_keywords" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                    Target Kata Kunci (Keywords)
                </label>
                <textarea name="target_keywords" id="target_keywords" rows="2"
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 text-sm">{{ old('target_keywords', $assignment?->target_keywords) }}</textarea>
            </div>

            <div>
                <label for="target_backlink_url" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                    URL Target Backlink
                </label>
                <input type="url" name="target_backlink_url" id="target_backlink_url" value="{{ old('target_backlink_url', $assignment?->target_backlink_url) }}"
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 text-sm font-mono">
            </div>

            <div>
                <label for="custom_instructions" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                    Catatan / Panduan Khusus
                </label>
                <textarea name="custom_instructions" id="custom_instructions" rows="2"
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 text-sm">{{ old('custom_instructions', $assignment?->custom_instructions) }}</textarea>
            </div>

            <div class="pt-2">
                <button type="submit" class="w-full py-3.5 px-6 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm shadow-md shadow-emerald-100 hover:shadow-lg transition cursor-pointer">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
