@extends('layouts.app')

@section('title', 'Rekap Gaji & Payroll')

@section('content')
<div class="space-y-6" x-data="{ payModalOpen: false, payActionUrl: '', workerName: '', payAmount: 0, payCount: 0 }">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Rekap Gaji & Pembayaran Worker</h1>
            <p class="text-sm text-slate-500 mt-1">Daftar komisi komentar yang disetujui dan siap ditransfer ke anggota tim.</p>
        </div>
    </div>

    <!-- Unpaid Summary Cards -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-base font-bold text-slate-900">Komentar Disetujui Siap Bayar (Unpaid)</h2>
            <span class="text-xs px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 font-bold">
                {{ $unpaidSummaries->count() }} Blogwalker Menunggu Pencairan
            </span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-[11px] uppercase font-bold tracking-wider text-slate-500 border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-3.5">Nama Blogwalker</th>
                        <th class="px-6 py-3.5">Kontak / No. Rekening</th>
                        <th class="px-6 py-3.5 text-center">Jumlah Komentar Approved</th>
                        <th class="px-6 py-3.5">Tarif Satuan</th>
                        <th class="px-6 py-3.5 font-bold">Total Rupiah yang Harus Ditransfer</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($unpaidSummaries as $worker)
                    <tr class="hover:bg-slate-50/60 transition">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="font-bold text-slate-900 text-sm block">{{ $worker->name }}</span>
                            <span class="text-xs text-slate-500">{{ $worker->email }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-600">
                            {{ $worker->phone ?: 'Belum diisi' }}
                        </td>
                        <td class="px-6 py-4 text-center whitespace-nowrap">
                            <span class="px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 font-bold text-xs border border-emerald-200">
                                {{ $worker->unpaid_count }} Komentar
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-500">
                            Rp {{ number_format($worker->default_rate, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap font-black text-emerald-700 text-base">
                            Rp {{ number_format($worker->unpaid_total, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 text-right whitespace-nowrap">
                            <button type="button" @click="
                                payActionUrl = '{{ route('admin.payouts.process', $worker) }}';
                                workerName = '{{ $worker->name }}';
                                payAmount = '{{ number_format($worker->unpaid_total, 0, ',', '.') }}';
                                payCount = '{{ $worker->unpaid_count }}';
                                payModalOpen = true;
                            " class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition cursor-pointer">
                                💸 Tandai Sudah Ditransfer
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-xs text-slate-400">
                            ✨ Semua komentar disetujui sudah lunas dibayarkan!
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Payout History Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h2 class="text-base font-bold text-slate-900">Riwayat Pembayaran yang Pernah Diproses</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-[11px] uppercase font-bold tracking-wider text-slate-500 border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-3.5">Tanggal Transfer</th>
                        <th class="px-6 py-3.5">Worker Penerima</th>
                        <th class="px-6 py-3.5 text-center">Jumlah Komentar</th>
                        <th class="px-6 py-3.5">Metode Pembayaran</th>
                        <th class="px-6 py-3.5">Catatan</th>
                        <th class="px-6 py-3.5">Diproses Oleh</th>
                        <th class="px-6 py-3.5 text-right">Total Ditransfer</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($payoutHistory as $pay)
                    <tr class="hover:bg-slate-50/60 transition">
                        <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-500">
                            {{ $pay->paid_at->format('d M Y, H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap font-bold text-slate-900 text-xs">
                            {{ $pay->user->name }}
                        </td>
                        <td class="px-6 py-4 text-center whitespace-nowrap text-xs">
                            {{ $pay->total_submissions }} Komentar
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-xs">
                            <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 font-semibold">
                                {{ $pay->payment_method ?: 'Transfer Bank' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-xs text-slate-500">
                            {{ $pay->notes ?: '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-400">
                            {{ $pay->processor?->name ?? 'Admin' }}
                        </td>
                        <td class="px-6 py-4 text-right whitespace-nowrap font-bold text-slate-900 text-sm">
                            Rp {{ number_format($pay->total_amount, 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-xs text-slate-400">
                            Belum ada histori pembayaran yang dicatat.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($payoutHistory->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $payoutHistory->links() }}
            </div>
        @endif
    </div>

    <!-- Confirm Payout Modal -->
    <div x-show="payModalOpen" x-cloak class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">
        <div class="bg-white max-w-md w-full rounded-2xl p-6 shadow-xl border border-slate-200" @click.outside="payModalOpen = false">
            <h3 class="text-base font-bold text-slate-900 mb-1">Konfirmasi Pembayaran Gaji</h3>
            <p class="text-xs text-slate-500 mb-4">
                Catat bahwa Anda telah mentransfer gaji ke rekening worker berikut:
            </p>

            <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 mb-4 text-xs text-emerald-900 space-y-1">
                <div>Nama Worker: <b x-text="workerName"></b></div>
                <div>Jumlah Komentar: <b x-text="payCount + ' komentar'"></b></div>
                <div class="text-sm font-bold text-emerald-700 pt-1">
                    Total Nominal: Rp <span x-text="payAmount"></span>
                </div>
            </div>

            <form :action="payActionUrl" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Metode Transfer / Rekening</label>
                    <input type="text" name="payment_method" placeholder="Contoh: BCA / Mandiri / Dana / Gopay"
                        class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Catatan Tambahan (Opsional)</label>
                    <input type="text" name="notes" placeholder="Contoh: Transfer batch minggu ke-1 September"
                        class="w-full px-3 py-2 text-xs border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="payModalOpen = false" class="px-4 py-2 rounded-xl text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 text-white">
                        Konfirmasi & Tandai Lunas
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
