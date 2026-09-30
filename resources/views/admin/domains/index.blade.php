@extends('layouts.app')

@section('title', 'Master Domain & Kuota')

@section('content')
<div class="space-y-6" x-data="{
    selectedIds: [],
    editModalOpen: false,
    editDomainName: '',
    editDomainAction: '',
    editDomainLimit: 5,
    editDomainCount: 0,
    bulkQuotaModalOpen: false,
    bulkQuotaLimit: 5,
    openEditQuota(name, action, currentLimit, count) {
        this.editDomainName = name;
        this.editDomainAction = action;
        this.editDomainLimit = currentLimit;
        this.editDomainCount = count;
        this.editModalOpen = true;
    },
    toggleAll(e) {
        if (e.target.checked) {
            this.selectedIds = Array.from(document.querySelectorAll('.domain-checkbox')).map(el => el.value);
        } else {
            this.selectedIds = [];
        }
    }
}">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Database Domain & Kuota URL</h1>
            <p class="text-sm text-slate-500 mt-1">Pantau kuota maksimal URL per domain dan deteksi C-Class Subnet IP untuk mencegah risiko footprint PBN / cluster Google penalty.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <!-- Refresh DNS & Subnet Button -->
            <form method="POST" action="{{ route('admin.domains.refresh-subnets') }}">
                @csrf
                <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition border border-slate-200 cursor-pointer" title="Resolve DNS IP & Subnet C-Class untuk domain yang belum terdeteksi">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    <span>Deteksi Ulang IP / Subnet</span>
                </button>
            </form>

            <!-- Bulk Quota Button -->
            <button type="button" @click="bulkQuotaModalOpen = true" x-show="selectedIds.length > 0" x-cloak
                class="px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-sm transition flex items-center gap-1.5 cursor-pointer">
                <span>⚙️ Atur Kuota Terpilih (<span x-text="selectedIds.length"></span>)</span>
            </button>

            <!-- Bulk Reset Form -->
            <form method="POST" action="{{ route('admin.domains.bulk-reset') }}" x-show="selectedIds.length > 0" x-cloak
                onsubmit="return confirm('Apakah Anda yakin ingin me-reset kuota seluruh domain terpilih kembali ke 0?')">
                @csrf
                <template x-for="id in selectedIds" :key="id">
                    <input type="hidden" name="domain_ids[]" :value="id">
                </template>
                <button type="submit" class="px-3.5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-sm transition flex items-center gap-2 cursor-pointer">
                    <span>🔄 Reset Kuota (<span x-text="selectedIds.length"></span>)</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Stats Bar -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="p-4 bg-white rounded-xl border border-slate-200 shadow-xs">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Domain</div>
            <div class="text-2xl font-bold text-slate-900 mt-1">{{ number_format($totalDomains ?? $domains->total()) }}</div>
        </div>
        <div class="p-4 bg-rose-50 rounded-xl border border-rose-200 shadow-xs">
            <div class="text-xs font-semibold text-rose-700 uppercase tracking-wider">Penuh / Terkunci (5/5)</div>
            <div class="text-2xl font-bold text-rose-800 mt-1">{{ number_format($lockedDomains ?? 0) }}</div>
        </div>
        <div class="p-4 bg-emerald-50 rounded-xl border border-emerald-200 shadow-xs">
            <div class="text-xs font-semibold text-emerald-700 uppercase tracking-wider">Subnet IP Terdeteksi</div>
            <div class="text-2xl font-bold text-emerald-800 mt-1">{{ number_format($detectedSubnetsCount ?? 0) }}</div>
        </div>
        <div class="p-4 bg-amber-50 rounded-xl border border-amber-200 shadow-xs">
            <div class="text-xs font-semibold text-amber-700 uppercase tracking-wider">Cluster Subnet Kembar</div>
            <div class="text-2xl font-bold text-amber-800 mt-1">{{ number_format($clusterSubnetsCount ?? 0) }}</div>
            <div class="text-[10px] text-amber-600 mt-0.5">Potensi server/PBN sama</div>
        </div>
    </div>

    <!-- Filter & Search -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex flex-wrap gap-1.5 text-xs font-semibold">
            <a href="{{ route('admin.domains.index', ['status' => 'all', 'search' => $search]) }}" 
                class="px-3 py-1.5 rounded-lg {{ $currentStatus === 'all' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Semua Domain
            </a>
            <a href="{{ route('admin.domains.index', ['status' => 'locked', 'search' => $search]) }}" 
                class="px-3 py-1.5 rounded-lg {{ $currentStatus === 'locked' ? 'bg-rose-600 text-white' : 'bg-rose-50 text-rose-800 hover:bg-rose-100' }}">
                Penuh / Terkunci (5/5)
            </a>
            <a href="{{ route('admin.domains.index', ['status' => 'available', 'search' => $search]) }}" 
                class="px-3 py-1.5 rounded-lg {{ $currentStatus === 'available' ? 'bg-emerald-600 text-white' : 'bg-emerald-50 text-emerald-800 hover:bg-emerald-100' }}">
                Masih Ada Slot (< 5)
            </a>
        </div>

        <form method="GET" action="{{ route('admin.domains.index') }}" class="flex gap-2">
            <input type="hidden" name="status" value="{{ $currentStatus }}">
            <input type="text" name="search" value="{{ $search }}" placeholder="Cari domain, IP, atau subnet..."
                class="px-3 py-1.5 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none w-56">
            <button type="submit" class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs cursor-pointer">
                Cari
            </button>
        </form>
    </div>

    <!-- Domains Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-[11px] uppercase font-bold tracking-wider text-slate-500 border-b border-slate-100">
                    <tr>
                        <th class="px-4 py-3.5 w-10 text-center">
                            <input type="checkbox" @change="toggleAll($event)" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                        </th>
                        <th class="px-6 py-3.5">Nama Root Domain</th>
                        <th class="px-6 py-3.5">IP Address & Subnet C-Class</th>
                        <th class="px-6 py-3.5">Ekstensi (TLD)</th>
                        <th class="px-6 py-3.5">Penggunaan Kuota (URL)</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5">Terakhir Direset</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($domains as $domain)
                    <tr class="hover:bg-slate-50/60 transition">
                        <td class="px-4 py-4 text-center">
                            <input type="checkbox" value="{{ $domain->id }}" x-model="selectedIds" class="domain-checkbox rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                        </td>
                        <td class="px-6 py-4">
                            <a href="{{ route('admin.domains.show', $domain) }}" class="font-bold text-slate-900 hover:text-emerald-600 transition block text-sm">
                                {{ $domain->root_domain }} &rarr;
                            </a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($domain->ip_address)
                                <div class="font-mono text-xs font-semibold text-slate-800">{{ $domain->ip_address }}</div>
                                <div class="mt-1 flex items-center gap-1.5">
                                    <span class="font-mono text-[11px] px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-medium">
                                        {{ $domain->ip_subnet ?? '-' }}
                                    </span>
                                    @if($domain->ip_subnet && in_array($domain->ip_subnet, $duplicateSubnets ?? []))
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800" title="Subnet C-Class ini digunakan oleh lebih dari 1 domain di sistem (potensi 1 server/PBN)">
                                            ⚠️ Cluster
                                        </span>
                                    @endif
                                </div>
                            @else
                                <span class="text-xs text-slate-400 italic">Belum terdeteksi</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 font-mono text-xs font-semibold">
                                {{ $domain->tld }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <div class="w-20 bg-slate-200 rounded-full h-2 overflow-hidden">
                                    <div class="h-2 rounded-full {{ $domain->is_locked ? 'bg-rose-500' : 'bg-emerald-500' }}"
                                        style="width: {{ min(100, ($domain->url_count / max(1, $domain->max_limit)) * 100) }}%"></div>
                                </div>
                                <span class="font-bold text-xs {{ $domain->is_locked ? 'text-rose-600' : 'text-slate-700' }}">
                                    {{ $domain->url_count }}/{{ $domain->max_limit }}
                                </span>
                                <button type="button" 
                                    @click="openEditQuota('{{ $domain->root_domain }}', '{{ route('admin.domains.quota', $domain) }}', {{ $domain->max_limit }}, {{ $domain->url_count }})"
                                    class="text-xs text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 px-1.5 py-0.5 rounded transition cursor-pointer" 
                                    title="Ubah batas kuota maksimal domain ini">
                                    ✏️
                                </button>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-xs">
                            @if($domain->is_locked)
                                <span class="px-2.5 py-1 rounded-full bg-rose-100 text-rose-800 font-bold">🔒 PENUH (TERKUNCI)</span>
                            @else
                                <span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 font-bold">🟢 {{ $domain->remainingSlots() }} Slot Sisa</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-400">
                            {{ $domain->last_reset_at ? $domain->last_reset_at->format('d M Y, H:i') : '-' }}
                        </td>
                        <td class="px-6 py-4 text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-1.5">
                                <button type="button" 
                                    @click="openEditQuota('{{ $domain->root_domain }}', '{{ route('admin.domains.quota', $domain) }}', {{ $domain->max_limit }}, {{ $domain->url_count }})"
                                    class="px-2.5 py-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 font-semibold text-xs transition cursor-pointer">
                                    ⚙️ Atur Kuota
                                </button>
                                <a href="{{ route('admin.domains.show', $domain) }}" class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
                                    Lihat URL ({{ $domain->submissions_count }})
                                </a>
                                <form method="POST" action="{{ route('admin.domains.reset', $domain) }}" onsubmit="return confirm('Reset kuota domain {{ $domain->root_domain }} kembali ke 0?')">
                                    @csrf
                                    <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-semibold text-xs transition cursor-pointer" title="Reset hitungan URL kembali ke 0">
                                        🔄 Reset
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-xs text-slate-400">
                            Belum ada domain yang tersimpan di sistem.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($domains->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $domains->links() }}
            </div>
        @endif
    </div>

    <!-- Modal: Ubah Kuota Satuan -->
    <div x-show="editModalOpen" x-cloak class="fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4 backdrop-blur-2xs" @click="editModalOpen = false">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-4" @click.stop>
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h3 class="font-bold text-slate-900 text-base">Atur Kuota URL Domain</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Ubah batas maksimal artikel yang boleh disubmit di web ini.</p>
                </div>
                <button type="button" @click="editModalOpen = false" class="text-slate-400 hover:text-slate-600 font-bold text-lg cursor-pointer">&times;</button>
            </div>

            <form :action="editDomainAction" method="POST" class="space-y-4">
                @csrf
                <div class="bg-slate-50 p-3 rounded-xl border border-slate-200/80 space-y-1">
                    <div class="text-xs text-slate-500">Nama Domain:</div>
                    <div class="font-bold text-sm text-slate-900 font-mono" x-text="editDomainName"></div>
                    <div class="text-xs text-slate-500 pt-1">
                        Jumlah URL saat ini: <strong class="text-slate-800" x-text="editDomainCount"></strong> URL
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Batas Maksimal Kuota (URL) *
                    </label>
                    <input type="number" name="max_limit" x-model="editDomainLimit" min="1" max="5000" required
                        class="w-full px-3.5 py-2.5 text-sm font-bold rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    <p class="text-[11px] text-slate-400 mt-1">
                        Standar default adalah 5 URL. Anda bisa menambah menjadi 10, 20, atau angka lainnya.
                    </p>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="editModalOpen = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs cursor-pointer">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Ubah Kuota Massal (Bulk) -->
    <div x-show="bulkQuotaModalOpen" x-cloak class="fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4 backdrop-blur-2xs" @click="bulkQuotaModalOpen = false">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-4" @click.stop>
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h3 class="font-bold text-slate-900 text-base">Atur Kuota Massal Terpilih</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Terapkan batas kuota baru ke <span class="font-bold text-slate-900" x-text="selectedIds.length"></span> domain terpilih.</p>
                </div>
                <button type="button" @click="bulkQuotaModalOpen = false" class="text-slate-400 hover:text-slate-600 font-bold text-lg cursor-pointer">&times;</button>
            </div>

            <form action="{{ route('admin.domains.bulk-quota') }}" method="POST" class="space-y-4">
                @csrf
                <template x-for="id in selectedIds" :key="id">
                    <input type="hidden" name="domain_ids[]" :value="id">
                </template>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Batas Maksimal Kuota Baru (URL) *
                    </label>
                    <input type="number" name="max_limit" x-model="bulkQuotaLimit" min="1" max="5000" required
                        class="w-full px-3.5 py-2.5 text-sm font-bold rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    <p class="text-[11px] text-slate-400 mt-1">
                        Semua domain yang dicentang akan diperbarui batas maksimalnya ke angka ini.
                    </p>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="bulkQuotaModalOpen = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white shadow-xs cursor-pointer">
                        Terapkan ke Semua Terpilih
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
