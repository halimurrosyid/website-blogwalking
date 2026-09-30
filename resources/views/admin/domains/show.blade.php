@extends('layouts.app')

@section('title', 'Detail Domain: ' . $domain->root_domain)

@section('content')
<div class="space-y-6" x-data="{ lightboxOpen: false, lightboxImg: '' }">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-2xl font-bold text-slate-900">{{ $domain->root_domain }}</h1>
                @if($domain->is_locked)
                    <span class="px-2.5 py-1 rounded-full bg-rose-100 text-rose-800 font-bold text-xs">🔒 Penuh (5/5 URL)</span>
                @else
                    <span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 font-bold text-xs">🟢 Tersedia {{ $domain->remainingSlots() }} Slot</span>
                @endif
            </div>
            <p class="text-sm text-slate-500 mt-1">
                TLD: <span class="font-mono font-bold text-slate-700">{{ $domain->tld }}</span> &bull; 
                Total URL Terpakai: <span class="font-bold text-slate-900">{{ $domain->url_count }}/{{ $domain->max_limit }}</span> &bull; 
                IP Address: <span class="font-mono font-bold text-slate-700">{{ $domain->ip_address ?? 'Belum terdeteksi' }}</span> &bull; 
                Subnet C-Class: <span class="font-mono font-bold text-slate-700">{{ $domain->ip_subnet ?? '-' }}</span>
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.domains.index') }}" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
                &larr; Kembali
            </a>
            <form method="POST" action="{{ route('admin.domains.reset', $domain) }}" onsubmit="return confirm('Reset kuota domain ini ke 0?')">
                @csrf
                <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs transition cursor-pointer">
                    🔄 Reset Kuota ke 0
                </button>
            </form>
        </div>
    </div>

    <!-- URLs Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h2 class="text-base font-bold text-slate-900">Daftar Komentar pada Domain Ini</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-[11px] uppercase font-bold tracking-wider text-slate-500 border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-3.5">Screenshot</th>
                        <th class="px-6 py-3.5">Target URL Artikel</th>
                        <th class="px-6 py-3.5">Worker</th>
                        <th class="px-6 py-3.5">Tanggal</th>
                        <th class="px-6 py-3.5">Status Review</th>
                        <th class="px-6 py-3.5 text-right">Tarif</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($submissions as $sub)
                    <tr class="hover:bg-slate-50/60 transition">
                        <td class="px-6 py-4">
                            <div class="w-16 h-12 rounded-lg overflow-hidden bg-slate-100 border border-slate-200 cursor-pointer hover:opacity-80 transition relative group"
                                @click="lightboxImg = '{{ $sub->screenshot_url }}'; lightboxOpen = true">
                                <img src="{{ $sub->screenshot_url }}" alt="SS" class="w-full h-full object-cover">
                                <div class="absolute inset-0 bg-black/30 flex items-center justify-center opacity-0 group-hover:opacity-100 transition text-white text-[10px]">
                                    Zoom
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <a href="{{ $sub->target_url }}" target="_blank" class="text-xs text-emerald-600 hover:underline font-mono block max-w-md truncate">
                                {{ $sub->target_url }}
                            </a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-xs font-semibold text-slate-800">
                            {{ $sub->user->name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-400">
                            {{ $sub->created_at->format('d M Y, H:i') }}
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
                        <td class="px-6 py-4 text-right whitespace-nowrap font-bold text-slate-900 text-xs">
                            Rp {{ number_format($sub->rate_amount, 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-xs text-slate-400">
                            Belum ada komentar yang tercatat untuk domain ini.
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

    <!-- Lightbox Zoom Modal -->
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
