<div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs" x-data="{
    tld: 'co.id',
    customTld: '',
    footprint: 'tinggalkan komentar',
    keyword: '',
    copied: false,

    get effectiveTld() {
        if (this.tld === 'custom') return this.customTld.replace(/^\./, '').trim();
        return this.tld;
    },

    quote(s) {
        return String.fromCharCode(34) + s + String.fromCharCode(34);
    },

    get fullQuery() {
        let parts = [];
        if (this.effectiveTld && this.tld !== 'none') {
            parts.push('site:' + this.effectiveTld);
        }
        if (this.footprint) {
            parts.push(this.quote(this.footprint.trim()));
        }
        if (this.keyword && this.keyword.trim()) {
            parts.push(this.quote(this.keyword.trim()));
        }
        return parts.join(' ');
    },

    get searchUrl() {
        return 'https://www.google.com/search?q=' + encodeURIComponent(this.fullQuery);
    },

    openSearch() {
        if (!this.fullQuery) return;
        window.open(this.searchUrl, '_blank');
    },

    copyQuery() {
        navigator.clipboard.writeText(this.fullQuery);
        this.copied = true;
        setTimeout(() => this.copied = false, 2000);
    }
}">
    <!-- Header -->
    <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-lg shadow-2xs">
                ⚡
            </div>
            <div>
                <h3 class="text-base font-bold text-slate-900 leading-tight">Google Dork & Footprint Generator</h3>
                <p class="text-xs text-slate-500">Buat link pencarian Google 1-klik untuk menemukan blog & website yang kolom komentarnya terbuka.</p>
            </div>
        </div>
        <span class="hidden sm:inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
            1-Klik Langsung Cari
        </span>
    </div>

    <!-- Generator Controls -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Target TLD / Platform -->
        <div>
            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                1. Ekstensi / Platform Target
            </label>
            <select x-model="tld" class="w-full text-sm border-slate-300 rounded-lg px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500">
                <optgroup label="Indonesia">
                    <option value="co.id">.co.id (Bisnis / Brand Indo)</option>
                    <option value="web.id">.web.id (Personal / Komunitas)</option>
                    <option value="id">.id (Umum Indonesia)</option>
                    <option value="sch.id">.sch.id (Sekolah - DA Tinggi)</option>
                    <option value="ac.id">.ac.id (Kampus - DA Tinggi)</option>
                    <option value="my.id">.my.id (Blog Pribadi)</option>
                    <option value="biz.id">.biz.id (UMKM / Bisnis)</option>
                </optgroup>
                <optgroup label="Asia & Pasifik">
                    <option value="com.my">.com.my (Malaysia)</option>
                    <option value="com.sg">.com.sg (Singapura)</option>
                    <option value="co.th">.co.th (Thailand)</option>
                    <option value="com.ph">.com.ph (Filipina)</option>
                    <option value="com.au">.com.au (Australia)</option>
                    <option value="co.nz">.co.nz (Selandia Baru)</option>
                    <option value="co.jp">.co.jp (Jepang)</option>
                    <option value="co.in">.co.in (India)</option>
                </optgroup>
                <optgroup label="Eropa">
                    <option value="co.uk">.co.uk (Inggris Raya)</option>
                    <option value="de">.de (Jerman)</option>
                    <option value="fr">.fr (Prancis)</option>
                    <option value="it">.it (Italia)</option>
                    <option value="es">.es (Spanyol)</option>
                    <option value="nl">.nl (Belanda)</option>
                </optgroup>
                <optgroup label="Amerika">
                    <option value="com">.com (Global / US)</option>
                    <option value="ca">.ca (Kanada)</option>
                    <option value="com.br">.com.br (Brasil)</option>
                    <option value="com.mx">.com.mx (Meksiko)</option>
                    <option value="com.ar">.com.ar (Argentina)</option>
                </optgroup>
                <optgroup label="Timur Tengah & Afrika">
                    <option value="co.za">.co.za (Afrika Selatan)</option>
                    <option value="com.ng">.com.ng (Nigeria)</option>
                    <option value="co.ae">.co.ae (Uni Emirat Arab)</option>
                    <option value="com.sa">.com.sa (Arab Saudi)</option>
                    <option value="com.eg">.com.eg (Mesir)</option>
                </optgroup>
                <optgroup label="Platform Blog Populer">
                    <option value="*.blogspot.com">*.blogspot.com (Blogger)</option>
                    <option value="*.wordpress.com">*.wordpress.com (WordPress Free)</option>
                </optgroup>
                <optgroup label="Global & Ekstensi Baru">
                    <option value="net">.net</option>
                    <option value="org">.org</option>
                    <option value="xyz">.xyz</option>
                    <option value="online">.online</option>
                    <option value="tech">.tech</option>
                    <option value="ai">.ai / .io</option>
                    <option value="none">Bebas Semua Ekstensi</option>
                    <option value="custom">Kustom Sendiri (Ketik Manual)...</option>
                </optgroup>
            </select>
            <div x-show="tld === 'custom'" x-cloak class="mt-2">
                <input type="text" x-model="customTld" placeholder="Contoh: or.id atau namaweb.com" class="w-full text-xs border-slate-300 rounded-lg px-3 py-1.5 focus:ring-emerald-500 focus:border-emerald-500">
            </div>
        </div>

        <!-- Footprint Preset -->
        <div>
            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                2. Footprint Kolom Komentar
            </label>
            <select x-model="footprint" class="w-full text-sm border-slate-300 rounded-lg px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500">
                <option value="tinggalkan komentar">"tinggalkan komentar" (WordPress Indo)</option>
                <option value="leave a reply">"leave a reply" (WordPress Global)</option>
                <option value="post a comment">"post a comment" (Blogger / Umum)</option>
                <option value="tulis komentar">"tulis komentar" (Blog Portal)</option>
                <option value="kirim komentar">"kirim komentar"</option>
                <option value="notify me of follow-up comments">"notify me of follow-up comments" (CommentLuv)</option>
                <option value="berikan komentar">"berikan komentar"</option>
            </select>
        </div>

        <!-- Keyword Niche -->
        <div>
            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                3. Kata Kunci Niche / Topik
            </label>
            <input type="text" x-model="keyword" placeholder="Contoh: kuliner, bisnis, teknologi" class="w-full text-sm border-slate-300 rounded-lg px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500">
        </div>
    </div>

    <!-- Output Query Preview & Action Buttons -->
    <div class="mt-5 p-4 rounded-xl bg-slate-900 text-white flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
        <div class="min-w-0 flex-1">
            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-400 block mb-1">Rumus Query Dork Dihasilkan:</span>
            <div class="font-mono text-sm text-emerald-200 truncate select-all" x-text="fullQuery || '(Pilih opsi di atas)'"></div>
        </div>

        <div class="flex items-center gap-2 shrink-0 w-full sm:w-auto">
            <!-- Copy Button -->
            <button type="button" @click="copyQuery()" class="flex-1 sm:flex-initial px-3.5 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold transition border border-slate-700 flex items-center justify-center gap-1.5 cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                <span x-text="copied ? 'Tersalin!' : 'Salin'"></span>
            </button>

            <!-- Search 1-Click Button -->
            <button type="button" @click="openSearch()" class="flex-1 sm:flex-initial px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold transition shadow-xs flex items-center justify-center gap-1.5 cursor-pointer">
                <span>Cari di Google</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
            </button>
        </div>
    </div>

    <!-- Quick Preset Combinations -->
    <div class="mt-4 pt-3 border-t border-slate-100">
        <span class="text-xs font-semibold text-slate-500 block mb-2">⚡ Rekomendasi Rumus Siap Pakai:</span>
        <div class="flex flex-wrap gap-2 text-xs">
            <button type="button" @click="tld = 'co.id'; footprint = 'tinggalkan komentar'; keyword = 'tips'" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 transition cursor-pointer">
                Web .co.id + Komentar + "tips"
            </button>
            <button type="button" @click="tld = 'web.id'; footprint = 'leave a reply'; keyword = 'bisnis'" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 transition cursor-pointer">
                Web .web.id + "leave a reply" + "bisnis"
            </button>
            <button type="button" @click="tld = '*.blogspot.com'; footprint = 'post a comment'; keyword = 'informasi'" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 transition cursor-pointer">
                Blogspot + "post a comment"
            </button>
            <button type="button" @click="tld = 'sch.id'; footprint = 'tinggalkan komentar'; keyword = 'berita'" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 transition cursor-pointer">
                Sekolah .sch.id (DA Tinggi)
            </button>
            <button type="button" @click="tld = 'ac.id'; footprint = 'leave a reply'; keyword = 'artikel'" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 transition cursor-pointer">
                Kampus .ac.id (DA Tinggi)
            </button>
        </div>
    </div>
</div>
