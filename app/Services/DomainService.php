<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\Domain;
use App\Models\Submission;
use App\Models\TargetUrl;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DomainService
{
    /**
     * Common multi-part second-level domain extensions.
     *
     * @var array<string>
     */
    protected static array $multiPartTlds = [
        // Indonesia
        '.co.id', '.web.id', '.ac.id', '.sch.id', '.or.id', '.go.id', '.mil.id', '.net.id', '.biz.id', '.my.id',
        // UK & Commonwealth
        '.co.uk', '.org.uk', '.me.uk', '.gov.uk', '.ac.uk', '.net.uk',
        '.com.au', '.net.au', '.org.au', '.edu.au', '.gov.au',
        '.co.nz', '.net.nz', '.org.nz', '.govt.nz',
        '.com.sg', '.edu.sg', '.gov.sg', '.net.sg',
        '.com.my', '.edu.my', '.gov.my', '.org.my',
        '.co.za', '.org.za', '.web.za',
        '.com.br', '.net.br', '.org.br',
        '.com.mx', '.edu.mx', '.gob.mx',
        '.co.jp', '.ne.jp', '.or.jp',
        '.co.kr', '.or.kr',
        '.com.tr', '.edu.tr',
        '.com.ph', '.edu.ph',
        '.co.th', '.ac.th',
        '.com.vn', '.edu.vn',
        '.com.tw', '.org.tw',
        '.co.in', '.net.in', '.org.in',
    ];

    public function __construct(
        protected ?PeriodService $periodService = null
    ) {
        $this->periodService = $periodService ?? app(PeriodService::class);
    }

    /**
     * Parse any given URL or hostname and extract the root domain and TLD.
     *
     * @return array{root_domain: string, tld: string, hostname: string}|null
     */
    public function extractRootDomain(string $url): ?array
    {
        $trimmed = trim($url);
        if (empty($trimmed)) {
            return null;
        }

        // Security: Reject control characters, newlines, or null bytes
        if (preg_match('/[\x00-\x1F\x7F]/', $trimmed)) {
            return null;
        }

        // Security: Ensure scheme is strictly http or https
        if (preg_match('~^([a-z0-9+.-]+)://~i', $trimmed, $schemeMatches)) {
            $scheme = strtolower($schemeMatches[1]);
            if (! in_array($scheme, ['http', 'https'], true)) {
                return null;
            }
        } else {
            $trimmed = 'https://'.$trimmed;
        }

        $parsed = parse_url($trimmed);
        if (! $parsed || empty($parsed['host'])) {
            return null;
        }

        $host = strtolower($parsed['host']);

        // Security: Reject localhost or raw IP addresses (SSRF / internal network scanning prevention)
        if ($host === 'localhost' || filter_var($host, FILTER_VALIDATE_IP)) {
            return null;
        }

        // Strip leading www. if present
        if (str_starts_with($host, 'www.')) {
            $host = substr($host, 4);
        }

        // 1. Check against explicit multi-part TLD list first
        foreach (self::$multiPartTlds as $multiTld) {
            if (str_ends_with($host, $multiTld)) {
                $withoutTld = substr($host, 0, -strlen($multiTld));
                $parts = explode('.', $withoutTld);
                $rootName = end($parts);

                if (empty($rootName)) {
                    return null;
                }

                return [
                    'root_domain' => $rootName.$multiTld,
                    'tld' => $multiTld,
                    'hostname' => $host,
                ];
            }
        }

        // 2. Universal international ccSLD pattern for all ~200+ countries:
        // Matches .{category}.{2-letter ccTLD} e.g. .com.ng, .org.ar, .co.il, .edu.pl, .gob.mx, etc.
        if (preg_match('/(?:^|\.)([a-z0-9\-]+)\.(co|com|net|org|edu|gov|ac|or|sch|web|biz|info|mil|gob|me|ne|asso|nom|gen|priv|res|med)\.([a-z]{2})$/i', $host, $matches)) {
            $matchedTld = '.'.$matches[2].'.'.$matches[3];
            $rootName = $matches[1];

            return [
                'root_domain' => $rootName.$matchedTld,
                'tld' => $matchedTld,
                'hostname' => $host,
            ];
        }

        // Single TLD handling (e.g., .id, .com, .org, .net, etc.)
        $parts = explode('.', $host);
        $totalParts = count($parts);

        if ($totalParts < 2) {
            return null;
        }

        $tld = '.'.$parts[$totalParts - 1];
        $rootName = $parts[$totalParts - 2];

        return [
            'root_domain' => $rootName.$tld,
            'tld' => $tld,
            'hostname' => $host,
        ];
    }

    /**
     * Check if a domain has remaining comment slots.
     *
     * @return array<string, mixed>
     */
    public function checkDomainAvailability(string $url): array
    {
        $parsed = $this->extractRootDomain($url);

        if (! $parsed) {
            return [
                'success' => false,
                'message' => 'Format URL atau domain tidak valid. Pastikan memasukkan alamat website yang benar.',
                'can_submit' => false,
            ];
        }

        $rootDomain = $parsed['root_domain'];
        $domain = Domain::where('root_domain', $rootDomain)->first();

        $maxLimit = $domain?->max_limit ?? 5;
        $urlCount = $domain?->url_count ?? 0;
        $isLocked = $domain?->is_locked ?? false;

        if ($isLocked || $urlCount >= $maxLimit) {
            return [
                'success' => true,
                'root_domain' => $rootDomain,
                'tld' => $parsed['tld'],
                'url_count' => $urlCount,
                'max_limit' => $maxLimit,
                'remaining_slots' => 0,
                'is_locked' => true,
                'can_submit' => false,
                'message' => "Domain [{$rootDomain}] SUDAH PENUH ({$urlCount}/{$maxLimit} URL). Jangan komentar di web ini, silakan cari website lain.",
            ];
        }

        $remaining = $maxLimit - $urlCount;

        return [
            'success' => true,
            'root_domain' => $rootDomain,
            'tld' => $parsed['tld'],
            'url_count' => $urlCount,
            'max_limit' => $maxLimit,
            'remaining_slots' => $remaining,
            'is_locked' => false,
            'can_submit' => true,
            'message' => "Domain [{$rootDomain}] AMAN! Masih tersedia {$remaining} dari {$maxLimit} slot URL.",
        ];
    }

    /**
     * Known domains exempt from the 5-URL root domain quota (e.g. social media platforms).
     *
     * @var array<string>
     */
    protected static array $unlimitedDomains = [
        'facebook.com',
        'x.com',
        'twitter.com',
        'instagram.com',
        'linkedin.com',
        'pinterest.com',
        'tiktok.com',
        'threads.net',
        'youtube.com',
        'medium.com',
    ];

    /**
     * Resolve IP address and C-Class subnet for a root domain.
     *
     * @return array{ip: ?string, subnet: ?string}
     */
    public function resolveIpAndSubnet(string $rootDomain): array
    {
        try {
            $host = preg_replace('/:\d+$/', '', trim($rootDomain));
            $ip = @gethostbyname($host);

            if ($ip === $host || ! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                return ['ip' => null, 'subnet' => null];
            }

            // Extract C-Class subnet: e.g. 104.21.58.123 -> 104.21.58.0/24
            $parts = explode('.', $ip);
            $subnet = "{$parts[0]}.{$parts[1]}.{$parts[2]}.0/24";

            return [
                'ip' => $ip,
                'subnet' => $subnet,
            ];
        } catch (\Throwable $e) {
            return ['ip' => null, 'subnet' => null];
        }
    }

    /**
     * Safely record a new submission with database lock.
     *
     * @throws ValidationException
     */
    public function recordSubmission(
        User $user,
        string $targetUrl,
        string $screenshotPath,
        string $commentType = 'approved_live',
        string $taskType = 'comment',
        ?string $clientUrl = null,
        ?string $keyword = null,
        ?string $publishedUrl = null,
        ?string $platform = null,
        ?float $rewardAmount = null,
        ?int $domainRating = null,
        ?int $targetUrlId = null
    ): Submission {
        $parsed = $this->extractRootDomain($targetUrl);

        if (! $parsed) {
            throw ValidationException::withMessages([
                'target_url' => 'Format URL tidak valid.',
            ]);
        }

        $rootDomain = $parsed['root_domain'];
        $tld = $parsed['tld'];

        // 1. Get active period and blogwalker assignment
        $activePeriod = $this->periodService->getActivePeriod();
        $assignment = Assignment::where('user_id', $user->id)
            ->when($activePeriod, function ($query, $period) {
                $query->where(function ($q) use ($period) {
                    $q->where('period_id', $period->id)
                        ->orWhereNull('period_id');
                });
            })
            ->latest()
            ->first();

        // 2. Validate eligibility (must not be suspended/disqualified)
        if ($assignment && $assignment->isDisqualified()) {
            throw ValidationException::withMessages([
                'target_url' => 'Akun Anda saat ini ditangguhkan untuk periode ini karena tidak mencapai target minimal periode sebelumnya.',
            ]);
        }

        // 3. Validate assigned TLD restriction (skip for social media tasks)
        if ($taskType !== TaskTypeService::SOCIAL_MEDIA && $assignment && ! empty($assignment->allowed_tlds)) {
            $allowed = array_map(fn ($item) => strtolower(trim($item)), $assignment->allowed_tlds);
            $currentTld = strtolower($tld);

            if (! in_array($currentTld, $allowed, true)) {
                $allowedList = implode(', ', $assignment->allowed_tlds);
                throw ValidationException::withMessages([
                    'target_url' => "Domain ini berekstensi [{$tld}], sedangkan plotting target Anda pada periode ini adalah: [{$allowedList}]. Silakan cari website sesuai plotting Anda.",
                ]);
            }
        }

        // 4. Validate personal maximum submission cap if configured
        if ($assignment && $assignment->max_target) {
            $userTotalSubmissions = $activePeriod->submissions()
                ->where('user_id', $user->id)
                ->whereIn('review_status', ['pending', 'approved'])
                ->count();

            if ($userTotalSubmissions >= $assignment->max_target) {
                throw ValidationException::withMessages([
                    'target_url' => "Anda telah mencapai batas maksimal pengerjaan periode ini ({$assignment->max_target} tugas).",
                ]);
            }
        }

        $maxDomainLimit = $activePeriod->max_urls_per_domain ?? 5;
        $isExemptDomain = in_array(strtolower($rootDomain), self::$unlimitedDomains, true) || $taskType === TaskTypeService::SOCIAL_MEDIA;

        // Resolve rate amount
        $rateToPay = $rewardAmount ?? TaskTypeService::getRate($taskType);
        if ($rateToPay <= 0) {
            $rateToPay = $user->default_rate ?? 700.00;
        }

        return DB::transaction(function () use (
            $user,
            $targetUrl,
            $screenshotPath,
            $commentType,
            $taskType,
            $clientUrl,
            $keyword,
            $publishedUrl,
            $platform,
            $rateToPay,
            $domainRating,
            $targetUrlId,
            $rootDomain,
            $tld,
            $activePeriod,
            $maxDomainLimit,
            $isExemptDomain
        ) {
            // Resolve IP and Subnet
            $ipData = $this->resolveIpAndSubnet($rootDomain);

            // Lock or create domain
            $domain = Domain::lockForUpdate()->firstOrCreate(
                ['root_domain' => $rootDomain],
                [
                    'tld' => $tld,
                    'ip_address' => $ipData['ip'],
                    'ip_subnet' => $ipData['subnet'],
                    'url_count' => 0,
                    'max_limit' => $isExemptDomain ? 999999 : $maxDomainLimit,
                    'is_locked' => false,
                ]
            );

            // Update IP if previously missing
            if (empty($domain->ip_subnet) && ! empty($ipData['subnet'])) {
                $domain->update([
                    'ip_address' => $ipData['ip'],
                    'ip_subnet' => $ipData['subnet'],
                ]);
            }

            if (! $isExemptDomain && ($domain->is_locked || $domain->url_count >= $domain->max_limit)) {
                throw ValidationException::withMessages([
                    'target_url' => "Maaf, domain [{$rootDomain}] sudah mencapai batas maksimal {$domain->max_limit} URL dan telah dikunci.",
                ]);
            }

            // Check if exact target_url has already been submitted (unless social media)
            if (! $isExemptDomain) {
                $duplicate = Submission::where('domain_id', $domain->id)
                    ->where('target_url', $targetUrl)
                    ->exists();

                if ($duplicate) {
                    throw ValidationException::withMessages([
                        'target_url' => 'URL spesifik ini sudah pernah disubmit sebelumnya di sistem.',
                    ]);
                }
            }

            // Create submission linked to active period
            $submission = Submission::create([
                'period_id' => $activePeriod->id,
                'user_id' => $user->id,
                'domain_id' => $domain->id,
                'target_url_id' => $targetUrlId,
                'task_type' => $taskType,
                'target_url' => $targetUrl,
                'client_url' => $clientUrl,
                'keyword' => $keyword,
                'published_url' => $publishedUrl ?? $targetUrl,
                'platform' => $platform,
                'domain_rating' => $domainRating,
                'screenshot_path' => $screenshotPath,
                'comment_type' => $commentType,
                'review_status' => 'pending',
                'rate_amount' => $rateToPay,
                'is_paid' => false,
            ]);

            // Increment count & check limit (if not exempt)
            if (! $isExemptDomain) {
                $domain->url_count += 1;
                if ($domain->url_count >= $domain->max_limit) {
                    $domain->is_locked = true;
                }
                $domain->save();
            }

            // If linked to a target_url, update its status
            if ($targetUrlId) {
                TargetUrl::where('id', $targetUrlId)->update([
                    'status' => 'completed',
                    'submission_id' => $submission->id,
                ]);
            }

            return $submission;
        });
    }

    /**
     * Reset a domain so it can be commented on again.
     */
    public function resetDomain(Domain $domain): void
    {
        $domain->url_count = 0;
        $domain->is_locked = false;
        $domain->last_reset_at = now();
        $domain->save();
    }
}
