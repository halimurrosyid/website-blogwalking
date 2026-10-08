<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ImageUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function showRegistrationForm(): View|RedirectResponse
    {
        if (auth()->check()) {
            return auth()->user()->isAdmin()
                ? redirect()->route('admin.dashboard')
                : redirect()->route('blogwalker.dashboard');
        }

        return view('auth.register', [
            'banks' => User::$conventionalBanks,
        ]);
    }

    public function register(Request $request, ImageUploadService $imageUploadService): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'alpha_dash', 'min:3', 'max:50', 'unique:users,username'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'confirmed', 'min:8'],
            'phone' => ['required', 'string', 'max:20'],
            'bank_name' => ['required', 'string', Rule::in(array_keys(User::$conventionalBanks))],
            'bank_account_number' => ['required', 'string', 'max:50'],
            'bank_account_name' => ['required', 'string', 'max:255'],
            'id_card_photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'bank_book_photo' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'], // Max 5MB
        ], [
            'username.alpha_dash' => 'Username hanya boleh berisi huruf, angka, tanda hubung (-), dan garis bawah (_).',
            'username.unique' => 'Username ini sudah digunakan, silakan pilih username lain.',
            'email.unique' => 'Email ini sudah terdaftar di sistem.',
            'password.confirmed' => 'Konfirmasi password tidak sesuai.',
            'bank_book_photo.required' => 'Foto buku rekening wajib diunggah untuk verifikasi pembayaran.',
        ]);

        // Securely store documents (processed into WebP)
        $idCardPath = $request->hasFile('id_card_photo')
            ? $imageUploadService->storeScreenshot($request->file('id_card_photo'))
            : null;
        $bankBookPath = $imageUploadService->storeScreenshot($request->file('bank_book_photo'));

        $defaultRate = 700.00;

        $user = User::create([
            'name' => $validated['name'],
            'username' => strtolower($validated['username']),
            'email' => strtolower($validated['email']),
            'password' => Hash::make($validated['password']),
            'role' => 'blogwalker',
            'default_rate' => $defaultRate,
            'phone' => $validated['phone'],
            'bank_name' => $validated['bank_name'],
            'bank_account_number' => $validated['bank_account_number'],
            'bank_account_name' => $validated['bank_account_name'],
            'id_card_path' => $idCardPath,
            'bank_book_path' => $bankBookPath,
            'is_active' => false, // Inactive until Super Admin approves
            'approval_status' => 'pending',
        ]);

        return redirect()->route('login')->with('success', 'Pendaftaran berhasil! Akun Anda sedang menunggu verifikasi dan persetujuan dari Super Admin. Anda dapat login setelah akun di-approve.');
    }
}
