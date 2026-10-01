@extends('layouts.app')

@section('title', 'Manajemen Blogwalker & Plotting')

@section('content')
<div class="space-y-6" x-data="{
    resetModalOpen: false,
    resetActionUrl: '',
    resetWorkerName: '',
    resetWorkerEmail: '',
    newPassword: '',
    showPassword: true,

    openResetPassword(url, name, email) {
        this.resetActionUrl = url;
        this.resetWorkerName = name;
        this.resetWorkerEmail = email;
        this.newPassword = 'BW-' + Math.random().toString(36).slice(-6) + Math.floor(Math.random() * 90 + 10);
        this.showPassword = true;
        this.resetModalOpen = true;
    },

    generatePassword() {
        this.newPassword = 'BW-' + Math.random().toString(36).slice(-6) + Math.floor(Math.random() * 90 + 10);
    }
}">

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
                            <div class="flex items-center justify-end gap-1.5">
                                <button type="button" 
                                    @click="openResetPassword('{{ route('admin.workers.reset-password', $worker) }}', '{{ addslashes($worker->name) }}', '{{ addslashes($worker->email) }}')"
                                    class="px-2.5 py-1.5 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 font-semibold text-xs transition cursor-pointer"
                                    title="Reset Password Manual">
                                    🔑 Reset Password
                                </button>
                                <a href="{{ route('admin.workers.edit', $worker) }}" class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
                                    Edit / Plotting
                                </a>
                            </div>
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

    <!-- Modal: Reset Password Manual Super Admin -->
    <div x-show="resetModalOpen" x-cloak class="fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4 backdrop-blur-2xs" @click="resetModalOpen = false">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-4" @click.stop>
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <span class="text-xl">🔑</span>
                    <h3 class="font-bold text-slate-900 text-base">Reset Password Manual</h3>
                </div>
                <button type="button" @click="resetModalOpen = false" class="text-slate-400 hover:text-slate-600 font-bold text-lg cursor-pointer">&times;</button>
            </div>

            <div class="bg-slate-50 p-3 rounded-xl border border-slate-200 text-xs">
                <div>Pengguna: <strong class="text-slate-900" x-text="resetWorkerName"></strong></div>
                <div class="text-slate-500 font-mono mt-0.5" x-text="resetWorkerEmail"></div>
            </div>

            <form :action="resetActionUrl" method="POST" class="space-y-4">
                @csrf
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label for="new_password" class="block text-xs font-semibold text-slate-700">
                            Password Baru (Min. 8 Karakter) <span class="text-rose-500">*</span>
                        </label>
                        <button type="button" @click="generatePassword()" class="text-[11px] font-bold text-emerald-600 hover:text-emerald-700 cursor-pointer">
                            🎲 Buat Acak
                        </button>
                    </div>
                    <div class="relative">
                        <input :type="showPassword ? 'text' : 'password'" name="password" id="new_password" x-model="newPassword" required minlength="8" placeholder="Masukkan password baru..."
                            class="w-full text-sm font-mono border-slate-300 rounded-xl px-3.5 py-2.5 pr-20 focus:ring-amber-500 focus:border-amber-500">
                        <button type="button" @click="showPassword = !showPassword" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-[11px] font-semibold text-slate-500 hover:text-slate-700 bg-slate-100 px-2 py-1 rounded-md">
                            <span x-text="showPassword ? 'Sembunyi' : 'Lihat'"></span>
                        </button>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">Super Admin dapat menyalin password ini untuk diberikan langsung kepada pengguna.</p>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="resetModalOpen = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-amber-600 hover:bg-amber-700 text-white shadow-xs cursor-pointer">
                        Simpan Password Baru
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
