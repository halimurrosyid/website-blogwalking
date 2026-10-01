<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    /**
     * Display a listing of all Super Admins.
     */
    public function index(): View
    {
        $admins = User::where('role', 'admin')
            ->latest()
            ->paginate(15);

        return view('admin.admins.index', compact('admins'));
    }

    /**
     * Show the form for creating a new Super Admin.
     */
    public function create(): View
    {
        return view('admin.admins.create');
    }

    /**
     * Store a newly created Super Admin in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:25'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $admin = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => 'admin',
            'is_active' => $request->boolean('is_active', true),
            'approval_status' => 'approved',
            'approved_at' => now(),
            'approved_by' => auth()->id(),
            'email_verified_at' => now(),
        ]);

        return redirect()->route('admin.admins.index')
            ->with('success', "Akun Super Admin [{$admin->name}] berhasil dibuat dan siap digunakan.");
    }

    /**
     * Show the form for editing the specified Super Admin.
     */
    public function edit(User $admin): View|RedirectResponse
    {
        if ($admin->role !== 'admin') {
            abort(404, 'User bukan Super Admin.');
        }

        return view('admin.admins.edit', compact('admin'));
    }

    /**
     * Update the specified Super Admin in storage.
     */
    public function update(Request $request, User $admin): RedirectResponse
    {
        if ($admin->role !== 'admin') {
            abort(404, 'User bukan Super Admin.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($admin->id)],
            'phone' => ['nullable', 'string', 'max:25'],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        // Prevent self-deactivation
        $isActive = $request->boolean('is_active', true);
        if ($admin->id === auth()->id() && ! $isActive) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan status akun Anda sendiri.');
        }

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'is_active' => $isActive,
        ];

        if (! empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $admin->update($updateData);

        return redirect()->route('admin.admins.index')
            ->with('success', "Data Super Admin [{$admin->name}] berhasil diperbarui.");
    }

    /**
     * Toggle the active status of a Super Admin.
     */
    public function toggleStatus(User $admin): RedirectResponse
    {
        if ($admin->role !== 'admin') {
            abort(404, 'User bukan Super Admin.');
        }

        if ($admin->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        if ($admin->is_active && User::where('role', 'admin')->where('is_active', true)->count() <= 1) {
            return back()->with('error', 'Tidak dapat menonaktifkan Super Admin aktif terakhir dalam sistem.');
        }

        $admin->is_active = ! $admin->is_active;
        $admin->save();

        $statusText = $admin->is_active ? 'diaktifkan kembali' : 'dinonaktifkan';

        return back()->with('success', "Akun Super Admin [{$admin->name}] berhasil {$statusText}.");
    }

    /**
     * Reset password of a Super Admin.
     */
    public function resetPassword(Request $request, User $admin): RedirectResponse
    {
        if ($admin->role !== 'admin') {
            abort(404, 'User bukan Super Admin.');
        }

        $validated = $request->validate([
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $admin->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', "Password untuk Super Admin [{$admin->name}] berhasil diperbarui.");
    }

    /**
     * Delete a Super Admin account.
     */
    public function destroy(User $admin): RedirectResponse
    {
        if ($admin->role !== 'admin') {
            abort(404, 'User bukan Super Admin.');
        }

        if ($admin->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        if (User::where('role', 'admin')->count() <= 1) {
            return back()->with('error', 'Tidak dapat menghapus Super Admin terakhir dalam sistem.');
        }

        $name = $admin->name;
        $admin->delete();

        return redirect()->route('admin.admins.index')
            ->with('success', "Akun Super Admin [{$name}] berhasil dihapus.");
    }
}
