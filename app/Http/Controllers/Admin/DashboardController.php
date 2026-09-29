<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use App\Models\Submission;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_workers' => User::whereIn('role', ['blogwalker', 'worker'])->where('is_active', true)->count(),
            'total_domains' => Domain::count(),
            'locked_domains' => Domain::where('is_locked', true)->count(),
            'pending_reviews' => Submission::where('review_status', 'pending')->count(),
            'total_approved' => Submission::where('review_status', 'approved')->count(),
            'unpaid_payout' => Submission::where('review_status', 'approved')->where('is_paid', false)->sum('rate_amount'),
            'total_paid' => Submission::where('is_paid', true)->sum('rate_amount'),
        ];

        // Recent 6 pending submissions for quick review
        $pendingSubmissions = Submission::with(['user', 'domain'])
            ->where('review_status', 'pending')
            ->oldest()
            ->take(6)
            ->get();

        // Top 5 blogwalkers this month
        $topWorkers = User::whereIn('role', ['blogwalker', 'worker'])
            ->withCount(['submissions as approved_count' => function ($q) {
                $q->where('review_status', 'approved');
            }])
            ->orderByDesc('approved_count')
            ->take(5)
            ->get();

        return view('admin.dashboard', [
            'stats' => $stats,
            'pendingSubmissions' => $pendingSubmissions,
            'topWorkers' => $topWorkers,
        ]);
    }
}
