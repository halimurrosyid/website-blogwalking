@extends('layouts.app')

@section('title', 'Antrean Target Komentar')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Antrean Target Komentar</h1>
            <p class="text-sm text-slate-500 mt-1">Daftar website target yang telah disediakan admin untuk langsung dikomentari.</p>
        </div>
        <div>
            <a href="{{ route('blogwalker.submissions.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-sm font-semibold transition">
                + Kirim URL Manual (Cari Sendiri)
            </a>
        </div>
    </div>

    <!-- Stats Bar -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl shadow-xs">
            <div class="text-xs font-semibold text-emerald-700 uppercase tracking-wider">Target Tersedia di Antrean</div>
            <div class="text-2xl font-bold text-emerald-800 mt-1">{{ number_format($stats['available_count']) }} URL</div>
        </div>
        <div class="p-4 bg-blue-50 border border-blue-200 rounded-xl shadow-xs">
            <div class="text-xs font-semibold text-blue-700 uppercase tracking-wider">Sedang Anda Kerjakan</div>
            <div class="text-2xl font-bold text-blue-800 mt-1">{{ $stats['my_in_progress'] }} URL</div>
        </div>
        <div class="p-4 bg-purple-50 border border-purple-200 rounded-xl shadow-xs">
            <div class="text-xs font-semibold text-purple-700 uppercase tracking-wider">Selesai Hari Ini</div>
            <div class="text-2xl font-bold text-purple-800 mt-1">{{ $stats['completed_today'] }} URL</div>
        </div>
    </div>

    <!-- Active Tasks (Sedang Dikerjakan oleh Blogwalker ini) -->
    @if($myActiveTargets->count() > 0)
        <div class="bg-amber-50/70 border border-amber-200 rounded-xl p-5 shadow-xs">
            <div class="flex items-center gap-2 font-bold text-amber-900 text-base mb-3">
                <svg class="w-5 h-5 text-amber-600 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Target yang Sedang Anda Kerjakan ({{ $myActiveTargets->count() }})
            </div>

            <div class="space-y-3">
                @foreach($myActiveTargets as $myTarget)
                    <div class="bg-white p-4 rounded-lg border border-amber-200 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 shadow-2xs">
                        <div class="space-y-1 max-w-xl">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 bg-amber-100 text-amber-800 rounded text-xs font-semibold">Aktif</span>
                                <span class="text-xs font-mono font-medium text-slate-500">{{ $myTarget->root_domain }}</span>
                            </div>
                            <div class="font-medium text-sm text-slate-900 break-all">
                                <a href="{{ $myTarget->url }}" target="_blank" rel="noopener noreferrer" class="text-emerald-700 hover:text-emerald-900 underline flex items-center gap-1">
                                    {{ $myTarget->url }}
                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                </a>
                            </div>
                            @if($myTarget->keyword)
                                <div class="text-xs text-slate-600">
                                    Keyword Target: <strong class="text-slate-800">{{ $myTarget->keyword }}</strong>
                                </div>
                            @endif
                            @if($myTarget->notes)
                                <div class="text-xs text-slate-500 italic">{{ $myTarget->notes }}</div>
                            @endif
                        </div>

                        <div class="flex items-center gap-2 shrink-0 w-full md:w-auto">
                            <a href="{{ route('blogwalker.submissions.create', ['target_id' => $myTarget->id]) }}" class="flex-1 md:flex-initial inline-flex justify-center items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold shadow-xs transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Upload Bukti Screenshot
                            </a>
                            <form method="POST" action="{{ route('blogwalker.targets.skip', $myTarget->id) }}" onsubmit="return confirm('Lewati target ini jika link mati atau form komentar ditutup?')">
                                @csrf
                                <button type="submit" class="px-3 py-2 bg-slate-100 hover:bg-rose-50 hover:text-rose-700 text-slate-600 rounded-lg text-xs font-medium transition" title="Laporkan web mati / lewati">
                                    Lewati
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Available Targets Pool -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
            <h2 class="font-bold text-slate-800 text-base">Daftar Target Siap Dikomentari</h2>
            <form method="GET" action="{{ route('blogwalker.targets.index') }}" class="flex items-center gap-2 w-full sm:w-auto">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari domain atau keyword..." class="text-xs border-slate-300 rounded-lg px-3 py-1.5 focus:ring-emerald-500 focus:border-emerald-500 w-full sm:w-64">
                <button type="submit" class="px-3 py-1.5 bg-slate-800 text-white rounded-lg text-xs font-medium">Cari</button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-600 uppercase tracking-wider">
                        <th class="py-3 px-4">URL Target</th>
                        <th class="py-3 px-4">Domain & Kuota</th>
                        <th class="py-3 px-4">Keyword Rekomendasi</th>
                        <th class="py-3 px-4 text-right">Aksi Cepat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($availableTargets as $target)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3.5 px-4 max-w-md">
                                <div class="font-medium text-slate-800 line-clamp-1" title="{{ $target->url }}">
                                    {{ $target->url }}
                                </div>
                                @if($target->notes)
                                    <div class="text-[11px] text-slate-500 mt-0.5 line-clamp-1">{{ $target->notes }}</div>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="font-semibold text-slate-800">{{ $target->root_domain }}</span>
                                @if($target->domain)
                                    <span class="ml-2 text-xs text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full font-medium">
                                        Slot sisa: {{ $target->domain->remainingSlots() }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap text-xs">
                                @if($target->keyword)
                                    <span class="px-2 py-1 bg-slate-100 text-slate-700 rounded font-medium">{{ $target->keyword }}</span>
                                @else
                                    <span class="text-slate-400">Bebas / Sesuai Topik</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <!-- Direct Claim & Execute Button -->
                                    <form method="POST" action="{{ route('blogwalker.targets.claim', $target->id) }}">
                                        @csrf
                                        <button type="submit" onclick="window.open('{{ $target->url }}', '_blank');" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold shadow-xs transition">
                                            <span>Komen Sekarang</span>
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                        </button>
                                    </form>

                                    <!-- Skip button -->
                                    <form method="POST" action="{{ route('blogwalker.targets.skip', $target->id) }}" onsubmit="return confirm('Lewati target ini?')">
                                        @csrf
                                        <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 rounded transition" title="Lewati Target">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-12 text-center text-slate-500">
                                <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <p class="font-medium text-slate-700">Semua target URL saat ini sudah habis atau selesai dikerjakan!</p>
                                <p class="text-xs text-slate-400 mt-1">Anda juga dapat mencari website secara mandiri lalu kirimkan via menu "+ Kirim Komentar".</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($availableTargets->hasPages())
            <div class="p-4 border-t border-slate-200">
                {{ $availableTargets->links() }}
            </div>
        @endif
    </div>

    <!-- Google Dork & Footprint Generator for Blogwalker -->
    @include('components.dork-generator')
</div>
@endsection
