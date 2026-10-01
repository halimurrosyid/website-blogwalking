<?php

namespace App\Http\Controllers\Worker;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\TargetUrl;
use App\Services\PeriodService;
use App\Services\TaskTypeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TargetUrlController extends Controller
{
    public function index(Request $request): View
    {
        // Auto-release any claims inactive for more than 2 hours so others can take them
        TargetUrl::releaseStaleClaims(2);

        $user = auth()->user();

        // Targets currently claimed by this user
        $myActiveTargets = TargetUrl::with('domain')
            ->where('taken_by_user_id', $user->id)
            ->where('status', 'in_progress')
            ->latest('taken_at')
            ->get();

        // Pool of available targets
        $query = TargetUrl::with('domain')
            ->available()
            ->latest();

        if ($request->filled('task_type')) {
            $query->where('task_type', $request->task_type);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('url', 'like', "%{$search}%")
                    ->orWhere('root_domain', 'like', "%{$search}%")
                    ->orWhere('keyword', 'like', "%{$search}%")
                    ->orWhere('client_url', 'like', "%{$search}%");
            });
        }

        $availableTargets = $query->paginate(20)->withQueryString();
        $taskTypes = TaskTypeService::all();

        $periodService = app(PeriodService::class);
        $activePeriod = $periodService->getActivePeriod();
        $assignment = Assignment::where('user_id', $user->id)
            ->when($activePeriod, function ($query, $period) {
                $query->where(function ($q) use ($period) {
                    $q->where('period_id', $period->id)
                        ->orWhereNull('period_id');
                });
            })
            ->latest()
            ->first();

        $stats = [
            'available_count' => TargetUrl::available()->count(),
            'my_in_progress' => $myActiveTargets->count(),
            'completed_today' => TargetUrl::where('taken_by_user_id', $user->id)
                ->where('status', 'completed')
                ->whereDate('updated_at', today())
                ->count(),
        ];

        return view('worker.targets.index', compact('availableTargets', 'myActiveTargets', 'stats', 'taskTypes', 'assignment'));
    }

    public function claim(TargetUrl $target): RedirectResponse
    {
        // Double check availability
        if ($target->status !== 'available' && ! ($target->status === 'in_progress' && $target->taken_by_user_id === auth()->id())) {
            return redirect()->route('blogwalker.targets.index')->with('error', 'Target URL ini sudah diambil oleh blogwalker lain atau sudah tidak tersedia.');
        }

        if ($target->domain && (! $target->domain->isAvailable())) {
            $target->update(['status' => 'domain_full']);

            return redirect()->route('blogwalker.targets.index')->with('error', "Domain {$target->root_domain} sudah mencapai batas 5 URL target.");
        }

        $target->claimBy(auth()->user());

        return redirect()->route('blogwalker.submissions.create', ['target_id' => $target->id])
            ->with('success', 'Target URL berhasil diklaim. Silakan buka halaman target, beri komentar, dan unggah screenshot bukti!');
    }

    public function release(TargetUrl $target): RedirectResponse
    {
        if ($target->status !== 'in_progress' || $target->taken_by_user_id !== auth()->id()) {
            return redirect()->route('blogwalker.targets.index')
                ->with('error', 'Hanya misi target yang sedang Anda kerjakan yang dapat dikembalikan ke antrean.');
        }

        $target->releaseToPool();

        return redirect()->route('blogwalker.targets.index')
            ->with('success', 'Target URL berhasil dilepas dan dikembalikan ke antrean bersama.');
    }

    public function skip(Request $request, TargetUrl $target): RedirectResponse
    {
        $request->validate([
            'reason' => 'nullable|string|max:255',
        ]);

        if ($target->status === 'in_progress' && $target->taken_by_user_id !== auth()->id()) {
            return redirect()->route('blogwalker.targets.index')
                ->with('error', 'Target URL ini sedang dikerjakan oleh rekan lain.');
        }

        $reason = $request->input('reason') ?: 'Kolom komentar tidak ditemukan / tertutup';
        $target->markSkipped($reason);

        return redirect()->route('blogwalker.targets.index')
            ->with('success', "Target URL telah dilewati ({$reason}).");
    }
}
