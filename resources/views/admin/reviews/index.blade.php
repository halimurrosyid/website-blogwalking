@extends('layouts.app')

@section('title', 'Antrean Verifikasi Komentar')

@section('content')
<div class="space-y-6" x-data="{ 
    lightboxOpen: false, 
    lightboxImg: '', 
    rejectModalOpen: false, 
    rejectUrl: '',
    selectedIds: [],
    toggleAll(e) {
        if (e.target.checked) {
            this.selectedIds = Array.from(document.querySelectorAll('.submission-checkbox')).map(el => el.value);
        } else {
            this.selectedIds = [];
        }
    }
}">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Antrean Verifikasi Komentar</h1>
            <p class="text-sm text-slate-500 mt-1">Periksa bukti screenshot dan setujui atau tolak pengiriman worker.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.reviews.export-csv', request()->query()) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition border border-slate-200" title="Download data backlink untuk laporan klien">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                <span>Export CSV (Laporan Klien)</span>
            </a>
            
            <!-- Bulk Approve Form -->
            <form method="POST" action="{{ route('admin.reviews.bulk-approve') }}" x-show="selectedIds.length > 0" x-cloak>
                @csrf
                <template x-for="id in selectedIds" :key="id">
                    <input type="hidden" name="submission_ids[]" :value="id">
                </template>
                <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-sm transition flex items-center gap-2">
                    <span>✓ Setujui Sekaligus (<span x-text="selectedIds.length"></span>)</span>
                </button>
            </form>
        </div>
    </div>
            </button>
        </form>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <!-- Status Filter Tabs -->
        <div class="flex flex-wrap gap-1.5 text-xs font-semibold">
            <a href="{{ route('admin.reviews.index', array_merge(request()->query(), ['status' => 'pending'])) }}" 
                class="px-3 py-1.5 rounded-lg {{ $currentStatus === 'pending' ? 'bg-amber-600 text-white' : 'bg-amber-50 text-amber-800 hover:bg-amber-100' }}">
                Menunggu Review
            </a>
            <a href="{{ route('admin.reviews.index', array_merge(request()->query(), ['status' => 'approved'])) }}" 
                class="px-3 py-1.5 rounded-lg {{ $currentStatus === 'approved' ? 'bg-emerald-600 text-white' : 'bg-emerald-50 text-emerald-800 hover:bg-emerald-100' }}">
                Disetujui
            </a>
            <a href="{{ route('admin.reviews.index', array_merge(request()->query(), ['status' => 'rejected'])) }}" 
                class="px-3 py-1.5 rounded-lg {{ $currentStatus === 'rejected' ? 'bg-rose-600 text-white' : 'bg-rose-50 text-rose-800 hover:bg-rose-100' }}">
                Ditolak
            </a>
            <a href="{{ route('admin.reviews.index', array_merge(request()->query(), ['status' => 'all'])) }}" 
                class="px-3 py-1.5 rounded-lg {{ $currentStatus === 'all' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Semua
            </a>
        </div>

        <!-- Filter by Worker & Search -->
        <form method="GET" action="{{ route('admin.reviews.index') }}" class="flex flex-wrap items-center gap-2">
            <input type="hidden" name="status" value="{{ $currentStatus }}">

            <select name="task_type" onchange="this.form.submit()" class="px-3 py-1.5 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none bg-white">
                <option value="">-- Semua Kategori Misi --</option>
                @foreach($taskTypes as $k => $t)
                    <option value="{{ $k }}" {{ ($currentTaskType ?? '') === $k ? 'selected' : '' }}>{{ $t['label'] }}</option>
                @endforeach
            </select>
            
            <select name="worker_id" onchange="this.form.submit()" class="px-3 py-1.5 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none bg-white">
                <option value="">-- Semua Blogwalker --</option>
                @foreach($workers as $w)
                    <option value="{{ $w->id }}" {{ $currentWorkerId == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                @endforeach
            </select>

            <input type="text" name="search" value="{{ $search }}" placeholder="Cari domain, URL, keyword..."
                class="px-3 py-1.5 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none w-48">
            <button type="submit" class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs">
                Cari
            </button>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-[11px] uppercase font-bold tracking-wider text-slate-500 border-b border-slate-100">
                    <tr>
                        <th class="px-4 py-3.5 w-10 text-center">
                            <input type="checkbox" @change="toggleAll($event)" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                        </th>
                        <th class="px-4 py-3.5">Screenshot</th>
                        <th class="px-6 py-3.5">Worker & Misi</th>
                        <th class="px-6 py-3.5">Detail Tugas & Bukti</th>
                        <th class="px-6 py-3.5">Tanggal</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($submissions as $sub)
                    @php
                        $subTask = $taskTypes[$sub->task_type ?? 'comment'] ?? $taskTypes['comment'];
                    @endphp
                    <tr class="hover:bg-slate-50/60 transition">
                        <td class="px-4 py-4 text-center">
                            <input type="checkbox" value="{{ $sub->id }}" x-model="selectedIds" class="submission-checkbox rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                        </td>
                        <td class="px-4 py-4">
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
                            <div class="flex items-center gap-1.5 mt-1">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $subTask['badge'] }}">
                                    {{ $subTask['label'] }}
                                </span>
                            </div>
                            <span class="text-xs font-bold text-emerald-700 mt-1 block">Rp {{ number_format($sub->rate_amount, 0, ',', '.') }}</span>
                        </td>
                        <td class="px-6 py-4 max-w-sm">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-slate-900 text-xs">{{ $sub->domain->root_domain }}</span>
                                @if($sub->platform)
                                    <span class="text-[10px] bg-blue-100 text-blue-800 px-1.5 py-0.5 rounded font-semibold">{{ $sub->platform }}</span>
                                @endif
                                @if($sub->domain_rating)
                                    <span class="text-[10px] bg-amber-100 text-amber-800 px-1.5 py-0.5 rounded font-semibold">DR {{ $sub->domain_rating }}</span>
                                @endif
                            </div>
                            <a href="{{ $sub->target_url }}" target="_blank" class="text-xs text-slate-500 hover:text-emerald-600 hover:underline truncate block font-mono mt-0.5" title="{{ $sub->target_url }}">
                                Target: {{ $sub->target_url }}
                            </a>
                            @if($sub->published_url)
                                <div class="mt-1 text-xs">
                                    <a href="{{ $sub->published_url }}" target="_blank" class="text-emerald-700 font-bold hover:underline flex items-center gap-1 truncate" title="{{ $sub->published_url }}">
                                        <span>🔗 Hasil Post: {{ $sub->published_url }}</span>
                                    </a>
                                </div>
                            @endif
                            @if($sub->client_url || $sub->keyword)
                                <div class="mt-1 flex flex-wrap items-center gap-1.5 text-[11px] text-slate-500">
                                    @if($sub->keyword)
                                        <span class="bg-slate-100 px-1.5 py-0.5 rounded text-slate-700 font-medium">Anchor: {{ $sub->keyword }}</span>
                                    @endif
                                    @if($sub->client_url)
                                        <a href="{{ $sub->client_url }}" target="_blank" class="text-blue-600 hover:underline truncate max-w-[150px]">Klien: {{ $sub->client_url }}</a>
                                    @endif
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-xs text-slate-500 whitespace-nowrap">
                            {{ $sub->created_at->format('d M Y, H:i') }}
                        </td>
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
                        <td class="px-6 py-4 text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-1.5">
                                @if($sub->review_status !== 'approved')
                                <form method="POST" action="{{ route('admin.reviews.approve', $sub) }}">
                                    @csrf
                                    <button type="submit" class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition cursor-pointer">
                                        ✓ Setujui
                                    </button>
                                </form>
                                @endif

                                @if($sub->review_status !== 'rejected')
                                <button type="button" @click="rejectUrl = '{{ route('admin.reviews.reject', $sub) }}'; rejectModalOpen = true"
                                    class="px-2.5 py-1 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-bold text-xs transition cursor-pointer">
                                    ✕ Tolak
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-xs text-slate-400">
                            Tidak ada pengiriman komentar yang cocok dengan filter ini.
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

    <!-- Reject Reason Modal -->
    <div x-show="rejectModalOpen" x-cloak class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">
        <div class="bg-white max-w-md w-full rounded-2xl p-6 shadow-xl border border-slate-200" @click.outside="rejectModalOpen = false">
            <h3 class="text-base font-bold text-slate-900 mb-2">Tolak Komentar & Bebaskan Slot Domain</h3>
            <p class="text-xs text-slate-500 mb-4">
                Sebutkan alasan penolakan. Slot kuota domain akan otomatis dikembalikan sehingga bisa dikomentari kembali.
            </p>
            <form :action="rejectUrl" method="POST" class="space-y-4">
                @csrf
                <textarea name="rejection_reason" rows="3" required placeholder="Contoh: Komentar terhapus / Link nofollow tidak sesuai / Komentar spam"
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
