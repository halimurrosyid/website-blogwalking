<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\Domain;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SeoMetricService
{
    /**
     * Get configured Moz API Token (or AccessID:SecretKey).
     */
    public function getMozToken(): ?string
    {
        $val = trim((string) AppSetting::get('moz_api_token', ''));

        return $val !== '' ? $val : null;
    }

    /**
     * Get configured Ahrefs API Key.
     */
    public function getAhrefsKey(): ?string
    {
        $val = trim((string) AppSetting::get('ahrefs_api_key', ''));

        return $val !== '' ? $val : null;
    }

    /**
     * Get configured OpenPageRank API Key.
     */
    public function getOpenPageRankKey(): ?string
    {
        $val = trim((string) AppSetting::get('openpagerank_api_key', ''));

        return $val !== '' ? $val : null;
    }

    /**
     * Check if at least one SEO API provider has an active key.
     */
    public function hasAnyKeyConfigured(): bool
    {
        return $this->getMozToken() !== null
            || $this->getAhrefsKey() !== null
            || $this->getOpenPageRankKey() !== null;
    }

    /**
     * Get status of each API provider configuration.
     *
     * @return array<string, bool>
     */
    public function getProvidersStatus(): array
    {
        return [
            'moz' => $this->getMozToken() !== null,
            'ahrefs' => $this->getAhrefsKey() !== null,
            'openpagerank' => $this->getOpenPageRankKey() !== null,
        ];
    }

    /**
     * Fetch Domain Rating (DR) from Ahrefs official API if key is present.
     */
    public function fetchAhrefsDr(string $rootDomain): ?int
    {
        $key = $this->getAhrefsKey();
        if (! $key) {
            return null;
        }

        try {
            $response = Http::timeout(8)
                ->withToken($key)
                ->acceptJson()
                ->get('https://api.ahrefs.com/v3/public/domain-rating-free', [
                    'target' => $rootDomain,
                    'output' => 'json',
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $dr = $data['domain_rating'] ?? $data['domainRating'] ?? $data['dr'] ?? null;

                if ($dr !== null && is_numeric($dr)) {
                    return (int) round((float) $dr);
                }
            } else {
                Log::warning("Ahrefs DR API returned status {$response->status()} for {$rootDomain}: {$response->body()}");
            }
        } catch (\Throwable $e) {
            Log::warning("Ahrefs DR API error for {$rootDomain}: {$e->getMessage()}");
        }

        return null;
    }

    /**
     * Fetch Domain Authority (DA) & Page Authority (PA) from Moz API v2 if token is present.
     *
     * @return array{da: ?int, pa: ?int}
     */
    public function fetchMozMetrics(string $rootDomain): array
    {
        $token = $this->getMozToken();
        if (! $token) {
            return ['da' => null, 'pa' => null];
        }

        try {
            $request = Http::timeout(8)->acceptJson();

            // Check if token is Basic Auth (access_id:secret_key) or Bearer/x-moz-token
            if (str_contains($token, ':')) {
                [$accessId, $secretKey] = explode(':', $token, 2);
                $request = $request->withBasicAuth(trim($accessId), trim($secretKey));
            } else {
                $request = $request->withHeaders(['x-moz-token' => $token]);
            }

            $response = $request->post('https://lsapi.seomoz.com/v2/url_metrics', [
                'targets' => [$rootDomain],
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $result = $data['results'][0] ?? null;

                if ($result) {
                    $da = isset($result['domain_authority']) && is_numeric($result['domain_authority'])
                        ? (int) round((float) $result['domain_authority'])
                        : null;

                    $pa = isset($result['page_authority']) && is_numeric($result['page_authority'])
                        ? (int) round((float) $result['page_authority'])
                        : null;

                    return ['da' => $da, 'pa' => $pa];
                }
            } else {
                Log::warning("Moz API returned status {$response->status()} for {$rootDomain}: {$response->body()}");
            }
        } catch (\Throwable $e) {
            Log::warning("Moz API error for {$rootDomain}: {$e->getMessage()}");
        }

        return ['da' => null, 'pa' => null];
    }

    /**
     * Fetch PageRank (PR) from OpenPageRank API if key is present.
     */
    public function fetchOpenPageRank(string $rootDomain): ?string
    {
        $key = $this->getOpenPageRankKey();
        if (! $key) {
            return null;
        }

        try {
            $response = Http::timeout(8)
                ->withHeaders(['API-OPR' => $key])
                ->acceptJson()
                ->get('https://openpagerank.com/api/v1.0/getPageRank', [
                    'domains' => [$rootDomain],
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $item = $data['response'][0] ?? null;

                if ($item && isset($item['page_rank_decimal'])) {
                    return (string) round((float) $item['page_rank_decimal'], 1);
                }

                if ($item && isset($item['page_rank_integer'])) {
                    return (string) $item['page_rank_integer'];
                }
            } else {
                Log::warning("OpenPageRank API returned status {$response->status()} for {$rootDomain}: {$response->body()}");
            }
        } catch (\Throwable $e) {
            Log::warning("OpenPageRank API error for {$rootDomain}: {$e->getMessage()}");
        }

        return null;
    }

    /**
     * Fetch all available metrics for a single domain and save them.
     *
     * @return array{da: ?int, pa: ?int, dr: ?int, pr: ?string, updated: bool}
     */
    public function fetchMetricsForDomain(Domain $domain): array
    {
        $rootDomain = $domain->root_domain;
        $updated = false;

        // 1. Moz DA & PA
        if ($this->getMozToken()) {
            $moz = $this->fetchMozMetrics($rootDomain);
            if ($moz['da'] !== null) {
                $domain->da = $moz['da'];
                $updated = true;
            }
            if ($moz['pa'] !== null) {
                $domain->pa = $moz['pa'];
                $updated = true;
            }
        }

        // 2. Ahrefs DR
        if ($this->getAhrefsKey()) {
            $dr = $this->fetchAhrefsDr($rootDomain);
            if ($dr !== null) {
                $domain->dr = $dr;
                $updated = true;
            }
        }

        // 3. OpenPageRank PR
        if ($this->getOpenPageRankKey()) {
            $pr = $this->fetchOpenPageRank($rootDomain);
            if ($pr !== null) {
                $domain->pr = $pr;
                $updated = true;
            }
        }

        if ($updated) {
            $domain->seo_updated_at = now();
            $domain->save();
        }

        return [
            'da' => $domain->da,
            'pa' => $domain->pa,
            'dr' => $domain->dr,
            'pr' => $domain->pr,
            'updated' => $updated,
        ];
    }
}
