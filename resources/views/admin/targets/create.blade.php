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
            @php
                $activePeriod = app(\App\Services\PeriodService::class)->getActivePeriod();
                $maxUrlsPerDomain = $activePeriod?->max_urls_per_domain ?? 5;
            @endphp
            <ul class="list-disc list-inside space-y-0.5 text-emerald-800">
                <li>Satu baris untuk satu URL (misal: <code class="bg-white/80 px-1 py-0.5 rounded text-emerald-900">https://namaweb.co.id/artikel-1/</code> atau <code class="bg-white/80 px-1 py-0.5 rounded text-emerald-900">subdomain.blogspot.com/post-1</code>).</li>
                <li>Root domain diekstrak secara cerdas (termasuk domain bertingkat seperti <code class="bg-white/80 px-1 py-0.5 rounded text-emerald-900">.co.id</code>, <code class="bg-white/80 px-1 py-0.5 rounded text-emerald-900">.web.id</code>, dll).</li>
                <li>Sistem otomatis mendeteksi kuota maksimal {{ $maxUrlsPerDomain }} URL per root domain (sesuai aturan periode berjalan). Jika domain sudah mencapai batas ini, target akan otomatis berstatus "Domain Penuh".</li>
                <li>URL duplikat yang sudah pernah dimasukkan akan otomatis dilewati.</li>
            </ul>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-xs">
        <form method="POST" action="{{ route('admin.targets.store') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf

            <!-- Textarea & File Upload URLs -->
            <div>
                <label for="urls" class="block text-sm font-semibold text-slate-800 mb-1">
                    Daftar URL / Subdomain Target
                </label>
                <div class="text-xs text-slate-500 mb-2">
                    Paste daftar URL hasil pencarian atau tools Anda di sini (1 URL per baris). Sistem dioptimasi dengan batch processing sehingga dapat mengimpor ribuan URL secara instan tanpa timeout.
                </div>
                <textarea
                    id="urls"
                    name="urls"
                    rows="10"
                    placeholder="https://contoh-web.co.id/artikel-seo-pemula/
https://portal-berita.id/cara-menulis-artikel/
blog-pendidikan.blogspot.com/2026/tips-belajar.html
https://website-kuliner.com/resep-masakan-nusantara/"
                    class="w-full font-mono text-xs border-slate-300 rounded-lg p-3 focus:ring-emerald-500 focus:border-emerald-500 leading-relaxed"
                >{{ old('urls') }}</textarea>

                <!-- File Upload Option (.txt / .csv) -->
                <div class="mt-3 p-3.5 bg-slate-50 border border-slate-200 rounded-xl flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <span class="text-xs font-bold text-slate-800 block">📁 Atau Unggah File (.txt / .csv)</span>
                        <p class="text-[11px] text-slate-500">Sangat direkomendasikan untuk memasukkan ribuan hingga puluhan ribu URL sekaligus langsung dari file teks.</p>
                    </div>
                    <input type="file" name="url_file" accept=".txt,.csv,text/plain" class="text-xs text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-600 file:text-white hover:file:bg-emerald-700 cursor-pointer shrink-0">
                </div>
            </div>

            <!-- Kategori Tugas / Misi -->
            <div x-data="{
                taskType: '{{ old('task_type', 'comment') }}',
                rates: {
                    comment: {{ $taskTypes['comment']['default_rate'] ?? 750 }},
                    guestpost: {{ $taskTypes['guestpost']['default_rate'] ?? 2000 }},
                    social_media: {{ $taskTypes['social_media']['default_rate'] ?? 1500 }},
                    comment_high_dr: {{ $taskTypes['comment_high_dr']['default_rate'] ?? 1000 }},
                    internal_article: {{ $taskTypes['internal_article']['default_rate'] ?? 10000 }}
                },
                rewardAmount: '{{ old('reward_amount', '') }}',
                setPresetRate(amount) {
                    this.rewardAmount = amount;
                }
            }" class="space-y-5">

                <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-4">
                    <div>
                        <label for="task_type" class="block text-sm font-bold text-slate-800 mb-1">
                            🎯 Kategori Tugas / Misi <span class="text-rose-500">*</span>
                        </label>
                        <select
                            id="task_type"
                            name="task_type"
                            x-model="taskType"
                            @change="if (!rewardAmount) rewardAmount = rates[taskType]"
                            class="w-full text-sm font-semibold border-slate-300 rounded-lg px-3 py-2.5 bg-white focus:ring-emerald-500 focus:border-emerald-500 text-slate-900"
                        >
                            @foreach($taskTypes as $key => $type)
                                <option value="{{ $key }}" {{ old('task_type', 'comment') === $key ? 'selected' : '' }}>
                                    {{ $type['label'] }} (Standar: Rp {{ number_format($type['default_rate'], 0, ',', '.') }})
                                </option>
                            @endforeach
                        </select>
                        <div class="mt-2 text-xs text-slate-600 bg-white p-2.5 rounded-lg border border-slate-200">
                            <span class="font-semibold text-slate-800">Petunjuk Tugas:</span>
                            <span x-show="taskType === 'comment'">Komentar bermutu di artikel target dengan mencantumkan link/anchor keyword.</span>
                            <span x-show="taskType === 'guestpost'">Tulis & publikasikan artikel di blog eksternal dengan backlink ke URL target klien.</span>
                            <span x-show="taskType === 'social_media'">Posting konten relevan di sosial media (FB, X, LinkedIn, dll) dengan keyword & link klien.</span>
                            <span x-show="taskType === 'comment_high_dr'">Komentar di website otoritas tinggi (DR > 40). Pastikan domain memenuhi kriteria.</span>
                            <span x-show="taskType === 'internal_article'">Posting artikel lengkap di jaringan blog internal/PBN perusahaan sesuai standar SEO.</span>
                        </div>
                    </div>

                    <!-- Custom Reward Amount (Opsi A) -->
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label for="reward_amount" class="block text-sm font-bold text-slate-800">
                                💰 Tarif / Komisi per Tugas Selesai (Rp)
                            </label>
                            <span class="text-xs text-slate-500">Kosongkan jika ingin mengikuti tarif master</span>
                        </div>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-500 font-semibold text-sm">Rp</span>
                            <input
                                type="number"
                                step="50"
                                min="0"
                                id="reward_amount"
                                name="reward_amount"
                                x-model="rewardAmount"
                                placeholder="Biarkan kosong untuk tarif master default"
                                class="w-full text-sm font-semibold border-slate-300 rounded-lg pl-10 pr-3 py-2 bg-white focus:ring-emerald-500 focus:border-emerald-500"
                            >
                        </div>
                        <div class="flex flex-wrap items-center gap-1.5 mt-2">
                            <span class="text-[11px] text-slate-500 mr-1">Preset Cepat:</span>
                            <button type="button" @click="setPresetRate(500)" class="px-2 py-0.5 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded text-[11px] font-medium transition">Rp 500</button>
                            <button type="button" @click="setPresetRate(750)" class="px-2 py-0.5 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded text-[11px] font-medium transition">Rp 750</button>
                            <button type="button" @click="setPresetRate(1000)" class="px-2 py-0.5 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded text-[11px] font-medium transition">Rp 1.000</button>
                            <button type="button" @click="setPresetRate(1500)" class="px-2 py-0.5 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded text-[11px] font-medium transition">Rp 1.500</button>
                            <button type="button" @click="setPresetRate(2000)" class="px-2 py-0.5 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded text-[11px] font-medium transition">Rp 2.000</button>
                            <button type="button" @click="setPresetRate(10000)" class="px-2 py-0.5 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded text-[11px] font-medium transition">Rp 10.000</button>
                            <button type="button" @click="setPresetRate(25000)" class="px-2 py-0.5 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded text-[11px] font-medium transition">Rp 25.000</button>
                            <button type="button" @click="rewardAmount = ''" class="px-2 py-0.5 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded text-[11px] font-medium transition">Default</button>
                        </div>
                    </div>
                </div>

                <!-- URL Backlink Klien & Target Keyword -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="client_url" class="block text-sm font-semibold text-slate-800 mb-1">
                            🔗 URL Target Backlink Klien <span class="text-xs font-normal text-slate-400">(Opsional / Dianjurkan)</span>
                        </label>
                        <input
                            type="url"
                            id="client_url"
                            name="client_url"
                            value="{{ old('client_url') }}"
                            placeholder="https://klien-kami.com/jasa-seo/"
                            class="w-full text-sm border-slate-300 rounded-lg px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500"
                        >
                        <div class="text-[11px] text-slate-400 mt-1">Halaman web klien yang ingin disisipkan backlink oleh blogwalker.</div>
                    </div>

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
                </div>

                <div>
                    <label for="notes" class="block text-sm font-semibold text-slate-800 mb-1">
                        Catatan Khusus / Brief Tugas <span class="text-xs font-normal text-slate-400">(Opsional)</span>
                    </label>
                    <input
                        type="text"
                        id="notes"
                        name="notes"
                        value="{{ old('notes') }}"
                        placeholder="Contoh: Beri komentar minimal 2 kalimat bermutu / Artikel min 500 kata"
                        class="w-full text-sm border-slate-300 rounded-lg px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500"
                    >
                    <div class="text-[11px] text-slate-400 mt-1">Petunjuk pengerjaan yang akan dibaca langsung oleh blogwalker.</div>
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
