@extends('layouts.app')

@section('title', 'Verifikasi Pendaftaran Blogwalker')

@section('content')
<div class="space-y-6" x-data="{
    lightboxOpen: false,
    lightboxImg: '',
    lightboxTitle: '',
    openLightbox(img, title) {
        this.lightboxImg = img;
        this.lightboxTitle = title;
        this.lightboxOpen = true;
    },
    approveModalOpen: false,
    approveUrl: '',
    approveName: '',
    defaultRate: 700,
    allowedTlds: '',
    targetKeywords: '',
    targetBacklinkUrl: '',
    openApproveModal(url, name, rate) {
        this.approveUrl = url;
        this.approveName = name;
        this.defaultRate = rate;
        this.allowedTlds = '';
        this.targetKeywords = '';
        this.targetBacklinkUrl = '';
        this.approveModalOpen = true;
    },
    rejectModalOpen: false,
    rejectUrl: '',
    rejectName: '',
    openRejectModal(url, name) {
        this.rejectUrl = url;
        this.rejectName = name;
        this.rejectModalOpen = true;
    }
}">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Verifikasi Pendaftaran Blogwalker</h1>
            <p class="text-sm text-slate-500 mt-1">
                Tinjau foto KTP, data rekening bank konvensional, dan setujui atau tolak pendaftaran calon blogwalker.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.workers.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
                + Tambah Manual Admin
            </a>
        </div>
    </div>

    <!-- Status Tabs -->
    <div class="bg-white p-3 rounded-2xl border border-slate-200 shadow-xs flex flex-wrap gap-2 text-xs font-semibold">
        <a href="{{ route('admin.registrations.index', ['status' => 'pending']) }}" 
            class="px-4 py-2 rounded-xl flex items-center gap-2 transition {{ $currentStatus === 'pending' ? 'bg-amber-600 text-white shadow-xs' : 'bg-slate-50 text-slate-600 hover:bg-slate-100' }}">
            <span>⏳ Menunggu Approval</span>
            @if($pendingCount > 0)
                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold {{ $currentStatus === 'pending' ? 'bg-white text-amber-800' : 'bg-amber-500 text-white' }}">{{ $pendingCount }}</span>
            @endif
        </a>
        <a href="{{ route('admin.registrations.index', ['status' => 'approved']) }}" 
            class="px-4 py-2 rounded-xl flex items-center gap-2 transition {{ $currentStatus === 'approved' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-50 text-slate-600 hover:bg-slate-100' }}">
            <span>✓ Telah Disetujui</span>
            <span class="px-2 py-0.5 rounded-full text-[11px] font-bold {{ $currentStatus === 'approved' ? 'bg-white text-emerald-800' : 'bg-emerald-100 text-emerald-800' }}">{{ $approvedCount }}</span>
        </a>
        <a href="{{ route('admin.registrations.index', ['status' => 'rejected']) }}" 
            class="px-4 py-2 rounded-xl flex items-center gap-2 transition {{ $currentStatus === 'rejected' ? 'bg-rose-600 text-white shadow-xs' : 'bg-slate-50 text-slate-600 hover:bg-slate-100' }}">
            <span>✗ Ditolak</span>
            <span class="px-2 py-0.5 rounded-full text-[11px] font-bold {{ $currentStatus === 'rejected' ? 'bg-white text-rose-800' : 'bg-rose-100 text-rose-800' }}">{{ $rejectedCount }}</span>
        </a>
        <a href="{{ route('admin.registrations.index', ['status' => 'all']) }}" 
            class="px-4 py-2 rounded-xl transition {{ $currentStatus === 'all' ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-50 text-slate-600 hover:bg-slate-100' }}">
            Semua Pendaftar
        </a>
    </div>

    <!-- Applicants Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-[11px] uppercase font-bold tracking-wider text-slate-500 border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-3.5">Calon Blogwalker</th>
                        <th class="px-6 py-3.5">Rekening Bank Konvensional</th>
                        <th class="px-6 py-3.5 text-center">Foto KTP</th>
                        <th class="px-6 py-3.5 text-center">Foto Buku Rekening</th>
                        <th class="px-6 py-3.5">Waktu Pendaftaran</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5 text-center">Aksi / Verifikasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($applicants as $app)
                    <tr class="hover:bg-slate-50/60 transition">
                        <!-- Profile -->
                        <td class="px-6 py-4">
                            <div class="font-bold text-slate-900 text-sm">{{ $app->name }}</div>
                            <div class="font-mono text-xs text-emerald-600 font-semibold">{{ '@' . ($app->username ?? '-') }}</div>
                            <div class="text-xs text-slate-500 mt-1 flex flex-col gap-0.5">
                                <span>📧 {{ $app->email }}</span>
                                <span>📱 {{ $app->phone ?? '-' }}</span>
                            </div>
                        </td>

                        <!-- Bank Details -->
                        <td class="px-6 py-4">
                            <div class="font-bold text-slate-900 text-xs flex items-center gap-1.5">
                                <span class="px-2 py-0.5 rounded bg-blue-50 text-blue-700 font-bold border border-blue-200 text-[11px]">
                                    {{ $app->bank_name ?? '-' }}
                                </span>
                            </div>
                            <div class="font-mono text-sm text-slate-900 font-bold mt-1 tracking-wider">
                                {{ $app->bank_account_number ?? '-' }}
                            </div>
                            <div class="text-xs text-slate-500 mt-0.5">
                                a.n. <strong class="text-slate-700">{{ $app->bank_account_name ?? $app->name }}</strong>
                            </div>
                        </td>

                        <!-- KTP Photo -->
                        <td class="px-6 py-4 text-center">
                            @if($app->id_card_url)
                                <div class="w-16 h-12 rounded-lg overflow-hidden bg-slate-100 border border-slate-200 mx-auto cursor-pointer hover:opacity-80 transition relative group shadow-2xs"
                                    @click="openLightbox('{{ $app->id_card_url }}', 'Foto KTP - {{ $app->name }}')">
                                    <img src="{{ $app->id_card_url }}" alt="KTP" class="w-full h-full object-cover"
                                        onerror="this.onerror=null; this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'64\' height=\'48\' fill=\'%2394a3b8\' viewBox=\'0 0 24 24\'><rect width=\'24\' height=\'24\' fill=\'%23f1f5f9\'/><path d=\'M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z\'/></svg>'">
                                    <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition text-white text-[10px] font-bold">
                                        🔍 Zoom
                                    </div>
                                </div>
                                <a href="{{ $app->id_card_url }}" target="_blank" rel="noopener noreferrer" class="text-[10px] text-emerald-600 hover:text-emerald-800 hover:underline block text-center mt-1 font-semibold">
                                    Buka Foto ↗
                                </a>
                            @else
                                <span class="text-xs text-slate-400 italic">-</span>
                            @endif
                        </td>

                        <!-- Bank Book Photo -->
                        <td class="px-6 py-4 text-center">
                            @if($app->bank_book_url)
                                <div class="w-16 h-12 rounded-lg overflow-hidden bg-slate-100 border border-slate-200 mx-auto cursor-pointer hover:opacity-80 transition relative group shadow-2xs"
                                    @click="openLightbox('{{ $app->bank_book_url }}', 'Foto Buku Rekening - {{ $app->name }}')">
                                    <img src="{{ $app->bank_book_url }}" alt="Buku Rekening" class="w-full h-full object-cover"
                                        onerror="this.onerror=null; this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'64\' height=\'48\' fill=\'%2394a3b8\' viewBox=\'0 0 24 24\'><rect width=\'24\' height=\'24\' fill=\'%23f1f5f9\'/><path d=\'M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z\'/></svg>'">
                                    <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition text-white text-[10px] font-bold">
                                        🔍 Zoom
                                    </div>
                                </div>
                                <a href="{{ $app->bank_book_url }}" target="_blank" rel="noopener noreferrer" class="text-[10px] text-emerald-600 hover:text-emerald-800 hover:underline block text-center mt-1 font-semibold">
                                    Buka Foto ↗
                                </a>
                            @else
                                <span class="text-xs text-slate-400 italic">-</span>
                            @endif
                        </td>

                        <!-- Registered At -->
                        <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-500">
                            {{ $app->created_at->format('d M Y, H:i') }}
                        </td>

                        <!-- Status -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($app->approval_status === 'pending')
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                                    Menunggu Review
                                </span>
                            @elseif($app->approval_status === 'approved')
                                <div>
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                                        Disetujui
                                    </span>
                                    @if($app->approved_at)
                                        <div class="text-[10px] text-slate-400 mt-1">
                                            {{ $app->approved_at->format('d M Y') }}
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div>
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800">
                                        Ditolak
                                    </span>
                                    @if($app->rejection_reason)
                                        <div class="text-[11px] text-rose-600 mt-1 italic max-w-xs truncate" title="{{ $app->rejection_reason }}">
                                            "{{ $app->rejection_reason }}"
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </td>

                        <!-- Action Buttons -->
                        <td class="px-6 py-4 text-center whitespace-nowrap">
                            @if($app->approval_status === 'pending')
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button" 
                                        @click="openApproveModal('{{ route('admin.registrations.approve', $app) }}', '{{ $app->name }}', {{ $app->default_rate ?? 700 }})"
                                        class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition shadow-xs flex items-center gap-1 cursor-pointer">
                                        <span>✓ Approve & Plot</span>
                                    </button>
                                    <button type="button" 
                                        @click="openRejectModal('{{ route('admin.registrations.reject', $app) }}', '{{ $app->name }}')"
                                        class="px-3 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 font-semibold text-xs transition border border-rose-200 cursor-pointer">
                                        Tolak
                                    </button>
                                </div>
                            @elseif($app->approval_status === 'approved')
                                <a href="{{ route('admin.workers.edit', $app) }}" class="text-xs text-emerald-600 font-semibold hover:underline">
                                    Kelola Akun & Plotting &rarr;
                                </a>
                            @else
                                <button type="button" 
                                    @click="openApproveModal('{{ route('admin.registrations.approve', $app) }}', '{{ $app->name }}', {{ $app->default_rate ?? 700 }})"
                                    class="text-xs text-slate-500 font-semibold hover:text-emerald-600 underline cursor-pointer">
                                    Tinjau Ulang & Approve
                                </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-xs text-slate-400">
                            Tidak ada data pendaftaran calon blogwalker pada filter ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($applicants->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $applicants->links() }}
            </div>
        @endif
    </div>

    <!-- Approve Modal with Plotting Setup -->
    <div x-show="approveModalOpen" x-cloak class="fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl border border-slate-200 space-y-4" @click.outside="approveModalOpen = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h3 class="font-bold text-slate-900 text-base">Approve & Tentukan Plotting Tugas</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Calon: <strong class="text-slate-900" x-text="approveName"></strong></p>
                </div>
                <button type="button" @click="approveModalOpen = false" class="text-slate-400 hover:text-slate-600 font-bold text-lg">&times;</button>
            </div>

            <form :action="approveUrl" method="POST" class="space-y-4">
                @csrf

                <!-- Tarif Komentar -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Tarif Komentar Disetujui (Rp) *</label>
                    <input type="number" name="default_rate" x-model="defaultRate" required min="0" step="50"
                        class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>

                <!-- Plotting Ekstensi Domain (TLDs) -->
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Plotting Ekstensi Domain (TLDs)</label>
                    <div class="flex flex-wrap gap-1 mb-1">
                        <button type="button" @click="allowedTlds = ''" class="px-2 py-0.5 rounded text-[11px] font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300 transition">✨ Bebas Semua</button>
                        <button type="button" @click="allowedTlds = '.id, .co.id, .web.id, .my.id, .biz.id'" class="px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-300 transition">🇮🇩 Semua .ID</button>
                        <button type="button" @click="allowedTlds = '.co.id'" class="px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-300 transition">🏢 .co.id</button>
                        <button type="button" @click="allowedTlds = '.com, .net, .org, .info'" class="px-2 py-0.5 rounded text-[11px] font-semibold bg-blue-50 hover:bg-blue-100 text-blue-800 border border-blue-300 transition">🌐 Global (.com dll)</button>
                        <button type="button" @click="allowedTlds = '.ac.id, .sch.id, .edu'" class="px-2 py-0.5 rounded text-[11px] font-semibold bg-purple-50 hover:bg-purple-100 text-purple-800 border border-purple-300 transition">🎓 Edu (.ac.id)</button>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <select @change="if ($event.target.value) { allowedTlds = allowedTlds ? allowedTlds + ', ' + $event.target.value : $event.target.value; $event.target.value = ''; }"
                            class="w-full px-2.5 py-2 text-xs rounded-xl border border-slate-300 bg-white">
                            <option value="">➕ Tambah dari Daftar (230+ TLD Seluruh Dunia)...</option>
                            @foreach (\App\Services\TldCatalogService::groupedCatalog() as $groupName => $options)
                                <optgroup label="{{ $groupName }}">
                                    @foreach ($options as $ext => $label)
                                        <option value="{{ $ext }}">{{ $label }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                        <input type="text" name="allowed_tlds" x-model="allowedTlds" placeholder="Kosongkan jika bebas semua domain"
                            class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 font-mono">
                    </div>
                    <p class="text-[11px] text-slate-400">Pilih dari dropdown atau klik tombol preset di atas.</p>
                </div>

                <!-- Target Keywords -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Target Kata Kunci / Anchor</label>
                    <input type="text" name="target_keywords" x-model="targetKeywords" placeholder="jasa seo, backlink murah, konveksi seragam"
                        class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>

                <!-- Target Backlink URL -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Target URL Backlink Klien</label>
                    <input type="url" name="target_backlink_url" x-model="targetBacklinkUrl" placeholder="https://website-klien.com/promo"
                        class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="approveModalOpen = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs">
                        ✓ Setujui & Aktifkan Blogwalker
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Reject Modal -->
    <div x-show="rejectModalOpen" x-cloak class="fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-200 space-y-4" @click.outside="rejectModalOpen = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h3 class="font-bold text-slate-900 text-base">Tolak Pendaftaran</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Calon: <strong class="text-slate-900" x-text="rejectName"></strong></p>
                </div>
                <button type="button" @click="rejectModalOpen = false" class="text-slate-400 hover:text-slate-600 font-bold text-lg">&times;</button>
            </div>

            <form :action="rejectUrl" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Alasan Penolakan *</label>
                    <textarea name="rejection_reason" required rows="3"
                        class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 focus:ring-2 focus:ring-rose-500 focus:outline-none"
                        placeholder="Contoh: Foto KTP buram dan nomor rekening tidak terbaca."></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="rejectModalOpen = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-rose-600 hover:bg-rose-700 text-white shadow-xs">
                        Tolak Pendaftaran
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Zoom Lightbox Modal -->
    <div x-show="lightboxOpen" x-cloak class="fixed inset-0 z-50 bg-black/85 flex items-center justify-center p-4 backdrop-blur-2xs" @click="lightboxOpen = false">
        <div class="relative max-w-4xl max-h-[90vh] bg-slate-900 rounded-2xl overflow-hidden p-3 shadow-2xl border border-slate-700" @click.stop>
            <div class="flex items-center justify-between text-white pb-2 px-1">
                <span class="text-xs font-semibold" x-text="lightboxTitle"></span>
                <div class="flex items-center gap-2">
                    <a :href="lightboxImg" target="_blank" rel="noopener noreferrer" class="px-3 py-1 rounded-full bg-black/60 hover:bg-black/90 text-white text-xs font-semibold transition flex items-center gap-1">
                        Buka Ukuran Asli ↗
                    </a>
                    <button @click="lightboxOpen = false" class="text-white hover:text-rose-400 font-bold text-lg cursor-pointer">&times;</button>
                </div>
            </div>
            <img :src="lightboxImg" alt="Preview Dokumen" class="max-h-[80vh] w-auto mx-auto object-contain rounded-xl">
        </div>
    </div>

</div>
@endsection
