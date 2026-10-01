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
                <div class="rounded-xl overflow-hidden border border-slate-200 shadow-sm bg-slate-50">
                    <img src="{{ asset('images/guide/worker_dashboard.png') }}" alt="Dashboard Blogwalker" class="w-full h-auto object-cover">
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
                <div class="rounded-xl overflow-hidden border border-slate-200 shadow-sm bg-slate-50 order-2 lg:order-1">
                    <img src="{{ asset('images/guide/worker_target_claim.png') }}" alt="Daftar Target URL Blogwalker" class="w-full h-auto object-cover">
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
                <div class="rounded-xl overflow-hidden border border-slate-200 shadow-sm bg-slate-50">
                    <img src="{{ asset('images/guide/worker_submission_form.png') }}" alt="Formulir Kirim Bukti Blogwalker" class="w-full h-auto object-cover">
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
                        Demi keamanan payroll, pergantian rekening bank memerlukan persetujuan Super Admin. Silakan pilih bank baru dan ketikkan nomor rekening Anda, lalu tunggu admin memvalidasi permohonan tersebut.
                    </p>
                </div>
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
                <div class="rounded-xl overflow-hidden border border-slate-200 shadow-sm bg-slate-50">
                    <img src="{{ asset('images/guide/admin_period_settings.png') }}" alt="Pengaturan Periode Admin" class="w-full h-auto object-cover">
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
                <div class="rounded-xl overflow-hidden border border-slate-200 shadow-sm bg-slate-50 order-2 lg:order-1">
                    <img src="{{ asset('images/guide/admin_target_queue.png') }}" alt="Antrean Target URL Admin" class="w-full h-auto object-cover">
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
                <div class="rounded-xl overflow-hidden border border-slate-200 shadow-sm bg-slate-50">
                    <img src="{{ asset('images/guide/admin_review_queue.png') }}" alt="Antrean Verifikasi Admin" class="w-full h-auto object-cover">
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
                <div class="rounded-xl overflow-hidden border border-slate-200 shadow-sm bg-slate-50 order-2 lg:order-1">
                    <img src="{{ asset('images/guide/admin_domain_monitoring.png') }}" alt="Monitoring Domain dan Kuota" class="w-full h-auto object-cover">
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

            <div class="py-4 last:pb-0">
                <h3 class="font-bold text-sm text-slate-900 flex items-center gap-2">
                    <span class="text-emerald-600">Q:</span> Bagaimana jika Super Admin menginput ribuan link target sekaligus?
                </h3>
                <p class="text-xs text-slate-600 mt-1.5 leading-relaxed pl-6">
                    Sistem sudah dioptimasi menggunakan <em>in-memory chunking</em> dan mendukung upload dokumen <code>.txt</code> / <code>.csv</code> hingga 10 MB. Proses impor 1.000–5.000 link selesai instan dalam hitungan detik tanpa resiko *504 Gateway Time-out*.
                </p>
            </div>

        </div>
    </div>

</div>
@endsection
