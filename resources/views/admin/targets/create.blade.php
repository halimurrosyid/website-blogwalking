@extends('layouts.app')

@section('title', 'Input Target URL Massal')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Breadcrumb & Header -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('admin.targets.index') }}" class="hover:text-emerald-700">Pool Target URL</a>
                <span>&rsaquo;</span>
                <span class="text-slate-800 font-medium">Input Massal</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Input Target URL / Subdomain</h1>
            <p class="text-sm text-slate-500 mt-0.5">Masukkan kumpulan link website yang siap dikomentari oleh tim blogwalker.</p>
        </div>
        <a href="{{ route('admin.targets.index') }}" class="px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold transition">
            &larr; Kembali
        </a>
    </div>

    <!-- Information Card -->
    <div class="p-4 bg-emerald-50/70 border border-emerald-200 rounded-xl text-xs text-emerald-900 flex items-start gap-3">
        <svg class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
        <div class="space-y-1">
            <div class="font-semibold text-emerald-950">Aturan Otomatis Sistem:</div>
            <ul class="list-disc list-inside space-y-0.5 text-emerald-800">
                <li>Satu baris untuk satu URL (misal: <code class="bg-white/80 px-1 py-0.5 rounded text-emerald-900">https://namaweb.co.id/artikel-1/</code> atau <code class="bg-white/80 px-1 py-0.5 rounded text-emerald-900">subdomain.blogspot.com/post-1</code>).</li>
                <li>Root domain diekstrak secara cerdas (termasuk domain bertingkat seperti <code class="bg-white/80 px-1 py-0.5 rounded text-emerald-900">.co.id</code>, <code class="bg-white/80 px-1 py-0.5 rounded text-emerald-900">.web.id</code>, dll).</li>
                <li>Sistem otomatis mendeteksi kuota 5 URL per root domain. Jika domain sudah penuh (5 URL), target akan otomatis berstatus terkunci.</li>
                <li>URL duplikat yang sudah pernah dimasukkan akan otomatis dilewati.</li>
            </ul>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-xs">
        <form method="POST" action="{{ route('admin.targets.store') }}" class="space-y-5">
            @csrf

            <!-- Textarea URLs -->
            <div>
                <label for="urls" class="block text-sm font-semibold text-slate-800 mb-1">
                    Daftar URL / Subdomain Target <span class="text-rose-500">*</span>
                </label>
                <div class="text-xs text-slate-500 mb-2">
                    Paste daftar URL hasil pencarian atau tools Anda di sini (1 URL per baris).
                </div>
                <textarea
                    id="urls"
                    name="urls"
                    rows="10"
                    required
                    placeholder="https://contoh-web.co.id/artikel-seo-pemula/
https://portal-berita.id/cara-menulis-artikel/
blog-pendidikan.blogspot.com/2026/tips-belajar.html
https://website-kuliner.com/resep-masakan-nusantara/"
                    class="w-full font-mono text-xs border-slate-300 rounded-lg p-3 focus:ring-emerald-500 focus:border-emerald-500 leading-relaxed"
                >{{ old('urls') }}</textarea>
                <div class="text-[11px] text-slate-400 mt-1">Bisa memuat puluhan hingga ratusan URL sekaligus.</div>
            </div>

            <!-- Target Keyword -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="keyword" class="block text-sm font-semibold text-slate-800 mb-1">
                        Anchor Text / Keyword Target <span class="text-xs font-normal text-slate-400">(Opsional)</span>
                    </label>
                    <input
                        type="text"
                        id="keyword"
                        name="keyword"
                        value="{{ old('keyword') }}"
                        placeholder="Contoh: Jasa Pembuatan Website"
                        class="w-full text-sm border-slate-300 rounded-lg px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500"
                    >
                    <div class="text-[11px] text-slate-400 mt-1">Kata kunci yang diarahkan kepada blogwalker untuk dipasang.</div>
                </div>

                <div>
                    <label for="notes" class="block text-sm font-semibold text-slate-800 mb-1">
                        Catatan Khusus <span class="text-xs font-normal text-slate-400">(Opsional)</span>
                    </label>
                    <input
                        type="text"
                        id="notes"
                        name="notes"
                        value="{{ old('notes') }}"
                        placeholder="Contoh: Beri komentar minimal 2 kalimat bermutu"
                        class="w-full text-sm border-slate-300 rounded-lg px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500"
                    >
                    <div class="text-[11px] text-slate-400 mt-1">Petunjuk pengerjaan yang akan dibaca oleh tim blogwalker.</div>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('admin.targets.index') }}" class="px-4 py-2 border border-slate-300 hover:bg-slate-50 text-slate-700 rounded-lg text-sm font-medium transition">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-semibold shadow-xs transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                    Proses & Masukkan ke Antrean
                </button>
            </div>
        </form>
    </div>

    <!-- Google Dork Helper Generator -->
    @include('components.dork-generator')
</div>
@endsection
