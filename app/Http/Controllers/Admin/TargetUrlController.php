<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use App\Models\TargetUrl;
use App\Services\DomainService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TargetUrlController extends Controller
{
    public function __construct(
        protected DomainService $domainService
    ) {}

    public function index(Request $request): View
    {
        $query = TargetUrl::with(['domain', 'takenBy', 'submission']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('url', 'like', "%{$search}%")
                    ->orWhere('root_domain', 'like', "%{$search}%")
                    ->orWhere('keyword', 'like', "%{$search}%");
            });
        }

        $targets = $query->latest()->paginate(25)->withQueryString();

        $stats = [
            'total' => TargetUrl::count(),
            'available' => TargetUrl::available()->count(),
            'in_progress' => TargetUrl::where('status', 'in_progress')->count(),
            'completed' => TargetUrl::where('status', 'completed')->count(),
            'skipped' => TargetUrl::where('status', 'skipped')->count(),
        ];

        return view('admin.targets.index', compact('targets', 'stats'));
    }

    public function create(): View
    {
        return view('admin.targets.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'urls' => 'required|string',
            'keyword' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500',
        ]);

        $rawUrls = preg_split('/[\r\n]+/', $request->urls, -1, PREG_SPLIT_NO_EMPTY);
        $importedCount = 0;
        $skippedCount = 0;
        $domainFullCount = 0;

        foreach ($rawUrls as $rawUrl) {
            $rawUrl = trim($rawUrl);
            if (empty($rawUrl)) {
                continue;
            }

            // Ensure schema prefix
            if (! preg_match('#^https?://#i', $rawUrl)) {
                $rawUrl = 'https://'.$rawUrl;
            }

            // Extract root domain
            $parsedDomain = $this->domainService->extractRootDomain($rawUrl);
            if (! $parsedDomain) {
                $skippedCount++;

                continue;
            }

            $rootDomain = $parsedDomain['root_domain'];
            $tld = $parsedDomain['tld'];

            // Check if exact URL already exists in targets
            if (TargetUrl::where('url', $rawUrl)->exists()) {
                $skippedCount++;

                continue;
            }

            // Find or create domain
            $domain = Domain::firstOrCreate(
                ['root_domain' => $rootDomain],
                [
                    'tld' => $tld,
                    'max_limit' => 5,
                    'url_count' => 0,
                    'is_locked' => false,
                ]
            );

            // Determine status based on domain quota
            $status = 'available';
            if ($domain->is_locked || $domain->url_count >= $domain->max_limit) {
                $status = 'domain_full';
                $domainFullCount++;
            }

            TargetUrl::create([
                'url' => $rawUrl,
                'domain_id' => $domain->id,
                'root_domain' => $rootDomain,
                'keyword' => $request->keyword,
                'notes' => $request->notes,
                'status' => $status,
                'created_by_user_id' => auth()->id(),
            ]);

            $importedCount++;
        }

        $message = "Berhasil mengimpor {$importedCount} target URL.";
        if ($skippedCount > 0) {
            $message .= " ({$skippedCount} URL dilewati karena format tidak valid atau sudah ada).";
        }
        if ($domainFullCount > 0) {
            $message .= " ({$domainFullCount} URL masuk status 'Domain Penuh' karena batas 5 URL tercapai).";
        }

        return redirect()->route('admin.targets.index')->with('success', $message);
    }

    public function destroy(TargetUrl $target): RedirectResponse
    {
        $target->delete();

        return redirect()->route('admin.targets.index')->with('success', 'Target URL berhasil dihapus.');
    }

    public function clearCompleted(): RedirectResponse
    {
        $deleted = TargetUrl::where('status', 'completed')->delete();

        return redirect()->route('admin.targets.index')->with('success', "{$deleted} target URL yang sudah selesai berhasil dibersihkan.");
    }
}
