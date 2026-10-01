@extends('layouts.app')

@section('title', 'Edit Super Admin')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <!-- Header Section -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.admins.index') }}" class="p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Edit Super Admin</h1>
            </div>
            <p class="text-xs sm:text-sm text-slate-500 mt-1 pl-9">Perbarui data profil, kontak, status akses, atau kata sandi akun.</p>
        </div>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 sm:p-8" x-data="{
        changePassword: false,
        password: '',
        passwordConfirmation: '',
        showPass: false
    }">
        <form action="{{ route('admin.admins.update', $admin) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <!-- Nama Lengkap -->
            <div>
                <label for="name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Nama Lengkap Administrator <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="name" id="name" value="{{ old('name', $admin->name) }}" required
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition @error('name') border-rose-500 @enderror">
                @error('name')
                    <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Alamat Email Login <span class="text-rose-500">*</span>
                </label>
                <input type="email" name="email" id="email" value="{{ old('email', $admin->email) }}" required
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition @error('email') border-rose-500 @enderror">
                @error('email')
                    <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Nomor Telepon / WhatsApp -->
            <div>
                <label for="phone" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Nomor WhatsApp / HP (Opsional)
                </label>
                <input type="text" name="phone" id="phone" value="{{ old('phone', $admin->phone) }}"
                    placeholder="Contoh: 081234567890"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition @error('phone') border-rose-500 @enderror">
                @error('phone')
                    <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Toggle Ganti Password -->
            <div class="pt-2">
                <button type="button" @click="changePassword = !changePassword" class="text-xs font-bold text-emerald-700 hover:text-emerald-800 flex items-center gap-1.5 cursor-pointer">
                    <span x-text="changePassword ? '− Batalkan Ganti Password' : '+ Ingin Mengganti Password?'"></span>
                </button>
            </div>

            <!-- Password Fields (Shown only if changePassword is true) -->
            <div x-show="changePassword" x-cloak class="p-4 bg-slate-50 rounded-2xl border border-slate-200/80 space-y-4">
                <span class="text-xs font-bold text-slate-900 block">Ubah Password Akun</span>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="password" class="block text-[11px] font-bold text-slate-600 mb-1">
                            Password Baru (Minimal 6 karakter)
                        </label>
                        <div class="relative">
                            <input :type="showPass ? 'text' : 'password'" name="password" id="password" x-model="password" :required="changePassword" minlength="6"
                                placeholder="Kosongkan jika tidak diubah"
                                class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-mono text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 pr-10 @error('password') border-rose-500 @enderror">
                            <button type="button" @click="showPass = !showPass" class="absolute right-2.5 top-2 text-slate-400 hover:text-slate-600">
                                <svg x-show="!showPass" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg x-show="showPass" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                            </button>
                        </div>
                        @error('password')
                            <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-[11px] font-bold text-slate-600 mb-1">
                            Ulangi Password Baru
                        </label>
                        <input :type="showPass ? 'text' : 'password'" name="password_confirmation" id="password_confirmation" x-model="passwordConfirmation" :required="changePassword" minlength="6"
                            placeholder="Ulangi password"
                            class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-mono text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                </div>
            </div>

            <!-- Status Aktif -->
            @php $isSelf = ($admin->id === auth()->id()); @endphp
            <div class="flex items-center gap-3 p-3.5 bg-white rounded-xl border border-slate-200">
                <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $admin->is_active) ? 'checked' : '' }}
                    {{ $isSelf ? 'disabled' : '' }}
                    class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300">
                <label for="is_active" class="text-xs text-slate-700 select-none">
                    <span class="font-bold">Status Akun Aktif</span>
                    @if($isSelf)
                        <span class="block text-[11px] text-amber-600 font-semibold">Anda tidak dapat menonaktifkan akun yang sedang digunakan saat ini.</span>
                    @else
                        <span class="block text-[11px] text-slate-400">Jika dinonaktifkan, akun ini tidak akan bisa login ke dalam sistem.</span>
                    @endif
                </label>
            </div>

            <!-- Submit Buttons -->
            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                <a href="{{ route('admin.admins.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50 transition">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-sm transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Perbarui Super Admin</span>
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
