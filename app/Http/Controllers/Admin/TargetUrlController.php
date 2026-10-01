<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use App\Models\TargetUrl;
use App\Services\DomainService;
use App\Services\TaskTypeService;
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

        if ($request->filled('task_type')) {
            $query->where('task_type', $request->task_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
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

        $targets = $query->latest()->paginate(25)->withQueryString();
        $taskTypes = TaskTypeService::all();

        $stats = [
            'total' => TargetUrl::count(),
            'available' => TargetUrl::available()->count(),
            'in_progress' => TargetUrl::where('status', 'in_progress')->count(),
            'completed' => TargetUrl::where('status', 'completed')->count(),
            'skipped' => TargetUrl::where('status', 'skipped')->count(),
        ];

        return view('admin.targets.index', compact('targets', 'stats', 'taskTypes'));
    }

    public function create(): View
    {
        $taskTypes = TaskTypeService::all();

        return view('admin.targets.create', compact('taskTypes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'urls' => 'required|string',
            'task_type' => 'nullable|string',
            'client_url' => 'nullable|string',
            'keyword' => 'nullable|string|max:255',
            'reward_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $taskType = $validated['task_type'] ?? TaskTypeService::COMMENT;
        $clientUrl = ! empty($validated['client_url']) ? trim($validated['client_url']) : null;
        $rewardAmount = ! empty($validated['reward_amount']) ? (float) $validated['reward_amount'] : TaskTypeService::getRate($taskType);

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

            // Resolve IP and Subnet for domain
            $ipData = $this->domainService->resolveIpAndSubnet($rootDomain);

            // Find or create domain
            $domain = Domain::firstOrCreate(
                ['root_domain' => $rootDomain],
                [
                    'tld' => $tld,
                    'ip_address' => $ipData['ip'],
                    'ip_subnet' => $ipData['subnet'],
                    'max_limit' => 5,
                    'url_count' => 0,
                    'is_locked' => false,
                ]
            );

            if (empty($domain->ip_subnet) && ! empty($ipData['subnet'])) {
                $domain->update([
                    'ip_address' => $ipData['ip'],
                    'ip_subnet' => $ipData['subnet'],
                ]);
            }

            // Determine status based on domain quota
            $status = 'available';
            if ($domain->is_locked || $domain->url_count >= $domain->max_limit) {
                $status = 'domain_full';
                $domainFullCount++;
            }

            TargetUrl::create([
                'url' => $rawUrl,
                'task_type' => $taskType,
                'client_url' => $clientUrl,
                'domain_id' => $domain->id,
                'root_domain' => $rootDomain,
                'keyword' => $validated['keyword'] ?? null,
                'reward_amount' => $rewardAmount,
                'notes' => $validated['notes'] ?? null,
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

    public function requeue(TargetUrl $target): RedirectResponse
    {
        $target->requeue();

        return redirect()->route('admin.targets.index')
            ->with('success', "Target URL [{$target->url}] berhasil diaktifkan kembali ke antrean siap dikerjakan.");
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
