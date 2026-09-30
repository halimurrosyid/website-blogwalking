@extends('layouts.app')

@section('title', 'Target Bulanan, Periode & Evaluasi Tim')

@section('content')
<div class="space-y-6" x-data="{
    showConfigModal: false,
    showRolloverModal: false,
    editAssignmentData: null,
    dispenseAssignmentData: null,

    openEditAssignment(p) {
        this.editAssignmentData = {
            id: p.assignment.id,
            name: p.user.name,
            allowed_tlds: (p.assignment.allowed_tlds || []).join(', '),
            target_keywords: p.assignment.target_keywords || '',
            target_backlink_url: p.assignment.target_backlink_url || '',
            min_target: p.assignment.min_target || '',
            max_target: p.assignment.max_target || '',
            custom_instructions: p.assignment.custom_instructions || '',
            actionUrl: '{{ url('admin/periods/assignment') }}/' + p.assignment.id
        };
    },

    openDispense(p) {
        this.dispenseAssignmentData = {
            id: p.assignment.id,
            name: p.user.name,
            actionUrl: '{{ url('admin/periods/assignment') }}/' + p.assignment.id + '/dispense'
        };
    }
}">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Target Bulanan & Evaluasi Blogwalker</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola target minimal pengerjaan bulanan, batasan URL per domain, dan evaluasi kelolosan tim.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <button type="button" @click="showConfigModal = true" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-lg text-sm font-semibold shadow-xs transition flex items-center gap-2 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                Ubah Target & Aturan
            </button>
            <button type="button" @click="showRolloverModal = true" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-semibold shadow-xs transition flex items-center gap-2 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                Tutup Periode & Evaluasi
            </button>
        </div>
    </div>

    <!-- Active Period Summary Card -->
    <div class="bg-gradient-to-r from-slate-900 to-slate-800 text-white rounded-2xl p-6 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center justify-between pb-4 border-b border-slate-700/80 gap-3">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-xl border border-emerald-500/30">
                    📅
                </div>
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-emerald-400">Periode Aktif Berjalan</span>
                    <h2 class="text-2xl font-bold text-white">{{ $activePeriod?->name ?? 'Belum Ada Periode Aktif' }}</h2>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/40">
                    Sisa {{ $activePeriod ? $activePeriod->remainingDays() : 0 }} Hari Lagi
                </span>
                <span class="text-xs text-slate-400">
                    @if($activePeriod && $activePeriod->starts_at && $activePeriod->ends_at)
                        {{ $activePeriod->starts_at->format('d M') }} &ndash; {{ $activePeriod->ends_at->format('d M Y') }}
                    @endif
                </span>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-5">
            <div>
                <span class="text-xs font-medium text-slate-400 block uppercase">Target Minimal Lolos</span>
                <span class="text-2xl font-bold text-white mt-0.5 block">{{ $activePeriod?->min_target ?? 100 }} <span class="text-xs font-normal text-slate-400">Approved URL</span></span>
            </div>
            <div>
                <span class="text-xs font-medium text-slate-400 block uppercase">Batas URL per Domain</span>
                <span class="text-2xl font-bold text-emerald-400 mt-0.5 block">{{ $activePeriod?->max_urls_per_domain ?? 5 }} <span class="text-xs font-normal text-slate-400">URL / Domain</span></span>
            </div>
            <div>
                <span class="text-xs font-medium text-slate-400 block uppercase">Batas Maksimal Kuota</span>
                <span class="text-2xl font-bold text-white mt-0.5 block">{{ $activePeriod?->max_target ?: 'Tanpa Batas' }}</span>
            </div>
            <div>
                <span class="text-xs font-medium text-slate-400 block uppercase">Partisipan Blogwalker</span>
                <span class="text-2xl font-bold text-white mt-0.5 block">{{ count($participants) }} <span class="text-xs font-normal text-slate-400">Orang</span></span>
            </div>
        </div>
    </div>

    <!-- Participants & Plotting Progress Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-200 flex justify-between items-center">
            <div>
                <h3 class="font-bold text-slate-800 text-base">Capaian & Plotting Tugas Blogwalker</h3>
                <p class="text-xs text-slate-500">Blogwalker yang tidak mencapai target minimal di akhir bulan tidak akan diikutkan pada periode berikutnya.</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-600 uppercase tracking-wider">
                        <th class="py-3 px-4">Blogwalker</th>
                        <th class="py-3 px-4">Plotting Ekstensi Domain</th>
                        <th class="py-3 px-4">Keyword & Link Target</th>
                        <th class="py-3 px-4">Target & Progres</th>
                        <th class="py-3 px-4">Status Kelolosan</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($participants as $p)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-900">{{ $p['user']->name }}</div>
                                <div class="text-xs text-slate-500">{{ $p['user']->email }}</div>
                                <div class="text-[11px] text-emerald-600 font-medium mt-0.5">Rp {{ number_format($p['user']->default_rate, 0, ',', '.') }} / approved</div>
                            </td>

                            <td class="py-3 px-4">
                                @if(!empty($p['assignment']->allowed_tlds))
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($p['assignment']->allowed_tlds as $tld)
                                            <span class="px-2 py-0.5 rounded text-xs font-mono font-bold bg-slate-100 text-slate-800 border border-slate-200">{{ $tld }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="px-2 py-0.5 rounded text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Bebas Semua Domain
                                    </span>
                                @endif
                            </td>

                            <td class="py-3 px-4 max-w-xs">
                                <div class="text-xs font-medium text-slate-800 truncate" title="{{ $p['assignment']->target_keywords }}">
                                    {{ $p['assignment']->target_keywords ?: '-' }}
                                </div>
                                @if($p['assignment']->target_backlink_url)
                                    <div class="text-[11px] text-emerald-600 truncate mt-0.5">
                                        <a href="{{ $p['assignment']->target_backlink_url }}" target="_blank" class="hover:underline">{{ $p['assignment']->target_backlink_url }}</a>
                                    </div>
                                @endif
                            </td>

                            <td class="py-3 px-4 min-w-[180px]">
                                <div class="flex justify-between items-center text-xs mb-1">
                                    <span class="font-bold text-slate-800">{{ $p['progress']['approved'] }} / {{ $p['progress']['target'] }}</span>
                                    <span class="font-bold {{ $p['progress']['is_qualified'] ? 'text-emerald-600' : 'text-slate-600' }}">{{ $p['progress']['percentage'] }}%</span>
                                </div>
                                <div class="w-full h-2 rounded-full bg-slate-100 overflow-hidden">
                                    <div class="h-full rounded-full {{ $p['progress']['is_qualified'] ? 'bg-emerald-500' : ($p['progress']['percentage'] >= 50 ? 'bg-amber-500' : 'bg-rose-500') }}" style="width: {{ min(100, $p['progress']['percentage']) }}%"></div>
                                </div>
                                <div class="text-[11px] text-slate-400 mt-1 flex justify-between">
                                    <span>Pending: {{ $p['progress']['pending'] }}</span>
                                    @if(!$p['progress']['is_qualified'])
                                        <span class="text-rose-600 font-medium">Kurang {{ $p['progress']['remaining_needed'] }}</span>
                                    @endif
                                </div>
                            </td>

                            <td class="py-3 px-4 whitespace-nowrap">
                                @if($p['assignment']->status === 'disqualified')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500 mr-1.5"></span>
                                        Ditangguhkan (Gagal)
                                    </span>
                                @elseif($p['assignment']->status === 'dispensed')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500 mr-1.5"></span>
                                        Dispensasi Admin
                                    </span>
                                @elseif($p['progress']['is_qualified'])
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>
                                        Lolos Periode Depan
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 mr-1.5"></span>
                                        Belum Capai Target
                                    </span>
                                @endif
                            </td>

                            <td class="py-3 px-4 whitespace-nowrap text-right text-xs">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button type="button" @click="openEditAssignment({{ json_encode($p) }})" class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg font-medium transition cursor-pointer">
                                        Edit Plotting
                                    </button>
                                    @if($p['assignment']->status === 'disqualified')
                                        <button type="button" @click="openDispense({{ json_encode($p) }})" class="px-2.5 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 rounded-lg font-semibold transition cursor-pointer">
                                            Beri Dispensasi
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-slate-500">
                                Belum ada blogwalker terdaftar pada periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Past Periods History -->
    @if($pastPeriods->count() > 0)
    <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-6">
        <h3 class="font-bold text-slate-800 text-base mb-4">Riwayat Periode Sebelumnya</h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            @foreach($pastPeriods as $past)
                <div class="p-4 rounded-xl border border-slate-200 bg-slate-50 space-y-1 text-xs">
                    <div class="font-bold text-slate-900 text-sm">{{ $past->name }}</div>
                    <div class="text-slate-500">Target Min: <strong>{{ $past->min_target }}</strong> &bull; Max/Domain: <strong>{{ $past->max_urls_per_domain }}</strong></div>
                    <div class="text-slate-400">Ditutup pada: {{ $past->closed_at?->format('d M Y') }}</div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- MODAL: Ubah Konfigurasi Target Periode & Global -->
    <div x-show="showConfigModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-xl border border-slate-100" @click.outside="showConfigModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Ubah Konfigurasi Target Periode & Domain</h3>
                <button type="button" @click="showConfigModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>

            <form method="POST" action="{{ route('admin.periods.config', $activePeriod->id) }}" class="space-y-4 text-xs">
                @csrf

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Target Minimal Submit per Blogwalker (Bulan Ini) <span class="text-rose-500">*</span></label>
                    <input type="number" name="min_target" value="{{ old('min_target', $activePeriod->min_target) }}" min="1" required class="w-full text-sm border-slate-300 rounded-lg p-2.5">
                    <span class="text-slate-400 mt-1 block">Blogwalker yang approved komentarnya di bawah angka ini akan tereliminasi di akhir bulan.</span>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Maksimal URL per Root Domain <span class="text-rose-500">*</span></label>
                    <input type="number" name="max_urls_per_domain" value="{{ old('max_urls_per_domain', $activePeriod->max_urls_per_domain) }}" min="1" max="100" required class="w-full text-sm border-slate-300 rounded-lg p-2.5">
                    <span class="text-slate-400 mt-1 block">Batas maksimal URL target per root domain (Bawaan: 5). Sistem akan otomatis mengunci domain jika batas ini tercapai.</span>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Maksimal Kuota Submit per Orang (Opsional)</label>
                    <input type="number" name="max_target" value="{{ old('max_target', $activePeriod->max_target) }}" placeholder="Kosongkan jika tanpa batas" class="w-full text-sm border-slate-300 rounded-lg p-2.5">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Tanggal Mulai</label>
                        <input type="date" name="starts_at" value="{{ old('starts_at', $activePeriod->starts_at->format('Y-m-d')) }}" required class="w-full text-sm border-slate-300 rounded-lg p-2">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Tanggal Berakhir</label>
                        <input type="date" name="ends_at" value="{{ old('ends_at', $activePeriod->ends_at->format('Y-m-d')) }}" required class="w-full text-sm border-slate-300 rounded-lg p-2">
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 flex justify-end gap-2">
                    <button type="button" @click="showConfigModal = false" class="px-4 py-2 border border-slate-300 rounded-lg text-slate-700 font-medium">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold">Simpan Konfigurasi</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: Edit Plotting Individu Blogwalker -->
    <div x-show="editAssignmentData" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-xl border border-slate-100" @click.outside="editAssignmentData = null">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Edit Plotting: <span x-text="editAssignmentData?.name"></span></h3>
                <button type="button" @click="editAssignmentData = null" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>

            <form method="POST" :action="editAssignmentData?.actionUrl" class="space-y-4 text-xs">
                @csrf

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Plotting Ekstensi Domain Target</label>
                    <input type="text" name="allowed_tlds" x-model="editAssignmentData.allowed_tlds" placeholder="Contoh: .co.id, .web.id (pisahkan koma)" class="w-full text-sm border-slate-300 rounded-lg p-2.5">
                    <span class="text-slate-400 mt-1 block">Kosongkan jika blogwalker ini boleh bebas komentar di semua ekstensi (.id, .com, dll.).</span>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Target Keyword Anchor</label>
                    <input type="text" name="target_keywords" x-model="editAssignmentData.target_keywords" placeholder="Contoh: jasa seo terpercaya, pakar backlink" class="w-full text-sm border-slate-300 rounded-lg p-2.5">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Target URL Backlink Klien</label>
                    <input type="url" name="target_backlink_url" x-model="editAssignmentData.target_backlink_url" placeholder="https://website-klien.com" class="w-full text-sm border-slate-300 rounded-lg p-2.5">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Target Minimal Khusus</label>
                        <input type="number" name="min_target" x-model="editAssignmentData.min_target" placeholder="Bawaan Periode" class="w-full text-sm border-slate-300 rounded-lg p-2.5">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Maksimal Submit Khusus</label>
                        <input type="number" name="max_target" x-model="editAssignmentData.max_target" placeholder="Tanpa Batas" class="w-full text-sm border-slate-300 rounded-lg p-2.5">
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Catatan Panduan Khusus</label>
                    <textarea name="custom_instructions" x-model="editAssignmentData.custom_instructions" rows="2" class="w-full text-sm border-slate-300 rounded-lg p-2.5"></textarea>
                </div>

                <div class="pt-3 border-t border-slate-100 flex justify-end gap-2">
                    <button type="button" @click="editAssignmentData = null" class="px-4 py-2 border border-slate-300 rounded-lg text-slate-700 font-medium">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold">Simpan Plotting</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: Beri Dispensasi -->
    <div x-show="dispenseAssignmentData" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 shadow-xl border border-slate-100" @click.outside="dispenseAssignmentData = null">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Beri Dispensasi: <span x-text="dispenseAssignmentData?.name"></span></h3>
                <button type="button" @click="dispenseAssignmentData = null" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>

            <form method="POST" :action="dispenseAssignmentData?.actionUrl" class="space-y-4 text-xs">
                @csrf
                <p class="text-slate-600">
                    Memberikan dispensasi akan mengaktifkan kembali akun blogwalker ini sehingga dapat mengirimkan komentar pada periode ini.
                </p>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Alasan Dispensasi <span class="text-rose-500">*</span></label>
                    <input type="text" name="reason" required placeholder="Contoh: Izin sakit dengan surat keterangan" class="w-full text-sm border-slate-300 rounded-lg p-2.5">
                </div>
                <div class="pt-3 border-t border-slate-100 flex justify-end gap-2">
                    <button type="button" @click="dispenseAssignmentData = null" class="px-4 py-2 border border-slate-300 rounded-lg text-slate-700 font-medium">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold">Aktifkan Kembali</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: Tutup Periode & Buka Periode Baru -->
    <div x-show="showRolloverModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-xl border border-slate-100" @click.outside="showRolloverModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Tutup Periode & Evaluasi Bulanan</h3>
                <button type="button" @click="showRolloverModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>

            <div class="p-3.5 bg-amber-50 rounded-xl border border-amber-200 text-xs text-amber-900">
                <span class="font-bold block mb-1">⚠️ Perhatian Evaluasi Otomatis:</span>
                Sistem akan mengecek seluruh blogwalker. Yang <strong>mencapai target minimal</strong> akan otomatis lanjut ke bulan depan, sedangkan yang <strong>tidak mencapai target</strong> akan otomatis ditangguhkan.
            </div>

            <form method="POST" action="{{ route('admin.periods.rollover', $activePeriod->id) }}" class="space-y-4 text-xs">
                @csrf

                @php
                    $nextMonthCarbon = now()->addMonth();
                @endphp

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Bulan Periode Baru</label>
                        <select name="next_month" class="w-full text-sm border-slate-300 rounded-lg p-2.5">
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ $nextMonthCarbon->month === $m ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create(null, $m, 1)->translatedFormat('F') }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Tahun Periode Baru</label>
                        <input type="number" name="next_year" value="{{ $nextMonthCarbon->year }}" min="2025" max="2035" required class="w-full text-sm border-slate-300 rounded-lg p-2.5">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Target Minimal Baru</label>
                        <input type="number" name="next_min_target" value="{{ $activePeriod->min_target }}" min="1" required class="w-full text-sm border-slate-300 rounded-lg p-2.5">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Batas URL per Domain Baru</label>
                        <input type="number" name="next_max_urls_per_domain" value="{{ $activePeriod->max_urls_per_domain }}" min="1" required class="w-full text-sm border-slate-300 rounded-lg p-2.5">
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 flex justify-end gap-2">
                    <button type="button" @click="showRolloverModal = false" class="px-4 py-2 border border-slate-300 rounded-lg text-slate-700 font-medium">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold shadow-xs">
                        Tutup & Buka Periode Baru
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
