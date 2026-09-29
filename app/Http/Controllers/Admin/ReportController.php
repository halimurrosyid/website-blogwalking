<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Period;
use App\Models\Submission;
use App\Models\User;
use App\Services\PeriodService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request, PeriodService $periodService): View
    {
        $periods = Period::orderByDesc('starts_at')->get();
        $workers = User::where('role', 'blogwalker')->orderBy('name')->get();

        $selectedPeriodId = $request->input('period_id');
        $selectedWorkerId = $request->input('worker_id');

        // Default to active period, or latest period, or null if no periods exist
        if ($selectedPeriodId === null) {
            $active = $periodService->getActivePeriod();
            $selectedPeriodId = $active?->id ?? $periods->first()?->id ?? 'all';
        }

        $selectedPeriod = null;
        if ($selectedPeriodId !== 'all') {
            $selectedPeriod = Period::find($selectedPeriodId);
            if (! $selectedPeriod && $periods->isNotEmpty()) {
                $selectedPeriod = $periods->first();
                $selectedPeriodId = $selectedPeriod->id;
            }
        }

        // Submissions Query
        $subQuery = Submission::query();
        if ($selectedPeriod) {
            $subQuery->where('period_id', $selectedPeriod->id);
        }
        if ($selectedWorkerId) {
            $subQuery->where('user_id', $selectedWorkerId);
        }

        // Aggregate Metrics
        $totalSubmissions = (clone $subQuery)->count();
        $approvedCount = (clone $subQuery)->where('review_status', 'approved')->count();
        $pendingCount = (clone $subQuery)->where('review_status', 'pending')->count();
        $rejectedCount = (clone $subQuery)->where('review_status', 'rejected')->count();

        $approvalRate = $totalSubmissions > 0
            ? round(($approvedCount / $totalSubmissions) * 100, 1)
            : 0;

        $totalEarnings = (clone $subQuery)->where('review_status', 'approved')->sum('rate_amount');
        $totalPaid = (clone $subQuery)->where('review_status', 'approved')->where('is_paid', true)->sum('rate_amount');
        $totalUnpaid = $totalEarnings - $totalPaid;

        // Workers Performance Breakdown
        $targetWorkers = $selectedWorkerId
            ? $workers->where('id', $selectedWorkerId)
            : $workers;

        $workerReports = [];
        $qualifiedCount = 0;
        $disqualifiedCount = 0;

        foreach ($targetWorkers as $worker) {
            $workerSubQuery = Submission::where('user_id', $worker->id);
            if ($selectedPeriod) {
                $workerSubQuery->where('period_id', $selectedPeriod->id);
            }

            $wTotal = (clone $workerSubQuery)->count();
            $wApproved = (clone $workerSubQuery)->where('review_status', 'approved')->count();
            $wPending = (clone $workerSubQuery)->where('review_status', 'pending')->count();
            $wRejected = (clone $workerSubQuery)->where('review_status', 'rejected')->count();
            $wEarnings = (clone $workerSubQuery)->where('review_status', 'approved')->sum('rate_amount');
            $wPaid = (clone $workerSubQuery)->where('review_status', 'approved')->where('is_paid', true)->sum('rate_amount');

            // Assignment info for this period
            $assignment = null;
            if ($selectedPeriod) {
                $assignment = Assignment::where('user_id', $worker->id)
                    ->where('period_id', $selectedPeriod->id)
                    ->first();
            }

            if (! $assignment) {
                $assignment = Assignment::where('user_id', $worker->id)->latest()->first();
            }

            $minTarget = $assignment?->min_target ?? ($selectedPeriod?->min_target_default ?? 100);
            $progressPercent = min(100, round(($wApproved / max(1, $minTarget)) * 100, 1));

            // Status label & badge
            $statusLabel = 'Menuju Target';
            $statusBadge = 'amber';

            if ($assignment?->status === 'dispensed') {
                $statusLabel = 'Dispensasi Admin';
                $statusBadge = 'purple';
                $qualifiedCount++;
            } elseif ($selectedPeriod && $selectedPeriod->is_closed) {
                if ($assignment?->is_eligible_next_period) {
                    $statusLabel = 'Lolos Periode';
                    $statusBadge = 'emerald';
                    $qualifiedCount++;
                } else {
                    $statusLabel = 'Tereliminasi';
                    $statusBadge = 'rose';
                    $disqualifiedCount++;
                }
            } else {
                if ($wApproved >= $minTarget) {
                    $statusLabel = 'Target Tercapai';
                    $statusBadge = 'emerald';
                    $qualifiedCount++;
                } else {
                    $statusLabel = 'Kurang '.max(0, $minTarget - $wApproved);
                    $statusBadge = 'amber';
                }
            }

            $tldsDisplay = 'Semua Domain';
            if ($assignment && ! empty($assignment->allowed_tlds)) {
                $tldsDisplay = implode(', ', $assignment->allowed_tlds);
            }

            $workerReports[] = [
                'worker' => $worker,
                'assignment' => $assignment,
                'tlds_display' => $tldsDisplay,
                'min_target' => $minTarget,
                'total' => $wTotal,
                'approved' => $wApproved,
                'pending' => $wPending,
                'rejected' => $wRejected,
                'progress_percent' => $progressPercent,
                'status_label' => $statusLabel,
                'status_badge' => $statusBadge,
                'earnings' => $wEarnings,
                'paid' => $wPaid,
                'unpaid' => $wEarnings - $wPaid,
            ];
        }

        return view('admin.reports.index', [
            'periods' => $periods,
            'workers' => $workers,
            'selectedPeriod' => $selectedPeriod,
            'selectedPeriodId' => $selectedPeriodId,
            'selectedWorkerId' => $selectedWorkerId,
            'totalSubmissions' => $totalSubmissions,
            'approvedCount' => $approvedCount,
            'pendingCount' => $pendingCount,
            'rejectedCount' => $rejectedCount,
            'approvalRate' => $approvalRate,
            'totalEarnings' => $totalEarnings,
            'totalPaid' => $totalPaid,
            'totalUnpaid' => $totalUnpaid,
            'workerReports' => $workerReports,
            'qualifiedCount' => $qualifiedCount,
            'disqualifiedCount' => $disqualifiedCount,
        ]);
    }

    public function export(Request $request, PeriodService $periodService): StreamedResponse
    {
        $selectedPeriodId = $request->input('period_id');
        $selectedWorkerId = $request->input('worker_id');

        $period = null;
        if ($selectedPeriodId && $selectedPeriodId !== 'all') {
            $period = Period::find($selectedPeriodId);
        }

        $periodName = $period ? str_replace(' ', '_', $period->name) : 'Semua_Periode';
        $filename = "Laporan_Kinerja_Blogwalker_{$periodName}_".date('Ymd_His').'.csv';

        $workers = User::where('role', 'blogwalker')->orderBy('name')->get();
        if ($selectedWorkerId) {
            $workers = $workers->where('id', $selectedWorkerId);
        }

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($workers, $period) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM for Excel
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, [
                'Periode',
                'Nama Blogwalker',
                'Email',
                'Plotting Ekstensi TLD',
                'Target Minimal',
                'Total Submit',
                'Disetujui (Approved)',
                'Menunggu Review',
                'Ditolak (Rejected)',
                'Capaian Target (%)',
                'Status Kualifikasi',
                'Total Penghasilan (Rp)',
                'Sudah Dibayar (Rp)',
                'Sisa Belum Dibayar (Rp)',
            ]);

            foreach ($workers as $worker) {
                $subQuery = Submission::where('user_id', $worker->id);
                if ($period) {
                    $subQuery->where('period_id', $period->id);
                }

                $total = (clone $subQuery)->count();
                $approved = (clone $subQuery)->where('review_status', 'approved')->count();
                $pending = (clone $subQuery)->where('review_status', 'pending')->count();
                $rejected = (clone $subQuery)->where('review_status', 'rejected')->count();
                $earnings = (clone $subQuery)->where('review_status', 'approved')->sum('rate_amount');
                $paid = (clone $subQuery)->where('review_status', 'approved')->where('is_paid', true)->sum('rate_amount');
                $unpaid = $earnings - $paid;

                $assignment = null;
                if ($period) {
                    $assignment = Assignment::where('user_id', $worker->id)
                        ->where('period_id', $period->id)
                        ->first();
                }
                if (! $assignment) {
                    $assignment = Assignment::where('user_id', $worker->id)->latest()->first();
                }

                $minTarget = $assignment?->min_target ?? ($period?->min_target_default ?? 100);
                $percent = round(($approved / max(1, $minTarget)) * 100, 1);

                $status = 'Sedang Berjalan';
                if ($assignment?->status === 'dispensed') {
                    $status = 'Dispensasi Admin';
                } elseif ($period && $period->is_closed) {
                    $status = $assignment?->is_eligible_next_period ? 'Lolos Periode' : 'Tereliminasi';
                } else {
                    $status = $approved >= $minTarget ? 'Target Tercapai' : 'Menuju Target';
                }

                $tlds = 'Semua Domain';
                if ($assignment && ! empty($assignment->allowed_tlds)) {
                    $tlds = implode(', ', $assignment->allowed_tlds);
                }

                fputcsv($handle, array_map([$this, 'sanitizeCsv'], [
                    $period?->name ?? 'Semua Periode',
                    $worker->name,
                    $worker->email,
                    $tlds,
                    $minTarget,
                    $total,
                    $approved,
                    $pending,
                    $rejected,
                    $percent.'%',
                    $status,
                    $earnings,
                    $paid,
                    $unpaid,
                ]));
            }

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Prevent CSV Formula Injection (CWE-1236).
     */
    protected function sanitizeCsv(mixed $value): string
    {
        $str = (string) $value;
        if ($str !== '' && in_array($str[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "'".$str;
        }

        return $str;
    }
}
