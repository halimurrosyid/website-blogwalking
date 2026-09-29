@extends('layouts.app')

@section('title', 'Manajemen Blogwalker & Plotting')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Manajemen Anggota Tim Blogwalker</h1>
            <p class="text-sm text-slate-500 mt-1">Atur plotting kata kunci target, backlink URL, dan tarif per komentar.</p>
        </div>
        <div>
            <a href="{{ route('admin.workers.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-sm transition">
                + Tambah Blogwalker Baru
            </a>
        </div>
    </div>

    <!-- Workers Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-[11px] uppercase font-bold tracking-wider text-slate-500 border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-3.5">Nama & Kontak</th>
                        <th class="px-6 py-3.5">Cakupan Domain</th>
                        <th class="px-6 py-3.5">Target Keyword & Backlink</th>
                        <th class="px-6 py-3.5">Tarif / Komentar</th>
                        <th class="px-6 py-3.5">Total Submit</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($workers as $worker)
                    @php $assignment = $worker->activeAssignment; @endphp
                    <tr class="hover:bg-slate-50/60 transition">
                        <!-- Worker Info -->
                        <td class="px-6 py-4">
                            <span class="font-bold text-slate-900 block text-sm">{{ $worker->name }}</span>
                            <span class="text-xs text-slate-500 block">{{ $worker->email }}</span>
                            @if($worker->phone)
                                <span class="text-[11px] text-slate-400 font-mono block">{{ $worker->phone }}</span>
                            @endif
                        </td>

                        <!-- Plotting TLD -->
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-1 max-w-xs">
                                @if(empty($assignment?->allowed_tlds))
                                    <span class="px-2.5 py-1 rounded-md bg-emerald-50 text-emerald-800 border border-emerald-200 text-xs font-semibold">✨ Bebas Semua Domain</span>
                                @else
                                    @foreach($assignment->allowed_tlds as $tld)
                                        <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 border border-slate-200 text-xs font-mono font-semibold">{{ $tld }}</span>
                                    @endforeach
                                @endif
                            </div>
                        </td>

                        <!-- Target Keyword & Link -->
                        <td class="px-6 py-4">
                            @if($assignment)
                                <span class="text-xs font-semibold text-slate-800 block line-clamp-1">{{ $assignment->target_keywords }}</span>
                                <a href="{{ $assignment->target_backlink_url }}" target="_blank" class="text-[11px] text-emerald-600 hover:underline block truncate max-w-xs font-mono">
                                    {{ $assignment->target_backlink_url }}
                                </a>
                            @else
                                <span class="text-xs text-slate-400 italic">Belum ada plotting</span>
                            @endif
                        </td>

                        <!-- Rate -->
                        <td class="px-6 py-4 font-bold text-slate-900 whitespace-nowrap text-xs">
                            Rp {{ number_format($worker->default_rate, 0, ',', '.') }}
                        </td>

                        <!-- Total Submit Stats -->
                        <td class="px-6 py-4 whitespace-nowrap text-xs">
                            <span class="font-bold text-emerald-600">{{ $worker->approved_submissions }} Disetujui</span>
                            <span class="text-slate-400 block text-[11px]">dari {{ $worker->total_submissions }} submit</span>
                        </td>

                        <!-- Status Toggle -->
                        <td class="px-6 py-4 whitespace-nowrap text-xs">
                            <form method="POST" action="{{ route('admin.workers.toggle', $worker) }}">
                                @csrf
                                <button type="submit" class="cursor-pointer">
                                    @if($worker->is_active)
                                        <span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 font-bold hover:bg-emerald-200 transition">Aktif</span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full bg-slate-200 text-slate-600 font-bold hover:bg-slate-300 transition">Nonaktif</span>
                                    @endif
                                </button>
                            </form>
                        </td>

                        <!-- Actions -->
                        <td class="px-6 py-4 text-right whitespace-nowrap">
                            <a href="{{ route('admin.workers.edit', $worker) }}" class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
                                Edit / Plotting
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-xs text-slate-400">
                            Belum ada anggota tim worker. Klik "Tambah Worker Baru" di atas.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($workers->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $workers->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
