@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="space-y-6" x-data="{ lightboxOpen: false, lightboxImg: '', rejectModalOpen: false, rejectUrl: '' }">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Panel Manajemen Admin 👑</h1>
            <p class="text-sm text-slate-500 mt-1">Monitoring tim blogwalker, verifikasi komentar, dan rekap pembayaran.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.reviews.index') }}" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-sm transition">
                Buka Antrean Verifikasi ({{ $stats['pending_reviews'] }})
            </a>
            <a href="{{ route('admin.workers.create') }}" class="px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs shadow-sm transition">
                + Tambah Blogwalker
            </a>
        </div>
    </div>

    <!-- Stat Metrics -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <!-- Blogwalker Aktif -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Blogwalker Aktif</span>
            <div class="text-2xl font-black text-slate-900 mt-1">{{ number_format($stats['total_workers']) }}</div>
            <a href="{{ route('admin.workers.index') }}" class="text-[11px] text-emerald-600 hover:underline mt-1 block">Kelola tim &rarr;</a>
        </div>

        <!-- Total Domain -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Total Domain</span>
            <div class="text-2xl font-black text-slate-900 mt-1">{{ number_format($stats['total_domains']) }}</div>
            <span class="text-[11px] text-slate-500 mt-1 block">Tercatat di sistem</span>
        </div>

        <!-- Domain Penuh (Locked) -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-[11px] font-bold text-rose-500 uppercase tracking-wider block">Domain Penuh / Terkunci</span>
            <div class="text-2xl font-black text-rose-600 mt-1">{{ number_format($stats['locked_domains']) }}</div>
            <a href="{{ route('admin.domains.index', ['status' => 'locked']) }}" class="text-[11px] text-rose-600 hover:underline mt-1 block">Buka / Reset &rarr;</a>
        </div>

        <!-- Menunggu Review -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-[11px] font-bold text-amber-500 uppercase tracking-wider block">Antrean Review</span>
            <div class="text-2xl font-black text-amber-600 mt-1">{{ number_format($stats['pending_reviews']) }}</div>
            <a href="{{ route('admin.reviews.index') }}" class="text-[11px] text-amber-600 hover:underline mt-1 block">Verifikasi &rarr;</a>
        </div>

        <!-- Gaji Belum Dibayar -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider block">Unpaid Payout</span>
            <div class="text-xl font-black text-emerald-700 mt-1">Rp {{ number_format($stats['unpaid_payout'], 0, ',', '.') }}</div>
            <a href="{{ route('admin.payouts.index') }}" class="text-[11px] text-emerald-600 hover:underline mt-1 block">Rekap & Bayar &rarr;</a>
        </div>

        <!-- Total Sudah Dibayar -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Total Gaji Dibayar</span>
            <div class="text-xl font-black text-slate-900 mt-1">Rp {{ number_format($stats['total_paid'], 0, ',', '.') }}</div>
            <span class="text-[11px] text-slate-500 mt-1 block">Histori transfer</span>
        </div>
    </div>

    <!-- Quick Review Queue (Antrean Mendesak) -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-pulse"></span>
                <h2 class="text-base font-bold text-slate-900">Antrean Verifikasi Komentar Mendesak</h2>
            </div>
            <a href="{{ route('admin.reviews.index') }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700">Lihat Semua Antrean &rarr;</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-[11px] uppercase font-bold tracking-wider text-slate-500 border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-3.5">Screenshot</th>
                        <th class="px-6 py-3.5">Worker</th>
                        <th class="px-6 py-3.5">Domain & URL</th>
                        <th class="px-6 py-3.5">Jenis</th>
                        <th class="px-6 py-3.5">Tarif</th>
                        <th class="px-6 py-3.5 text-right">Aksi 1-Klik</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($pendingSubmissions as $sub)
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
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="font-bold text-slate-800 text-xs block">{{ $sub->user->name }}</span>
                            <span class="text-[11px] text-slate-400">{{ $sub->user->email }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="font-bold text-slate-900 block text-xs">{{ $sub->domain->root_domain }}</span>
                            <a href="{{ $sub->target_url }}" target="_blank" class="text-xs text-slate-500 hover:text-emerald-600 hover:underline truncate max-w-xs block font-mono" title="{{ $sub->target_url }}">
                                {{ $sub->target_url }}
                            </a>
                            @if($sub->published_url)
                                <a href="{{ $sub->published_url }}" target="_blank" class="text-xs font-semibold text-emerald-700 hover:underline truncate max-w-xs block mt-0.5" title="Hasil Post: {{ $sub->published_url }}">
                                    🔗 Hasil: {{ $sub->published_url }}
                                </a>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-xs">
                            @php
                                $taskInfo = $taskTypes[$sub->task_type ?? 'comment'] ?? $taskTypes['comment'] ?? ['label' => 'Komentar', 'badge' => 'bg-emerald-100 text-emerald-800'];
                            @endphp
                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold {{ $taskInfo['badge'] ?? 'bg-emerald-100 text-emerald-800' }}">
                                {{ $taskInfo['label'] ?? 'Komentar' }}
                            </span>
                            <div class="mt-1">
                                @if($sub->comment_type === 'approved_live')
                                    <span class="text-[10px] text-emerald-700 font-medium">Live Langsung</span>
                                @else
                                    <span class="text-[10px] text-slate-500 font-medium">Moderasi</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap font-bold text-slate-900 text-xs">
                            Rp {{ number_format($sub->rate_amount, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-2">
                                <form method="POST" action="{{ route('admin.reviews.approve', $sub) }}">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition cursor-pointer">
                                        ✓ Setujui
                                    </button>
                                </form>
                                <button type="button" @click="rejectUrl = '{{ route('admin.reviews.reject', $sub) }}'; rejectModalOpen = true"
                                    class="px-3 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-bold text-xs transition cursor-pointer">
                                    ✕ Tolak
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-xs text-slate-400">
                            🎉 Hebat! Tidak ada antrean komentar yang menunggu verifikasi saat ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Reject Reason Modal -->
    <div x-show="rejectModalOpen" x-cloak class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">
        <div class="bg-white max-w-md w-full rounded-2xl p-6 shadow-xl border border-slate-200" @click.outside="rejectModalOpen = false">
            <h3 class="text-base font-bold text-slate-900 mb-2">Tolak Komentar & Bebaskan Slot Domain</h3>
            <p class="text-xs text-slate-500 mb-4">
                Sebutkan alasan penolakan agar worker mengetahui kesalahannya. Slot kuota domain akan otomatis dikembalikan (berkurang 1).
            </p>
            <form :action="rejectUrl" method="POST" class="space-y-4">
                @csrf
                <textarea name="rejection_reason" rows="3" required placeholder="Contoh: Link tidak ditemukan / komentar tidak relevan / spam terhapus"
                    class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:outline-none"></textarea>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="rejectModalOpen = false" class="px-4 py-2 rounded-xl text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-semibold bg-rose-600 hover:bg-rose-700 text-white">
                        Konfirmasi Tolak
                    </button>
                </div>
            </form>
        </div>
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
