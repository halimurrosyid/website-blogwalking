<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\Domain;
use App\Models\Submission;
use App\Models\TargetUrl;
use App\Models\User;
use App\Services\SeoMetricService;
use App\Services\TaskTypeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SeoMetricsIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $worker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
            'approval_status' => 'approved',
        ]);

        $this->worker = User::factory()->create([
            'role' => 'blogwalker',
            'is_active' => true,
            'approval_status' => 'approved',
        ]);
    }

    public function test_super_admin_can_save_api_keys(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.domains.api-keys'), [
            'ahrefs_api_key' => 'ahrefs_test_key_123',
            'moz_api_token' => 'moz_test_token_456',
            'openpagerank_api_key' => 'opr_test_key_789',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertEquals('ahrefs_test_key_123', AppSetting::get('ahrefs_api_key'));
        $this->assertEquals('moz_test_token_456', AppSetting::get('moz_api_token'));
        $this->assertEquals('opr_test_key_789', AppSetting::get('openpagerank_api_key'));

        $seoService = app(SeoMetricService::class);
        $this->assertTrue($seoService->hasAnyKeyConfigured());
    }

    public function test_fetch_seo_blocked_if_no_api_key_configured(): void
    {
        AppSetting::set('ahrefs_api_key', '');
        AppSetting::set('moz_api_token', '');
        AppSetting::set('openpagerank_api_key', '');

        $domain = Domain::create([
            'root_domain' => 'tech-portal.id',
            'tld' => '.id',
            'max_limit' => 5,
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.domains.fetch-seo', $domain));

        $response->assertRedirect();
        $response->assertSessionHasErrors('error');
    }

    public function test_fetch_seo_works_when_api_keys_are_configured_and_http_is_mocked(): void
    {
        AppSetting::set('ahrefs_api_key', 'mock_ahrefs_key');
        AppSetting::set('moz_api_token', 'mock_moz_token');
        AppSetting::set('openpagerank_api_key', 'mock_opr_key');

        Http::fake([
            'https://api.ahrefs.com/v3/public/domain-rating-free*' => Http::response([
                'domain_rating' => 58,
            ], 200),
            'https://lsapi.seomoz.com/v2/url_metrics*' => Http::response([
                'results' => [
                    [
                        'domain_authority' => 45,
                        'page_authority' => 38,
                    ],
                ],
            ], 200),
            'https://openpagerank.com/api/v1.0/getPageRank*' => Http::response([
                'response' => [
                    [
                        'page_rank_decimal' => 4.3,
                        'page_rank_integer' => 4,
                    ],
                ],
            ], 200),
        ]);

        $domain = Domain::create([
            'root_domain' => 'authority-site.com',
            'tld' => '.com',
            'max_limit' => 5,
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.domains.fetch-seo', $domain));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $domain->refresh();
        $this->assertEquals(58, $domain->dr);
        $this->assertEquals(45, $domain->da);
        $this->assertEquals(38, $domain->pa);
        $this->assertEquals('4.3', $domain->pr);
        $this->assertNotNull($domain->seo_updated_at);
    }

    public function test_super_admin_can_update_metrics_manually(): void
    {
        $domain = Domain::create([
            'root_domain' => 'manual-seo.com',
            'tld' => '.com',
            'max_limit' => 5,
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.domains.metrics', $domain), [
            'da' => 50,
            'pa' => 40,
            'dr' => 65,
            'pr' => '5.0',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $domain->refresh();
        $this->assertEquals(50, $domain->da);
        $this->assertEquals(40, $domain->pa);
        $this->assertEquals(65, $domain->dr);
        $this->assertEquals('5.0', $domain->pr);
    }

    public function test_worker_can_see_da_pa_dr_pr_metrics_in_targets_and_submissions(): void
    {
        $domain = Domain::create([
            'root_domain' => 'popular-news.com',
            'tld' => '.com',
            'da' => 60,
            'pa' => 50,
            'dr' => 70,
            'pr' => '6.0',
            'max_limit' => 5,
        ]);

        $target = TargetUrl::create([
            'domain_id' => $domain->id,
            'url' => 'https://popular-news.com/article/1',
            'root_domain' => 'popular-news.com',
            'tld' => '.com',
            'status' => 'available',
            'task_type' => TaskTypeService::COMMENT,
            'max_claims' => 5,
        ]);

        Submission::create([
            'user_id' => $this->worker->id,
            'domain_id' => $domain->id,
            'target_url' => 'https://popular-news.com/article/1',
            'screenshot_path' => 'screenshots/test.png',
            'task_type' => TaskTypeService::COMMENT,
            'review_status' => 'approved',
            'rate_amount' => 700.00,
            'is_paid' => false,
        ]);

        // 1. When API keys are NOT configured, metrics must NOT be visible
        AppSetting::set('moz_api_token', '');
        AppSetting::set('ahrefs_api_key', '');
        AppSetting::set('openpagerank_api_key', '');

        $targetResponse = $this->actingAs($this->worker)->get(route('blogwalker.targets.index'));
        $targetResponse->assertStatus(200);
        $targetResponse->assertDontSee('DA 60');
        $targetResponse->assertDontSee('DR 70');

        $subResponse = $this->actingAs($this->worker)->get(route('blogwalker.submissions.index'));
        $subResponse->assertStatus(200);
        $subResponse->assertDontSee('DA 60');
        $subResponse->assertDontSee('DR 70');

        // 2. When API keys ARE configured, metrics become visible
        AppSetting::set('moz_api_token', 'mock_moz');
        AppSetting::set('ahrefs_api_key', 'mock_ahrefs');
        AppSetting::set('openpagerank_api_key', 'mock_opr');

        $targetResponse = $this->actingAs($this->worker)->get(route('blogwalker.targets.index'));
        $targetResponse->assertStatus(200);
        $targetResponse->assertSee('DA 60');
        $targetResponse->assertSee('PA 50');
        $targetResponse->assertSee('DR 70');
        $targetResponse->assertSee('PR 6.0');

        $subResponse = $this->actingAs($this->worker)->get(route('blogwalker.submissions.index'));
        $subResponse->assertStatus(200);
        $subResponse->assertSee('DA 60');
        $subResponse->assertSee('PA 50');
        $subResponse->assertSee('DR 70');
        $subResponse->assertSee('PR 6.0');
    }
}
