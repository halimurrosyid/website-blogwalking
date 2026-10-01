@extends('layouts.app')

@section('title', 'Persetujuan Ganti Rekening & Profil')

@section('content')
<div class="space-y-6" x-data="{
    rejectModalOpen: false,
    rejectActionUrl: '',
    rejectWorkerName: '',
    lightboxOpen: false,
    lightboxImage: '',

    openReject(url, name) {
        this.rejectActionUrl = url;
        this.rejectWorkerName = name;
        this.rejectModalOpen = true;
    },
    openLightbox(img) {
        this.lightboxImage = img;
        this.lightboxOpen = true;
    }
}">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Persetujuan Ganti Rekening & Profil</h1>
            <p class="text-sm text-slate-500 mt-0.5">Verifikasi dan setujui permohonan perubahan rekening bank dan nama worker demi keamanan payroll.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.workers.index') }}" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 transition shadow-2xs">
                &larr; Daftar Blogwalker
            </a>
            <a href="{{ route('admin.payouts.index') }}" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-emerald-50 border border-emerald-200 text-emerald-800 hover:bg-emerald-100 transition shadow-2xs">
                Rekap Payroll
            </a>
        </div>
    </div>

    <!-- Filter Tabs & Stats -->
    <div class="flex flex-wrap items-center gap-2 border-b border-slate-200 pb-3">
        <a href="{{ route('admin.profile-requests.index', ['status' => 'pending']) }}"
           class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition {{ $status === 'pending' ? 'bg-amber-500 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
            <span>Menunggu Review</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] {{ $status === 'pending' ? 'bg-amber-600 text-white' : 'bg-amber-100 text-amber-800 font-bold' }}">
                {{ $stats['pending'] }}
            </span>
        </a>

        <a href="{{ route('admin.profile-requests.index', ['status' => 'approved']) }}"
           class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition {{ $status === 'approved' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
            <span>Disetujui</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] {{ $status === 'approved' ? 'bg-emerald-700 text-white' : 'bg-slate-200 text-slate-700' }}">
                {{ $stats['approved'] }}
            </span>
        </a>

        <a href="{{ route('admin.profile-requests.index', ['status' => 'rejected']) }}"
           class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition {{ $status === 'rejected' ? 'bg-rose-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
            <span>Ditolak</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] {{ $status === 'rejected' ? 'bg-rose-700 text-white' : 'bg-slate-200 text-slate-700' }}">
                {{ $stats['rejected'] }}
            </span>
        </a>

        <a href="{{ route('admin.profile-requests.index', ['status' => 'all']) }}"
           class="px-3.5 py-2 rounded-xl text-xs font-semibold transition {{ $status === 'all' ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
            Semua Riwayat
        </a>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-xs font-bold uppercase tracking-wider text-slate-600">
                    <tr>
                        <th class="px-5 py-3.5">Blogwalker</th>
                        <th class="px-5 py-3.5">Rekening Lama (Aktif)</th>
                        <th class="px-5 py-3.5">Rekening Baru Diajukan</th>
                        <th class="px-5 py-3.5">Bukti / Dokumen</th>
                        <th class="px-5 py-3.5">Status & Waktu</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($requests as $req)
                        <tr class="hover:bg-slate-50/70 transition">
                            <!-- Worker Info -->
                            <td class="px-5 py-4">
                                <div class="font-bold text-slate-900 text-sm">{{ $req->user->name }}</div>
                                <div class="text-slate-500 font-mono text-[11px]">{{ '@' . $req->user->username }} &bull; {{ $req->user->email }}</div>
                                <div class="text-emerald-700 font-semibold text-[11px] mt-0.5">WA: {{ $req->new_phone ?? $req->user->phone }}</div>
                                @if($req->reason)
                                    <div class="mt-1 text-[11px] text-slate-600 bg-slate-100 p-1.5 rounded-lg border border-slate-200">
                                        <span class="font-semibold text-slate-700">Alasan:</span> {{ $req->reason }}
                                    </div>
                                @endif
                            </td>

                            <!-- Old Bank Details -->
                            <td class="px-5 py-4">
                                <div class="font-semibold text-slate-800">{{ \App\Services\BankListService::getLabel($req->old_bank_name) }}</div>
                                <div class="font-mono text-slate-600 font-bold mt-0.5">{{ $req->old_bank_account_number ?? '-' }}</div>
                                <div class="text-[11px] text-slate-500">a.n. {{ $req->old_bank_account_name ?? '-' }}</div>
                            </td>

                            <!-- New Bank Details -->
                            <td class="px-5 py-4 bg-emerald-50/30">
                                <div class="font-bold text-emerald-900">{{ \App\Services\BankListService::getLabel($req->new_bank_name) }}</div>
                                <div class="font-mono text-emerald-800 font-bold text-xs mt-0.5">{{ $req->new_bank_account_number }}</div>
                                <div class="text-[11px] text-emerald-700 font-semibold">a.n. {{ $req->new_bank_account_name }}</div>
                                @if($req->old_name !== $req->new_name && $req->new_name)
                                    <div class="text-[10px] text-blue-700 font-bold mt-1 bg-blue-100 px-1.5 py-0.5 rounded border border-blue-200 inline-block">
                                        Ganti Nama: {{ $req->new_name }}
                                    </div>
                                @endif
                            </td>

                            <!-- Evidence / Document -->
                            <td class="px-5 py-4">
                                @if($req->bank_book_path)
                                    <button type="button" @click="openLightbox('{{ asset('storage/' . $req->bank_book_path) }}')"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-[11px] border border-slate-200 transition cursor-pointer">
                                        <span>👁️ Lihat Bukti</span>
                                    </button>
                                @else
                                    <span class="text-slate-400 italic text-[11px]">Tidak dilampirkan</span>
                                @endif
                            </td>

                            <!-- Status & Timing -->
                            <td class="px-5 py-4 whitespace-nowrap">
                                @if($req->isPending())
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                        Menunggu Review
                                    </span>
                                @elseif($req->isApproved())
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                        ✓ Disetujui
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800 border border-rose-300">
                                        ✕ Ditolak
                                    </span>
                                @endif
                                <div class="text-[10px] text-slate-400 mt-1">
                                    {{ $req->created_at->diffForHumans() }}
                                </div>
                                @if($req->reviewed_at)
                                    <div class="text-[10px] text-slate-500">
                                        Diproses: {{ $req->reviewed_at->translatedFormat('d M Y') }}
                                    </div>
                                @endif
                            </td>

                            <!-- Action Buttons -->
                            <td class="px-5 py-4 text-right whitespace-nowrap">
                                @if($req->isPending())
                                    <div class="flex items-center justify-end gap-1.5">
                                        <!-- Approve Button Form -->
                                        <form action="{{ route('admin.profile-requests.approve', $req) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin MENYETUJUI permohonan ganti rekening ini? Data rekening aktif worker akan langsung diperbarui.');">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 rounded-lg font-bold text-xs bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs transition cursor-pointer">
                                                ✓ Setujui
                                            </button>
                                        </form>

                                        <!-- Reject Trigger Modal Button -->
                                        <button type="button" @click="openReject('{{ route('admin.profile-requests.reject', $req) }}', '{{ addslashes($req->user->name) }}')"
                                            class="px-3 py-1.5 rounded-lg font-bold text-xs bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-300 transition cursor-pointer">
                                            ✕ Tolak
                                        </button>
                                    </div>
                                @else
                                    <span class="text-[11px] text-slate-400 italic">Selesai diproses</span>
                                    @if($req->admin_notes)
                                        <div class="text-[10px] text-slate-500 mt-0.5 max-w-xs truncate" title="{{ $req->admin_notes }}">
                                            Catatan: {{ $req->admin_notes }}
                                        </div>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                <svg class="w-12 h-12 mx-auto text-slate-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <p class="text-sm font-semibold text-slate-600">Tidak ada permohonan ganti rekening</p>
                                <p class="text-xs text-slate-400 mt-0.5">Semua permohonan yang masuk telah selesai ditindaklanjuti.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($requests->hasPages())
            <div class="px-5 py-4 border-t border-slate-200">
                {{ $requests->links() }}
            </div>
        @endif
    </div>

    <!-- Modal: Tolak Permohonan Rekening -->
    <div x-show="rejectModalOpen" x-cloak class="fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4 backdrop-blur-2xs" @click="rejectModalOpen = false">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-4" @click.stop>
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="font-bold text-slate-900 text-base">Tolak Permohonan Ganti Rekening</h3>
                <button type="button" @click="rejectModalOpen = false" class="text-slate-400 hover:text-slate-600 font-bold text-lg cursor-pointer">&times;</button>
            </div>

            <p class="text-xs text-slate-600">
                Anda akan menolak permohonan perubahan rekening untuk worker <strong x-text="rejectWorkerName"></strong>. Data rekening lama mereka akan tetap digunakan.
            </p>

            <form :action="rejectActionUrl" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="admin_notes" class="block text-xs font-semibold text-slate-700 mb-1">
                        Alasan Penolakan <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="admin_notes" id="admin_notes" rows="3" required placeholder="Contoh: Nama pemilik rekening tidak cocok dengan nama pada identitas KTP yang terverifikasi..."
                        class="w-full text-xs border-slate-300 rounded-xl px-3 py-2 focus:ring-rose-500 focus:border-rose-500"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="rejectModalOpen = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-rose-600 hover:bg-rose-700 text-white shadow-xs cursor-pointer">
                        Tolak Permohonan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Lightbox Modal Bukti Gambar -->
    <div x-show="lightboxOpen" x-cloak class="fixed inset-0 z-50 bg-black/80 flex items-center justify-center p-4 backdrop-blur-sm" @click="lightboxOpen = false">
        <div class="relative max-w-3xl max-h-[90vh] bg-slate-900 rounded-2xl overflow-hidden p-2" @click.stop>
            <button type="button" @click="lightboxOpen = false" class="absolute top-4 right-4 text-white bg-black/60 rounded-full w-8 h-8 flex items-center justify-center font-bold text-lg hover:bg-black cursor-pointer z-10">&times;</button>
            <img :src="lightboxImage" alt="Bukti Rekening" class="max-h-[85vh] w-auto mx-auto rounded-xl object-contain">
        </div>
    </div>

</div>
@endsection
