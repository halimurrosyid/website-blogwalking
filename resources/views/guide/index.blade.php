@extends('layouts.app')

@section('title', 'Buku Panduan Penggunaan')

@section('content')
<div class="space-y-6" x-data="{
    tab: '{{ $activeTab }}',
    search: '',
    matchesSearch(text) {
        if (!this.search.trim()) return true;
        return text.toLowerCase().includes(this.search.toLowerCase().trim());
    }
}">

    <!-- Header Banner -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-emerald-950 text-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-800 relative overflow-hidden">
        <div class="relative z-10 max-w-2xl">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 text-xs font-semibold mb-3 border border-emerald-500/30">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                Pusat Bantuan & Dokumentasi Resmi
            </span>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Panduan Penggunaan BlogwalkerPro</h1>
            <p class="text-slate-300 text-xs sm:text-sm mt-2 leading-relaxed">
                Pelajari alur operasional link building, tata cara pengerjaan tugas, verifikasi bukti screenshot, aturan kuota domain, hingga pencairan komisi (payroll).
            </p>
        </div>
        <div class="absolute -right-8 -bottom-8 w-64 h-64 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
    </div>

    <!-- Navigation Tabs & Search -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-3 sm:p-4 rounded-2xl border border-slate-200 shadow-xs">
        <!-- Tabs -->
        <div class="flex flex-wrap gap-1.5 text-xs font-bold">
            @if($isAdmin)
                <button type="button" @click="tab = 'admin'" 
                    :class="tab === 'admin' ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'" 
                    class="px-4 py-2 rounded-xl transition cursor-pointer flex items-center gap-1.5">
                    <span>👑 Panduan Super Admin</span>
                </button>
            @endif

            <button type="button" @click="tab = 'worker'" 
                :class="tab === 'worker' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'" 
                class="px-4 py-2 rounded-xl transition cursor-pointer flex items-center gap-1.5">
                <span>✍️ Panduan Blogwalker (Worker)</span>
            </button>

            <button type="button" @click="tab = 'faq'" 
                :class="tab === 'faq' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'" 
                class="px-4 py-2 rounded-xl transition cursor-pointer flex items-center gap-1.5">
                <span>💡 Tanya Jawab (FAQ)</span>
            </button>
        </div>

        <!-- Quick Filter Search -->
        <div class="relative w-full sm:w-64">
            <input type="text" x-model="search" placeholder="Cari panduan / topik..." 
                class="w-full text-xs pl-8 pr-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 bg-slate-50 focus:bg-white transition">
            <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>
    </div>

    <!-- TAB 1: PANDUAN BLOGWALKER (WORKER) -->
    <div x-show="tab === 'worker'" x-cloak class="space-y-6">

        <!-- Banner Info Worker -->
        <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 text-xs text-emerald-900 flex items-start gap-3">
            <svg class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
            <div>
                <span class="font-bold">Panduan Kerja Tim Lapangan:</span> Halaman ini berisi instruksi praktis bagi seluruh Blogwalker dalam mengambil tugas, mengeksekusi komentar, mengirimkan screenshot bukti yang valid, dan mengamankan pencairan komisi ke rekening bank.
            </div>
        </div>

        <!-- Step 1: Memahami Dashboard & Target -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 space-y-4">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-xl bg-emerald-600 text-white font-bold text-sm flex items-center justify-center shrink-0">1</span>
                <div>
                    <h2 class="text-base font-bold text-slate-900">Memahami Dashboard & Target Periode</h2>
                    <p class="text-xs text-slate-500">Cek status akun dan sisa hari sebelum batas akhir cutoff bulanan.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-center">
                <div class="space-y-3 text-xs text-slate-600 leading-relaxed">
                    <p>Setelah login, Anda akan langsung melihat halaman <strong>Dashboard Saya</strong>:</p>
                    <ul class="list-disc list-inside space-y-1.5 ml-1">
                        <li><strong>Status Akun:</strong> Pastikan status Anda adalah <span class="text-emerald-600 font-bold">Aktif</span>. Jika ditangguhkan, Anda belum bisa mengirimkan tugas.</li>
                        <li><strong>Target Minimal Periode:</strong> Jumlah minimal komentar yang harus Anda selesaikan dalam bulan ini.</li>
                        <li><strong>Countdown Sisa Hari:</strong> Saat deadline mendekat (&le; 5 hari lagi), muncul peringatan merah yang mengingatkan Anda untuk segera menuntaskan target sebelum evaluasi sistem.</li>
                        <li><strong>Plotting Ekstensi Anda:</strong> Menampilkan domain apa saja yang diizinkan untuk Anda kerjakan (misal: <code>.com</code>, <code>.id</code>).</li>
                    </ul>
                </div>
                <!-- UI Preview Card: Dashboard Worker -->
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden text-xs">
                    <div class="bg-slate-900 text-white px-4 py-2.5 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-rose-400"></span>
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                            <span class="ml-2 font-mono text-[11px] text-slate-300 font-semibold">Dashboard Utama Blogwalker</span>
                        </div>
                        <span class="px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 text-[10px] font-bold">Akun Aktif</span>
                    </div>
                    <div class="p-4 space-y-3.5 bg-slate-50/50">
                        <div class="flex items-center justify-between bg-white p-3 rounded-xl border border-slate-200 shadow-xs">
                            <div>
                                <div class="text-[11px] text-slate-500">Halo, Blogwalker</div>
                                <div class="font-extrabold text-sm text-slate-900">Budi Pratama</div>
                            </div>
                            <div class="text-right">
                                <div class="text-[11px] text-slate-500">Komisi Disetujui</div>
                                <div class="font-extrabold text-sm text-emerald-600 font-mono">Rp 48.750</div>
                            </div>
                        </div>
                        <div class="p-2.5 bg-amber-500/10 border border-amber-300/60 rounded-xl text-amber-900 flex items-center justify-between text-[11px]">
                            <span class="font-bold flex items-center gap-1.5">
                                <span>⏳</span> Periode: Oktober 2026
                            </span>
                            <span class="font-bold px-2 py-0.5 bg-amber-200 text-amber-900 rounded-md text-[10px]">Tersisa 12 Hari</span>
                        </div>
                        <div class="bg-white p-3 rounded-xl border border-slate-200 shadow-xs space-y-2">
                            <div class="flex justify-between items-center text-[11px]">
                                <span class="font-bold text-slate-800">Target: 100 Komentar</span>
                                <span class="font-bold text-emerald-600 font-mono">65 / 100 (65%)</span>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
                                <div class="bg-emerald-600 h-2.5 rounded-full" style="width: 65%"></div>
                            </div>
                            <div class="flex justify-between text-[10px] text-slate-500 pt-0.5">
                                <span>✓ 65 Disetujui</span>
                                <span>⏳ 4 Pending</span>
                                <span>🎯 Butuh 35 Lagi</span>
                            </div>
                        </div>
                        <div class="bg-white p-2.5 rounded-xl border border-slate-200 text-[11px] flex items-center justify-between">
                            <span class="text-slate-600 font-medium">Plotting Ekstensi Anda:</span>
                            <div class="flex gap-1.5 font-mono font-bold text-[10px]">
                                <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700">.co.id</span>
                                <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700">.web.id</span>
                                <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700">.id</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 2: Mengambil & Mengunci Target URL -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 space-y-4">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-xl bg-emerald-600 text-white font-bold text-sm flex items-center justify-center shrink-0">2</span>
                <div>
                    <h2 class="text-base font-bold text-slate-900">Mengambil & Mengunci Target URL (Anti-Tabrakan)</h2>
                    <p class="text-xs text-slate-500">Cara memilih website target dan sistem penguncian 2 jam.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-center">
                <!-- UI Preview Card: Ambil Target -->
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden text-xs order-2 lg:order-1">
                    <div class="bg-slate-900 text-white px-4 py-2.5 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-rose-400"></span>
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                            <span class="ml-2 font-mono text-[11px] text-slate-300 font-semibold">Antrean Target URL</span>
                        </div>
                        <span class="px-2 py-0.5 rounded-full bg-blue-500/20 text-blue-300 text-[10px] font-bold">Anti-Tabrakan</span>
                    </div>
                    <div class="p-3.5 space-y-2.5 bg-slate-50/50">
                        <div class="p-3 bg-white rounded-xl border border-slate-200 shadow-xs space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold text-[10px]">Tersedia</span>
                                <span class="font-mono font-bold text-emerald-700 text-[11px]">Tarif: Rp 750</span>
                            </div>
                            <div class="space-y-0.5">
                                <div class="text-[10px] text-slate-400">Target Website:</div>
                                <div class="font-mono font-bold text-slate-900 text-[11px] truncate">https://beritaekonomi.co.id/bisnis-digital</div>
                            </div>
                            <div class="bg-slate-50 p-2 rounded-lg text-[10px] text-slate-600 space-y-0.5 border border-slate-100">
                                <div><strong>Klien:</strong> https://solusi-seo.com/jasa-backlink</div>
                                <div><strong>Keyword:</strong> jasa backlink terpercaya</div>
                            </div>
                            <div class="flex gap-2 pt-0.5">
                                <button type="button" class="flex-1 py-1.5 px-3 bg-emerald-600 text-white font-bold rounded-lg text-[11px] flex items-center justify-center gap-1 shadow-xs pointer-events-none">
                                    <span>🔒 Ambil Tugas (Kunci 2 Jam)</span>
                                </button>
                                <button type="button" class="py-1.5 px-2.5 bg-slate-100 text-slate-600 font-bold rounded-lg text-[10px] pointer-events-none">
                                    Lewati
                                </button>
                            </div>
                        </div>
                        <div class="text-[10px] text-slate-500 text-center flex items-center justify-center gap-1">
                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                            Tugas otomatis terkunci 2 jam eksklusif untuk akun Anda
                        </div>
                    </div>
                </div>

                <div class="space-y-3 text-xs text-slate-600 leading-relaxed order-1 lg:order-2">
                    <p>Buka menu <strong>Antrean Target URL</strong>:</p>
                    <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-amber-900 text-[11px] leading-relaxed">
                        <strong>🔒 Sistem Lock Otomatis 2 Jam:</strong><br>
                        Saat Anda mengklik tombol hijau <strong>"Ambil Tugas"</strong>, URL tersebut akan langsung <strong>dikunci eksklusif untuk Anda selama 2 jam</strong>. Rekan tim lain tidak akan bisa mengambil link yang sama, sehingga tidak akan pernah terjadi tugas ganda (tabrakan link).
                    </div>
                    <ul class="list-disc list-inside space-y-1.5 ml-1">
                        <li><strong>URL Klien & Keyword:</strong> Perhatikan link klien dan kata kunci yang tertera untuk disisipkan di kolom komentar website target.</li>
                        <li><strong>Opsi Lewati Target (Skip):</strong> Jika website target ternyata kolom komentarnya mati/error, klik tombol kuning <strong>"Lewati Target Ini"</strong> dan berikan alasannya agar Anda bisa memilih link lain.</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Step 3: Mengerjakan & Mengirimkan Bukti Tugas -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 space-y-4">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-xl bg-emerald-600 text-white font-bold text-sm flex items-center justify-center shrink-0">3</span>
                <div>
                    <h2 class="text-base font-bold text-slate-900">Menulis Komentar & Mengunggah Screenshot Bukti</h2>
                    <p class="text-xs text-slate-500">Standar kualitas komentar dan pengisian formulir bukti tugas.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-center">
                <div class="space-y-3 text-xs text-slate-600 leading-relaxed">
                    <p>Setelah mengklik <em>Ambil Tugas</em>, Anda akan otomatis diarahkan ke formulir pengiriman:</p>
                    <ol class="list-decimal list-inside space-y-2 ml-1">
                        <li><strong>Kunjungi Website Target:</strong> Baca artikel dan tuliskan komentar yang relevan dengan topik bahasan (bukan komentar spam pendek).</li>
                        <li><strong>Sisipkan Backlink Klien:</strong> Masukkan URL klien di kolom website atau anchor text komentar.</li>
                        <li><strong>Ambil Screenshot:</strong> Tangkap layar yang memperlihatkan komentar Anda aktif di website tersebut (format PNG/JPG).</li>
                        <li><strong>Salin Live URL:</strong> Ambil link langsung halaman artikel tersebut.</li>
                        <li><strong>Kirim Formulir:</strong> Tempelkan screenshot dan klik tombol hijau <strong>"Kirim Bukti Pengerjaan"</strong>.</li>
                    </ol>
                </div>
                <!-- UI Preview Card: Formulir Bukti -->
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden text-xs">
                    <div class="bg-slate-900 text-white px-4 py-2.5 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-rose-400"></span>
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                            <span class="ml-2 font-mono text-[11px] text-slate-300 font-semibold">Formulir Kirim Bukti</span>
                        </div>
                        <span class="px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 text-[10px] font-bold">Sisa Waktu: 01:45:20</span>
                    </div>
                    <div class="p-4 space-y-2.5 bg-slate-50/50">
                        <div>
                            <label class="block font-bold text-slate-700 text-[10px] mb-1">Target URL Terkunci:</label>
                            <div class="p-2 bg-slate-100 rounded-lg text-slate-600 font-mono text-[10px] border border-slate-200 flex items-center justify-between">
                                <span class="truncate">https://beritaekonomi.co.id/bisnis-digital</span>
                                <span class="text-slate-400 text-xs">🔒</span>
                            </div>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 text-[10px] mb-1">Live URL Artikel / Komentar:</label>
                            <div class="p-2 bg-white rounded-lg text-slate-900 font-mono text-[10px] border border-emerald-500 shadow-xs">
                                https://beritaekonomi.co.id/bisnis-digital#comment-104
                            </div>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 text-[10px] mb-1">Unggah Screenshot Bukti Komentar:</label>
                            <div class="p-3 bg-white rounded-xl border border-dashed border-emerald-500 text-center space-y-0.5">
                                <div class="text-emerald-700 font-bold text-[10px] flex items-center justify-center gap-1">
                                    <span>🖼️</span> bukti_komentar_beritaekonomi.png
                                </div>
                                <div class="text-slate-400 text-[9px]">Ukuran: 184 KB • Gambar valid</div>
                            </div>
                        </div>
                        <button type="button" class="w-full py-2 bg-emerald-600 text-white font-bold rounded-xl text-xs flex items-center justify-center gap-1.5 shadow-xs pointer-events-none">
                            <span>✓ Kirim Bukti Pengerjaan</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 4: Melacak Status Review & Pengaturan Rekening -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 space-y-4">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-xl bg-emerald-600 text-white font-bold text-sm flex items-center justify-center shrink-0">4</span>
                <div>
                    <h2 class="text-base font-bold text-slate-900">Status Review Tugas & Ganti Nomor Rekening Bank</h2>
                    <p class="text-xs text-slate-500">Melihat hasil verifikasi admin dan prosedur pergantian data bank.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 space-y-2">
                    <div class="font-bold text-slate-900 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                        Status pada Menu "Riwayat Pengerjaan":
                    </div>
                    <ul class="space-y-1.5 text-slate-600">
                        <li><span class="px-2 py-0.5 rounded bg-amber-100 text-amber-800 font-semibold text-[10px]">Menunggu Review</span> : Sedang dicek oleh admin.</li>
                        <li><span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-semibold text-[10px]">Disetujui</span> : Komisi langsung masuk saldo akun Anda.</li>
                        <li><span class="px-2 py-0.5 rounded bg-rose-100 text-rose-800 font-semibold text-[10px]">Ditolak</span> : Tugas gagal kurasi (bisa baca alasan penolakan).</li>
                        <li><span class="px-2 py-0.5 rounded bg-purple-100 text-purple-800 font-semibold text-[10px]">Sudah Dibayar</span> : Komisi telah ditransfer ke rekening Anda.</li>
                    </ul>
                </div>

                <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 space-y-2">
                    <div class="font-bold text-slate-900 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        Ganti Rekening Bank (Menu Profil & Rekening):
                    </div>
                    <p class="text-slate-600 leading-relaxed">
                        Demi keamanan payroll (anti-fraud), pergantian rekening bank memerlukan persetujuan Super Admin. Silakan pilih bank baru dan ketikkan nomor rekening Anda, lalu tunggu admin memvalidasi permohonan tersebut sebelum pencairan berikutnya.
                    </p>
                </div>
            </div>
        </div>

        <!-- Step 5: Standar Kualitas Bukti & Kriteria Anti-Tolak -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 space-y-4">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-xl bg-emerald-600 text-white font-bold text-sm flex items-center justify-center shrink-0">5</span>
                <div>
                    <h2 class="text-base font-bold text-slate-900">Standar Kualitas Screenshot & Kriteria Anti-Tolak (Anti-Reject)</h2>
                    <p class="text-xs text-slate-500">Panduan visual agar setiap bukti tugas yang Anda kirimkan 100% lolos verifikasi admin.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                <!-- Checklist Sah -->
                <div class="p-4 bg-emerald-50/60 border border-emerald-200 rounded-2xl space-y-2.5">
                    <div class="flex items-center gap-2 text-emerald-900 font-bold text-sm">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Kriteria Bukti Sah (Disetujui)</span>
                    </div>
                    <ul class="space-y-2 text-emerald-950 leading-relaxed">
                        <li class="flex items-start gap-2">
                            <span class="text-emerald-600 font-bold shrink-0">✓</span>
                            <span><strong>Komentar Terlihat Jelas:</strong> Menampilkan paragraf opini yang relevan dengan topik artikel blog.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-emerald-600 font-bold shrink-0">✓</span>
                            <span><strong>Backlink Klien Aktif:</strong> Link menuju website klien aktif terpasang pada anchor text atau kolom nama pengomentar.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-emerald-600 font-bold shrink-0">✓</span>
                            <span><strong>Status Komentar Live:</strong> Komentar sudah muncul di halaman publik blog tanpa tulisan peringatan moderasi.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-emerald-600 font-bold shrink-0">✓</span>
                            <span><strong>Live URL Akurat:</strong> Tautan yang disalin adalah link artikel tempat komentar berada (bukan sekadar homepage).</span>
                        </li>
                    </ul>
                </div>

                <!-- Checklist Ditolak -->
                <div class="p-4 bg-rose-50/60 border border-rose-200 rounded-2xl space-y-2.5">
                    <div class="flex items-center gap-2 text-rose-900 font-bold text-sm">
                        <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Penyebab Tugas Ditolak (Reject)</span>
                    </div>
                    <ul class="space-y-2 text-rose-950 leading-relaxed">
                        <li class="flex items-start gap-2">
                            <span class="text-rose-600 font-bold shrink-0">✗</span>
                            <span><strong>Komentar Tertahan Moderasi:</strong> Masih terdapat keterangan <em>"Your comment is awaiting moderation"</em> (untuk tipe tugas Approved Live).</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-rose-600 font-bold shrink-0">✗</span>
                            <span><strong>Gambar Buram / Terpotong:</strong> Screenshot beresolusi terlalu rendah, teks terpotong, atau tidak menunjukkan isi komentar.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-rose-600 font-bold shrink-0">✗</span>
                            <span><strong>Salah Link Klien / Spam:</strong> Link klien tidak ada, salah mengetik URL, atau komentar hanya 1-2 kata umum (misal: <em>"nice info gan"</em>).</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-rose-600 font-bold shrink-0">✗</span>
                            <span><strong>Duplikat / Mengulang Domain Penuh:</strong> Menaruh komentar berulang pada domain yang kuotanya sudah habis.</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Step 6: Jenis Tugas, Skema Tarif & Kebijakan Dispensasi -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 space-y-4">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-xl bg-emerald-600 text-white font-bold text-sm flex items-center justify-center shrink-0">6</span>
                <div>
                    <h2 class="text-base font-bold text-slate-900">Variasi Jenis Tugas, Tarif Komisi & Prosedur Dispensasi</h2>
                    <p class="text-xs text-slate-500">Mengenal tipe pengerjaan, estimasi komisi, dan solusi jika akun terkena suspensi.</p>
                </div>
            </div>

            <!-- Tipe Tugas Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 text-xs">
                <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl space-y-1">
                    <div class="font-bold text-slate-900 flex items-center justify-between">
                        <span>💬 Komentar Blog Standar</span>
                        <span class="text-emerald-700 font-mono text-[11px] font-bold">~Rp 750</span>
                    </div>
                    <p class="text-slate-500 text-[11px] leading-relaxed">Menulis komentar relevan di website blog dengan menyisipkan link/nama klien.</p>
                </div>

                <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl space-y-1">
                    <div class="font-bold text-slate-900 flex items-center justify-between">
                        <span>⭐ Komentar High DA/DR</span>
                        <span class="text-emerald-700 font-mono text-[11px] font-bold">~Rp 1.000</span>
                    </div>
                    <p class="text-slate-500 text-[11px] leading-relaxed">Komentar di portal berita atau website dengan otoritas Domain Rating (DR) tinggi.</p>
                </div>

                <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl space-y-1">
                    <div class="font-bold text-slate-900 flex items-center justify-between">
                        <span>📱 Media Sosial & Forum</span>
                        <span class="text-emerald-700 font-mono text-[11px] font-bold">~Rp 1.500</span>
                    </div>
                    <p class="text-slate-500 text-[11px] leading-relaxed">Interaksi alami di forum tanya jawab, thread komunitas, atau media sosial.</p>
                </div>

                <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl space-y-1">
                    <div class="font-bold text-slate-900 flex items-center justify-between">
                        <span>📝 Guest Post / Kontributor</span>
                        <span class="text-emerald-700 font-mono text-[11px] font-bold">~Rp 2.000</span>
                    </div>
                    <p class="text-slate-500 text-[11px] leading-relaxed">Mengirimkan artikel artikel kontributor lengkap yang memuat link backlink klien.</p>
                </div>

                <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl space-y-1 sm:col-span-2 lg:col-span-2">
                    <div class="font-bold text-slate-900 flex items-center justify-between">
                        <span>✍️ Penulisan Artikel Internal</span>
                        <span class="text-emerald-700 font-mono text-[11px] font-bold">~Rp 10.000</span>
                    </div>
                    <p class="text-slate-500 text-[11px] leading-relaxed">Pembuatan draft artikel SEO orisinal 500–1000 kata untuk konten aset internal klien.</p>
                </div>
            </div>

            <!-- Box Dispensasi -->
            <div class="p-4 bg-amber-50 border border-amber-200 rounded-2xl text-amber-950 text-xs space-y-2">
                <div class="font-bold flex items-center gap-2 text-sm text-amber-900">
                    <svg class="w-4 h-4 text-amber-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                    <span>Bagaimana Jika Akun Ditangguhkan Karena Tidak Mencapai Target Minimal?</span>
                </div>
                <p class="leading-relaxed">
                    Setiap akhir bulan dilakukan evaluasi otomatis. Anggota yang tidak mencapai batas target minimal akan ditangguhkan (*disqualified*) pada periode berikutnya. Namun, jika Anda mengalami kendala kesehatan (sakit) atau urusan darurat, Anda dapat <strong>mengajukan permohonan dispensasi kepada Super Admin</strong>. Admin memiliki wewenang untuk memberikan status dispensasi sehingga akun Anda langsung aktif kembali.
                </p>
            </div>
        </div>

    </div>

    <!-- TAB 2: PANDUAN SUPER ADMIN -->
    @if($isAdmin)
    <div x-show="tab === 'admin'" x-cloak class="space-y-6">

        <!-- Banner Info Admin -->
        <div class="bg-slate-900 text-white rounded-2xl p-4 text-xs flex items-start gap-3">
            <span class="text-base">👑</span>
            <div>
                <span class="font-bold text-emerald-400">Panduan Operasional Super Admin:</span> Modul ini menjelaskan konfigurasi sistem, setup kuota domain, kurasi bukti live backlink, integrasi metrik SEO, dan proses pembayaran gaji tim.
            </div>
        </div>

        <!-- Section A: Setup Periode & Batasan Kuota Domain -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 space-y-4">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-xl bg-slate-900 text-white font-bold text-sm flex items-center justify-center shrink-0">1</span>
                <div>
                    <h2 class="text-base font-bold text-slate-900">Pengaturan Periode & Batasan Kuota Root Domain</h2>
                    <p class="text-xs text-slate-500">Mencegah over-commenting dan menjaga profil backlink klien tetap aman.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-center">
                <div class="space-y-3 text-xs text-slate-600 leading-relaxed">
                    <p>Buka menu <strong>Operasional & Target &rarr; Periode & Target Bulanan</strong>:</p>
                    <ul class="list-disc list-inside space-y-1.5 ml-1">
                        <li><strong>Maksimal URL per Root Domain:</strong> Atur jumlah maksimal komentar di satu website yang sama (misal diisi <code>2</code> URL). Begitu 2 URL tercapai, domain otomatis berstatus <em>Penuh / Terkunci</em>.</li>
                        <li><strong>Batasan Ekstensi Domain (TLD Multi-Select):</strong> Gunakan dropdown pencarian untuk memilih ekstensi domain yang sah (misal <code>.com</code>, <code>.id</code>, <code>.org</code>).</li>
                        <li><strong>Target Minimal per Anggota:</strong> Tentukan standar batas minimal agar anggota tim tetap aktif di periode berikutnya.</li>
                    </ul>
                </div>
                <!-- UI Preview Card: Periode & Kuota -->
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden text-xs">
                    <div class="bg-slate-900 text-white px-4 py-2.5 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-rose-400"></span>
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                            <span class="ml-2 font-mono text-[11px] text-slate-300 font-semibold">Konfigurasi Periode & Batasan Kuota</span>
                        </div>
                        <span class="px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 text-[10px] font-bold">Periode Aktif</span>
                    </div>
                    <div class="p-4 space-y-3 bg-slate-50/50">
                        <div class="grid grid-cols-2 gap-3">
                            <div class="p-3 bg-white rounded-xl border border-slate-200">
                                <label class="block text-slate-500 text-[10px] font-medium">Target Minimal / User:</label>
                                <div class="font-extrabold text-slate-900 text-base font-mono">100 <span class="text-xs font-normal text-slate-400">komentar</span></div>
                            </div>
                            <div class="p-3 bg-white rounded-xl border border-emerald-500/60 shadow-xs">
                                <label class="block text-emerald-700 text-[10px] font-bold">Maks. URL per Domain:</label>
                                <div class="font-extrabold text-emerald-700 text-base font-mono">2 <span class="text-xs font-normal text-emerald-600/70">URL / domain</span></div>
                            </div>
                        </div>
                        <div class="p-3 bg-white rounded-xl border border-slate-200 flex items-center justify-between text-[11px]">
                            <div>
                                <span class="text-slate-500 block text-[10px]">Rentang Waktu Periode:</span>
                                <span class="font-bold text-slate-800">01 Oktober 2026 &mdash; 31 Oktober 2026</span>
                            </div>
                            <span class="px-2 py-1 rounded bg-slate-100 text-slate-700 font-bold text-[10px]">Aktif</span>
                        </div>
                        <div class="flex gap-2">
                            <button type="button" class="flex-1 py-1.5 bg-slate-900 text-white font-bold rounded-lg text-[11px] pointer-events-none">
                                Simpan Konfigurasi
                            </button>
                            <button type="button" class="py-1.5 px-3 bg-rose-600 text-white font-bold rounded-lg text-[11px] pointer-events-none">
                                Tutup & Evaluasi Periode
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section B: Antrean Target URL & Bulk Action -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 space-y-4">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-xl bg-slate-900 text-white font-bold text-sm flex items-center justify-center shrink-0">2</span>
                <div>
                    <h2 class="text-base font-bold text-slate-900">Impor Target URL Massal & Aksi Checklist Massal</h2>
                    <p class="text-xs text-slate-500">Memasukkan ribuan stok website klien dan mengeksekusi aksi massal.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-center">
                <!-- UI Preview Card: Checklist Massal -->
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden text-xs order-2 lg:order-1">
                    <div class="bg-slate-900 text-white px-4 py-2.5 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-rose-400"></span>
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                            <span class="ml-2 font-mono text-[11px] text-slate-300 font-semibold">Antrean Target URL</span>
                        </div>
                        <span class="px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 text-[10px] font-bold">+ Input Massal</span>
                    </div>
                    <div class="p-3 bg-slate-50/50 space-y-2">
                        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
                            <table class="w-full text-[11px] text-left">
                                <thead class="bg-slate-100 text-slate-600 font-bold border-b border-slate-200">
                                    <tr>
                                        <th class="p-2 w-6 text-center"><input type="checkbox" checked class="rounded text-emerald-600 pointer-events-none"></th>
                                        <th class="p-2">Target URL</th>
                                        <th class="p-2">Root Domain</th>
                                        <th class="p-2">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <tr class="bg-emerald-50/30">
                                        <td class="p-2 text-center"><input type="checkbox" checked class="rounded text-emerald-600 pointer-events-none"></td>
                                        <td class="p-2 font-mono text-slate-800 truncate max-w-[120px]">teknoviral.com/ai-tools</td>
                                        <td class="p-2 font-mono text-slate-600">teknoviral.com</td>
                                        <td class="p-2"><span class="px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 text-[10px] font-bold">Tersedia</span></td>
                                    </tr>
                                    <tr class="bg-emerald-50/30">
                                        <td class="p-2 text-center"><input type="checkbox" checked class="rounded text-emerald-600 pointer-events-none"></td>
                                        <td class="p-2 font-mono text-slate-800 truncate max-w-[120px]">beritapro.co.id/tips</td>
                                        <td class="p-2 font-mono text-slate-600">beritapro.co.id</td>
                                        <td class="p-2"><span class="px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 text-[10px] font-bold">Tersedia</span></td>
                                    </tr>
                                    <tr>
                                        <td class="p-2 text-center"><input type="checkbox" class="rounded text-slate-300 pointer-events-none"></td>
                                        <td class="p-2 font-mono text-slate-400 truncate max-w-[120px]">mediaukm.id/info</td>
                                        <td class="p-2 font-mono text-slate-400">mediaukm.id</td>
                                        <td class="p-2"><span class="px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 text-[10px] font-semibold">Domain Penuh</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="p-2 bg-slate-900 text-white rounded-xl flex items-center justify-between text-[11px] shadow-sm">
                            <span class="font-bold flex items-center gap-1.5 pl-1">
                                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                                <span>2 Target Dipilih:</span>
                            </span>
                            <div class="flex gap-1.5">
                                <span class="px-2 py-0.5 rounded bg-emerald-600 text-white font-bold text-[10px]">Aktifkan</span>
                                <span class="px-2 py-0.5 rounded bg-amber-600 text-white font-bold text-[10px]">Lewati</span>
                                <span class="px-2 py-0.5 rounded bg-rose-600 text-white font-bold text-[10px]">Hapus</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-3 text-xs text-slate-600 leading-relaxed order-1 lg:order-2">
                    <p>Buka menu <strong>Operasional & Target &rarr; Antrean Target URL</strong>:</p>
                    <ol class="list-decimal list-inside space-y-1.5 ml-1">
                        <li><strong>Input Target Massal:</strong> Klik tombol hijau <code>+ Input Target Massal</code>. Anda dapat menempelkan ribuan link di textarea atau mengunggah file <code>.txt</code> / <code>.csv</code> (hingga 10 MB). Sistem memproses ribuan link dalam 1–2 detik tanpa timeout.</li>
                        <li><strong>Fitur Checklist Interaktif:</strong> Centang satu atau beberapa target URL (atau centang header untuk pilih semua).</li>
                        <li><strong>Bilah Aksi Massal (Floating Toolbar):</strong>
                            <ul class="list-disc list-inside ml-4 mt-1 space-y-1 text-slate-500">
                                <li><strong>Aktifkan (Ready):</strong> Mengembalikan target yang dilewati ke antrean siap kerja.</li>
                                <li><strong>Tandai Dilewati:</strong> Melewati target tanpa menghapusnya.</li>
                                <li><strong>Hapus:</strong> Menghapus target secara permanen.</li>
                            </ul>
                        </li>
                    </ol>
                </div>
            </div>
        </div>

        <!-- Section C: Verifikasi Tugas & Bukti Screenshot -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 space-y-4">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-xl bg-slate-900 text-white font-bold text-sm flex items-center justify-center shrink-0">3</span>
                <div>
                    <h2 class="text-base font-bold text-slate-900">Verifikasi Screenshot Bukti & Ekspor CSV Klien</h2>
                    <p class="text-xs text-slate-500">Menyetujui, menolak dengan alasan, dan mengunduh laporan klien.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-center">
                <div class="space-y-3 text-xs text-slate-600 leading-relaxed">
                    <p>Buka menu <strong>Utama &rarr; Verifikasi Tugas & Misi</strong>:</p>
                    <ul class="list-disc list-inside space-y-2 ml-1">
                        <li><strong>Lightbox Zoom:</strong> Klik thumbnail foto screenshot untuk memeriksa keaslian bukti backlink dalam resolusi besar.</li>
                        <li><strong>Setujui Sekaligus (Bulk Approve):</strong> Centang beberapa tugas lalu klik tombol hijau <code>✓ Setujui Sekaligus</code> untuk review cepat.</li>
                        <li><strong>Tolak (Reject):</strong> Klik tombol merah dan ketikkan alasan penolakan. Slot kuota domain yang tadinya terpakai otomatis dibebaskan kembali.</li>
                        <li><strong>Export CSV:</strong> Unduh daftar link yang sudah disetujui untuk diserahkan ke klien sebagai bukti laporan pekerjaan.</li>
                    </ul>
                </div>
                <!-- UI Preview Card: Verifikasi Tugas -->
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden text-xs">
                    <div class="bg-slate-900 text-white px-4 py-2.5 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-rose-400"></span>
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                            <span class="ml-2 font-mono text-[11px] text-slate-300 font-semibold">Verifikasi Tugas & Misi</span>
                        </div>
                        <div class="flex gap-1.5">
                            <span class="px-2 py-0.5 rounded bg-emerald-600 text-white text-[10px] font-bold">✓ Bulk Approve</span>
                            <span class="px-2 py-0.5 rounded bg-slate-800 text-slate-200 text-[10px] font-bold">Export CSV</span>
                        </div>
                    </div>
                    <div class="p-3 bg-slate-50/50 space-y-2">
                        <div class="bg-white p-3 rounded-xl border border-slate-200 shadow-xs space-y-2">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-slate-900 text-[11px]">Budi Pratama</span>
                                    <span class="text-slate-400 text-[10px]">• 5 menit lalu</span>
                                </div>
                                <span class="px-2 py-0.5 rounded bg-amber-100 text-amber-800 font-bold text-[10px]">Menunggu Review</span>
                            </div>
                            <div class="flex gap-3 items-center">
                                <div class="w-16 h-12 rounded-lg bg-slate-800 text-white flex flex-col items-center justify-center shrink-0 border border-slate-200 text-[9px]">
                                    <span>🖼️ Zoom</span>
                                    <span class="text-[8px] text-slate-400">Bukti Live</span>
                                </div>
                                <div class="text-[11px] space-y-0.5 min-w-0">
                                    <div class="font-mono text-slate-800 truncate">https://beritaekonomi.co.id/bisnis-digital</div>
                                    <div class="text-slate-500 text-[10px]">Klien: solusi-seo.com • Tarif: <strong class="text-emerald-600">Rp 750</strong></div>
                                </div>
                            </div>
                            <div class="flex gap-2 pt-0.5">
                                <button type="button" class="flex-1 py-1 bg-emerald-600 text-white font-bold rounded-lg text-[10px] flex items-center justify-center gap-1 pointer-events-none">
                                    <span>✓ Setujui</span>
                                </button>
                                <button type="button" class="py-1 px-3 bg-rose-600 text-white font-bold rounded-lg text-[10px] pointer-events-none">
                                    Tolak
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section D: Monitoring Domain & Otoritas SEO -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 space-y-4">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-xl bg-slate-900 text-white font-bold text-sm flex items-center justify-center shrink-0">4</span>
                <div>
                    <h2 class="text-base font-bold text-slate-900">Monitoring Domain Dikerjakan, SEO & Subnet IP</h2>
                    <p class="text-xs text-slate-500">Pelacakan kuota domain riil, metrik DA/PA/DR, dan footprint hosting PBN.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-center">
                <!-- UI Preview Card: Domain Monitoring -->
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden text-xs order-2 lg:order-1">
                    <div class="bg-slate-900 text-white px-4 py-2.5 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-rose-400"></span>
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                            <span class="ml-2 font-mono text-[11px] text-slate-300 font-semibold">Monitoring Domain & Kuota Dikerjakan</span>
                        </div>
                        <span class="px-2 py-0.5 rounded-full bg-indigo-500/20 text-indigo-300 text-[10px] font-bold">⚡ Cek SEO Terpilih</span>
                    </div>
                    <div class="p-3 bg-slate-50/50 space-y-2">
                        <div class="p-2 bg-emerald-50 rounded-lg text-emerald-900 text-[10px] flex items-center gap-1.5 border border-emerald-200">
                            <span>✓</span>
                            <span>Hanya mencatat domain yang <strong>sudah riil dikerjakan</strong> (bukan stok target mentah).</span>
                        </div>
                        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
                            <table class="w-full text-[10px] text-left">
                                <thead class="bg-slate-100 text-slate-600 font-bold border-b border-slate-200">
                                    <tr>
                                        <th class="p-2">Root Domain</th>
                                        <th class="p-2">Kuota</th>
                                        <th class="p-2">Moz DA</th>
                                        <th class="p-2">Ahrefs DR</th>
                                        <th class="p-2">Subnet IP</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 font-mono">
                                    <tr>
                                        <td class="p-2 font-bold text-slate-900">portalberita.co.id</td>
                                        <td class="p-2"><span class="px-1.5 py-0.5 rounded bg-rose-100 text-rose-800 font-bold">2/2 (Penuh)</span></td>
                                        <td class="p-2 text-indigo-600 font-bold">DA 42</td>
                                        <td class="p-2 text-orange-600 font-bold">DR 51</td>
                                        <td class="p-2 text-slate-500">104.21.48.0/24</td>
                                    </tr>
                                    <tr>
                                        <td class="p-2 font-bold text-slate-900">bisnisupdate.com</td>
                                        <td class="p-2"><span class="px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold">1/2 (Aktif)</span></td>
                                        <td class="p-2 text-indigo-600 font-bold">DA 38</td>
                                        <td class="p-2 text-orange-600 font-bold">DR 44</td>
                                        <td class="p-2 text-slate-500">172.67.182.0/24</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="space-y-3 text-xs text-slate-600 leading-relaxed order-1 lg:order-2">
                    <p>Buka menu <strong>Operasional & Target &rarr; Monitoring Domain & Kuota</strong>:</p>
                    <div class="p-3 bg-slate-100 rounded-xl text-slate-700 text-[11px] leading-relaxed">
                        <strong>📌 Prinsip Database Domain:</strong> Halaman ini <em>hanya mencatat website yang riil sudah dikerjakan</em> oleh worker, sehingga tidak bercampur dengan stok target mentah.
                    </div>
                    <ul class="list-disc list-inside space-y-1.5 ml-1">
                        <li><strong>Metrik Otoritas SEO Resmi:</strong> Terhubung dengan API Moz (DA/PA), Ahrefs (DR), dan OpenPageRank (PR). Klik <code>⚡ Cek SEO Terpilih</code> untuk sinkronisasi nilai otomatis.</li>
                        <li><strong>Deteksi Subnet IP (Anti-PBN):</strong> Sistem otomatis melacak IP server domain dan mendeteksi kluster subnet kembar untuk mencegah footprint backlink di server yang sama.</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Section E: Payroll & Manajemen Anggota -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 space-y-4">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-xl bg-slate-900 text-white font-bold text-sm flex items-center justify-center shrink-0">5</span>
                <div>
                    <h2 class="text-base font-bold text-slate-900">Pencairan Gaji (Payroll) & Verifikasi Anggota</h2>
                    <p class="text-xs text-slate-500">Persetujuan pendaftar baru, verifikasi rekening, dan rekap pembayaran.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 space-y-2">
                    <div class="font-bold text-slate-900">Verifikasi Pendaftar Baru</div>
                    <p class="text-slate-600 leading-relaxed">
                        Tinjau foto KTP dan data bank pendaftar baru di menu <em>Verifikasi Pendaftar Baru</em>. Klik setujui untuk memberikan akses kerja ke dalam sistem.
                    </p>
                </div>
                <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 space-y-2">
                    <div class="font-bold text-slate-900">Perubahan Profil & Rekening</div>
                    <p class="text-slate-600 leading-relaxed">
                        Cek permohonan pergantian rekening bank worker. Bandingkan nomor rekening lama dan baru sebelum memberikan persetujuan.
                    </p>
                </div>
                <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 space-y-2">
                    <div class="font-bold text-slate-900">Pembayaran & Payroll</div>
                    <p class="text-slate-600 leading-relaxed">
                        Menampilkan total rupiah tugas <em>Approved</em> yang belum dibayar. Tersedia tombol cepat <strong>"Salin Nomor Rekening"</strong> dan form bukti transfer.
                    </p>
                </div>
            </div>
        </div>

        <!-- Section F: Evaluasi Periode Bulanan (Rollover) & Dispensasi -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 space-y-4">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-xl bg-slate-900 text-white font-bold text-sm flex items-center justify-center shrink-0">6</span>
                <div>
                    <h2 class="text-base font-bold text-slate-900">Evaluasi Periode Bulanan (Tutup Buku) & Pemberian Dispensasi</h2>
                    <p class="text-xs text-slate-500">Mekanisme otomatis tutup buku, plotting ke periode berikutnya, dan perlakuan anggota berstatus suspensi.</p>
                </div>
            </div>

            <div class="space-y-3 text-xs text-slate-600 leading-relaxed">
                <p>Ketika bulan kerja berakhir, buka menu <strong>Operasional & Target &rarr; Periode & Target Bulanan</strong> lalu klik tombol <code>Tutup & Evaluasi Periode</code>:</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="p-4 bg-emerald-50/70 border border-emerald-200 rounded-xl space-y-1.5">
                        <div class="font-bold text-emerald-950 flex items-center gap-1.5">
                            <span class="text-emerald-600">✓</span> Anggota Lolos Target (Qualified):
                        </div>
                        <p class="text-slate-600">
                            Anggota yang menyelesaikan komentar &ge; target minimal otomatis diikutsertakan (*enrolled*) ke periode baru beserta pengaturan plotting TLD & instruksinya.
                        </p>
                    </div>
                    <div class="p-4 bg-rose-50/70 border border-rose-200 rounded-xl space-y-1.5">
                        <div class="font-bold text-rose-950 flex items-center gap-1.5">
                            <span class="text-rose-600">✗</span> Anggota Tidak Lolos (Disqualified):
                        </div>
                        <p class="text-slate-600">
                            Akun otomatis disuspensi (ditangguhkan) untuk periode berikutnya sehingga tidak dapat mengambil maupun mengirimkan tugas.
                        </p>
                    </div>
                </div>

                <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl space-y-1">
                    <span class="font-bold text-slate-900">Fitur Pemberian Dispensasi Admin:</span>
                    <p class="text-slate-600">
                        Jika ada anggota tim yang tidak mencapai target karena izin sakit atau keperluan darurat yang sah, Super Admin dapat mengklik tombol <strong>"Beri Dispensasi"</strong> pada baris nama anggota tersebut dan mengetikkan alasan. Status anggota akan seketika berubah menjadi <em>Dispensed</em> dan hak akses tugasnya kembali terbuka normal.
                    </p>
                </div>
            </div>
        </div>

        <!-- Section G: Pemeliharaan Sistem & Backup (Zero-Terminal) -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 space-y-4">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-xl bg-slate-900 text-white font-bold text-sm flex items-center justify-center shrink-0">7</span>
                <div>
                    <h2 class="text-base font-bold text-slate-900">Pemeliharaan Hosting & Cadangan Data (Zero-Terminal)</h2>
                    <p class="text-xs text-slate-500">Operasional cPanel / shared hosting tanpa perlu menyentuh command line atau terminal SSH.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
                <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl space-y-1">
                    <div class="font-bold text-slate-900 flex items-center gap-1.5">
                        <span>💾 Backup Database (.SQL)</span>
                    </div>
                    <p class="text-slate-500 text-[11px] leading-relaxed">Unduh salinan cadangan seluruh tabel database hanya dengan 1 klik.</p>
                </div>

                <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl space-y-1">
                    <div class="font-bold text-slate-900 flex items-center gap-1.5">
                        <span>📦 Backup Lengkap (.ZIP)</span>
                    </div>
                    <p class="text-slate-500 text-[11px] leading-relaxed">Mendownload arsip zip berisi database beserta seluruh file upload screenshot & dokumen.</p>
                </div>

                <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl space-y-1">
                    <div class="font-bold text-slate-900 flex items-center gap-1.5">
                        <span>⚡ Bersihkan Cache</span>
                    </div>
                    <p class="text-slate-500 text-[11px] leading-relaxed">Menghapus cache konfigurasi, routing, dan blade views jika ada tampilan yang belum update.</p>
                </div>

                <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl space-y-1">
                    <div class="font-bold text-slate-900 flex items-center gap-1.5">
                        <span>🔗 Perbaiki Storage Link</span>
                    </div>
                    <p class="text-slate-500 text-[11px] leading-relaxed">Menghubungkan folder upload publik cPanel tanpa ketergantungan symlink manual.</p>
                </div>
            </div>
        </div>

        <!-- Section H: Manajemen Akun Super Admin (Multi-Admin) -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 space-y-4">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-xl bg-slate-900 text-white font-bold text-sm flex items-center justify-center shrink-0">8</span>
                <div>
                    <h2 class="text-base font-bold text-slate-900">Manajemen Akun Super Admin (Multi-Admin)</h2>
                    <p class="text-xs text-slate-500">Menambah rekan Super Admin baru, kontrol status aktif/nonaktif, dan reset password mandiri.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 space-y-2">
                    <div class="font-bold text-slate-900">Tambah Super Admin Baru</div>
                    <p class="text-slate-600 leading-relaxed">
                        Buka menu <strong>Pengaturan &rarr; Kelola Super Admin</strong> dan klik <code>+ Tambah Super Admin</code>. Tersedia tombol pembuat kata sandi acak yang kuat secara otomatis.
                    </p>
                </div>
                <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 space-y-2">
                    <div class="font-bold text-slate-900">Reset Sandi & Nonaktifkan Akun</div>
                    <p class="text-slate-600 leading-relaxed">
                        Super Admin dapat mereset sandi rekan admin lain kapan saja atau menonaktifkan akun yang sudah tidak bertugas dengan 1 klik tombol toggle.
                    </p>
                </div>
                <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 space-y-2">
                    <div class="font-bold text-slate-900">Proteksi Keamanan Sistem</div>
                    <p class="text-slate-600 leading-relaxed">
                        Sistem melindungi Super Admin agar tidak dapat menghapus atau menonaktifkan akun sendiri, serta mencegah penghapusan jika hanya tersisa 1 Super Admin terakhir.
                    </p>
                </div>
            </div>
        </div>

    </div>
    @endif

    <!-- TAB 3: FAQ & TANYA JAWAB -->
    <div x-show="tab === 'faq'" x-cloak class="space-y-4">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 divide-y divide-slate-100">
            
            <div class="py-4 first:pt-0">
                <h3 class="font-bold text-sm text-slate-900 flex items-center gap-2">
                    <span class="text-emerald-600">Q:</span> Apa yang terjadi jika target URL yang diambil tidak dikerjakan dalam 2 jam?
                </h3>
                <p class="text-xs text-slate-600 mt-1.5 leading-relaxed pl-6">
                    Kunci tugas (*lock*) akan kedaluwarsa secara otomatis dan status target URL tersebut akan dikembalikan menjadi <strong>Tersedia</strong> sehingga anggota tim lain dapat mengambilnya kembali.
                </p>
            </div>

            <div class="py-4">
                <h3 class="font-bold text-sm text-slate-900 flex items-center gap-2">
                    <span class="text-emerald-600">Q:</span> Mengapa muncul peringatan "Domain ini sudah penuh"?
                </h3>
                <p class="text-xs text-slate-600 mt-1.5 leading-relaxed pl-6">
                    Setiap website target memiliki batas maksimal pengerjaan per periode (misal maksimal 2 URL per root domain). Jika kuota domain tersebut sudah terpenuhi oleh anggota tim lain, sistem otomatis menguncinya demi menjaga kualitas link building yang alami.
                </p>
            </div>

            <div class="py-4">
                <h3 class="font-bold text-sm text-slate-900 flex items-center gap-2">
                    <span class="text-emerald-600">Q:</span> Kapan jadwal pencairan komisi (payroll) dan bagaimana cara kerjanya?
                </h3>
                <p class="text-xs text-slate-600 mt-1.5 leading-relaxed pl-6">
                    Pencairan komisi diproses oleh Super Admin melalui menu <em>Pembayaran & Payroll</em>. Seluruh tugas dengan status <strong>Disetujui</strong> akan diakumulasikan ke saldo yang siap dibayarkan. Admin mentransfer langsung ke nomor rekening terdaftar Anda dan mengunggah bukti transfer ke sistem.
                </p>
            </div>

            <div class="py-4">
                <h3 class="font-bold text-sm text-slate-900 flex items-center gap-2">
                    <span class="text-emerald-600">Q:</span> Apakah Blogwalker bisa mencari website target sendiri di luar antrean admin?
                </h3>
                <p class="text-xs text-slate-600 mt-1.5 leading-relaxed pl-6">
                    Bisa. Blogwalker dapat menginput URL target mandiri pada halaman <em>Kirim Bukti Tugas</em>. Sistem akan tetap memvalidasi apakah domain tersebut sesuai dengan plotting TLD worker dan belum melebihi batas kuota domain.
                </p>
            </div>

            <div class="py-4">
                <h3 class="font-bold text-sm text-slate-900 flex items-center gap-2">
                    <span class="text-emerald-600">Q:</span> Mengapa nomor rekening bank worker harus disetujui Super Admin?
                </h3>
                <p class="text-xs text-slate-600 mt-1.5 leading-relaxed pl-6">
                    Ini adalah fitur proteksi keamanan keuangan (anti-fraud). Tujuannya agar akun blogwalker tidak dapat disalahgunakan oleh pihak lain yang ingin membelokkan nomor rekening pencairan komisi tanpa sepengetahuan Super Admin.
                </p>
            </div>

            <div class="py-4">
                <h3 class="font-bold text-sm text-slate-900 flex items-center gap-2">
                    <span class="text-emerald-600">Q:</span> Bagaimana jika Super Admin ingin menaikkan atau menurunkan kuota domain tertentu saja?
                </h3>
                <p class="text-xs text-slate-600 mt-1.5 leading-relaxed pl-6">
                    Buka menu <em>Monitoring Domain & Kuota</em>. Admin dapat mengklik tombol kuota pada baris domain yang diinginkan untuk mengubah nilai batas maksimal secara custom, atau menggunakan fitur <strong>Ubah Kuota Massal</strong>.
                </p>
            </div>

            <div class="py-4">
                <h3 class="font-bold text-sm text-slate-900 flex items-center gap-2">
                    <span class="text-emerald-600">Q:</span> Bagaimana jika Super Admin menginput ribuan link target sekaligus?
                </h3>
                <p class="text-xs text-slate-600 mt-1.5 leading-relaxed pl-6">
                    Sistem sudah dioptimasi menggunakan <em>in-memory chunking</em> dan mendukung upload dokumen <code>.txt</code> / <code>.csv</code> hingga 10 MB. Proses impor 1.000–5.000 link selesai instan dalam hitungan detik tanpa resiko *504 Gateway Time-out*.
                </p>
            </div>

            <div class="py-4">
                <h3 class="font-bold text-sm text-slate-900 flex items-center gap-2">
                    <span class="text-emerald-600">Q:</span> Apakah file upload bukti screenshot dan gambar KTP aman di server?
                </h3>
                <p class="text-xs text-slate-600 mt-1.5 leading-relaxed pl-6">
                    Sangat aman. Sistem menggunakan router streaming aman yang memvalidasi otorisasi peran (*role-based authorization*). Dokumen identitas KTP hanya dapat dibuka oleh Super Admin, dan gambar screenshot dilindungi dari akses manipulasi pihak luar.
                </p>
            </div>

            <div class="py-4 last:pb-0">
                <h3 class="font-bold text-sm text-slate-900 flex items-center gap-2">
                    <span class="text-emerald-600">Q:</span> Bagaimana cara menambah akun rekan Super Admin baru?
                </h3>
                <p class="text-xs text-slate-600 mt-1.5 leading-relaxed pl-6">
                    Buka menu <strong>Pengaturan &rarr; Kelola Super Admin</strong>, lalu klik tombol <strong>+ Tambah Super Admin</strong>. Cukup masukkan nama, email, dan password. Akun tersebut langsung aktif dan memiliki hak akses Super Admin penuh ke seluruh dashboard.
                </p>
            </div>

        </div>
    </div>

</div>
@endsection
