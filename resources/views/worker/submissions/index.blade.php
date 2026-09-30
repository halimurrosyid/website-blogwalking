@extends('layouts.app')

@section('title', 'Riwayat Komentar')

@section('content')
<div class="space-y-6" x-data="{ 
    lightboxOpen: false, 
    lightboxImg: '',
    openLightbox(url) {
        this.lightboxImg = url;
        this.lightboxOpen = true;
        if (typeof openWorkerLightbox === 'function') {
            openWorkerLightbox(url);
        }
    }
}">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Riwayat Pengiriman Komentar</h1>
            <p class="text-sm text-slate-500 mt-1">Daftar seluruh link komentar yang telah Anda kirimkan.</p>
        </div>
        <div>
            <a href="{{ route('blogwalker.submissions.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-sm transition">
                + Kirim Komentar Baru
            </a>
        </div>
    </div>

    <!-- Period Summary KPI Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-xs">
            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Total Submit</span>
            <span class="text-xl font-black text-slate-900 mt-1 block">{{ $stats['total'] }}</span>
        </div>
        <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-xs">
            <span class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider block">Disetujui</span>
            <span class="text-xl font-black text-emerald-600 mt-1 block">{{ $stats['approved'] }}</span>
        </div>
        <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-xs">
            <span class="text-[11px] font-bold text-amber-600 uppercase tracking-wider block">Menunggu</span>
            <span class="text-xl font-black text-amber-600 mt-1 block">{{ $stats['pending'] }}</span>
        </div>
        <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-xs">
            <span class="text-[11px] font-bold text-rose-600 uppercase tracking-wider block">Ditolak</span>
            <span class="text-xl font-black text-rose-600 mt-1 block">{{ $stats['rejected'] }}</span>
        </div>
        <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-xs sm:col-span-2">
            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Total Gaji Disetujui</span>
            <div class="flex items-baseline justify-between mt-1">
                <span class="text-lg font-black text-slate-900">Rp {{ number_format($stats['earned'], 0, ',', '.') }}</span>
                <span class="text-[11px] text-blue-600 font-semibold">Cair: Rp {{ number_format($stats['paid'], 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <!-- Period Filter Dropdown & Status Tabs -->
        <div class="flex flex-wrap items-center gap-3">
            <form method="GET" action="{{ route('blogwalker.submissions.index') }}" class="flex flex-wrap items-center gap-2">
                <input type="hidden" name="status" value="{{ $currentStatus }}">
                <input type="hidden" name="search" value="{{ $search }}">
                
                <select name="period_id" onchange="this.form.submit()" class="px-3 py-1.5 rounded-lg border border-slate-300 text-xs font-semibold bg-white text-slate-700 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    <option value="all" {{ $selectedPeriodId === 'all' || $selectedPeriodId === '' ? 'selected' : '' }}>-- Semua Periode --</option>
                    @foreach($periods as $p)
                        <option value="{{ $p->id }}" {{ $selectedPeriodId == $p->id ? 'selected' : '' }}>
                            {{ $p->name }} {{ $p->is_active ? '🟢 (Aktif)' : '⚪ (Lalu)' }}
                        </option>
                    @endforeach
                </select>

                <select name="task_type" onchange="this.form.submit()" class="px-3 py-1.5 rounded-lg border border-slate-300 text-xs font-semibold bg-white text-slate-700 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    <option value="">-- Semua Kategori Misi --</option>
                    @foreach($taskTypes as $k => $t)
                        <option value="{{ $k }}" {{ ($currentTaskType ?? '') === $k ? 'selected' : '' }}>{{ $t['label'] }}</option>
                    @endforeach
                </select>
            </form>

            <div class="flex flex-wrap gap-1.5 text-xs font-semibold">
                <a href="{{ route('blogwalker.submissions.index', ['status' => '', 'period_id' => $selectedPeriodId, 'task_type' => $currentTaskType, 'search' => $search]) }}" 
                    class="px-3 py-1.5 rounded-lg {{ $currentStatus === '' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    Semua
                </a>
                <a href="{{ route('blogwalker.submissions.index', ['status' => 'pending', 'period_id' => $selectedPeriodId, 'task_type' => $currentTaskType, 'search' => $search]) }}" 
                    class="px-3 py-1.5 rounded-lg {{ $currentStatus === 'pending' ? 'bg-amber-600 text-white' : 'bg-amber-50 text-amber-800 hover:bg-amber-100' }}">
                    Menunggu Review
                </a>
                <a href="{{ route('blogwalker.submissions.index', ['status' => 'approved', 'period_id' => $selectedPeriodId, 'task_type' => $currentTaskType, 'search' => $search]) }}" 
                    class="px-3 py-1.5 rounded-lg {{ $currentStatus === 'approved' ? 'bg-emerald-600 text-white' : 'bg-emerald-50 text-emerald-800 hover:bg-emerald-100' }}">
                    Disetujui
                </a>
                <a href="{{ route('blogwalker.submissions.index', ['status' => 'rejected', 'period_id' => $selectedPeriodId, 'task_type' => $currentTaskType, 'search' => $search]) }}" 
                    class="px-3 py-1.5 rounded-lg {{ $currentStatus === 'rejected' ? 'bg-rose-600 text-white' : 'bg-rose-50 text-rose-800 hover:bg-rose-100' }}">
                    Ditolak
                </a>
            </div>
        </div>

        <!-- Search input -->
        <form method="GET" action="{{ route('blogwalker.submissions.index') }}" class="flex gap-2">
            <input type="hidden" name="period_id" value="{{ $selectedPeriodId }}">
            <input type="hidden" name="status" value="{{ $currentStatus }}">
            <input type="hidden" name="task_type" value="{{ $currentTaskType }}">
            <input type="text" name="search" value="{{ $search }}" placeholder="Cari domain atau URL..."
                class="px-3 py-1.5 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none w-56">
            <button type="submit" class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs">
                Cari
            </button>
        </form>
    </div>

    <!-- Submissions Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-[11px] uppercase font-bold tracking-wider text-slate-500 border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-3.5">Bukti Screenshot</th>
                        <th class="px-6 py-3.5">Domain & Target URL</th>
                        <th class="px-6 py-3.5">Kategori Misi</th>
                        <th class="px-6 py-3.5">Tanggal</th>
                        <th class="px-6 py-3.5">Status Verifikasi</th>
                        <th class="px-6 py-3.5">Status Gaji</th>
                        <th class="px-6 py-3.5 text-right">Tarif Komisi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($submissions as $sub)
                    @php
                        $subTask = $taskTypes[$sub->task_type ?? 'comment'] ?? $taskTypes['comment'] ?? ['label' => 'Komentar', 'badge' => 'bg-emerald-100 text-emerald-800'];
                    @endphp
                    <tr class="hover:bg-slate-50/60 transition">
                        <!-- Screenshot Preview -->
                        <td class="px-6 py-4">
                            <div class="w-16 h-12 rounded-lg overflow-hidden bg-slate-100 border border-slate-200 cursor-pointer hover:opacity-80 transition group relative shadow-2xs"
                                @click="openLightbox('{{ $sub->screenshot_url }}')"
                                onclick="openWorkerLightbox('{{ $sub->screenshot_url }}')">
                                <img src="{{ $sub->screenshot_url }}" alt="SS" class="w-full h-full object-cover"
                                    onerror="this.onerror=null; this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'64\' height=\'48\' fill=\'%2394a3b8\' viewBox=\'0 0 24 24\'><rect width=\'24\' height=\'24\' fill=\'%23f1f5f9\'/><path d=\'M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z\'/></svg>'">
                                <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition text-white text-[10px] font-bold">
                                    🔍 Zoom
                                </div>
                            </div>
                            <a href="{{ $sub->screenshot_url }}" target="_blank" rel="noopener noreferrer" class="text-[10px] text-emerald-600 hover:text-emerald-800 hover:underline block text-center mt-1 font-semibold">
                                Buka Foto ↗
                            </a>
                        </td>

                        <!-- Domain & URL -->
                        <td class="px-6 py-4 max-w-sm">
                            <span class="font-bold text-slate-900 block text-xs">{{ $sub->domain->root_domain }}</span>
                            @php
                                $hasMozKey = \App\Services\SeoMetricService::isMozEnabled();
                                $hasAhrefsKey = \App\Services\SeoMetricService::isAhrefsEnabled();
                                $hasOprKey = \App\Services\SeoMetricService::isOpenPageRankEnabled();
                            @endphp
                            @if($sub->domain && (($hasMozKey && ($sub->domain->da || $sub->domain->pa)) || ($hasAhrefsKey && $sub->domain->dr) || ($hasOprKey && $sub->domain->pr)))
                                <div class="flex items-center gap-1 mt-0.5 mb-1 flex-wrap">
                                    @if($hasMozKey && $sub->domain->da)
                                        <span class="px-1.5 py-0.2 rounded text-[10px] font-bold font-mono bg-blue-50 text-blue-700 border border-blue-200">DA {{ $sub->domain->da }}</span>
                                    @endif
                                    @if($hasMozKey && $sub->domain->pa)
                                        <span class="px-1.5 py-0.2 rounded text-[10px] font-bold font-mono bg-cyan-50 text-cyan-700 border border-cyan-200">PA {{ $sub->domain->pa }}</span>
                                    @endif
                                    @if($hasAhrefsKey && $sub->domain->dr)
                                        <span class="px-1.5 py-0.2 rounded text-[10px] font-bold font-mono bg-amber-50 text-amber-800 border border-amber-200">DR {{ $sub->domain->dr }}</span>
                                    @endif
                                    @if($hasOprKey && $sub->domain->pr)
                                        <span class="px-1.5 py-0.2 rounded text-[10px] font-bold font-mono bg-purple-50 text-purple-700 border border-purple-200">PR {{ $sub->domain->pr }}</span>
                                    @endif
                                </div>
                            @endif
                            <a href="{{ $sub->target_url }}" target="_blank" class="text-xs text-slate-500 hover:text-emerald-600 hover:underline truncate block font-mono" title="{{ $sub->target_url }}">
                                {{ $sub->target_url }}
                            </a>
                            @if($sub->published_url)
                                <a href="{{ $sub->published_url }}" target="_blank" class="text-xs font-semibold text-emerald-700 hover:underline truncate block mt-1" title="Hasil Post: {{ $sub->published_url }}">
                                    🔗 Hasil: {{ $sub->published_url }}
                                </a>
                            @endif
                            @if($sub->client_url)
                                <div class="text-[11px] text-blue-600 truncate mt-0.5" title="Klien: {{ $sub->client_url }}">
                                    Klien: {{ $sub->client_url }}
                                </div>
                            @endif
                        </td>

                        <!-- Kategori Misi -->
                        <td class="px-6 py-4 whitespace-nowrap text-xs">
                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider {{ $subTask['badge'] }}">
                                {{ $subTask['label'] }}
                            </span>
                            @if($sub->platform)
                                <div class="text-[10px] font-semibold text-blue-800 bg-blue-50 px-1.5 py-0.2 rounded mt-1 inline-block">{{ $sub->platform }}</div>
                            @endif
                            @if($sub->social_account)
                                <div class="text-[10px] font-mono font-semibold text-purple-800 bg-purple-50 px-1.5 py-0.2 rounded mt-1 inline-block">👤 @<span>{{ $sub->social_account }}</span></div>
                            @endif
                            @if($sub->domain_rating && $sub->task_type === 'comment_high_dr')
                                <div class="text-[10px] font-semibold text-amber-800 bg-amber-50 px-1.5 py-0.2 rounded mt-1 inline-block">DR {{ $sub->domain_rating }}</div>
                            @endif
                        </td>

                        <!-- Date & Period -->
                        <td class="px-6 py-4 text-xs whitespace-nowrap">
                            <span class="text-slate-800 font-medium block">{{ $sub->created_at->format('d M Y, H:i') }}</span>
                            @if($sub->period)
                                <span class="text-[10px] text-slate-400 mt-0.5 block">
                                    Periode: {{ $sub->period->name }}
                                </span>
                            @endif
                        </td>

                        <!-- Review Status -->
                        <td class="px-6 py-4 whitespace-nowrap text-xs">
                            @if($sub->review_status === 'approved')
                                <span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 font-bold">Disetujui</span>
                            @elseif($sub->review_status === 'rejected')
                                <div>
                                    <span class="px-2.5 py-1 rounded-full bg-rose-100 text-rose-800 font-bold">Ditolak</span>
                                    @if($sub->rejection_reason)
                                        <p class="text-[11px] text-rose-600 mt-1 max-w-xs italic">"{{ $sub->rejection_reason }}"</p>
                                    @endif
                                </div>
                            @else
                                <span class="px-2.5 py-1 rounded-full bg-amber-100 text-amber-800 font-bold">Menunggu Review</span>
                            @endif
                        </td>

                        <!-- Payment Status -->
                        <td class="px-6 py-4 whitespace-nowrap text-xs">
                            @if($sub->is_paid)
                                <span class="px-2 py-0.5 rounded-full bg-blue-100 text-blue-800 font-bold">Sudah Cair</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-500 font-medium">Belum Cair</span>
                            @endif
                        </td>

                        <!-- Amount -->
                        <td class="px-6 py-4 text-right whitespace-nowrap font-bold text-slate-900">
                            Rp {{ number_format($sub->rate_amount, 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-xs text-slate-400">
                            Tidak ada pengiriman komentar yang cocok dengan filter.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($submissions->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $submissions->links() }}
            </div>
        @endif
    </div>

    <!-- Lightbox Modal for Screenshot Preview (Instant Pop-up) -->
    <div id="worker-lightbox-modal"
        x-show="lightboxOpen"
        x-cloak
        @keydown.escape.window="lightboxOpen = false; closeWorkerLightbox()"
        class="fixed inset-0 z-50 bg-black/85 flex items-center justify-center p-4 backdrop-blur-2xs"
        style="display: none;"
        @click="lightboxOpen = false; closeWorkerLightbox()"
        onclick="if(event.target === this) closeWorkerLightbox()">
        <div class="relative max-w-5xl max-h-[90vh] bg-slate-900 rounded-2xl overflow-hidden p-2 shadow-2xl border border-slate-700" @click.stop onclick="event.stopPropagation()">
            <div class="absolute top-4 right-4 z-10 flex items-center gap-2">
                <a id="worker-lightbox-link" :href="lightboxImg" href="#" target="_blank" rel="noopener noreferrer" class="px-3 py-1.5 rounded-full bg-black/70 hover:bg-black/95 text-white text-xs font-semibold transition flex items-center gap-1 shadow-sm">
                    Buka Ukuran Asli ↗
                </a>
                <button type="button" @click="lightboxOpen = false; closeWorkerLightbox()" onclick="closeWorkerLightbox()" class="text-white hover:text-rose-400 bg-black/70 hover:bg-black/95 rounded-full w-8 h-8 flex items-center justify-center font-bold text-lg cursor-pointer transition">
                    &times;
                </button>
            </div>
            <img id="worker-lightbox-img" :src="lightboxImg" src="" alt="Screenshot Zoom" class="max-h-[85vh] w-auto mx-auto object-contain rounded-xl shadow-lg">
        </div>
    </div>

    <script>
        function openWorkerLightbox(url) {
            const modal = document.getElementById('worker-lightbox-modal');
            const img = document.getElementById('worker-lightbox-img');
            const link = document.getElementById('worker-lightbox-link');
            if (img) img.src = url;
            if (link) link.href = url;
            if (modal) {
                modal.removeAttribute('x-cloak');
                modal.style.display = 'flex';
            }
        }

        function closeWorkerLightbox() {
            const modal = document.getElementById('worker-lightbox-modal');
            if (modal) {
                modal.style.display = 'none';
            }
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeWorkerLightbox();
            }
        });
    </script>

</div>
@endsection
