@extends('layouts.app')

@section('title', 'Pool Target URL Komentar')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Pool Target URL Komentar</h1>
            <p class="text-sm text-slate-500 mt-1">Daftar URL / subdomain yang siap dikerjakan oleh tim Blogwalker.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.targets.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-semibold shadow-xs transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                + Input Target Massal
            </a>
            @if($stats['completed'] > 0)
                <form method="POST" action="{{ route('admin.targets.clear-completed') }}" onsubmit="return confirm('Hapus semua target URL yang sudah selesai dikerjakan?')">
                    @csrf
                    <button type="submit" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-sm font-medium transition">
                        Bersihkan Selesai ({{ $stats['completed'] }})
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
        <div class="p-4 bg-white rounded-xl border border-slate-200 shadow-xs">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Target</div>
            <div class="text-2xl font-bold text-slate-900 mt-1">{{ number_format($stats['total']) }}</div>
        </div>
        <div class="p-4 bg-emerald-50 rounded-xl border border-emerald-200 shadow-xs">
            <div class="text-xs font-semibold text-emerald-700 uppercase tracking-wider">Siap Dikerjakan</div>
            <div class="text-2xl font-bold text-emerald-800 mt-1">{{ number_format($stats['available']) }}</div>
        </div>
        <div class="p-4 bg-blue-50 rounded-xl border border-blue-200 shadow-xs">
            <div class="text-xs font-semibold text-blue-700 uppercase tracking-wider">Sedang Dikerjakan</div>
            <div class="text-2xl font-bold text-blue-800 mt-1">{{ number_format($stats['in_progress']) }}</div>
        </div>
        <div class="p-4 bg-purple-50 rounded-xl border border-purple-200 shadow-xs">
            <div class="text-xs font-semibold text-purple-700 uppercase tracking-wider">Selesai</div>
            <div class="text-2xl font-bold text-purple-800 mt-1">{{ number_format($stats['completed']) }}</div>
        </div>
        <div class="p-4 bg-amber-50 rounded-xl border border-amber-200 shadow-xs">
            <div class="text-xs font-semibold text-amber-700 uppercase tracking-wider">Dilewati (Skip)</div>
            <div class="text-2xl font-bold text-amber-800 mt-1">{{ number_format($stats['skipped']) }}</div>
        </div>
    </div>

    <!-- Filter & Search -->
    <div class="p-4 bg-white rounded-xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('admin.targets.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-3">
            <div class="md:col-span-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari URL, domain, keyword, atau klien..." class="w-full text-sm border-slate-300 rounded-lg px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500">
            </div>
            <div>
                <select name="task_type" class="w-full text-sm border-slate-300 rounded-lg px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="">Semua Kategori Misi</option>
                    @foreach($taskTypes as $key => $type)
                        <option value="{{ $key }}" {{ request('task_type') === $key ? 'selected' : '' }}>{{ $type['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="status" class="w-full text-sm border-slate-300 rounded-lg px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="">Semua Status</option>
                    <option value="available" {{ request('status') === 'available' ? 'selected' : '' }}>Tersedia (Ready)</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>Sedang Dikerjakan</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Selesai</option>
                    <option value="skipped" {{ request('status') === 'skipped' ? 'selected' : '' }}>Dilewati</option>
                    <option value="domain_full" {{ request('status') === 'domain_full' ? 'selected' : '' }}>Domain Penuh (5/5)</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-lg text-sm font-semibold transition">
                    Filter
                </button>
                @if(request()->hasAny(['search', 'task_type', 'status']))
                    <a href="{{ route('admin.targets.index') }}" class="text-xs text-slate-500 hover:text-slate-800 underline">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Target URLs Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-600 uppercase tracking-wider">
                        <th class="py-3 px-4">URL Target & Klien</th>
                        <th class="py-3 px-4">Kategori & Tarif</th>
                        <th class="py-3 px-4">Root Domain & Kuota</th>
                        <th class="py-3 px-4">Keyword / Catatan</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Dikerjakan Oleh</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($targets as $target)
                        @php
                            $taskInfo = $taskTypes[$target->task_type ?? 'comment'] ?? $taskTypes['comment'];
                            $effectiveRate = $target->getEffectiveRate();
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3 px-4 max-w-xs">
                                <a href="{{ $target->url }}" target="_blank" rel="noopener noreferrer" class="font-medium text-emerald-700 hover:text-emerald-900 underline flex items-center gap-1.5 truncate" title="{{ $target->url }}">
                                    <span class="truncate">{{ $target->url }}</span>
                                    <svg class="w-3.5 h-3.5 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                </a>
                                @if($target->client_url)
                                    <div class="text-[11px] text-blue-600 truncate mt-0.5 flex items-center gap-1" title="Backlink Klien: {{ $target->client_url }}">
                                        <span class="font-semibold text-slate-500">Klien:</span>
                                        <a href="{{ $target->client_url }}" target="_blank" class="hover:underline truncate">{{ $target->client_url }}</a>
                                    </div>
                                @endif
                                <div class="text-[10px] text-slate-400 mt-0.5">Dibuat: {{ $target->created_at->format('d M Y, H:i') }}</div>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <span class="inline-block px-2 py-0.5 rounded text-[11px] font-semibold {{ $taskInfo['badge'] }}">
                                    {{ $taskInfo['label'] }}
                                </span>
                                <div class="font-bold text-slate-900 text-xs mt-1">
                                    Rp {{ number_format($effectiveRate, 0, ',', '.') }}
                                    @if($target->reward_amount)
                                        <span class="text-[10px] text-emerald-600 font-normal font-mono">(Khusus)</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <div class="font-semibold text-slate-800">{{ $target->root_domain }}</div>
                                @if($target->domain)
                                    <div class="text-xs mt-0.5">
                                        <span class="font-mono font-semibold {{ $target->domain->url_count >= 5 ? 'text-rose-600' : 'text-slate-600' }}">
                                            {{ $target->domain->url_count }} / 5 URL
                                        </span>
                                        @if($target->domain->is_locked)
                                            <span class="ml-1 text-[10px] px-1.5 py-0.5 bg-rose-100 text-rose-700 rounded font-semibold">Terkunci</span>
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td class="py-3 px-4 max-w-[200px]">
                                @if($target->keyword)
                                    <span class="inline-block px-2 py-0.5 bg-slate-100 text-slate-700 rounded text-xs font-medium">{{ $target->keyword }}</span>
                                @else
                                    <span class="text-slate-400 text-xs">-</span>
                                @endif
                                @if($target->notes)
                                    <div class="text-xs text-slate-500 mt-1 truncate" title="{{ $target->notes }}">{{ $target->notes }}</div>
                                @endif
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                @if($target->status === 'available')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>
                                        Tersedia
                                    </span>
                                @elseif($target->status === 'in_progress')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500 mr-1.5 animate-pulse"></span>
                                        Sedang Dikerjakan
                                    </span>
                                @elseif($target->status === 'completed')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-800">
                                        Selesai
                                    </span>
                                @elseif($target->status === 'skipped')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                                        Dilewati
                                    </span>
                                @elseif($target->status === 'domain_full')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-800">
                                        Domain Penuh (5/5)
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap text-xs text-slate-600">
                                @if($target->takenBy)
                                    <div class="font-medium text-slate-800">{{ $target->takenBy->name }}</div>
                                    <div class="text-slate-400 text-[11px]">{{ $target->taken_at?->diffForHumans() }}</div>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap text-right text-xs">
                                <form method="POST" action="{{ route('admin.targets.destroy', $target->id) }}" onsubmit="return confirm('Hapus target URL ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 transition" title="Hapus Target">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-500">
                                <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                <p class="font-medium text-slate-700">Belum ada target URL di dalam antrean.</p>
                                <p class="text-xs text-slate-400 mt-1">Gunakan tombol "+ Input Target Massal" untuk memasukkan daftar website yang ingin dikomentari.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($targets->hasPages())
            <div class="p-4 border-t border-slate-200">
                {{ $targets->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
