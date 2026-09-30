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
     * Check if Moz API is enabled.
     */
    public static function isMozEnabled(): bool
    {
        $val = trim((string) AppSetting::get('moz_api_token', ''));

        return $val !== '';
    }

    /**
     * Check if Ahrefs API is enabled.
     */
    public static function isAhrefsEnabled(): bool
    {
        $val = trim((string) AppSetting::get('ahrefs_api_key', ''));

        return $val !== '';
    }

    /**
     * Check if OpenPageRank API is enabled.
     */
    public static function isOpenPageRankEnabled(): bool
    {
        $val = trim((string) AppSetting::get('openpagerank_api_key', ''));

        return $val !== '';
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
     * Test an Ahrefs API Key and return validation status and message.
     *
     * @return array{success: bool, message: string, dr?: ?int}
     */
    public function testAhrefsKey(string $key): array
    {
        $key = trim($key);
        if (empty($key)) {
            return ['success' => false, 'message' => 'Ahrefs API Key tidak boleh kosong.'];
        }

        try {
            // First try site-explorer/domain-rating
            $response = Http::timeout(10)
                ->withoutVerifying()
                ->withToken($key)
                ->acceptJson()
                ->get('https://api.ahrefs.com/v3/site-explorer/domain-rating', [
                    'target' => 'google.com',
                    'date' => now()->toDateString(),
                ]);

            if (! $response->successful() && $response->status() === 404) {
                // Fallback to public endpoint
                $response = Http::timeout(10)
                    ->withoutVerifying()
                    ->withToken($key)
                    ->acceptJson()
                    ->get('https://api.ahrefs.com/v3/public/domain-rating-free', [
                        'target' => 'google.com',
                    ]);
            }

            $status = $response->status();
            $data = $response->json();

            if ($response->successful()) {
                $drInt = $this->extractDrFromResponse($data);

                return [
                    'success' => true,
                    'message' => 'Koneksi Ahrefs BERHASIL! (Test DR google.com: '.($drInt !== null ? $drInt : 'Terhubung').')',
                    'dr' => $drInt,
                ];
            }

            $errorMsg = $data['error']['message'] ?? $data['message'] ?? $response->body();
            if ($status === 403) {
                return [
                    'success' => false,
                    'message' => "Ahrefs API Menolak (403 Forbidden): {$errorMsg}. Akun Ahrefs Anda belum memiliki izin/paket API v3.",
                ];
            }

            if ($status === 401) {
                return [
                    'success' => false,
                    'message' => 'Ahrefs API Key SALAH / TIDAK VALID (401 Unauthorized). Silakan cek kembali API Key di dashboard Ahrefs.',
                ];
            }

            return [
                'success' => false,
                'message' => "Gagal terhubung ke Ahrefs (HTTP {$status}): {$errorMsg}",
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Kesalahan koneksi ke server Ahrefs: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Test a Moz API Token and return validation status and message.
     *
     * @return array{success: bool, message: string, da?: ?int, pa?: ?int}
     */
    public function testMozToken(string $token): array
    {
        $token = trim($token);
        if (empty($token)) {
            return ['success' => false, 'message' => 'Moz API Token tidak boleh kosong.'];
        }

        try {
            $request = Http::timeout(10)->withoutVerifying()->acceptJson();

            if (str_contains($token, ':')) {
                [$accessId, $secretKey] = explode(':', $token, 2);
                $request = $request->withBasicAuth(trim($accessId), trim($secretKey));
            } else {
                $request = $request->withHeaders(['x-moz-token' => $token]);
            }

            $response = $request->post('https://lsapi.seomoz.com/v2/url_metrics', [
                'targets' => ['google.com'],
            ]);

            $status = $response->status();
            $data = $response->json();

            if ($response->successful()) {
                $result = $data['results'][0] ?? null;
                $da = isset($result['domain_authority']) ? (int) round((float) $result['domain_authority']) : null;
                $pa = isset($result['page_authority']) ? (int) round((float) $result['page_authority']) : null;

                return [
                    'success' => true,
                    'message' => "Koneksi Moz BERHASIL! (Test google.com -> DA: {$da}, PA: {$pa})",
                    'da' => $da,
                    'pa' => $pa,
                ];
            }

            $errorMsg = $data['error_message'] ?? $data['message'] ?? $response->body();
            if ($status === 401 || $status === 403) {
                return [
                    'success' => false,
                    'message' => "Moz Token SALAH / TIDAK VALID (HTTP {$status}): {$errorMsg}",
                ];
            }

            return [
                'success' => false,
                'message' => "Gagal terhubung ke Moz (HTTP {$status}): {$errorMsg}",
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Kesalahan koneksi ke server Moz: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Test an OpenPageRank API Key and return validation status and message.
     *
     * @return array{success: bool, message: string, pr?: ?string}
     */
    public function testOpenPageRankKey(string $key): array
    {
        $key = trim($key);
        if (empty($key)) {
            return ['success' => false, 'message' => 'OpenPageRank Key tidak boleh kosong.'];
        }

        try {
            $response = Http::timeout(10)
                ->withoutVerifying()
                ->withHeaders(['API-OPR' => $key])
                ->acceptJson()
                ->get('https://openpagerank.com/api/v1.0/getPageRank', [
                    'domains' => ['google.com'],
                ]);

            $status = $response->status();
            $data = $response->json();

            if ($response->successful()) {
                $item = $data['response'][0] ?? null;
                $pr = isset($item['page_rank_decimal']) ? (string) round((float) $item['page_rank_decimal'], 1) : ($item['page_rank_integer'] ?? '-');

                return [
                    'success' => true,
                    'message' => "Koneksi OpenPageRank BERHASIL! (Test google.com -> PR: {$pr})",
                    'pr' => (string) $pr,
                ];
            }

            $errorMsg = $data['error'] ?? $response->body();

            return [
                'success' => false,
                'message' => "Gagal terhubung ke OpenPageRank (HTTP {$status}): {$errorMsg}",
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Kesalahan koneksi ke OpenPageRank: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Clean and normalize domain name for SEO APIs.
     */
    public function cleanTargetDomain(string $domain): string
    {
        $domain = trim($domain);
        $domain = preg_replace('#^https?://#i', '', $domain);
        $domain = explode('/', $domain)[0];
        $domain = explode(':', $domain)[0];

        return strtolower(trim($domain));
    }

    /**
     * Safely extract numeric DR value from various Ahrefs API response structures.
     */
    public function extractDrFromResponse(mixed $data): ?int
    {
        if (! is_array($data)) {
            return null;
        }

        // 1. Nested: { "domain_rating": { "domain_rating": 89.0, ... } }
        if (isset($data['domain_rating']) && is_array($data['domain_rating'])) {
            $inner = $data['domain_rating']['domain_rating'] ?? $data['domain_rating']['dr'] ?? null;
            if ($inner !== null && is_numeric($inner)) {
                return (int) round((float) $inner);
            }
        }

        // 2. Nested under 'data' or 'result'
        if (isset($data['data']) && is_array($data['data'])) {
            $val = $this->extractDrFromResponse($data['data']);
            if ($val !== null) {
                return $val;
            }
        }
        if (isset($data['result']) && is_array($data['result'])) {
            $val = $this->extractDrFromResponse($data['result']);
            if ($val !== null) {
                return $val;
            }
        }

        // 3. Flat: { "domain_rating": 89.0 } or { "domainRating": 89.0 } or { "dr": 89 }
        foreach (['domain_rating', 'domainRating', 'dr'] as $key) {
            if (isset($data[$key]) && is_numeric($data[$key])) {
                return (int) round((float) $data[$key]);
            }
        }

        return null;
    }

    /**
     * Fetch Domain Rating (DR) detailed from Ahrefs official API if key is present.
     *
     * @return array{dr: ?int, error: ?string}
     */
    public function fetchAhrefsDrDetailed(string $rootDomain): array
    {
        $key = $this->getAhrefsKey();
        if (! $key) {
            return ['dr' => null, 'error' => null];
        }

        $targetDomain = $this->cleanTargetDomain($rootDomain);

        try {
            $response = Http::timeout(8)
                ->withoutVerifying()
                ->withToken($key)
                ->acceptJson()
                ->get('https://api.ahrefs.com/v3/site-explorer/domain-rating', [
                    'target' => $targetDomain,
                    'date' => now()->toDateString(),
                ]);

            if (! $response->successful() && in_array($response->status(), [403, 404])) {
                $fallback = Http::timeout(8)
                    ->withoutVerifying()
                    ->withToken($key)
                    ->acceptJson()
                    ->get('https://api.ahrefs.com/v3/public/domain-rating-free', [
                        'target' => $targetDomain,
                        'output' => 'json',
                    ]);

                if ($fallback->successful()) {
                    $response = $fallback;
                }
            }

            if ($response->successful()) {
                $data = $response->json();
                $dr = $this->extractDrFromResponse($data);

                if ($dr !== null) {
                    return ['dr' => $dr, 'error' => null];
                }

                Log::warning("Ahrefs DR response could not parse DR for {$targetDomain}: ".$response->body());

                return ['dr' => null, 'error' => 'Ahrefs tidak menemukan data DR untuk domain ini.'];
            }

            $status = $response->status();
            $data = $response->json();
            $msg = $data['error']['message'] ?? $data['message'] ?? $response->body();

            if ($status === 401) {
                return ['dr' => null, 'error' => 'Ahrefs API Key salah atau kadaluarsa (HTTP 401 Unauthorized). Silakan cek kembali API Key di dashboard Ahrefs.'];
            }

            if ($status === 403) {
                return ['dr' => null, 'error' => "Ahrefs API menolak (HTTP 403 Forbidden): {$msg}. Akun Ahrefs belum memiliki paket API v3."];
            }

            Log::warning("Ahrefs DR API returned status {$status} for {$targetDomain}: {$response->body()}");

            return ['dr' => null, 'error' => "Ahrefs mengembalikan HTTP {$status}: {$msg}"];
        } catch (\Throwable $e) {
            Log::warning("Ahrefs DR API error for {$targetDomain}: {$e->getMessage()}");

            return ['dr' => null, 'error' => 'Koneksi ke Ahrefs gagal: '.$e->getMessage()];
        }
    }

    /**
     * Fetch Domain Rating (DR) from Ahrefs official API if key is present.
     */
    public function fetchAhrefsDr(string $rootDomain): ?int
    {
        return $this->fetchAhrefsDrDetailed($rootDomain)['dr'];
    }

    /**
     * Fetch Domain Authority (DA) & Page Authority (PA) from Moz API v2 if token is present.
     *
     * @return array{da: ?int, pa: ?int, error?: ?string}
     */
    public function fetchMozMetrics(string $rootDomain): array
    {
        $token = $this->getMozToken();
        if (! $token) {
            return ['da' => null, 'pa' => null, 'error' => null];
        }

        $targetDomain = $this->cleanTargetDomain($rootDomain);

        try {
            $request = Http::timeout(8)->withoutVerifying()->acceptJson();

            // Check if token is Basic Auth (access_id:secret_key) or Bearer/x-moz-token
            if (str_contains($token, ':')) {
                [$accessId, $secretKey] = explode(':', $token, 2);
                $request = $request->withBasicAuth(trim($accessId), trim($secretKey));
            } else {
                $request = $request->withHeaders(['x-moz-token' => $token]);
            }

            $response = $request->post('https://lsapi.seomoz.com/v2/url_metrics', [
                'targets' => [$targetDomain],
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

                    return ['da' => $da, 'pa' => $pa, 'error' => null];
                }
            }

            $status = $response->status();
            $data = $response->json();
            $msg = $data['error'] ?? $data['message'] ?? $response->body();

            if ($status === 401) {
                return ['da' => null, 'pa' => null, 'error' => 'Moz Token salah atau tidak valid (HTTP 401 Unauthorized). Silakan cek kembali di moz.com.'];
            }

            Log::warning("Moz API returned status {$status} for {$rootDomain}: {$response->body()}");

            return ['da' => null, 'pa' => null, 'error' => "Moz mengembalikan HTTP {$status}: {$msg}"];
        } catch (\Throwable $e) {
            Log::warning("Moz API error for {$rootDomain}: {$e->getMessage()}");

            return ['da' => null, 'pa' => null, 'error' => 'Koneksi ke Moz gagal: '.$e->getMessage()];
        }
    }

    /**
     * Fetch PageRank (PR) detailed from OpenPageRank API if key is present.
     *
     * @return array{pr: ?string, error: ?string}
     */
    public function fetchOpenPageRankDetailed(string $rootDomain): array
    {
        $key = $this->getOpenPageRankKey();
        if (! $key) {
            return ['pr' => null, 'error' => null];
        }

        $targetDomain = $this->cleanTargetDomain($rootDomain);

        try {
            $response = Http::timeout(8)
                ->withoutVerifying()
                ->withHeaders(['API-OPR' => $key])
                ->acceptJson()
                ->get('https://openpagerank.com/api/v1.0/getPageRank', [
                    'domains' => [$targetDomain],
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $item = $data['response'][0] ?? null;

                if ($item && isset($item['page_rank_decimal'])) {
                    return ['pr' => (string) round((float) $item['page_rank_decimal'], 1), 'error' => null];
                }

                if ($item && isset($item['page_rank_integer'])) {
                    return ['pr' => (string) $item['page_rank_integer'], 'error' => null];
                }

                return ['pr' => null, 'error' => 'OpenPageRank tidak menemukan data untuk domain ini.'];
            }

            $status = $response->status();
            $data = $response->json();
            $msg = $data['error'] ?? $response->body();

            if ($status === 401 || $status === 403) {
                return ['pr' => null, 'error' => "OpenPageRank API Key ditolak (HTTP {$status}). Silakan cek kembali API Key Anda."];
            }

            Log::warning("OpenPageRank API returned status {$status} for {$rootDomain}: {$response->body()}");

            return ['pr' => null, 'error' => "OpenPageRank HTTP {$status}: {$msg}"];
        } catch (\Throwable $e) {
            Log::warning("OpenPageRank API error for {$rootDomain}: {$e->getMessage()}");

            return ['pr' => null, 'error' => 'Koneksi ke OpenPageRank gagal: '.$e->getMessage()];
        }
    }

    /**
     * Fetch PageRank (PR) from OpenPageRank API if key is present.
     */
    public function fetchOpenPageRank(string $rootDomain): ?string
    {
        return $this->fetchOpenPageRankDetailed($rootDomain)['pr'];
    }

    /**
     * Fetch all available metrics for a single domain and save them.
     *
     * @return array{da: ?int, pa: ?int, dr: ?int, pr: ?string, updated: bool, errors: array<string>}
     */
    public function fetchMetricsForDomain(Domain $domain): array
    {
        $rootDomain = $domain->root_domain;
        $updated = false;
        $errors = [];

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
            if ($moz['da'] === null && $moz['pa'] === null && ! empty($moz['error'])) {
                $errors[] = $moz['error'];
            }
        }

        // 2. Ahrefs DR
        if ($this->getAhrefsKey()) {
            $ahrefs = $this->fetchAhrefsDrDetailed($rootDomain);
            if ($ahrefs['dr'] !== null) {
                $domain->dr = $ahrefs['dr'];
                $updated = true;
            } elseif (! empty($ahrefs['error'])) {
                $errors[] = $ahrefs['error'];
            }
        }

        // 3. OpenPageRank PR
        if ($this->getOpenPageRankKey()) {
            $opr = $this->fetchOpenPageRankDetailed($rootDomain);
            if ($opr['pr'] !== null) {
                $domain->pr = $opr['pr'];
                $updated = true;
            } elseif (! empty($opr['error'])) {
                $errors[] = $opr['error'];
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
            'errors' => $errors,
        ];
    }
}
