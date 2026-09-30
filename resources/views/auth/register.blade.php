@extends('layouts.app')

@section('title', 'Pendaftaran Blogwalker Baru')

@section('content')
<div class="max-w-2xl mx-auto py-6" x-data="{
    ktpPreview: null,
    bankBookPreview: null,
    handleFileChange(event, type) {
        const file = event.target.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = (e) => {
            if (type === 'ktp') this.ktpPreview = e.target.result;
            if (type === 'bank_book') this.bankBookPreview = e.target.result;
        };
        reader.readAsDataURL(file);
    }
}">
    <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-200">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-emerald-600 text-white font-bold text-2xl mb-3 shadow-md shadow-emerald-100">
                BW
            </div>
            <h1 class="text-2xl font-bold text-slate-900">Form Pendaftaran Blogwalker</h1>
            <p class="text-sm text-slate-500 mt-1">
                Lengkapi formulir identitas dan data rekening bank Anda untuk bergabung dengan tim Link Building kami.
            </p>
        </div>

        <form method="POST" action="{{ route('register.post') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- Section 1: Informasi Akun & Kontak -->
            <div class="border-b border-slate-100 pb-5">
                <h2 class="text-sm font-bold uppercase tracking-wider text-slate-800 flex items-center gap-2 mb-4">
                    <span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 text-xs font-bold flex items-center justify-center">1</span>
                    Data Diri & Akun
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Nama Lengkap -->
                    <div class="sm:col-span-2">
                        <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">Nama Lengkap (Sesuai KTP) *</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            placeholder="Contoh: Muhammad Rizki Pratama">
                    </div>

                    <!-- Username -->
                    <div>
                        <label for="username" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">Username *</label>
                        <input type="text" name="username" id="username" value="{{ old('username') }}" required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none font-mono"
                            placeholder="rizki_seo">
                        <span class="text-[11px] text-slate-400 mt-0.5 block">Huruf, angka, tanda - atau _</span>
                    </div>

                    <!-- No WhatsApp / HP -->
                    <div>
                        <label for="phone" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">No. WhatsApp / HP Aktif *</label>
                        <input type="text" name="phone" id="phone" value="{{ old('phone') }}" required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            placeholder="081234567890">
                    </div>

                    <!-- Email -->
                    <div class="sm:col-span-2">
                        <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">Alamat Email *</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            placeholder="nama@gmail.com">
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">Password *</label>
                        <div x-data="{ showPass: false }" class="relative">
                            <input :type="showPass ? 'text' : 'password'" name="password" id="password" required minlength="8"
                                class="w-full px-3.5 pr-10 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                                placeholder="Minimal 8 karakter">
                            <button type="button" @click="showPass = !showPass" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none cursor-pointer" tabindex="-1" title="Lihat/Sembunyikan Password">
                                <svg x-show="showPass" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg x-show="!showPass" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Konfirmasi Password -->
                    <div>
                        <label for="password_confirmation" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">Konfirmasi Password *</label>
                        <div x-data="{ showPass: false }" class="relative">
                            <input :type="showPass ? 'text' : 'password'" name="password_confirmation" id="password_confirmation" required minlength="8"
                                class="w-full px-3.5 pr-10 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                                placeholder="Ulangi password">
                            <button type="button" @click="showPass = !showPass" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none cursor-pointer" tabindex="-1" title="Lihat/Sembunyikan Password">
                                <svg x-show="showPass" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg x-show="!showPass" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 2: Informasi Bank Konvensional -->
            <div class="border-b border-slate-100 pb-5">
                <h2 class="text-sm font-bold uppercase tracking-wider text-slate-800 flex items-center gap-2 mb-4">
                    <span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 text-xs font-bold flex items-center justify-center">2</span>
                    Rekening Pembayaran Gaji (Bank Konvensional)
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Pilihan Bank -->
                    <div class="sm:col-span-2">
                        <label for="bank_name" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">Pilih Bank Konvensional *</label>
                        <select name="bank_name" id="bank_name" required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm bg-white font-medium focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            <option value="">-- Pilih Nama Bank --</option>
                            @foreach($banks as $key => $bankName)
                                <option value="{{ $key }}" {{ old('bank_name') === $key ? 'selected' : '' }}>
                                    {{ $bankName }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Nomor Rekening -->
                    <div>
                        <label for="bank_account_number" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">Nomor Rekening *</label>
                        <input type="text" name="bank_account_number" id="bank_account_number" value="{{ old('bank_account_number') }}" required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none font-mono"
                            placeholder="Contoh: 1234567890">
                    </div>

                    <!-- Atas Nama Rekening -->
                    <div>
                        <label for="bank_account_name" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">Atas Nama Rekening *</label>
                        <input type="text" name="bank_account_name" id="bank_account_name" value="{{ old('bank_account_name') }}" required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                            placeholder="Harus sama dengan KTP">
                    </div>
                </div>
            </div>

            <!-- Section 3: Upload Dokumen Verifikasi -->
            <div class="pb-2">
                <h2 class="text-sm font-bold uppercase tracking-wider text-slate-800 flex items-center gap-2 mb-4">
                    <span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 text-xs font-bold flex items-center justify-center">3</span>
                    Upload Foto Dokumen Verifikasi
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <!-- Foto KTP -->
                    <div class="space-y-2">
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700">Foto KTP Asli *</label>
                        <div class="border-2 border-dashed border-slate-300 hover:border-emerald-500 rounded-xl p-4 text-center transition bg-slate-50">
                            <template x-if="ktpPreview">
                                <div class="relative mb-2">
                                    <img :src="ktpPreview" alt="KTP Preview" class="max-h-40 mx-auto rounded-lg shadow-xs border border-slate-200 object-cover">
                                </div>
                            </template>
                            <template x-if="!ktpPreview">
                                <div class="py-4 text-slate-400">
                                    <svg class="w-8 h-8 mx-auto text-slate-300 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                                    <span class="text-xs">Format: JPG, PNG, WebP (Maks. 5MB)</span>
                                </div>
                            </template>
                            <input type="file" name="id_card_photo" required accept="image/*" @change="handleFileChange($event, 'ktp')"
                                class="block w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer">
                        </div>
                    </div>

                    <!-- Foto Buku Rekening -->
                    <div class="space-y-2">
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700">Foto Buku Rekening / M-Banking *</label>
                        <div class="border-2 border-dashed border-slate-300 hover:border-emerald-500 rounded-xl p-4 text-center transition bg-slate-50">
                            <template x-if="bankBookPreview">
                                <div class="relative mb-2">
                                    <img :src="bankBookPreview" alt="Buku Rekening Preview" class="max-h-40 mx-auto rounded-lg shadow-xs border border-slate-200 object-cover">
                                </div>
                            </template>
                            <template x-if="!bankBookPreview">
                                <div class="py-4 text-slate-400">
                                    <svg class="w-8 h-8 mx-auto text-slate-300 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                    <span class="text-xs">Bagian nomor rekening terlihat jelas</span>
                                </div>
                            </template>
                            <input type="file" name="bank_book_photo" required accept="image/*" @change="handleFileChange($event, 'bank_book')"
                                class="block w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notice Box -->
            <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs flex items-start gap-2.5">
                <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                <div class="leading-relaxed">
                    <strong>Penting:</strong> Setelah formulir ini dikirim, akun Anda akan diverifikasi secara manual oleh Super Admin. Setelah Super Admin menyetujui (approve) pendaftaran Anda dan menentukan plotting target domain, Anda akan dapat login dan langsung mulai mengerjakan tugas.
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="w-full py-3.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm shadow-md shadow-emerald-100 transition cursor-pointer">
                Kirim Pendaftaran Blogwalker
            </button>

            <!-- Back to Login -->
            <div class="text-center pt-2">
                <span class="text-xs text-slate-500">Sudah memiliki akun? </span>
                <a href="{{ route('login') }}" class="text-xs font-bold text-emerald-600 hover:underline">
                    Masuk ke Sistem
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
