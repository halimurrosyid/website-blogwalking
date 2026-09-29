<?php

namespace App\Http\Controllers\Worker;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Submission;
use App\Models\TargetUrl;
use App\Services\DomainService;
use App\Services\PeriodService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $user->load('activeAssignment');

        $today = now()->startOfDay();

        $stats = [
            'today_count' => Submission::where('user_id', $user->id)
                ->where('created_at', '>=', $today)
                ->count(),
            'pending_count' => Submission::where('user_id', $user->id)
                ->where('review_status', 'pending')
                ->count(),
            'approved_count' => Submission::where('user_id', $user->id)
                ->where('review_status', 'approved')
                ->count(),
            'unpaid_earnings' => Submission::where('user_id', $user->id)
                ->where('review_status', 'approved')
                ->where('is_paid', false)
                ->sum('rate_amount'),
            'total_paid' => Submission::where('user_id', $user->id)
                ->where('is_paid', true)
                ->sum('rate_amount'),
        ];

        $recentSubmissions = Submission::with('domain')
            ->where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        $availableTargets = TargetUrl::with('domain')
            ->available()
            ->latest()
            ->take(5)
            ->get();

        $myActiveTargets = TargetUrl::with('domain')
            ->where('taken_by_user_id', $user->id)
            ->where('status', 'in_progress')
            ->get();

        $periodService = app(PeriodService::class);
        $activePeriod = $periodService->getActivePeriod();
        $assignment = Assignment::where('user_id', $user->id)
            ->where(function ($q) use ($activePeriod) {
                $q->where('period_id', $activePeriod->id)
                    ->orWhereNull('period_id');
            })
            ->latest()
            ->first();

        $periodProgress = $activePeriod->progressForUser($user, $assignment);
        $isDisqualified = $assignment ? $assignment->isDisqualified() : false;

        return view('worker.dashboard', [
            'user' => $user,
            'assignment' => $assignment,
            'activePeriod' => $activePeriod,
            'periodProgress' => $periodProgress,
            'isDisqualified' => $isDisqualified,
            'stats' => $stats,
            'recentSubmissions' => $recentSubmissions,
            'availableTargets' => $availableTargets,
            'myActiveTargets' => $myActiveTargets,
        ]);
    }

    /**
     * AJAX endpoint to check domain availability and remaining quota.
     */
    public function checkDomain(Request $request, DomainService $domainService): JsonResponse
    {
        $request->validate([
            'url' => ['required', 'string'],
        ]);

        $result = $domainService->checkDomainAvailability($request->input('url'));

        return response()->json($result);
    }
}
