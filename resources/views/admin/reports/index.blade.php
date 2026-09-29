@extends('layouts.app')

@section('title', 'Laporan & Kinerja Tim Blogwalker')

@section('content')
<div class="space-y-6">

    <!-- Top Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Laporan & Kinerja Tim Blogwalker</h1>
            <p class="text-sm text-slate-500 mt-1">
                Pantau riwayat pencapaian target bulanan, tingkat persetujuan (approval rate), dan estimasi gaji seluruh blogwalker.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.reports.export', ['period_id' => $selectedPeriodId, 'worker_id' => $selectedWorkerId]) }}" 
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-xs transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Export Laporan CSV
            </a>
        </div>
    </div>

    <!-- Filter Bar (Periode & Blogwalker) -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('admin.reports.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
            <!-- Period Selector -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Pilih Periode</label>
                <select name="period_id" onchange="this.form.submit()" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 bg-white font-medium focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    <option value="all" {{ $selectedPeriodId === 'all' ? 'selected' : '' }}>-- Seluruh Periode (Akumulasi) --</option>
                    @foreach($periods as $p)
                        <option value="{{ $p->id }}" {{ $selectedPeriodId == $p->id ? 'selected' : '' }}>
                            {{ $p->name }} {{ $p->is_active ? '🟢 (Sedang Berjalan)' : '⚪ (Telah Selesai)' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Worker Selector -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Filter Blogwalker</label>
                <select name="worker_id" onchange="this.form.submit()" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 bg-white font-medium focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    <option value="">Semua Blogwalker</option>
                    @foreach($workers as $w)
                        <option value="{{ $w->id }}" {{ $selectedWorkerId == $w->id ? 'selected' : '' }}>
                            {{ $w->name }} ({{ $w->email }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Period Info Badge -->
            <div class="sm:col-span-2 flex items-center justify-start lg:justify-end gap-2 text-xs">
                @if($selectedPeriod)
                    <div class="px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full {{ $selectedPeriod->is_active ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400' }}"></span>
                        <span class="text-slate-600 font-medium">
                            Periode: <strong class="text-slate-900">{{ $selectedPeriod->name }}</strong>
                            ({{ $selectedPeriod->starts_at->format('d M') }} - {{ $selectedPeriod->ends_at->format('d M Y') }})
                        </span>
                        <span class="ml-2 px-2 py-0.5 rounded-md text-[11px] font-bold {{ $selectedPeriod->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700' }}">
                            {{ $selectedPeriod->is_active ? 'Sedang Aktif' : 'Telah Ditutup' }}
                        </span>
                    </div>
                @else
                    <div class="px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-600 font-medium">
                        Menampilkan rekapan seluruh periode historis
                    </div>
                @endif
            </div>
        </form>
    </div>

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Submissions Card -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Submissions</span>
                <span class="p-2 rounded-xl bg-blue-50 text-blue-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </span>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-black text-slate-900">{{ number_format($totalSubmissions) }}</span>
                <span class="text-xs text-slate-500 font-medium">link</span>
            </div>
            <div class="mt-2 text-xs text-slate-500 flex items-center gap-3">
                <span class="text-emerald-600 font-semibold">{{ $approvedCount }} Disetujui</span>
                <span>&bull;</span>
                <span class="text-amber-600 font-semibold">{{ $pendingCount }} Pending</span>
                <span>&bull;</span>
                <span class="text-rose-600 font-semibold">{{ $rejectedCount }} Ditolak</span>
            </div>
        </div>

        <!-- Approval Rate Card -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Tingkat Persetujuan</span>
                <span class="p-2 rounded-xl bg-emerald-50 text-emerald-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-black text-slate-900">{{ $approvalRate }}%</span>
                <span class="text-xs text-slate-500 font-medium">approval rate</span>
            </div>
            <div class="mt-2">
                <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                    <div class="bg-emerald-500 h-2 rounded-full" style="width: {{ $approvalRate }}%"></div>
                </div>
            </div>
        </div>

        <!-- Total Payout / Gaji Card -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Biaya Gaji</span>
                <span class="p-2 rounded-xl bg-amber-50 text-amber-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </span>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-black text-slate-900">Rp {{ number_format($totalEarnings, 0, ',', '.') }}</span>
            </div>
            <div class="mt-2 text-xs flex items-center justify-between">
                <span class="text-blue-600 font-semibold">Cair: Rp {{ number_format($totalPaid, 0, ',', '.') }}</span>
                <span class="text-amber-600 font-semibold">Sisa: Rp {{ number_format($totalUnpaid, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- Tim Qualification Card -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Kualifikasi Tim</span>
                <span class="p-2 rounded-xl bg-purple-50 text-purple-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </span>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-black text-emerald-600">{{ $qualifiedCount }}</span>
                <span class="text-xs text-slate-500 font-medium">lolos / tercapai</span>
            </div>
            <div class="mt-2 text-xs text-slate-500 flex items-center justify-between">
                <span>Total Tim: <strong class="text-slate-800">{{ count($workerReports) }} Blogwalker</strong></span>
                @if($disqualifiedCount > 0)
                    <span class="text-rose-600 font-bold">{{ $disqualifiedCount }} Tereliminasi</span>
                @endif
            </div>
        </div>
    </div>

    <!-- Blogwalker Performance Breakdown Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h2 class="text-base font-bold text-slate-900">Performa Detail Masing-Masing Blogwalker</h2>
                <p class="text-xs text-slate-500 mt-0.5">Monitoring target perorangan, persentase kelulusan, dan akumulasi penghasilan.</p>
            </div>
            <span class="text-xs font-semibold px-3 py-1 rounded-full bg-slate-100 text-slate-700">
                Total: {{ count($workerReports) }} Orang
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-[11px] uppercase font-bold tracking-wider text-slate-500 border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-3.5">Blogwalker</th>
                        <th class="px-6 py-3.5">Plotting Ekstensi Domain</th>
                        <th class="px-6 py-3.5 text-center">Progress Target Minimal</th>
                        <th class="px-6 py-3.5 text-center">Statistik Submit</th>
                        <th class="px-6 py-3.5">Status Periode</th>
                        <th class="px-6 py-3.5 text-right">Penghasilan (Rp)</th>
                        <th class="px-6 py-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($workerReports as $row)
                    <tr class="hover:bg-slate-50/60 transition">
                        <!-- Worker Profile -->
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-emerald-100 text-emerald-800 font-bold flex items-center justify-center text-sm shrink-0">
                                    {{ strtoupper(substr($row['worker']->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900 text-sm">{{ $row['worker']->name }}</div>
                                    <div class="text-xs text-slate-500">{{ $row['worker']->email }}</div>
                                </div>
                            </div>
                        </td>

                        <!-- Plotting TLDs -->
                        <td class="px-6 py-4">
                            <div class="font-mono text-xs text-slate-800 bg-slate-100 px-2.5 py-1 rounded-md inline-block max-w-xs truncate font-semibold">
                                {{ $row['tlds_display'] }}
                            </div>
                            @if($row['assignment'] && $row['assignment']->target_keywords)
                                <div class="text-[11px] text-slate-400 mt-1 truncate max-w-xs">
                                    Key: {{ implode(', ', $row['assignment']->target_keywords) }}
                                </div>
                            @endif
                        </td>

                        <!-- Progress Bar & Target -->
                        <td class="px-6 py-4 text-center">
                            <div class="w-36 mx-auto">
                                <div class="flex justify-between text-xs font-semibold mb-1">
                                    <span class="text-slate-900">{{ $row['approved'] }} / {{ $row['min_target'] }}</span>
                                    <span class="{{ $row['progress_percent'] >= 100 ? 'text-emerald-600 font-bold' : 'text-slate-500' }}">{{ $row['progress_percent'] }}%</span>
                                </div>
                                <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                                    <div class="h-2 rounded-full {{ $row['progress_percent'] >= 100 ? 'bg-emerald-500' : 'bg-amber-500' }}" style="width: {{ $row['progress_percent'] }}%"></div>
                                </div>
                            </div>
                        </td>

                        <!-- Submissions Breakdown -->
                        <td class="px-6 py-4 text-center whitespace-nowrap">
                            <div class="inline-flex items-center gap-2 text-xs font-medium">
                                <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 font-bold" title="Total Submit">
                                    {{ $row['total'] }}
                                </span>
                                <span class="px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 font-bold" title="Disetujui">
                                    ✓ {{ $row['approved'] }}
                                </span>
                                <span class="px-2 py-0.5 rounded-md bg-amber-50 text-amber-700 font-semibold" title="Pending">
                                    ⏳ {{ $row['pending'] }}
                                </span>
                                <span class="px-2 py-0.5 rounded-md bg-rose-50 text-rose-700 font-semibold" title="Ditolak">
                                    ✗ {{ $row['rejected'] }}
                                </span>
                            </div>
                        </td>

                        <!-- Qualification Status -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($row['status_badge'] === 'emerald')
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                                    {{ $row['status_label'] }}
                                </span>
                            @elseif($row['status_badge'] === 'purple')
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-800">
                                    {{ $row['status_label'] }}
                                </span>
                            @elseif($row['status_badge'] === 'rose')
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800">
                                    {{ $row['status_label'] }}
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                                    {{ $row['status_label'] }}
                                </span>
                            @endif
                        </td>

                        <!-- Earnings -->
                        <td class="px-6 py-4 text-right whitespace-nowrap">
                            <div class="font-bold text-slate-900 text-sm">
                                Rp {{ number_format($row['earnings'], 0, ',', '.') }}
                            </div>
                            <div class="text-[11px] text-slate-400 mt-0.5">
                                Cair: Rp {{ number_format($row['paid'], 0, ',', '.') }}
                            </div>
                        </td>

                        <!-- Actions -->
                        <td class="px-6 py-4 text-center whitespace-nowrap">
                            <a href="{{ route('admin.reviews.index', ['worker_id' => $row['worker']->id]) }}" 
                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition"
                                title="Lihat daftar submission komentar blogwalker ini">
                                Review Submissions
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-xs text-slate-400">
                            Belum ada blogwalker terdaftar dalam sistem.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
