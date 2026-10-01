<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\Domain;
use App\Models\TargetUrl;
use App\Services\DomainService;
use App\Services\PeriodService;
use App\Services\TaskTypeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TargetUrlController extends Controller
{
    public function __construct(
        protected DomainService $domainService,
        protected PeriodService $periodService,
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

        $activePeriod = $this->periodService->getActivePeriod();
        $maxUrlsPerDomain = $activePeriod?->max_urls_per_domain ?? (int) AppSetting::get('max_urls_per_domain', 5);

        $stats = [
            'total' => TargetUrl::count(),
            'available' => TargetUrl::available()->count(),
            'in_progress' => TargetUrl::where('status', 'in_progress')->count(),
            'completed' => TargetUrl::where('status', 'completed')->count(),
            'skipped' => TargetUrl::where('status', 'skipped')->count(),
        ];

        return view('admin.targets.index', compact('targets', 'stats', 'taskTypes', 'maxUrlsPerDomain'));
    }

    public function create(): View
    {
        $taskTypes = TaskTypeService::all();

        return view('admin.targets.create', compact('taskTypes'));
    }

    public function store(Request $request): RedirectResponse
    {
        @ini_set('max_execution_time', '300');
        @ini_set('memory_limit', '512M');

        $validated = $request->validate([
            'urls' => 'nullable|string',
            'url_file' => 'nullable|file|mimes:txt,csv,text|max:10240',
            'task_type' => 'nullable|string',
            'client_url' => 'nullable|string',
            'keyword' => 'nullable|string|max:255',
            'reward_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        if (empty($validated['urls']) && ! $request->hasFile('url_file')) {
            return back()->withErrors(['urls' => 'Silakan masukkan daftar URL atau unggah file .txt / .csv'])->withInput();
        }

        $taskType = $validated['task_type'] ?? TaskTypeService::COMMENT;
        $clientUrl = ! empty($validated['client_url']) ? trim($validated['client_url']) : null;
        $rewardAmount = ! empty($validated['reward_amount']) ? (float) $validated['reward_amount'] : TaskTypeService::getRate($taskType);

        // Gather raw text from file and/or textarea
        $rawContent = '';
        if ($request->hasFile('url_file')) {
            $rawContent .= file_get_contents($request->file('url_file')->getRealPath())."\n";
        }
        if (! empty($validated['urls'])) {
            $rawContent .= $validated['urls'];
        }

        $rawUrls = preg_split('/[\r\n]+/', $rawContent, -1, PREG_SPLIT_NO_EMPTY);
        $importedCount = 0;
        $skippedCount = 0;
        $domainFullCount = 0;

        $seenInBatch = [];
        $validEntries = [];

        // Step 1: In-memory normalization and batch deduplication
        foreach ($rawUrls as $line) {
            $rawUrl = trim($line, " \t\n\r\0\x0B,\"'");
            if (empty($rawUrl)) {
                continue;
            }

            if (! preg_match('#^https?://#i', $rawUrl)) {
                $rawUrl = 'https://'.$rawUrl;
            }

            $parsedDomain = $this->domainService->extractRootDomain($rawUrl);
            if (! $parsedDomain) {
                $skippedCount++;

                continue;
            }

            if (isset($seenInBatch[$rawUrl])) {
                $skippedCount++;

                continue;
            }
            $seenInBatch[$rawUrl] = true;

            $validEntries[] = [
                'url' => $rawUrl,
                'root_domain' => $parsedDomain['root_domain'],
                'tld' => $parsedDomain['tld'],
            ];
        }

        if (empty($validEntries)) {
            return redirect()->route('admin.targets.index')
                ->with('error', "Tidak ada URL valid yang ditemukan ({$skippedCount} baris dilewati karena format tidak valid).");
        }

        // Step 2: Chunked check against existing target URLs in database
        $existingUrls = [];
        $allUrls = array_column($validEntries, 'url');
        foreach (array_chunk($allUrls, 1000) as $urlChunk) {
            $found = TargetUrl::whereIn('url', $urlChunk)->pluck('url')->all();
            foreach ($found as $u) {
                $existingUrls[$u] = true;
            }
        }

        $newEntries = [];
        foreach ($validEntries as $entry) {
            if (isset($existingUrls[$entry['url']])) {
                $skippedCount++;

                continue;
            }
            $newEntries[] = $entry;
        }

        if (empty($newEntries)) {
            return redirect()->route('admin.targets.index')
                ->with('warning', "Semua URL yang Anda masukkan sudah pernah terdaftar di antrean target ({$skippedCount} URL dilewati).");
        }

        // Step 3: Match with existing worked domains (DO NOT create dummy domain records for targets)
        $uniqueRootDomains = [];
        foreach ($newEntries as $entry) {
            $uniqueRootDomains[$entry['root_domain']] = $entry['tld'];
        }

        $existingDomains = [];
        foreach (array_chunk(array_keys($uniqueRootDomains), 1000) as $domainChunk) {
            $domains = Domain::whereIn('root_domain', $domainChunk)->get();
            foreach ($domains as $d) {
                $existingDomains[$d->root_domain] = $d;
            }
        }

        $activePeriod = $this->periodService->getActivePeriod();
        $defaultMaxLimit = $activePeriod?->max_urls_per_domain ?? (int) AppSetting::get('max_urls_per_domain', 5);

        // Step 4: Prepare batch records and bulk insert target URLs
        $targetsToInsert = [];
        $userId = auth()->id();
        $now = now();

        foreach ($newEntries as $entry) {
            $domain = $existingDomains[$entry['root_domain']] ?? null;
            $domainId = $domain?->id;

            $status = 'available';
            if ($domain && ($domain->is_locked || $domain->url_count >= $domain->max_limit)) {
                $status = 'domain_full';
                $domainFullCount++;
            }

            $targetsToInsert[] = [
                'url' => $entry['url'],
                'task_type' => $taskType,
                'client_url' => $clientUrl,
                'domain_id' => $domainId,
                'root_domain' => $entry['root_domain'],
                'keyword' => $validated['keyword'] ?? null,
                'reward_amount' => $rewardAmount,
                'notes' => $validated['notes'] ?? null,
                'status' => $status,
                'created_by_user_id' => $userId,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $importedCount++;
        }

        DB::transaction(function () use ($targetsToInsert) {
            foreach (array_chunk($targetsToInsert, 500) as $chunk) {
                TargetUrl::insert($chunk);
            }
        });

        $message = "Berhasil mengimpor {$importedCount} target URL secara instan.";
        if ($skippedCount > 0) {
            $message .= " ({$skippedCount} URL dilewati karena format tidak valid atau duplikat).";
        }
        if ($domainFullCount > 0) {
            $message .= " ({$domainFullCount} URL masuk status 'Domain Penuh' karena batas {$defaultMaxLimit} URL tercapai).";
        }

        return redirect()->route('admin.targets.index')->with('success', $message);
    }

    public function bulkAction(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'action' => ['required', 'string', 'in:delete,requeue,skip'],
            'target_ids' => ['required', 'array', 'min:1'],
            'target_ids.*' => ['integer', 'exists:target_urls,id'],
        ]);

        $action = $validated['action'];
        $ids = $validated['target_ids'];
        $count = count($ids);

        switch ($action) {
            case 'delete':
                TargetUrl::whereIn('id', $ids)->delete();

                return back()->with('success', "{$count} target URL berhasil dihapus sekaligus.");

            case 'requeue':
                TargetUrl::whereIn('id', $ids)->update([
                    'status' => 'available',
                    'taken_by_user_id' => null,
                    'taken_at' => null,
                ]);

                return back()->with('success', "{$count} target URL berhasil diaktifkan kembali ke antrean siap dikerjakan.");

            case 'skip':
                TargetUrl::whereIn('id', $ids)->update([
                    'status' => 'skipped',
                    'notes' => 'Skip: Massal oleh Admin',
                ]);

                return back()->with('success', "{$count} target URL berhasil ditandai sebagai dilewati (skip).");

            default:
                return back()->with('error', 'Aksi massal tidak dikenali.');
        }
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
