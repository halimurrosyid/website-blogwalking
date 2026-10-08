<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WorkerController extends Controller
{
    public function index(): View
    {
        $workers = User::whereIn('role', ['blogwalker', 'worker'])
            ->with(['activeAssignment'])
            ->withCount(['submissions as total_submissions'])
            ->withCount(['submissions as approved_submissions' => function ($q) {
                $q->where('review_status', 'approved');
            }])
            ->latest()
            ->paginate(15);

        return view('admin.workers.index', [
            'workers' => $workers,
        ]);
    }

    public function create(): View
    {
        return view('admin.workers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:6'],
            'default_rate' => ['nullable', 'numeric', 'min:0'],
            'phone' => ['nullable', 'string', 'max:20'],
            'allowed_tlds' => ['nullable', 'string'], // Comma-separated: .co.id, .web.id (Kosong = semua domain)
            'target_keywords' => ['nullable', 'string'],
            'target_backlink_url' => ['nullable', 'string'],
            'custom_instructions' => ['nullable', 'string'],
        ]);

        $defaultRate = ! empty($validated['default_rate']) ? (float) $validated['default_rate'] : 700.00;

        $worker = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'blogwalker',
            'default_rate' => $defaultRate,
            'phone' => $validated['phone'] ?? null,
            'is_active' => true,
        ]);

        // Process allowed TLDs into array
        $tldArray = [];
        if (! empty($validated['allowed_tlds'])) {
            $tldArray = array_values(array_filter(array_map('trim', explode(',', $validated['allowed_tlds']))));
        }

        Assignment::create([
            'user_id' => $worker->id,
            'allowed_tlds' => $tldArray,
            'target_keywords' => $validated['target_keywords'] ?? null,
            'target_backlink_url' => $validated['target_backlink_url'] ?? null,
            'custom_instructions' => $validated['custom_instructions'] ?? null,
            'is_active' => true,
        ]);

        return redirect()->route('admin.workers.index')->with('success', "Worker [{$worker->name}] berhasil ditambahkan dengan plotting tugasnya.");
    }

    public function edit(User $worker): View|RedirectResponse
    {
        if ($worker->isAdmin()) {
            return redirect()->route('admin.workers.index')->with('error', 'Akun Administrator tidak dapat dikelola melalui menu Blogwalker.');
        }

        $worker->load('activeAssignment');

        return view('admin.workers.edit', [
            'worker' => $worker,
            'assignment' => $worker->activeAssignment,
        ]);
    }

    public function update(Request $request, User $worker): RedirectResponse
    {
        if ($worker->isAdmin()) {
            return redirect()->route('admin.workers.index')->with('error', 'Akun Administrator tidak dapat diubah melalui menu Blogwalker.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($worker->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'default_rate' => ['nullable', 'numeric', 'min:0'],
            'phone' => ['nullable', 'string', 'max:20'],
            'is_active' => ['required', 'boolean'],
            'allowed_tlds' => ['nullable', 'string'],
            'target_keywords' => ['nullable', 'string'],
            'target_backlink_url' => ['nullable', 'string'],
            'custom_instructions' => ['nullable', 'string'],
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'default_rate' => ! empty($validated['default_rate']) ? (float) $validated['default_rate'] : ($worker->default_rate ?? 700.00),
            'phone' => $validated['phone'] ?? null,
            'is_active' => $validated['is_active'],
        ];

        if (! empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $worker->update($updateData);

        // Process allowed TLDs
        $tldArray = [];
        if (! empty($validated['allowed_tlds'])) {
            $tldArray = array_values(array_filter(array_map('trim', explode(',', $validated['allowed_tlds']))));
        }

        $assignment = $worker->activeAssignment;
        if ($assignment) {
            $assignment->update([
                'allowed_tlds' => $tldArray,
                'target_keywords' => $validated['target_keywords'] ?? null,
                'target_backlink_url' => $validated['target_backlink_url'] ?? null,
                'custom_instructions' => $validated['custom_instructions'] ?? null,
            ]);
        } else {
            Assignment::create([
                'user_id' => $worker->id,
                'allowed_tlds' => $tldArray,
                'target_keywords' => $validated['target_keywords'] ?? null,
                'target_backlink_url' => $validated['target_backlink_url'] ?? null,
                'custom_instructions' => $validated['custom_instructions'] ?? null,
                'is_active' => true,
            ]);
        }

        return redirect()->route('admin.workers.index')->with('success', "Data worker [{$worker->name}] berhasil diperbarui.");
    }

    public function toggleStatus(User $worker): RedirectResponse
    {
        if ($worker->isAdmin()) {
            return back()->with('error', 'Status akun Administrator tidak dapat diubah.');
        }

        $worker->is_active = ! $worker->is_active;
        $worker->save();

        $statusStr = $worker->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Status worker [{$worker->name}] berhasil {$statusStr}.");
    }

    /**
     * Super Admin manual password reset for a worker/user.
     */
    public function resetPassword(Request $request, User $worker): RedirectResponse
    {
        if ($worker->isAdmin()) {
            return back()->with('error', 'Password Administrator tidak dapat direset dari menu ini.');
        }

        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8'],
        ], [
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password baru minimal harus 8 karakter.',
        ]);

        $worker->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()
            ->with('success', "Password untuk [{$worker->name}] berhasil direset menjadi: [{$validated['password']}]. Silakan bagikan password ini kepada pengguna.")
            ->with('temp_password', $validated['password']);
    }
}
