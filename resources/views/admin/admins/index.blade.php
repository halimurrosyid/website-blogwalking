@extends('layouts.app')

@section('title', 'Manajemen Super Admin')

@section('content')
<div class="space-y-6" x-data="{
    resetModalOpen: false,
    resetActionUrl: '',
    resetAdminName: '',
    resetAdminEmail: '',
    newPassword: '',
    newPasswordConfirmation: '',
    showPassword: true,

    openResetPassword(url, name, email) {
        this.resetActionUrl = url;
        this.resetAdminName = name;
        this.resetAdminEmail = email;
        const generated = 'ADM-' + Math.random().toString(36).slice(-6) + Math.floor(Math.random() * 90 + 10);
        this.newPassword = generated;
        this.newPasswordConfirmation = generated;
        this.showPassword = true;
        this.resetModalOpen = true;
    },

    generatePassword() {
        const generated = 'ADM-' + Math.random().toString(36).slice(-6) + Math.floor(Math.random() * 90 + 10);
        this.newPassword = generated;
        this.newPasswordConfirmation = generated;
    }
}">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="p-1.5 bg-slate-900 text-white rounded-lg">
                    <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1.323l3.954 1.582 1.599-.8a1 1 0 011.378 1.157l-.872 3.49 2.062 1.032a1 1 0 01.379 1.341l-2 3.464a1 1 0 01-1.287.439l-3.328-1.331-2.906 2.325V18a1 1 0 11-2 0v-1.978l-2.906-2.325-3.328 1.331a1 1 0 01-1.287-.439l-2-3.464a1 1 0 01.379-1.341l2.062-1.032-.872-3.49A1 1 0 013.447 5.1l1.599.8L9 4.323V3a1 1 0 011-1z" clip-rule="evenodd"></path></svg>
                </span>
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Manajemen Akun Super Admin</h1>
            </div>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Kelola hak akses istimewa, buat akun administrator baru, dan atur kredensial keamanan sistem.</p>
        </div>
        <div>
            <a href="{{ route('admin.admins.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-sm transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>+ Tambah Super Admin Baru</span>
            </a>
        </div>
    </div>

    <!-- Alert Keamanan -->
    <div class="p-4 bg-slate-900 text-white rounded-2xl text-xs flex items-start gap-3 shadow-xs">
        <svg class="w-5 h-5 text-amber-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        <div class="leading-relaxed">
            <span class="font-bold text-emerald-400">Pemberitahuan Hak Akses:</span> Seluruh akun yang terdaftar di halaman ini memiliki kewenangan penuh (Super Admin) untuk mengelola data target, menyetujui tugas, mengakses database SEO, serta memproses pembayaran payroll. Pastikan hanya memberikan hak akses kepada personil terpercaya.
        </div>
    </div>

    <!-- Table Admins -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-[11px] uppercase font-bold tracking-wider text-slate-500 border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-4">Administrator</th>
                        <th class="px-6 py-4">Email & Kontak</th>
                        <th class="px-6 py-4">Status Akun</th>
                        <th class="px-6 py-4">Terdaftar</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($admins as $adm)
                    @php $isSelf = ($adm->id === auth()->id()); @endphp
                    <tr class="hover:bg-slate-50/70 transition {{ $isSelf ? 'bg-emerald-50/20' : '' }}">
                        <!-- Admin Info -->
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl flex items-center justify-center font-bold text-xs uppercase shadow-xs {{ $isSelf ? 'bg-emerald-600 text-white' : 'bg-slate-800 text-white' }}">
                                    {{ substr($adm->name, 0, 2) }}
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900 flex items-center gap-1.5">
                                        <span>{{ $adm->name }}</span>
                                        @if($isSelf)
                                            <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-bold text-[10px]">Akun Anda</span>
                                        @endif
                                    </div>
                                    <div class="text-[11px] text-slate-400">Super Administrator</div>
                                </div>
                            </div>
                        </td>

                        <!-- Contact -->
                        <td class="px-6 py-4">
                            <div class="space-y-0.5">
                                <div class="font-mono text-slate-800 font-semibold">{{ $adm->email }}</div>
                                <div class="text-[11px] text-slate-400">{{ $adm->phone ?? 'Tidak ada nomor telepon' }}</div>
                            </div>
                        </td>

                        <!-- Status -->
                        <td class="px-6 py-4">
                            @if($adm->is_active)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 font-bold text-[10px]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                    <span>Aktif</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-rose-100 text-rose-800 font-bold text-[10px]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span>
                                    <span>Nonaktif</span>
                                </span>
                            @endif
                        </td>

                        <!-- Created At -->
                        <td class="px-6 py-4 text-slate-500 text-[11px]">
                            {{ $adm->created_at ? $adm->created_at->translatedFormat('d M Y') : '-' }}
                        </td>

                        <!-- Actions -->
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <!-- Edit -->
                                <a href="{{ route('admin.admins.edit', $adm) }}" class="p-1.5 text-slate-500 hover:text-emerald-700 hover:bg-emerald-50 rounded-lg transition" title="Edit Admin">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>

                                <!-- Reset Password -->
                                <button type="button" @click="openResetPassword('{{ route('admin.admins.reset-password', $adm) }}', '{{ addslashes($adm->name) }}', '{{ $adm->email }}')" class="p-1.5 text-slate-500 hover:text-amber-700 hover:bg-amber-50 rounded-lg transition" title="Reset Password">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                                </button>

                                @if(! $isSelf)
                                    <!-- Toggle Active -->
                                    <form action="{{ route('admin.admins.toggle', $adm) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin mengubah status akun {{ $adm->name }}?')">
                                        @csrf
                                        <button type="submit" class="p-1.5 text-slate-500 hover:text-blue-700 hover:bg-blue-50 rounded-lg transition" title="{{ $adm->is_active ? 'Nonaktifkan Akun' : 'Aktifkan Akun' }}">
                                            @if($adm->is_active)
                                                <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            @else
                                                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                            @endif
                                        </button>
                                    </form>

                                    <!-- Delete -->
                                    <form action="{{ route('admin.admins.destroy', $adm) }}" method="POST" class="inline" onsubmit="return confirm('PERINGATAN: Apakah Anda yakin ingin MENGHAPUS PERMANEN akun Super Admin [{{ $adm->name }}]? Tindakan ini tidak dapat dibatalkan!')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition" title="Hapus Akun Super Admin">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-slate-400">
                            Belum ada akun Super Admin terdaftar.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($admins->hasPages())
        <div class="p-4 border-t border-slate-100 bg-slate-50">
            {{ $admins->links() }}
        </div>
        @endif
    </div>

    <!-- Modal Reset Password -->
    <div x-show="resetModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="resetModalOpen" @click="resetModalOpen = false" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="resetModalOpen" class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-200">
                <form :action="resetActionUrl" method="POST">
                    @csrf
                    <div class="p-6 space-y-4">
                        <div class="flex items-center gap-3">
                            <span class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-lg shrink-0">
                                🔑
                            </span>
                            <div>
                                <h3 class="text-base font-bold text-slate-900">Reset Password Super Admin</h3>
                                <p class="text-xs text-slate-500" x-text="resetAdminName + ' (' + resetAdminEmail + ')'"></p>
                            </div>
                        </div>

                        <div class="space-y-3 pt-2 text-xs">
                            <div>
                                <div class="flex justify-between items-center mb-1">
                                    <label class="font-bold text-slate-700">Password Baru:</label>
                                    <button type="button" @click="generatePassword()" class="text-emerald-600 hover:text-emerald-700 font-semibold cursor-pointer">
                                        ⚡ Acak Password Kuat
                                    </button>
                                </div>
                                <div class="relative">
                                    <input :type="showPassword ? 'text' : 'password'" name="password" x-model="newPassword" required minlength="6"
                                        class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 font-mono text-xs pr-10">
                                    <button type="button" @click="showPassword = !showPassword" class="absolute right-2.5 top-2.5 text-slate-400 hover:text-slate-600">
                                        <svg x-show="!showPassword" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        <svg x-show="showPassword" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                    </button>
                                </div>
                            </div>

                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Konfirmasi Password Baru:</label>
                                <input :type="showPassword ? 'text' : 'password'" name="password_confirmation" x-model="newPasswordConfirmation" required minlength="6"
                                    class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 font-mono text-xs">
                            </div>
                        </div>
                    </div>

                    <div class="p-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-2">
                        <button type="button" @click="resetModalOpen = false" class="px-4 py-2 bg-white border border-slate-200 text-slate-700 font-semibold rounded-xl text-xs hover:bg-slate-100 transition">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs shadow-xs transition">
                            Simpan Password Baru
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
