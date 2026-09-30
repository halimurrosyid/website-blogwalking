<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\Domain;
use App\Services\DomainService;
use App\Services\SeoMetricService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DomainController extends Controller
{
    public function index(Request $request, SeoMetricService $seoService): View
    {
        $status = $request->input('status', 'all'); // 'all', 'locked', 'available'
        $search = $request->input('search');

        $query = Domain::query()->withCount('submissions');

        if ($status === 'locked') {
            $query->where('is_locked', true);
        } elseif ($status === 'available') {
            $query->where('is_locked', false);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('root_domain', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhere('ip_subnet', 'like', "%{$search}%");
            });
        }

        $domains = $query->orderByDesc('url_count')->paginate(20)->withQueryString();

        // Detect subnets shared by multiple domains
        $duplicateSubnets = Domain::whereNotNull('ip_subnet')
            ->select('ip_subnet')
            ->groupBy('ip_subnet')
            ->havingRaw('count(*) > 1')
            ->pluck('ip_subnet')
            ->toArray();

        $stats = [
            'total_domains' => Domain::count(),
            'unique_subnets' => Domain::whereNotNull('ip_subnet')->distinct('ip_subnet')->count('ip_subnet'),
            'cluster_subnets' => count($duplicateSubnets),
        ];

        return view('admin.domains.index', [
            'domains' => $domains,
            'currentStatus' => $status,
            'search' => $search,
            'duplicateSubnets' => $duplicateSubnets,
            'stats' => $stats,
            'seoProviders' => $seoService->getProvidersStatus(),
            'hasAnyKey' => $seoService->hasAnyKeyConfigured(),
            'mozToken' => $seoService->getMozToken(),
            'ahrefsKey' => $seoService->getAhrefsKey(),
            'oprKey' => $seoService->getOpenPageRankKey(),
        ]);
    }

    public function show(Domain $domain): View
    {
        $submissions = $domain->submissions()
            ->with('user')
            ->latest()
            ->paginate(20);

        return view('admin.domains.show', [
            'domain' => $domain,
            'submissions' => $submissions,
        ]);
    }

    public function reset(Domain $domain, DomainService $domainService): RedirectResponse
    {
        $domainService->resetDomain($domain);

        return back()->with('success', "Kuota domain [{$domain->root_domain}] telah di-RESET menjadi 0/5 URL. Domain kini aktif kembali.");
    }

    public function bulkReset(Request $request, DomainService $domainService): RedirectResponse
    {
        $request->validate([
            'domain_ids' => ['required', 'array'],
            'domain_ids.*' => ['exists:domains,id'],
        ]);

        $domains = Domain::whereIn('id', $request->input('domain_ids'))->get();
        foreach ($domains as $domain) {
            $domainService->resetDomain($domain);
        }

        return back()->with('success', count($domains).' domain berhasil di-reset kuotanya menjadi 0.');
    }

    /**
     * Update URL quota limit for a specific domain.
     */
    public function updateQuota(Request $request, Domain $domain): RedirectResponse
    {
        $validated = $request->validate([
            'max_limit' => ['required', 'integer', 'min:1', 'max:5000'],
        ]);

        $domain->max_limit = $validated['max_limit'];
        $domain->is_locked = $domain->url_count >= $domain->max_limit;
        $domain->save();

        return back()->with('success', "Batas kuota domain [{$domain->root_domain}] berhasil diubah menjadi {$domain->max_limit} URL.");
    }

    /**
     * Bulk update URL quota limit for selected domains.
     */
    public function bulkUpdateQuota(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'domain_ids' => ['required', 'array'],
            'domain_ids.*' => ['exists:domains,id'],
            'max_limit' => ['required', 'integer', 'min:1', 'max:5000'],
        ]);

        $domains = Domain::whereIn('id', $validated['domain_ids'])->get();
        foreach ($domains as $domain) {
            $domain->max_limit = $validated['max_limit'];
            $domain->is_locked = $domain->url_count >= $domain->max_limit;
            $domain->save();
        }

        return back()->with('success', count($domains)." domain berhasil diubah kuotanya menjadi {$validated['max_limit']} URL.");
    }

    /**
     * Resolve and update IP and Subnet for all domains that haven't been resolved yet.
     */
    public function refreshSubnets(DomainService $domainService): RedirectResponse
    {
        $domains = Domain::whereNull('ip_subnet')->orWhere('ip_subnet', '')->get();
        $updated = 0;

        foreach ($domains as $domain) {
            $ipData = $domainService->resolveIpAndSubnet($domain->root_domain);
            if (! empty($ipData['subnet'])) {
                $domain->update([
                    'ip_address' => $ipData['ip'],
                    'ip_subnet' => $ipData['subnet'],
                ]);
                $updated++;
            }
        }

        return back()->with('success', "Berhasil mendeteksi dan memperbarui IP Subnet untuk {$updated} domain.");
    }

    /**
     * Save API Keys for Moz, Ahrefs, and OpenPageRank with automatic connectivity testing.
     */
    public function saveApiKeys(Request $request, SeoMetricService $seoService): RedirectResponse
    {
        $request->validate([
            'moz_api_token' => ['nullable', 'string', 'max:255'],
            'ahrefs_api_key' => ['nullable', 'string', 'max:255'],
            'openpagerank_api_key' => ['nullable', 'string', 'max:255'],
        ]);

        $ahrefsKey = trim((string) $request->input('ahrefs_api_key'));
        $mozToken = trim((string) $request->input('moz_api_token'));
        $oprKey = trim((string) $request->input('openpagerank_api_key'));

        $errors = [];
        $successes = [];

        // 1. Test & Save Ahrefs Key
        if ($ahrefsKey !== '') {
            $test = $seoService->testAhrefsKey($ahrefsKey);
            if (! $test['success']) {
                $errors[] = $test['message'];
            } else {
                AppSetting::set('ahrefs_api_key', $ahrefsKey);
                $successes[] = 'Ahrefs API Key valid & aktif!';
            }
        } else {
            AppSetting::set('ahrefs_api_key', '');
        }

        // 2. Test & Save Moz Token
        if ($mozToken !== '') {
            $test = $seoService->testMozToken($mozToken);
            if (! $test['success']) {
                $errors[] = $test['message'];
            } else {
                AppSetting::set('moz_api_token', $mozToken);
                $successes[] = 'Moz API Token valid & aktif!';
            }
        } else {
            AppSetting::set('moz_api_token', '');
        }

        // 3. Test & Save OpenPageRank Key
        if ($oprKey !== '') {
            $test = $seoService->testOpenPageRankKey($oprKey);
            if (! $test['success']) {
                $errors[] = $test['message'];
            } else {
                AppSetting::set('openpagerank_api_key', $oprKey);
                $successes[] = 'OpenPageRank Key valid & aktif!';
            }
        } else {
            AppSetting::set('openpagerank_api_key', '');
        }

        if (! empty($errors)) {
            $msg = implode(' | ', $errors);
            if (! empty($successes)) {
                $msg .= ' (Sebagian berhasil: '.implode(', ', $successes).')';
            }

            return back()->withInput()->withErrors(['error' => $msg]);
        }

        $summary = ! empty($successes) ? implode(' ', $successes) : 'Pengaturan API Key SEO berhasil diperbarui.';

        return back()->with('success', $summary);
    }

    /**
     * AJAX endpoint to test a specific API Key before saving.
     */
    public function testApiKey(Request $request, SeoMetricService $seoService): JsonResponse
    {
        $provider = $request->input('provider');
        $key = trim((string) $request->input('key'));

        if ($key === '') {
            return response()->json(['success' => false, 'message' => 'Kunci API tidak boleh kosong.']);
        }

        $result = match ($provider) {
            'ahrefs' => $seoService->testAhrefsKey($key),
            'moz' => $seoService->testMozToken($key),
            'openpagerank' => $seoService->testOpenPageRankKey($key),
            default => ['success' => false, 'message' => 'Provider tidak dikenal.'],
        };

        return response()->json($result);
    }

    /**
     * Fetch SEO metrics from configured APIs for a single domain.
     */
    public function fetchSeo(Domain $domain, SeoMetricService $seoService): RedirectResponse
    {
        if (! $seoService->hasAnyKeyConfigured()) {
            return back()->withErrors(['error' => 'Fitur cek otomatis belum aktif. Super Admin wajib mengisi minimal salah satu API Key (Moz / Ahrefs) pada menu Pengaturan API Key terlebih dahulu.']);
        }

        $res = $seoService->fetchMetricsForDomain($domain);

        if ($res['updated']) {
            $parts = [];
            if ($domain->da !== null) {
                $parts[] = "DA {$domain->da}";
            }
            if ($domain->pa !== null) {
                $parts[] = "PA {$domain->pa}";
            }
            if ($domain->dr !== null) {
                $parts[] = "DR {$domain->dr}";
            }
            if ($domain->pr !== null) {
                $parts[] = "PR {$domain->pr}";
            }
            $msg = "Metrik SEO domain [{$domain->root_domain}] berhasil disinkronkan: ".implode(', ', $parts).'.';
            if (! empty($res['errors'])) {
                $msg .= ' (Catatan kendala sebagian: '.implode(' | ', $res['errors']).')';
            }

            return back()->with('success', $msg);
        }

        $errMsg = ! empty($res['errors'])
            ? "Pengecekan SEO untuk [{$domain->root_domain}] gagal: ".implode(' | ', $res['errors'])
            : "Pengecekan selesai, namun tidak ada perubahan data atau API tidak mengembalikan metrik untuk [{$domain->root_domain}].";

        return back()->with('error', $errMsg);
    }

    /**
     * Bulk fetch SEO metrics for selected or all domains.
     */
    public function bulkFetchSeo(Request $request, SeoMetricService $seoService): RedirectResponse
    {
        if (! $seoService->hasAnyKeyConfigured()) {
            return back()->withErrors(['error' => 'Fitur cek otomatis belum aktif. Super Admin wajib mengisi minimal salah satu API Key (Moz / Ahrefs) pada menu Pengaturan API Key terlebih dahulu.']);
        }

        $domainIds = $request->input('domain_ids');
        $domains = ! empty($domainIds)
            ? Domain::whereIn('id', $domainIds)->get()
            : Domain::take(50)->get();

        $updated = 0;
        foreach ($domains as $domain) {
            $res = $seoService->fetchMetricsForDomain($domain);
            if ($res['updated']) {
                $updated++;
            }
        }

        return back()->with('success', "Pengecekan metrik SEO selesai. {$updated} dari {$domains->count()} domain berhasil diperbarui.");
    }

    /**
     * Update SEO metrics manually for a domain (DA, PA, PR only; DR is locked to Ahrefs).
     */
    public function updateMetrics(Request $request, Domain $domain): RedirectResponse
    {
        $validated = $request->validate([
            'da' => ['nullable', 'integer', 'min:0', 'max:100'],
            'pa' => ['nullable', 'integer', 'min:0', 'max:100'],
            'pr' => ['nullable', 'string', 'max:10'],
        ]);

        $domain->update([
            'da' => $validated['da'] ?? null,
            'pa' => $validated['pa'] ?? null,
            'pr' => $validated['pr'] ?? null,
            'seo_updated_at' => now(),
        ]);

        return back()->with('success', "Nilai metrik SEO domain [{$domain->root_domain}] berhasil disimpan.");
    }
}
