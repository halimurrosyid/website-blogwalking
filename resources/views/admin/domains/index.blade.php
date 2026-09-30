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
    apiKeyModalOpen: false,
    ahrefsKeyInput: '{{ addslashes(old('ahrefs_api_key', $ahrefsKey ?? '')) }}',
    mozKeyInput: '{{ addslashes(old('moz_api_token', $mozToken ?? '')) }}',
    oprKeyInput: '{{ addslashes(old('openpagerank_api_key', $oprKey ?? '')) }}',
    testState: {
        ahrefs: { loading: false, msg: null, success: null },
        moz: { loading: false, msg: null, success: null },
        openpagerank: { loading: false, msg: null, success: null }
    },
    async testApiKey(provider, key) {
        if (!key || !key.trim()) {
            this.testState[provider] = { loading: false, msg: 'Masukkan API Key terlebih dahulu untuk ditest.', success: false };
            return;
        }
        this.testState[provider] = { loading: true, msg: 'Sedang menguji koneksi ke API...', success: null };
        try {
            const res = await fetch('{{ route('admin.domains.test-api-key') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ provider: provider, key: key })
            });
            const data = await res.json();
            this.testState[provider] = {
                loading: false,
                msg: data.message,
                success: data.success
            };
        } catch (err) {
            this.testState[provider] = {
                loading: false,
                msg: 'Gagal menghubungi server: ' + err.message,
                success: false
            };
        }
    },
    metricsModalOpen: false,
    metricsDomainName: '',
    metricsDomainAction: '',
    metricsDa: '',
    metricsPa: '',
    metricsDr: '',
    metricsPr: '',
    openEditQuota(name, action, currentLimit, count) {
        this.editDomainName = name;
        this.editDomainAction = action;
        this.editDomainLimit = currentLimit;
        this.editDomainCount = count;
        this.editModalOpen = true;
    },
    openEditMetrics(name, action, da, pa, dr, pr) {
        this.metricsDomainName = name;
        this.metricsDomainAction = action;
        this.metricsDa = (da && da !== 'null') ? da : '';
        this.metricsPa = (pa && pa !== 'null') ? pa : '';
        this.metricsDr = (dr && dr !== 'null') ? dr : '';
        this.metricsPr = (pr && pr !== 'null') ? pr : '';
        this.metricsModalOpen = true;
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
            <div class="flex items-center gap-2.5">
                <h1 class="text-2xl font-bold text-slate-900">Database Domain & Kuota URL</h1>
                <div class="flex items-center gap-1 text-[11px] font-semibold">
                    <span class="px-2 py-0.5 rounded-full {{ $seoProviders['moz'] ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-500' }}" title="{{ $seoProviders['moz'] ? 'Moz API Aktif' : 'Moz API belum diisi' }}">
                        Moz: {{ $seoProviders['moz'] ? '● Aktif' : '○ Nonaktif' }}
                    </span>
                    <span class="px-2 py-0.5 rounded-full {{ $seoProviders['ahrefs'] ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-500' }}" title="{{ $seoProviders['ahrefs'] ? 'Ahrefs API Aktif' : 'Ahrefs API belum diisi' }}">
                        Ahrefs: {{ $seoProviders['ahrefs'] ? '● Aktif' : '○ Nonaktif' }}
                    </span>
                </div>
            </div>
            <p class="text-sm text-slate-500 mt-1">Pantau kuota URL, metrik otoritas SEO (DA, PA, DR, PR) resmi Moz & Ahrefs, serta deteksi subnet IP untuk cegah footprint PBN.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <!-- API Key Settings Modal Trigger -->
            <button type="button" @click="apiKeyModalOpen = true"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl {{ $hasAnyKey ? 'bg-slate-100 hover:bg-slate-200 text-slate-700 border-slate-200' : 'bg-amber-50 hover:bg-amber-100 text-amber-800 border-amber-300 animate-pulse' }} text-xs font-bold transition border cursor-pointer">
                <span>🔑 Pengaturan API Key SEO</span>
                @if(! $hasAnyKey)
                    <span class="bg-amber-500 text-white rounded-full w-2 h-2"></span>
                @endif
            </button>

            <!-- Bulk SEO Fetch Form (for selected) -->
            <form method="POST" action="{{ route('admin.domains.bulk-fetch-seo') }}" x-show="selectedIds.length > 0" x-cloak
                onsubmit="return confirm('Mulai pengecekan metrik SEO otomatis (DA/PA/DR/PR) untuk domain terpilih?')">
                @csrf
                <template x-for="id in selectedIds" :key="id">
                    <input type="hidden" name="domain_ids[]" :value="id">
                </template>
                <button type="submit" class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-sm transition flex items-center gap-1.5 cursor-pointer">
                    <span>⚡ Cek SEO Terpilih (<span x-text="selectedIds.length"></span>)</span>
                </button>
            </form>

            <!-- Refresh DNS & Subnet Button -->
            <form method="POST" action="{{ route('admin.domains.refresh-subnets') }}">
                @csrf
                <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition border border-slate-200 cursor-pointer" title="Resolve DNS IP & Subnet C-Class untuk domain yang belum terdeteksi">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    <span>IP / Subnet</span>
                </button>
            </form>

            <!-- Bulk Quota Button -->
            <button type="button" @click="bulkQuotaModalOpen = true" x-show="selectedIds.length > 0" x-cloak
                class="px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-sm transition flex items-center gap-1.5 cursor-pointer">
                <span>⚙️ Kuota Terpilih (<span x-text="selectedIds.length"></span>)</span>
            </button>

            <!-- Bulk Reset Form -->
            <form method="POST" action="{{ route('admin.domains.bulk-reset') }}" x-show="selectedIds.length > 0" x-cloak
                onsubmit="return confirm('Apakah Anda yakin ingin me-reset kuota seluruh domain terpilih kembali ke 0?')">
                @csrf
                <template x-for="id in selectedIds" :key="id">
                    <input type="hidden" name="domain_ids[]" :value="id">
                </template>
                <button type="submit" class="px-3 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-sm transition flex items-center gap-1.5 cursor-pointer">
                    <span>🔄 Reset (<span x-text="selectedIds.length"></span>)</span>
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
                        <th class="px-6 py-3.5">Metrik SEO</th>
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
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if(! $hasAnyKey && ! ($domain->da || $domain->pa || $domain->dr || $domain->pr))
                                <button type="button" @click="apiKeyModalOpen = true" class="text-[11px] text-amber-700 bg-amber-50 hover:bg-amber-100 px-2 py-0.5 rounded border border-amber-200 transition cursor-pointer inline-flex items-center gap-1" title="Klik untuk membuka pengaturan API Key">
                                    <span>🔑 Menunggu API Key</span>
                                </button>
                            @else
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="px-1.5 py-0.5 rounded text-[11px] font-bold font-mono {{ $domain->da ? 'bg-blue-100 text-blue-800 border border-blue-200' : 'bg-slate-100 text-slate-400' }}" title="Moz Domain Authority (1-100)">
                                        DA {{ $domain->da ?? '-' }}
                                    </span>
                                    <span class="px-1.5 py-0.5 rounded text-[11px] font-bold font-mono {{ $domain->pa ? 'bg-cyan-100 text-cyan-800 border border-cyan-200' : 'bg-slate-100 text-slate-400' }}" title="Moz Page Authority (1-100)">
                                        PA {{ $domain->pa ?? '-' }}
                                    </span>
                                    <span class="px-1.5 py-0.5 rounded text-[11px] font-bold font-mono {{ $domain->dr ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-slate-100 text-slate-400' }}" title="Ahrefs Domain Rating (1-100)">
                                        DR {{ $domain->dr ?? '-' }}
                                    </span>
                                    <span class="px-1.5 py-0.5 rounded text-[11px] font-bold font-mono {{ $domain->pr ? 'bg-purple-100 text-purple-800 border border-purple-200' : 'bg-slate-100 text-slate-400' }}" title="PageRank / Score">
                                        PR {{ $domain->pr ?? '-' }}
                                    </span>
                                    <button type="button" 
                                        @click="openEditMetrics('{{ $domain->root_domain }}', '{{ route('admin.domains.metrics', $domain) }}', '{{ $domain->da }}', '{{ $domain->pa }}', '{{ $domain->dr }}', '{{ $domain->pr }}')"
                                        class="text-xs text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 px-1 py-0.5 rounded transition cursor-pointer" 
                                        title="Edit manual nilai DA, PA, DR, PR">
                                        ✏️
                                    </button>
                                </div>
                                @if($domain->seo_updated_at)
                                    <div class="text-[10px] text-slate-400 mt-1">Cek: {{ $domain->seo_updated_at->diffForHumans() }}</div>
                                @endif
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
                                <form method="POST" action="{{ route('admin.domains.fetch-seo', $domain) }}">
                                    @csrf
                                    <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 font-semibold text-xs transition cursor-pointer" title="Cek otomatis DA, PA, DR, PR ke API resmi">
                                        ⚡ Cek SEO
                                    </button>
                                </form>
                                <button type="button" 
                                    @click="openEditQuota('{{ $domain->root_domain }}', '{{ route('admin.domains.quota', $domain) }}', {{ $domain->max_limit }}, {{ $domain->url_count }})"
                                    class="px-2.5 py-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 font-semibold text-xs transition cursor-pointer">
                                    ⚙️ Kuota
                                </button>
                                <a href="{{ route('admin.domains.show', $domain) }}" class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
                                    URL ({{ $domain->submissions_count }})
                                </a>
                                <form method="POST" action="{{ route('admin.domains.reset', $domain) }}" onsubmit="return confirm('Reset kuota domain {{ $domain->root_domain }} kembali ke 0?')">
                                    @csrf
                                    <button type="submit" class="px-2 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-semibold text-xs transition cursor-pointer" title="Reset hitungan URL kembali ke 0">
                                        🔄
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center text-xs text-slate-400">
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

    <!-- Modal: Pengaturan API Key SEO (Moz & Ahrefs) -->
    <div x-show="apiKeyModalOpen" x-cloak class="fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4 backdrop-blur-2xs" @click="apiKeyModalOpen = false">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 space-y-4" @click.stop>
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h3 class="font-bold text-slate-900 text-base flex items-center gap-2">
                        <span>🔑 Pengaturan API Key SEO</span>
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Integrasi resmi Moz & Ahrefs untuk cek otomatis nilai DA, PA, DR, dan PR.</p>
                </div>
                <button type="button" @click="apiKeyModalOpen = false" class="text-slate-400 hover:text-slate-600 font-bold text-lg cursor-pointer">&times;</button>
            </div>

            <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-xs text-amber-800 leading-relaxed">
                ℹ️ <strong>Catatan:</strong> Fitur cek otomatis ke penyedia layanan hanya akan aktif jika Anda sudah mengisi API Key masing-masing di bawah ini.
            </div>

            <form action="{{ route('admin.domains.api-keys') }}" method="POST" class="space-y-4">
                @csrf

                <!-- Ahrefs API Key (DR) -->
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                            Ahrefs API Key (DR)
                        </label>
                        <span class="text-[11px] {{ $seoProviders['ahrefs'] ? 'text-emerald-700 font-bold' : 'text-slate-400' }}">
                            {{ $seoProviders['ahrefs'] ? '✓ Tersambung' : 'Belum diisi' }}
                        </span>
                    </div>
                    <div class="flex gap-2">
                        <input type="text" name="ahrefs_api_key" x-model="ahrefsKeyInput"
                            placeholder="Contoh: ahrefs_v3_api_key_anda..."
                            class="flex-1 px-3.5 py-2 text-xs font-mono rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-500 focus:outline-none">
                        <button type="button" 
                            @click="testApiKey('ahrefs', ahrefsKeyInput)" 
                            :disabled="testState.ahrefs.loading || !ahrefsKeyInput.trim()"
                            class="px-3 py-2 rounded-xl text-xs font-semibold bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 transition cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed whitespace-nowrap">
                            <span x-show="!testState.ahrefs.loading">🧪 Tes Koneksi</span>
                            <span x-show="testState.ahrefs.loading">⏳ Menguji...</span>
                        </button>
                    </div>
                    <template x-if="testState.ahrefs.msg">
                        <div class="mt-1.5 text-[11px] p-2 rounded-lg leading-tight" 
                            :class="testState.ahrefs.success ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-rose-50 text-rose-800 border border-rose-200'">
                            <span x-text="testState.ahrefs.msg"></span>
                        </div>
                    </template>
                    <p class="text-[11px] text-slate-500 mt-1">
                        Dapatkan gratis di: <em>Ahrefs Account Settings &rarr; API keys</em> (Menggunakan endpoint publik Domain Rating).
                    </p>
                </div>

                <!-- Moz API Token (DA & PA) -->
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                            Moz API Token / Credential (DA & PA)
                        </label>
                        <span class="text-[11px] {{ $seoProviders['moz'] ? 'text-emerald-700 font-bold' : 'text-slate-400' }}">
                            {{ $seoProviders['moz'] ? '✓ Tersambung' : 'Belum diisi' }}
                        </span>
                    </div>
                    <div class="flex gap-2">
                        <input type="text" name="moz_api_token" x-model="mozKeyInput"
                            placeholder="Contoh: moz_api_token_anda atau access_id:secret_key"
                            class="flex-1 px-3.5 py-2 text-xs font-mono rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <button type="button" 
                            @click="testApiKey('moz', mozKeyInput)" 
                            :disabled="testState.moz.loading || !mozKeyInput.trim()"
                            class="px-3 py-2 rounded-xl text-xs font-semibold bg-blue-50 hover:bg-blue-100 text-blue-800 border border-blue-200 transition cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed whitespace-nowrap">
                            <span x-show="!testState.moz.loading">🧪 Tes Koneksi</span>
                            <span x-show="testState.moz.loading">⏳ Menguji...</span>
                        </button>
                    </div>
                    <template x-if="testState.moz.msg">
                        <div class="mt-1.5 text-[11px] p-2 rounded-lg leading-tight" 
                            :class="testState.moz.success ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-rose-50 text-rose-800 border border-rose-200'">
                            <span x-text="testState.moz.msg"></span>
                        </div>
                    </template>
                    <p class="text-[11px] text-slate-500 mt-1">
                        Dapatkan token gratis di: <em>moz.com/products/api</em> (URL Metrics API v2).
                    </p>
                </div>

                <!-- OpenPageRank API Key (PR) -->
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                            OpenPageRank API Key (PR) <span class="text-slate-400 font-normal lowercase">(opsional)</span>
                        </label>
                        <span class="text-[11px] {{ $seoProviders['openpagerank'] ? 'text-emerald-700 font-bold' : 'text-slate-400' }}">
                            {{ $seoProviders['openpagerank'] ? '✓ Tersambung' : 'Belum diisi' }}
                        </span>
                    </div>
                    <div class="flex gap-2">
                        <input type="text" name="openpagerank_api_key" x-model="oprKeyInput"
                            placeholder="Contoh: opr_api_key_anda..."
                            class="flex-1 px-3.5 py-2 text-xs font-mono rounded-xl border border-slate-300 focus:ring-2 focus:ring-purple-500 focus:outline-none">
                        <button type="button" 
                            @click="testApiKey('openpagerank', oprKeyInput)" 
                            :disabled="testState.openpagerank.loading || !oprKeyInput.trim()"
                            class="px-3 py-2 rounded-xl text-xs font-semibold bg-purple-50 hover:bg-purple-100 text-purple-800 border border-purple-200 transition cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed whitespace-nowrap">
                            <span x-show="!testState.openpagerank.loading">🧪 Tes Koneksi</span>
                            <span x-show="testState.openpagerank.loading">⏳ Menguji...</span>
                        </button>
                    </div>
                    <template x-if="testState.openpagerank.msg">
                        <div class="mt-1.5 text-[11px] p-2 rounded-lg leading-tight" 
                            :class="testState.openpagerank.success ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-rose-50 text-rose-800 border border-rose-200'">
                            <span x-text="testState.openpagerank.msg"></span>
                        </div>
                    </template>
                    <p class="text-[11px] text-slate-500 mt-1">
                        Dapatkan gratis di: <em>openpagerank.com</em> (300.000 cek PageRank gratis/bulan).
                    </p>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="apiKeyModalOpen = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 cursor-pointer">
                        Tutup
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-slate-900 hover:bg-slate-800 text-white shadow-xs cursor-pointer">
                        Simpan Pengaturan API
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Ubah Manual Metrik SEO -->
    <div x-show="metricsModalOpen" x-cloak class="fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4 backdrop-blur-2xs" @click="metricsModalOpen = false">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-4" @click.stop>
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h3 class="font-bold text-slate-900 text-base">Edit Metrik SEO Domain</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Ubah nilai otoritas DA, PA, DR, dan PR untuk domain ini secara manual.</p>
                </div>
                <button type="button" @click="metricsModalOpen = false" class="text-slate-400 hover:text-slate-600 font-bold text-lg cursor-pointer">&times;</button>
            </div>

            <form :action="metricsDomainAction" method="POST" class="space-y-4">
                @csrf
                <div class="bg-slate-50 p-3 rounded-xl border border-slate-200/80">
                    <div class="text-xs text-slate-500">Domain:</div>
                    <div class="font-bold text-sm text-slate-900 font-mono" x-text="metricsDomainName"></div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-blue-700 uppercase tracking-wider mb-1">
                            DA (Domain Authority)
                        </label>
                        <input type="number" name="da" x-model="metricsDa" min="0" max="100" placeholder="0-100"
                            class="w-full px-3 py-2 text-sm font-bold font-mono rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-cyan-700 uppercase tracking-wider mb-1">
                            PA (Page Authority)
                        </label>
                        <input type="number" name="pa" x-model="metricsPa" min="0" max="100" placeholder="0-100"
                            class="w-full px-3 py-2 text-sm font-bold font-mono rounded-xl border border-slate-300 focus:ring-2 focus:ring-cyan-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-amber-700 uppercase tracking-wider mb-1">
                            DR (Domain Rating)
                        </label>
                        <input type="number" name="dr" x-model="metricsDr" min="0" max="100" placeholder="0-100"
                            class="w-full px-3 py-2 text-sm font-bold font-mono rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-purple-700 uppercase tracking-wider mb-1">
                            PR (PageRank / Score)
                        </label>
                        <input type="text" name="pr" x-model="metricsPr" maxlength="10" placeholder="e.g. 4.2"
                            class="w-full px-3 py-2 text-sm font-bold font-mono rounded-xl border border-slate-300 focus:ring-2 focus:ring-purple-500 focus:outline-none">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="metricsModalOpen = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs cursor-pointer">
                        Simpan Nilai Metrik
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
