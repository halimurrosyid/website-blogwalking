<?php

namespace App\Http\Controllers\Worker;

use App\Http\Controllers\Controller;
use App\Models\TargetUrl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TargetUrlController extends Controller
{
    public function index(Request $request): View
    {
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

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('url', 'like', "%{$search}%")
                    ->orWhere('root_domain', 'like', "%{$search}%")
                    ->orWhere('keyword', 'like', "%{$search}%");
            });
        }

        $availableTargets = $query->paginate(20)->withQueryString();

        $stats = [
            'available_count' => TargetUrl::available()->count(),
            'my_in_progress' => $myActiveTargets->count(),
            'completed_today' => TargetUrl::where('taken_by_user_id', $user->id)
                ->where('status', 'completed')
                ->whereDate('updated_at', today())
                ->count(),
        ];

        return view('worker.targets.index', compact('availableTargets', 'myActiveTargets', 'stats'));
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

    public function skip(Request $request, TargetUrl $target): RedirectResponse
    {
        $request->validate([
            'reason' => 'nullable|string|max:255',
        ]);

        $reason = $request->input('reason', 'Kolom komentar tidak ditemukan / tertutup');
        $target->markSkipped($reason);

        return redirect()->route('blogwalker.targets.index')->with('success', "Target URL telah dilewati dengan alasan: {$reason}.");
    }
}
