<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\User;
use App\Services\PeriodService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegistrationApprovalController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->input('status', 'pending');

        $query = User::where('role', 'blogwalker')->latest();

        if ($status !== 'all') {
            $query->where('approval_status', $status);
        }

        $applicants = $query->paginate(15)->withQueryString();

        $pendingCount = User::where('role', 'blogwalker')->where('approval_status', 'pending')->count();
        $approvedCount = User::where('role', 'blogwalker')->where('approval_status', 'approved')->count();
        $rejectedCount = User::where('role', 'blogwalker')->where('approval_status', 'rejected')->count();

        return view('admin.registrations.index', [
            'applicants' => $applicants,
            'currentStatus' => $status,
            'pendingCount' => $pendingCount,
            'approvedCount' => $approvedCount,
            'rejectedCount' => $rejectedCount,
        ]);
    }

    public function approve(Request $request, User $applicant, PeriodService $periodService): RedirectResponse
    {
        $validated = $request->validate([
            'default_rate' => ['nullable', 'numeric', 'min:0'],
            'allowed_tlds' => ['nullable', 'string'],
            'target_keywords' => ['nullable', 'string'],
            'target_backlink_url' => ['nullable', 'string'],
            'min_target' => ['nullable', 'integer', 'min:1'],
        ]);

        $activePeriod = $periodService->getActivePeriod();

        // Parse allowed TLDs
        $tldArray = [];
        if (! empty($validated['allowed_tlds'])) {
            $tldArray = array_values(array_filter(array_map('trim', explode(',', $validated['allowed_tlds']))));
        }

        $rate = ! empty($validated['default_rate']) ? (float) $validated['default_rate'] : ($applicant->default_rate ?? 700.00);

        // Activate and approve applicant
        $applicant->update([
            'approval_status' => 'approved',
            'is_active' => true,
            'default_rate' => $rate,
            'approved_at' => now(),
            'approved_by' => Auth::id(),
            'rejection_reason' => null,
        ]);

        // Create assignment for active period
        $minTarget = $validated['min_target'] ?? ($activePeriod->min_target_default ?? 100);

        Assignment::updateOrCreate(
            [
                'user_id' => $applicant->id,
                'period_id' => $activePeriod->id,
            ],
            [
                'allowed_tlds' => $tldArray,
                'target_keywords' => $validated['target_keywords'] ?? null,
                'target_backlink_url' => $validated['target_backlink_url'] ?? null,
                'min_target' => $minTarget,
                'status' => 'active',
                'is_eligible_next_period' => true,
                'is_active' => true,
            ]
        );

        return redirect()->route('admin.registrations.index')
            ->with('success', "Pendaftaran Blogwalker [{$applicant->name}] (@{$applicant->username}) berhasil DISETUJUI dan akun telah diaktifkan.");
    }

    public function reject(Request $request, User $applicant): RedirectResponse
    {
        $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ]);

        $applicant->update([
            'approval_status' => 'rejected',
            'is_active' => false,
            'rejection_reason' => $request->input('rejection_reason'),
        ]);

        return redirect()->route('admin.registrations.index')
            ->with('success', "Pendaftaran Blogwalker [{$applicant->name}] telah DITOLAK.");
    }
}
