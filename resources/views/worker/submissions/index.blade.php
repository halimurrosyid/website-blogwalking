@extends('layouts.app')

@section('title', 'Riwayat Komentar')

@section('content')
<div class="space-y-6" x-data="{ lightboxOpen: false, lightboxImg: '' }">

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
                            <div class="w-16 h-12 rounded-lg overflow-hidden bg-slate-100 border border-slate-200 cursor-pointer hover:opacity-80 transition group relative"
                                @click="lightboxImg = '{{ $sub->screenshot_url }}'; lightboxOpen = true">
                                <img src="{{ $sub->screenshot_url }}" alt="SS" class="w-full h-full object-cover">
                                <div class="absolute inset-0 bg-black/30 flex items-center justify-center opacity-0 group-hover:opacity-100 transition text-white text-[10px]">
                                    🔍 Zoom
                                </div>
                            </div>
                        </td>

                        <!-- Domain & URL -->
                        <td class="px-6 py-4 max-w-sm">
                            <span class="font-bold text-slate-900 block text-xs">{{ $sub->domain->root_domain }}</span>
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
                            @if($sub->domain_rating)
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

    <!-- Lightbox Modal for Screenshot Preview -->
    <div x-show="lightboxOpen" x-cloak class="fixed inset-0 z-50 bg-black/80 flex items-center justify-center p-4" @click="lightboxOpen = false">
        <div class="relative max-w-5xl max-h-[90vh] bg-slate-900 rounded-xl overflow-hidden p-2" @click.stop>
            <button @click="lightboxOpen = false" class="absolute top-4 right-4 text-white hover:text-rose-400 bg-black/60 rounded-full w-8 h-8 flex items-center justify-center font-bold text-lg z-10 cursor-pointer">
                &times;
            </button>
            <img :src="lightboxImg" alt="Screenshot Zoom" class="max-h-[85vh] w-auto mx-auto object-contain rounded-lg">
        </div>
    </div>

</div>
@endsection
