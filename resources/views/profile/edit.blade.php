@extends('layouts.app')

@section('title', 'Profil & Pengaturan Akun')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-200">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Profil & Pengaturan Akun</h1>
            <p class="text-sm text-slate-500 mt-0.5">Kelola identitas diri, data rekening penggajian, dan keamanan password Anda.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider {{ $user->isAdmin() ? 'bg-purple-100 text-purple-800 border border-purple-200' : 'bg-emerald-100 text-emerald-800 border border-emerald-200' }}">
                Role: {{ $user->isAdmin() ? 'Super Admin' : 'Blogwalker' }}
            </span>
        </div>
    </div>

    <!-- Active Pending Change Request Banner (Blogwalker) -->
    @if(isset($pendingRequest) && $pendingRequest)
        <div class="rounded-2xl border border-amber-300 bg-amber-50/80 p-5 shadow-xs space-y-3">
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-amber-500 text-white flex items-center justify-center font-bold text-lg shrink-0">
                        ⏳
                    </div>
                    <div>
                        <h3 class="font-bold text-amber-900 text-sm sm:text-base">Permohonan Perubahan Rekening Menunggu Persetujuan</h3>
                        <p class="text-xs text-amber-700 mt-0.5">
                            Diajukan pada {{ $pendingRequest->created_at->translatedFormat('d F Y, H:i') }} WIB. Rekening aktif saat ini tetap digunakan hingga Super Admin menyetujui.
                        </p>
                    </div>
                </div>

                <form action="{{ route('profile.cancel-request', $pendingRequest) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan permohonan perubahan rekening ini?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-white border border-amber-300 text-amber-800 hover:bg-amber-100 transition cursor-pointer">
                        Batalkan
                    </button>
                </form>
            </div>

            <!-- Comparison Table -->
            <div class="overflow-x-auto bg-white rounded-xl border border-amber-200">
                <table class="w-full text-xs text-left">
                    <thead class="bg-amber-100/60 text-amber-900 font-bold border-b border-amber-200">
                        <tr>
                            <th class="px-4 py-2.5">Kolom Data</th>
                            <th class="px-4 py-2.5">Data Aktif Saat Ini</th>
                            <th class="px-4 py-2.5">Data Baru Diajukan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-amber-100 font-medium">
                        @if($pendingRequest->old_name !== $pendingRequest->new_name)
                            <tr>
                                <td class="px-4 py-2 text-slate-500 font-semibold">Nama Lengkap</td>
                                <td class="px-4 py-2 text-slate-700">{{ $pendingRequest->old_name }}</td>
                                <td class="px-4 py-2 text-emerald-700 font-bold bg-emerald-50/50">{{ $pendingRequest->new_name }}</td>
                            </tr>
                        @endif
                        @if($pendingRequest->old_bank_name !== $pendingRequest->new_bank_name)
                            <tr>
                                <td class="px-4 py-2 text-slate-500 font-semibold">Nama Bank</td>
                                <td class="px-4 py-2 text-slate-700">{{ \App\Services\BankListService::getLabel($pendingRequest->old_bank_name) }}</td>
                                <td class="px-4 py-2 text-emerald-700 font-bold bg-emerald-50/50">{{ \App\Services\BankListService::getLabel($pendingRequest->new_bank_name) }}</td>
                            </tr>
                        @endif
                        @if($pendingRequest->old_bank_account_number !== $pendingRequest->new_bank_account_number)
                            <tr>
                                <td class="px-4 py-2 text-slate-500 font-semibold">Nomor Rekening</td>
                                <td class="px-4 py-2 font-mono text-slate-700">{{ $pendingRequest->old_bank_account_number }}</td>
                                <td class="px-4 py-2 font-mono text-emerald-700 font-bold bg-emerald-50/50">{{ $pendingRequest->new_bank_account_number }}</td>
                            </tr>
                        @endif
                        @if($pendingRequest->old_bank_account_name !== $pendingRequest->new_bank_account_name)
                            <tr>
                                <td class="px-4 py-2 text-slate-500 font-semibold">Atas Nama Rekening</td>
                                <td class="px-4 py-2 text-slate-700">{{ $pendingRequest->old_bank_account_name }}</td>
                                <td class="px-4 py-2 text-emerald-700 font-bold bg-emerald-50/50">{{ $pendingRequest->new_bank_account_name }}</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            @if($pendingRequest->reason)
                <div class="text-xs text-amber-800 bg-amber-100/50 p-2.5 rounded-lg border border-amber-200">
                    <strong>Alasan Pengajuan:</strong> {{ $pendingRequest->reason }}
                </div>
            @endif
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left / Main: Edit Profile & Bank Details (2 Cols) -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs">
                <div class="flex items-center gap-3 pb-4 mb-5 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-lg">
                        👤
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Data Diri & Rekening Penggajian</h2>
                        <p class="text-xs text-slate-500">
                            @if($user->isBlogwalker())
                                Perubahan data rekening dan nama akan melalui persetujuan Super Admin demi keamanan payroll.
                            @else
                                Perbarui data profil dan rekening akun Anda.
                            @endif
                        </p>
                    </div>
                </div>

                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <!-- Readonly System Credentials -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-slate-50 p-3.5 rounded-xl border border-slate-200/80">
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Username (Login)</label>
                            <input type="text" value="{{ $user->username ?? '-' }}" disabled
                                class="w-full text-xs font-mono font-bold bg-slate-200/60 border border-slate-300 text-slate-600 rounded-lg px-3 py-2 cursor-not-allowed">
                            <span class="text-[10px] text-slate-400">Username bersifat permanen</span>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Alamat Email</label>
                            <input type="email" value="{{ $user->email }}" disabled
                                class="w-full text-xs font-mono font-semibold bg-slate-200/60 border border-slate-300 text-slate-600 rounded-lg px-3 py-2 cursor-not-allowed">
                            <span class="text-[10px] text-slate-400">Email terdaftar untuk notifikasi</span>
                        </div>
                    </div>

                    <!-- Name & Phone -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="name" class="block text-xs font-semibold text-slate-700 mb-1">
                                Nama Lengkap Sesuai KTP <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                                class="w-full text-sm border-slate-300 rounded-xl px-3.5 py-2.5 focus:ring-emerald-500 focus:border-emerald-500 @error('name') border-rose-400 @enderror">
                            @error('name')
                                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="phone" class="block text-xs font-semibold text-slate-700 mb-1">
                                No. WhatsApp / HP Aktif <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="phone" id="phone" value="{{ old('phone', $user->phone) }}" required placeholder="Contoh: 081234567890"
                                class="w-full text-sm border-slate-300 rounded-xl px-3.5 py-2.5 focus:ring-emerald-500 focus:border-emerald-500 @error('phone') border-rose-400 @enderror">
                            @error('phone')
                                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Bank Details Section -->
                    <div class="pt-4 border-t border-slate-100 space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-600">Informasi Rekening Bank / E-Wallet</h3>
                            @if($user->isBlogwalker())
                                <span class="text-[10px] font-bold text-amber-700 bg-amber-100 border border-amber-300 px-2 py-0.5 rounded-full">
                                    Perlu Approval Admin
                                </span>
                            @endif
                        </div>

                        <div>
                            <label for="bank_name" class="block text-xs font-semibold text-slate-700 mb-1">
                                Pilih Bank / E-Wallet di Indonesia <span class="text-rose-500">*</span>
                            </label>
                            <select name="bank_name" id="bank_name" required
                                class="w-full text-sm border-slate-300 rounded-xl px-3.5 py-2.5 focus:ring-emerald-500 focus:border-emerald-500 @error('bank_name') border-rose-400 @enderror">
                                <option value="">-- Pilih Bank / E-Wallet --</option>
                                @foreach($groupedBanks as $groupName => $banks)
                                    <optgroup label="{{ $groupName }}">
                                        @foreach($banks as $code => $label)
                                            <option value="{{ $code }}" {{ old('bank_name', $user->bank_name) === $code ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            @error('bank_name')
                                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="bank_account_number" class="block text-xs font-semibold text-slate-700 mb-1">
                                    Nomor Rekening / No. HP E-Wallet <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" name="bank_account_number" id="bank_account_number" value="{{ old('bank_account_number', $user->bank_account_number) }}" required
                                    class="w-full font-mono text-sm border-slate-300 rounded-xl px-3.5 py-2.5 focus:ring-emerald-500 focus:border-emerald-500 @error('bank_account_number') border-rose-400 @enderror">
                                @error('bank_account_number')
                                    <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="bank_account_name" class="block text-xs font-semibold text-slate-700 mb-1">
                                    Nama Pemilik Rekening (Sesuai Buku Tabungan) <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" name="bank_account_name" id="bank_account_name" value="{{ old('bank_account_name', $user->bank_account_name) }}" required
                                    class="w-full text-sm border-slate-300 rounded-xl px-3.5 py-2.5 focus:ring-emerald-500 focus:border-emerald-500 @error('bank_account_name') border-rose-400 @enderror">
                                @error('bank_account_name')
                                    <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        @if($user->isBlogwalker())
                            <div>
                                <label for="bank_book_photo" class="block text-xs font-semibold text-slate-700 mb-1">
                                    Unggah Bukti Rekening Baru (Opsional)
                                </label>
                                <input type="file" name="bank_book_photo" id="bank_book_photo" accept="image/jpeg,image/png,image/jpg,image/webp"
                                    class="w-full text-xs text-slate-600 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer">
                                <p class="text-[11px] text-slate-400 mt-1">Foto halaman depan buku tabungan atau tangkapan layar e-banking yang menampilkan nama dan nomor rekening (Maks 5MB).</p>
                                @error('bank_book_photo')
                                    <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="reason" class="block text-xs font-semibold text-slate-700 mb-1">
                                    Alasan Perubahan Rekening / Nama (Opsional)
                                </label>
                                <textarea name="reason" id="reason" rows="2" placeholder="Tuliskan alasan singkat jika mengajukan pergantian rekening (misal: rekening lama hilang/ditutup)..."
                                    class="w-full text-xs border-slate-300 rounded-xl px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500">{{ old('reason') }}</textarea>
                            </div>
                        @endif
                    </div>

                    <div class="pt-4 flex items-center justify-end">
                        <button type="submit" class="px-5 py-2.5 rounded-xl font-bold text-xs bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs transition cursor-pointer">
                            @if($user->isBlogwalker())
                                Ajukan Perubahan Profil & Rekening
                            @else
                                Simpan Perubahan Profil
                            @endif
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Right Side: Password Change & Request History (1 Col) -->
        <div class="space-y-6">

            <!-- Card: Ganti Password -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs">
                <div class="flex items-center gap-3 pb-4 mb-4 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center font-bold text-lg">
                        🔒
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Ganti Password</h2>
                        <p class="text-xs text-slate-500">Perbarui kata sandi akun secara berkala.</p>
                    </div>
                </div>

                <form action="{{ route('profile.password') }}" method="POST" class="space-y-3.5">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="current_password" class="block text-xs font-semibold text-slate-700 mb-1">
                            Password Saat Ini <span class="text-rose-500">*</span>
                        </label>
                        <input type="password" name="current_password" id="current_password" required
                            class="w-full text-sm border-slate-300 rounded-xl px-3.5 py-2 focus:ring-purple-500 focus:border-purple-500 @error('current_password') border-rose-400 @enderror">
                        @error('current_password')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-xs font-semibold text-slate-700 mb-1">
                            Password Baru (Min 8 Karakter) <span class="text-rose-500">*</span>
                        </label>
                        <input type="password" name="password" id="password" required minlength="8"
                            class="w-full text-sm border-slate-300 rounded-xl px-3.5 py-2 focus:ring-purple-500 focus:border-purple-500 @error('password') border-rose-400 @enderror">
                        @error('password')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-xs font-semibold text-slate-700 mb-1">
                            Konfirmasi Password Baru <span class="text-rose-500">*</span>
                        </label>
                        <input type="password" name="password_confirmation" id="password_confirmation" required minlength="8"
                            class="w-full text-sm border-slate-300 rounded-xl px-3.5 py-2 focus:ring-purple-500 focus:border-purple-500">
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full py-2.5 rounded-xl font-bold text-xs bg-purple-600 hover:bg-purple-700 text-white shadow-xs transition cursor-pointer">
                            Perbarui Password
                        </button>
                    </div>
                </form>
            </div>

            <!-- Card: Riwayat Pengajuan Ganti Rekening (Blogwalker) -->
            @if($user->isBlogwalker() && isset($recentRequests) && $recentRequests->isNotEmpty())
                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs space-y-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-600">Riwayat Pengajuan Rekening</h3>
                    <div class="space-y-2">
                        @foreach($recentRequests as $req)
                            <div class="p-3 rounded-xl border {{ $req->isPending() ? 'border-amber-200 bg-amber-50/50' : ($req->isApproved() ? 'border-emerald-200 bg-emerald-50/40' : 'border-rose-200 bg-rose-50/40') }} text-xs space-y-1">
                                <div class="flex items-center justify-between font-bold">
                                    <span class="text-slate-700">{{ $req->created_at->translatedFormat('d M Y') }}</span>
                                    @if($req->isPending())
                                        <span class="px-2 py-0.5 rounded-full text-[10px] bg-amber-200 text-amber-900 font-bold">Menunggu Review</span>
                                    @elseif($req->isApproved())
                                        <span class="px-2 py-0.5 rounded-full text-[10px] bg-emerald-200 text-emerald-900 font-bold">Disetujui</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] bg-rose-200 text-rose-900 font-bold">Ditolak</span>
                                    @endif
                                </div>
                                <div class="text-slate-600">
                                    {{ \App\Services\BankListService::getLabel($req->new_bank_name) }} - <span class="font-mono">{{ $req->new_bank_account_number }}</span>
                                </div>
                                @if($req->admin_notes)
                                    <div class="text-[11px] text-slate-500 italic pt-1 border-t border-slate-200/60">
                                        Catatan Admin: {{ $req->admin_notes }}
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>

    </div>

</div>
@endsection
