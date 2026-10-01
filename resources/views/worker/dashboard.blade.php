@extends('layouts.app')

@section('title', 'Dashboard Blogwalker')

@section('content')
<div class="space-y-6">

    <!-- Header & Welcome -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Halo, {{ $user->name }}! 👋</h1>
            <p class="text-sm text-slate-500 mt-1">
                Tarif komentar Anda: <span class="font-bold text-emerald-600">Rp {{ number_format($user->default_rate, 0, ',', '.') }}</span> per komentar disetujui.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('blogwalker.submissions.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-sm transition">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Riwayat & Arsip Periode
            </a>
            <a href="{{ route('blogwalker.submissions.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm shadow-md shadow-emerald-100 transition cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Kirim Bukti Komentar
            </a>
        </div>
    </div>

    <!-- Disqualification Warning if Suspended -->
    @if($isDisqualified)
    <div class="p-5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 shadow-xs flex items-start gap-3.5">
        <div class="w-9 h-9 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center font-bold text-lg shrink-0">
            ⛔
        </div>
        <div class="space-y-1">
            <h2 class="text-sm font-bold text-rose-950">Akun Anda Ditangguhkan untuk Periode {{ $activePeriod->name }}</h2>
            <p class="text-xs text-rose-800 leading-relaxed">
                Mohon maaf, pada periode sebelumnya target minimal submit/approved tidak tercapai, sehingga akun Anda belum dapat mengirimkan komentar pada periode ini. Silakan hubungi Super Admin jika ingin mengajukan peninjauan atau dispensasi.
            </p>
        </div>
    </div>
    @endif

    <!-- URGENT COUNTDOWN & DEADLINE MOTIVATION BANNER (H-5 to H-0) -->
    @if($activePeriod && $activePeriod->remainingDays() <= 5 && !$periodProgress['is_qualified'] && !$isDisqualified)
    <div class="p-5 rounded-2xl bg-gradient-to-r from-amber-500/15 via-orange-500/10 to-amber-500/15 border-2 border-amber-500/40 text-amber-950 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="flex items-start gap-3.5">
            <div class="w-11 h-11 rounded-2xl bg-amber-500 text-white flex items-center justify-center font-bold text-2xl shrink-0 shadow-xs">
                ⏰
            </div>
            <div class="space-y-1">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-black uppercase tracking-wider {{ $activePeriod->remainingDays() <= 2 ? 'bg-rose-600 text-white animate-pulse' : 'bg-amber-600 text-white' }}">
                        @if($activePeriod->remainingDays() === 0)
                            Hari Terakhir Periode!
                        @else
                            Sisa {{ $activePeriod->remainingDays() }} Hari Lagi!
                        @endif
                    </span>
                    <h3 class="text-sm font-bold text-slate-900">Perhatian: Target Periode {{ $activePeriod->name }} Segera Berakhir</h3>
                </div>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Periode akan ditutup pada <strong>{{ $activePeriod->ends_at->format('d M Y') }}</strong>. Saat ini Anda masih kurang <strong>{{ $periodProgress['remaining_needed'] }} komentar disetujui</strong> untuk memenuhi kuota minimum dan mengamankan keikutsertaan Anda di bulan depan.
                </p>
            </div>
        </div>
        <div class="shrink-0 flex items-center gap-2 w-full md:w-auto">
            <a href="{{ route('blogwalker.targets.index') }}" class="w-full md:w-auto inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-xl shadow-xs transition">
                <span>🎯 Kejar Target di Antrean URL</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
            </a>
        </div>
    </div>
    @endif

    <!-- MONTHLY PERIOD TARGET & PROGRESS TRACKER -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 text-white p-6 rounded-2xl shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-slate-700/70 gap-2">
            <div class="flex items-center gap-2.5">
                <span class="w-3 h-3 rounded-full {{ $periodProgress['is_qualified'] ? 'bg-emerald-400' : 'bg-amber-400 animate-pulse' }}"></span>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-300">Target Periode {{ $activePeriod->name }}</span>
            </div>
            <div class="flex items-center gap-2 text-xs">
                <span class="text-slate-400">Batas Waktu: {{ $activePeriod->ends_at->format('d M Y') }}</span>
                <span class="px-2 py-0.5 rounded-full bg-slate-800 border border-slate-700 text-emerald-400 font-bold">
                    Sisa {{ $activePeriod->remainingDays() }} Hari
                </span>
            </div>
        </div>

        <div class="space-y-2">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                <div>
                    <span class="text-xs text-slate-400">Target Minimal Anda:</span>
                    <strong class="text-white text-base ml-1">{{ $periodProgress['target'] }} Komentar Disetujui</strong>
                    <span class="text-xs text-slate-400 ml-2">(Maks. {{ $activePeriod->max_urls_per_domain }} URL per domain)</span>
                </div>
                <div class="text-xs">
                    <span class="text-slate-300 font-bold text-sm">{{ $periodProgress['approved'] }} / {{ $periodProgress['target'] }}</span>
                    <span class="text-emerald-400 font-bold ml-1">({{ $periodProgress['percentage'] }}%)</span>
                </div>
            </div>

            <!-- Progress Bar -->
            <div class="w-full h-3 rounded-full bg-slate-800 overflow-hidden p-0.5 border border-slate-700/80">
                <div class="h-full rounded-full transition-all duration-500 {{ $periodProgress['is_qualified'] ? 'bg-emerald-500' : ($periodProgress['percentage'] >= 50 ? 'bg-amber-500' : 'bg-rose-500') }}"
                    style="width: {{ min(100, $periodProgress['percentage']) }}%"></div>
            </div>

            <!-- Status Indicator -->
            <div class="pt-1 flex flex-col sm:flex-row sm:items-center justify-between text-xs gap-1">
                @if($periodProgress['is_qualified'])
                    <span class="inline-flex items-center text-emerald-300 font-semibold gap-1.5">
                        <svg class="w-4 h-4 text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                        Target Tercapai! Anda Resmi Lolos untuk Periode Bulan Depan.
                    </span>
                @else
                    <span class="inline-flex items-center text-amber-300 font-medium gap-1.5">
                        <svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                        Kurang {{ $periodProgress['remaining_needed'] }} approved lagi agar tidak tereliminasi di periode depan!
                    </span>
                @endif

                <span class="text-slate-400">
                    Menunggu Review: <strong class="text-slate-200">{{ $periodProgress['pending'] }}</strong> submission
                </span>
            </div>
        </div>
    </div>

    <!-- Active Assignment Widget (Plotting Khusus Blogwalker) -->
    @if($assignment)
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div class="flex items-center gap-2">
                <span class="flex h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                <h2 class="text-sm font-bold uppercase tracking-wider text-slate-800">Plotting Tugas Khusus Anda Bulan Ini</h2>
            </div>
            <span class="text-xs px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-bold border border-emerald-200">Aktif</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- TLD Allowed -->
            <div>
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-1.5">Ekstensi Domain Target</span>
                <div class="flex flex-wrap gap-1.5">
                    @if(empty($assignment->allowed_tlds))
                        <span class="px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-800 border border-emerald-200 text-xs font-bold">
                            ✨ Bebas Semua Domain (.id, .com, dll.)
                        </span>
                    @else
                        @foreach($assignment->allowed_tlds as $tld)
                            <span class="px-2.5 py-1 rounded-lg bg-slate-100 border border-slate-200 text-slate-900 font-mono text-xs font-bold">{{ $tld }}</span>
                        @endforeach
                    @endif
                </div>
            </div>

            <!-- Target Keywords -->
            <div>
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-1.5">Target Kata Kunci (Keywords)</span>
                <p class="text-xs text-slate-800 font-medium bg-slate-50 p-2.5 rounded-xl border border-slate-200 leading-relaxed">
                    {{ $assignment->target_keywords ?: 'Gunakan keyword yang relevan dengan topik artikel' }}
                </p>
            </div>

            <!-- Target Backlink URL -->
            <div>
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-1.5">URL Target Backlink</span>
                <div class="flex items-center gap-2 bg-slate-50 p-2 rounded-xl border border-slate-200" x-data="{ copied: false }">
                    <input type="text" readonly value="{{ $assignment->target_backlink_url }}" class="bg-transparent text-xs text-emerald-700 font-mono w-full truncate focus:outline-none">
                    <button type="button" @click="navigator.clipboard.writeText('{{ $assignment->target_backlink_url }}'); copied = true; setTimeout(() => copied = false, 2000)"
                        class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-900 text-[11px] font-semibold text-white shrink-0 transition cursor-pointer">
                        <span x-text="copied ? 'Tersalin!' : 'Salin'"></span>
                    </button>
                </div>
            </div>
        </div>

        @if($assignment->custom_instructions)
            <div class="mt-4 pt-3 border-t border-slate-100">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-1">Panduan Khusus Admin:</span>
                <p class="text-xs text-slate-600 leading-relaxed">{{ $assignment->custom_instructions }}</p>
            </div>
        @endif
    </div>
    @endif

    <!-- QUICK TARGET URL POOL WIDGET -->
    @if($availableTargets->count() > 0 || $myActiveTargets->count() > 0)
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-sm">
                    🎯
                </div>
                <div>
                    <h2 class="text-base font-bold text-slate-900 leading-tight">Antrean Misi & Target Siap Dikerjakan</h2>
                    <p class="text-xs text-slate-500">Pilihan tugas (komentar, guestpost, medsos, artikel) yang siap langsung Anda ambil.</p>
                </div>
            </div>
            <a href="{{ route('blogwalker.targets.index') }}" class="text-xs font-semibold text-emerald-700 hover:text-emerald-800 bg-emerald-50 hover:bg-emerald-100 px-3 py-1.5 rounded-lg transition flex items-center gap-1">
                <span>Lihat Semua Antrean</span>
                <span>&rarr;</span>
            </a>
        </div>

        <!-- Active Task Alert if user has one -->
        @if($myActiveTargets->count() > 0)
            @php 
                $currentTask = $myActiveTargets->first(); 
                $currentTaskInfo = $taskTypes[$currentTask->task_type ?? 'comment'] ?? $taskTypes['comment'] ?? ['label' => 'Komentar', 'badge' => 'bg-emerald-100 text-emerald-800'];
            @endphp
            <div class="p-4 bg-amber-50 border border-amber-200 rounded-xl flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider {{ $currentTaskInfo['badge'] }}">
                            {{ $currentTaskInfo['label'] }}
                        </span>
                        <span class="text-xs font-bold text-emerald-700 font-mono bg-emerald-100/70 px-2 py-0.5 rounded">
                            Reward: Rp {{ number_format($currentTask->getEffectiveRate(), 0, ',', '.') }}
                        </span>
                    </div>
                    <div class="text-sm font-bold text-slate-900 break-all">{{ $currentTask->url }}</div>
                    @if($currentTask->keyword)
                        <div class="text-xs text-slate-600">Keyword Target: <strong class="text-slate-800">{{ $currentTask->keyword }}</strong></div>
                    @endif
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <a href="{{ route('blogwalker.submissions.create', ['target_id' => $currentTask->id]) }}" class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition">
                        Kirim Laporan Selesai
                    </a>
                </div>
            </div>
        @endif

        <!-- Available Targets Quick List -->
        @if($availableTargets->count() > 0)
            <div class="divide-y divide-slate-100">
                @foreach($availableTargets as $target)
                    @php 
                        $targetTaskInfo = $taskTypes[$target->task_type ?? 'comment'] ?? $taskTypes['comment'] ?? ['label' => 'Komentar', 'badge' => 'bg-emerald-100 text-emerald-800'];
                    @endphp
                    <div class="py-3 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 hover:bg-slate-50/60 rounded-lg px-2 -mx-2 transition">
                        <div class="space-y-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="inline-block px-1.5 py-0.2 rounded text-[10px] font-bold uppercase {{ $targetTaskInfo['badge'] }}">
                                    {{ $targetTaskInfo['label'] }}
                                </span>
                                <span class="font-bold text-slate-900 text-xs">
                                    Rp {{ number_format($target->getEffectiveRate(), 0, ',', '.') }}
                                </span>
                                <span class="font-mono text-[11px] text-slate-500">{{ $target->root_domain }}</span>
                            </div>
                            <div class="text-xs text-slate-700 truncate max-w-lg" title="{{ $target->url }}">
                                {{ $target->url }}
                            </div>
                        </div>

                        <form method="POST" action="{{ route('blogwalker.targets.claim', $target->id) }}" class="shrink-0">
                            @csrf
                            <button type="submit" onclick="window.open('{{ $target->url }}', '_blank');" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold shadow-xs transition">
                                <span>Ambil & Kerjakan</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
    @endif

    <!-- LIVE DOMAIN CHECKER TOOL (Crucial Feature) -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs" x-data="{
        urlToCheck: '',
        loading: false,
        result: null,
        error: null,
        async checkDomain() {
            if (!this.urlToCheck.trim()) return;
            this.loading = true;
            this.result = null;
            this.error = null;
            try {
                const response = await fetch('{{ route('blogwalker.check-domain') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ url: this.urlToCheck })
                });
                const data = await response.json();
                if (!response.ok) {
                    this.error = data.message || 'Gagal memeriksa domain.';
                } else {
                    this.result = data;
                }
            } catch (err) {
                this.error = 'Terjadi kesalahan jaringan.';
            } finally {
                this.loading = false;
            }
        }
    }">
        <div class="flex items-center gap-2 mb-2">
            <div class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-xs">
                🔍
            </div>
            <h2 class="text-base font-bold text-slate-900">Cek Kuota Domain (Sebelum Komentar)</h2>
        </div>
        <p class="text-xs text-slate-500 mb-4">
            Hindari buang waktu menulis komentar di website yang sudah penuh! Masukkan URL artikel blog di bawah untuk cek sisa kuotanya (Maks. 5 URL per domain).
        </p>

        <form @submit.prevent="checkDomain()" class="flex flex-col sm:flex-row gap-2">
            <div class="relative flex-1">
                <input type="text" x-model="urlToCheck" placeholder="Contoh: https://portalberita.co.id/post/123 atau nama domainnya saja"
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm transition">
            </div>
            <button type="submit" :disabled="loading" class="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-semibold text-sm transition disabled:opacity-50 cursor-pointer flex items-center justify-center gap-2">
                <span x-show="!loading">Periksa Kuota</span>
                <span x-show="loading" x-cloak class="flex items-center gap-1.5">
                    <svg class="animate-spin w-4 h-4" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    Mengecek...
                </span>
            </button>
        </form>

        <!-- Result Box -->
        <div x-show="result" x-cloak class="mt-4 p-4 rounded-xl border transition"
            :class="result && result.can_submit ? 'bg-emerald-50 border-emerald-300 text-emerald-900' : 'bg-rose-50 border-rose-300 text-rose-900'">
            <template x-if="result && result.can_submit">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-start gap-3">
                        <span class="text-2xl">🟢</span>
                        <div>
                            <div class="font-bold text-sm">DOMAIN AMAN & TERSEDIA!</div>
                            <div class="text-xs mt-0.5" x-text="result.message"></div>
                            <div class="text-[11px] font-mono mt-1 opacity-75">
                                Root Domain: <b x-text="result.root_domain"></b> | Terpakai: <span x-text="result.url_count"></span>/<span x-text="result.max_limit"></span> URL
                            </div>
                        </div>
                    </div>
                    <a :href="'{{ route('blogwalker.submissions.create') }}?prefill=' + encodeURIComponent(urlToCheck)"
                        class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs text-center shrink-0 transition">
                        Komentar di Sini &rarr;
                    </a>
                </div>
            </template>
            <template x-if="result && !result.can_submit">
                <div class="flex items-start gap-3">
                    <span class="text-2xl">🔴</span>
                    <div>
                        <div class="font-bold text-sm">DOMAIN PENUH / TIDAK BISA DIGUNAKAN!</div>
                        <div class="text-xs mt-0.5" x-text="result.message"></div>
                        <div class="text-[11px] font-mono mt-1 opacity-75">
                            Root Domain: <b x-text="result.root_domain"></b> | Status: SUDAH MENCAPAI BATAS 5 URL
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <div x-show="error" x-cloak class="mt-4 p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs" x-text="error"></div>
    </div>

    <!-- Summary Metrics Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <!-- Hari Ini -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Submit Hari Ini</span>
            <div class="text-2xl font-bold text-slate-900 mt-1">{{ number_format($stats['today_count']) }}</div>
            <span class="text-[11px] text-slate-400 mt-1 block">Komentar baru dikirim</span>
        </div>

        <!-- Menunggu Review -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-xs font-semibold text-amber-600 uppercase tracking-wider block">Menunggu Review</span>
            <div class="text-2xl font-bold text-amber-600 mt-1">{{ number_format($stats['pending_count']) }}</div>
            <span class="text-[11px] text-slate-400 mt-1 block">Dalam antrean admin</span>
        </div>

        <!-- Saldo Belum Dibayar -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-xs font-semibold text-emerald-600 uppercase tracking-wider block">Disetujui (Belum Cair)</span>
            <div class="text-2xl font-bold text-emerald-600 mt-1">Rp {{ number_format($stats['unpaid_earnings'], 0, ',', '.') }}</div>
            <span class="text-[11px] text-slate-400 mt-1 block">{{ $stats['approved_count'] }} komentar disetujui</span>
        </div>

        <!-- Total Diterima -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Total Gaji Diterima</span>
            <div class="text-2xl font-bold text-slate-900 mt-1">Rp {{ number_format($stats['total_paid'], 0, ',', '.') }}</div>
            <span class="text-[11px] text-slate-400 mt-1 block">Sudah ditransfer</span>
        </div>
    </div>

    <!-- Recent Submissions Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-base font-bold text-slate-900">5 Pengiriman Terakhir Anda</h3>
            <a href="{{ route('blogwalker.submissions.index') }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700">Lihat Semua &rarr;</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-[11px] uppercase font-bold tracking-wider text-slate-500 border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-3">Tanggal</th>
                        <th class="px-6 py-3">Domain & Target URL</th>
                        <th class="px-6 py-3">Jenis</th>
                        <th class="px-6 py-3">Status Review</th>
                        <th class="px-6 py-3 text-right">Tarif</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentSubmissions as $sub)
                    <tr class="hover:bg-slate-50/60 transition">
                        <td class="px-6 py-4 text-xs text-slate-500 whitespace-nowrap">
                            {{ $sub->created_at->format('d M Y, H:i') }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="font-bold text-slate-800 block text-xs">{{ $sub->domain->root_domain }}</span>
                            <a href="{{ $sub->target_url }}" target="_blank" class="text-xs text-emerald-600 hover:underline truncate max-w-xs block">
                                {{ $sub->target_url }}
                            </a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-xs">
                            @if($sub->comment_type === 'approved_live')
                                <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-semibold border border-emerald-200">Live Langsung</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 font-semibold border border-slate-200">Moderasi</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-xs">
                            @if($sub->review_status === 'approved')
                                <span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 font-bold">Disetujui</span>
                            @elseif($sub->review_status === 'rejected')
                                <span class="px-2.5 py-1 rounded-full bg-rose-100 text-rose-800 font-bold">Ditolak</span>
                            @else
                                <span class="px-2.5 py-1 rounded-full bg-amber-100 text-amber-800 font-bold">Menunggu</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right whitespace-nowrap font-semibold text-slate-900">
                            Rp {{ number_format($sub->rate_amount, 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-xs text-slate-400">
                            Belum ada riwayat komentar. Silakan klik tombol "Kirim Bukti Komentar Baru" di atas.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
