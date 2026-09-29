@extends('layouts.app')

@section('title', 'Master Domain & Kuota')

@section('content')
<div class="space-y-6" x-data="{
    selectedIds: [],
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
            <h1 class="text-2xl font-bold text-slate-900">Master Domain & Pembatasan 5 URL</h1>
            <p class="text-sm text-slate-500 mt-1">Pantau kuota komentar tiap domain. Domain yang mencapai 5 URL otomatis dikunci hingga Anda reset.</p>
        </div>

        <!-- Bulk Reset Form -->
        <form method="POST" action="{{ route('admin.domains.bulk-reset') }}" x-show="selectedIds.length > 0" x-cloak
            onsubmit="return confirm('Apakah Anda yakin ingin me-reset kuota seluruh domain terpilih kembali ke 0?')">
            @csrf
            <template x-for="id in selectedIds" :key="id">
                <input type="hidden" name="domain_ids[]" :value="id">
            </template>
            <button type="submit" class="px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-sm transition flex items-center gap-2 cursor-pointer">
                <span>🔄 Reset Kuota Terpilih (<span x-text="selectedIds.length"></span>)</span>
            </button>
        </form>
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
            <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama domain..."
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
                        <td class="px-6 py-4">
                            <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 font-mono text-xs font-semibold">
                                {{ $domain->tld }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <div class="w-24 bg-slate-200 rounded-full h-2 overflow-hidden">
                                    <div class="h-2 rounded-full {{ $domain->is_locked ? 'bg-rose-500' : 'bg-emerald-500' }}"
                                        style="width: {{ min(100, ($domain->url_count / $domain->max_limit) * 100) }}%"></div>
                                </div>
                                <span class="font-bold text-xs {{ $domain->is_locked ? 'text-rose-600' : 'text-slate-700' }}">
                                    {{ $domain->url_count }}/{{ $domain->max_limit }}
                                </span>
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
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.domains.show', $domain) }}" class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
                                    Lihat URL ({{ $domain->submissions_count }})
                                </a>
                                <form method="POST" action="{{ route('admin.domains.reset', $domain) }}" onsubmit="return confirm('Reset kuota domain {{ $domain->root_domain }} kembali ke 0?')">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-semibold text-xs transition cursor-pointer">
                                        🔄 Reset Kuota
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-xs text-slate-400">
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

</div>
@endsection
