<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Period;
use App\Services\PeriodService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PeriodController extends Controller
{
    public function __construct(
        protected PeriodService $periodService
    ) {}

    public function index(): View
    {
        $activePeriod = $this->periodService->getActivePeriod();

        // Load assignments with progress
        $assignments = $activePeriod ? $activePeriod->assignments()->with('user')->get() : collect();
        $participants = [];

        foreach ($assignments as $assignment) {
            $progress = $activePeriod
                ? $activePeriod->progressForUser($assignment->user, $assignment)
                : [
                    'approved' => 0,
                    'pending' => 0,
                    'total' => 0,
                    'target' => 100,
                    'percentage' => 0.0,
                    'is_qualified' => false,
                    'remaining_needed' => 100,
                ];

            $participants[] = [
                'assignment' => $assignment,
                'user' => $assignment->user,
                'progress' => $progress,
            ];
        }

        // Sort participants: in-danger / not qualified first
        usort($participants, function ($a, $b) {
            return $a['progress']['percentage'] <=> $b['progress']['percentage'];
        });

        // Past closed periods
        $pastPeriods = rescue(fn () => Period::where('status', 'closed')
            ->latest('closed_at')
            ->take(12)
            ->get(), collect());

        return view('admin.periods.index', compact('activePeriod', 'participants', 'pastPeriods'));
    }

    public function updateConfig(Request $request, Period $period): RedirectResponse
    {
        $validated = $request->validate([
            'min_target' => 'required|integer|min:1|max:10000',
            'max_target' => 'nullable|integer|min:1|max:50000',
            'max_urls_per_domain' => 'required|integer|min:1|max:100',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after_or_equal:starts_at',
            'notes' => 'nullable|string|max:1000',
        ]);

        $this->periodService->updatePeriodConfiguration($period, $validated);

        return redirect()->route('admin.periods.index')->with('success', 'Konfigurasi target periode berhasil diperbarui!');
    }

    public function updateAssignment(Request $request, Assignment $assignment): RedirectResponse
    {
        $validated = $request->validate([
            'allowed_tlds' => 'nullable|string', // Comma separated, e.g. ".co.id, .web.id"
            'target_keywords' => 'nullable|string|max:500',
            'target_backlink_url' => 'nullable|url|max:500',
            'custom_instructions' => 'nullable|string|max:1000',
            'min_target' => 'nullable|integer|min:1|max:10000',
            'max_target' => 'nullable|integer|min:1|max:50000',
        ]);

        $allowedTlds = null;
        if (! empty($validated['allowed_tlds'])) {
            $tlds = array_map(fn ($item) => trim($item), explode(',', $validated['allowed_tlds']));
            $allowedTlds = array_values(array_filter($tlds));
        }

        $assignment->update([
            'allowed_tlds' => $allowedTlds,
            'target_keywords' => $validated['target_keywords'] ?? null,
            'target_backlink_url' => $validated['target_backlink_url'] ?? null,
            'custom_instructions' => $validated['custom_instructions'] ?? null,
            'min_target' => ! empty($validated['min_target']) ? (int) $validated['min_target'] : null,
            'max_target' => ! empty($validated['max_target']) ? (int) $validated['max_target'] : null,
        ]);

        return redirect()->route('admin.periods.index')->with('success', "Plotting untuk {$assignment->user->name} berhasil diperbarui.");
    }

    public function closeAndRollOver(Request $request, Period $period): RedirectResponse
    {
        $request->validate([
            'next_month' => 'required|integer|min:1|max:12',
            'next_year' => 'required|integer|min:2025|max:2035',
            'next_min_target' => 'required|integer|min:1',
            'next_max_urls_per_domain' => 'required|integer|min:1',
        ]);

        $nextDate = Carbon::createFromDate($request->next_year, $request->next_month, 1);
        $indonesianMonths = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        $nextName = ($indonesianMonths[$nextDate->month] ?? $nextDate->format('F')).' '.$nextDate->year;

        $newPeriod = $this->periodService->closeAndRollOver($period, [
            'name' => $nextName,
            'month' => $nextDate->month,
            'year' => $nextDate->year,
            'min_target' => $request->next_min_target,
            'max_target' => $request->next_max_target,
            'max_urls_per_domain' => $request->next_max_urls_per_domain,
            'starts_at' => $nextDate->startOfMonth()->toDateString(),
            'ends_at' => $nextDate->endOfMonth()->toDateString(),
        ]);

        return redirect()->route('admin.periods.index')
            ->with('success', "Periode {$period->name} berhasil ditutup & dievaluasi. Periode baru [{$newPeriod->name}] resmi dibuka!");
    }

    public function dispense(Request $request, Assignment $assignment): RedirectResponse
    {
        $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        $this->periodService->grantDispensation($assignment, $request->reason);

        return redirect()->route('admin.periods.index')
            ->with('success', "Dispensasi berhasil diberikan kepada {$assignment->user->name}. Akun dapat kembali berpartisipasi.");
    }
}
