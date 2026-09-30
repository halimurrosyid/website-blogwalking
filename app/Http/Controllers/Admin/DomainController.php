<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use App\Services\DomainService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DomainController extends Controller
{
    public function index(Request $request): View
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
}
