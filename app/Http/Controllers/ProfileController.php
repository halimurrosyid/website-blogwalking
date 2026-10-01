<?php

namespace App\Http\Controllers;

use App\Models\ProfileChangeRequest;
use App\Models\User;
use App\Services\BankListService;
use App\Services\ImageUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Show the profile edit page.
     */
    public function edit(): View
    {
        /** @var User $user */
        $user = Auth::user();
        $groupedBanks = BankListService::groupedList();
        $pendingRequest = $user->pendingProfileChangeRequest;
        $recentRequests = $user->profileChangeRequests()->latest()->take(5)->get();

        return view('profile.edit', compact('user', 'groupedBanks', 'pendingRequest', 'recentRequests'));
    }

    /**
     * Update user profile information.
     */
    public function updateProfile(Request $request, ImageUploadService $imageUploadService): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $allBankKeys = array_keys(User::getBanks());

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'bank_name' => ['required', 'string', Rule::in($allBankKeys)],
            'bank_account_number' => ['required', 'string', 'max:50'],
            'bank_account_name' => ['required', 'string', 'max:255'],
            'bank_book_photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'reason' => ['nullable', 'string', 'max:500'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'phone.required' => 'Nomor WhatsApp / HP wajib diisi.',
            'bank_name.required' => 'Silakan pilih bank yang Anda gunakan.',
            'bank_name.in' => 'Pilihan bank tidak valid.',
            'bank_account_number.required' => 'Nomor rekening wajib diisi.',
            'bank_account_name.required' => 'Nama pemilik rekening wajib diisi.',
            'bank_book_photo.image' => 'Bukti rekening harus berupa file gambar.',
            'bank_book_photo.max' => 'Ukuran foto bukti rekening maksimal 5MB.',
        ]);

        // If user is Admin, allow direct update
        if ($user->isAdmin()) {
            $user->update([
                'name' => $validated['name'],
                'phone' => $validated['phone'],
                'bank_name' => $validated['bank_name'],
                'bank_account_number' => $validated['bank_account_number'],
                'bank_account_name' => $validated['bank_account_name'],
            ]);

            return redirect()->route('profile.edit')->with('success', 'Profil Anda berhasil diperbarui.');
        }

        // For Blogwalker: Check if any sensitive fields (Name, Bank details) changed
        $nameChanged = trim($user->name) !== trim($validated['name']);
        $bankNameChanged = trim((string) $user->bank_name) !== trim($validated['bank_name']);
        $accountNumChanged = trim((string) $user->bank_account_number) !== trim($validated['bank_account_number']);
        $accountHolderChanged = trim((string) $user->bank_account_name) !== trim($validated['bank_account_name']);
        $phoneChanged = trim((string) $user->phone) !== trim($validated['phone']);

        $hasSensitiveChanges = $nameChanged || $bankNameChanged || $accountNumChanged || $accountHolderChanged;

        if ($hasSensitiveChanges) {
            // Check for existing pending request
            if ($user->pendingProfileChangeRequest()->exists()) {
                return redirect()->route('profile.edit')->withInput()->with('error', 'Anda masih memiliki permohonan perubahan rekening/nama yang sedang menunggu persetujuan Super Admin. Harap tunggu atau batalkan permohonan sebelumnya.');
            }

            $bankBookPath = null;
            if ($request->hasFile('bank_book_photo')) {
                $bankBookPath = $imageUploadService->storeScreenshot($request->file('bank_book_photo'));
            }

            ProfileChangeRequest::create([
                'user_id' => $user->id,
                'old_name' => $user->name,
                'new_name' => $validated['name'],
                'old_phone' => $user->phone,
                'new_phone' => $validated['phone'],
                'old_bank_name' => $user->bank_name,
                'new_bank_name' => $validated['bank_name'],
                'old_bank_account_number' => $user->bank_account_number,
                'new_bank_account_number' => $validated['bank_account_number'],
                'old_bank_account_name' => $user->bank_account_name,
                'new_bank_account_name' => $validated['bank_account_name'],
                'bank_book_path' => $bankBookPath,
                'reason' => $request->input('reason'),
                'status' => 'pending',
            ]);

            // Update phone immediately if changed
            if ($phoneChanged) {
                $user->update(['phone' => $validated['phone']]);
            }

            return redirect()->route('profile.edit')->with('success', 'Permohonan perubahan rekening & nama telah diajukan! Demi keamanan penggajian, perubahan ini akan aktif setelah disetujui (approve) oleh Super Admin.');
        }

        // Only phone changed (no bank/name changes)
        if ($phoneChanged) {
            $user->update(['phone' => $validated['phone']]);

            return redirect()->route('profile.edit')->with('success', 'Nomor kontak WhatsApp/HP berhasil diperbarui.');
        }

        return redirect()->route('profile.edit')->with('info', 'Tidak ada perubahan data yang Anda masukkan.');
    }

    /**
     * Cancel an existing pending change request.
     */
    public function cancelRequest(ProfileChangeRequest $profileRequest): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if ($profileRequest->user_id !== $user->id || ! $profileRequest->isPending()) {
            return redirect()->route('profile.edit')->with('error', 'Permohonan tidak dapat dibatalkan.');
        }

        $profileRequest->delete();

        return redirect()->route('profile.edit')->with('success', 'Permohonan perubahan rekening berhasil dibatalkan.');
    }

    /**
     * Update user password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password baru minimal harus 8 karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
            'password.different' => 'Password baru harus berbeda dari password saat ini.',
        ]);

        if (! Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini yang Anda masukkan salah.']);
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('profile.edit')->with('success', 'Password Anda berhasil diperbarui! Silakan gunakan password baru ini untuk login berikutnya.');
    }
}
