<?php

namespace Tests\Feature;

use App\Models\Domain;
use App\Models\Submission;
use App\Models\TargetUrl;
use App\Models\User;
use App\Services\DomainService;
use App\Services\TaskTypeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class MultiTaskAndIpSubnetTest extends TestCase
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

    public function test_super_admin_can_update_master_task_rates(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.system.update-rates'), [
            'rate_comment' => 800,
            'rate_guestpost' => 2500,
            'rate_social_media' => 1750,
            'rate_comment_high_dr' => 1200,
            'rate_internal_article' => 15000,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertEquals(800.0, TaskTypeService::getRate('comment'));
        $this->assertEquals(2500.0, TaskTypeService::getRate('guestpost'));
        $this->assertEquals(1750.0, TaskTypeService::getRate('social_media'));
        $this->assertEquals(1200.0, TaskTypeService::getRate('comment_high_dr'));
        $this->assertEquals(15000.0, TaskTypeService::getRate('internal_article'));
    }

    public function test_super_admin_can_create_targets_with_task_types_and_custom_reward(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.targets.store'), [
            'urls' => "https://techno-blog.com/seo-tips/\nhttps://bisnis-online.id/cara-marketing/",
            'task_type' => 'guestpost',
            'reward_amount' => 3000,
            'client_url' => 'https://klien-kami.com/layanan-seo',
            'keyword' => 'Jasa SEO Handal',
            'notes' => 'Artikel minimal 600 kata dan lolos Copyscape',
        ]);

        $response->assertRedirect(route('admin.targets.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('target_urls', [
            'url' => 'https://techno-blog.com/seo-tips/',
            'task_type' => 'guestpost',
            'reward_amount' => 3000.00,
            'client_url' => 'https://klien-kami.com/layanan-seo',
            'keyword' => 'Jasa SEO Handal',
        ]);

        $target = TargetUrl::where('url', 'https://techno-blog.com/seo-tips/')->first();
        $this->assertEquals(3000.00, $target->getEffectiveRate());

        // Domain record should NOT be created for raw unworked targets
        $this->assertDatabaseMissing('domains', [
            'root_domain' => 'techno-blog.com',
        ]);
    }

    public function test_worker_can_claim_and_submit_guestpost_task(): void
    {
        $domain = Domain::create([
            'root_domain' => 'medium-external.org',
            'tld' => '.org',
            'url_count' => 0,
            'max_limit' => 5,
        ]);

        $target = TargetUrl::create([
            'domain_id' => $domain->id,
            'root_domain' => 'medium-external.org',
            'url' => 'https://medium-external.org/submit-article',
            'task_type' => 'guestpost',
            'reward_amount' => 2000.00,
            'client_url' => 'https://klien-kami.com/produk',
            'keyword' => 'Beli Backlink Berkualitas',
            'status' => 'available',
        ]);

        // Worker claims the target
        $this->actingAs($this->worker)->post(route('blogwalker.targets.claim', $target->id));
        $target->refresh();
        $this->assertEquals('in_progress', $target->status);
        $this->assertEquals($this->worker->id, $target->taken_by_user_id);

        // Worker submits proof
        $samplePngBase64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

        $response = $this->actingAs($this->worker)->post(route('blogwalker.submissions.store'), [
            'target_id' => $target->id,
            'target_url' => $target->url,
            'task_type' => 'guestpost',
            'published_url' => 'https://medium-external.org/postingan-saya-live',
            'client_url' => 'https://klien-kami.com/produk',
            'keyword' => 'Beli Backlink Berkualitas',
            'screenshot_base64' => $samplePngBase64,
        ]);

        $response->assertRedirect(route('blogwalker.submissions.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('submissions', [
            'user_id' => $this->worker->id,
            'target_url' => $target->url,
            'task_type' => 'guestpost',
            'published_url' => 'https://medium-external.org/postingan-saya-live',
            'client_url' => 'https://klien-kami.com/produk',
            'keyword' => 'Beli Backlink Berkualitas',
            'rate_amount' => 2000.00,
            'review_status' => 'pending',
        ]);

        $target->refresh();
        $this->assertEquals('completed', $target->status);
    }

    public function test_social_media_tasks_are_exempt_from_five_domain_quota(): void
    {
        $domainService = app(DomainService::class);
        $samplePngBase64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

        // Submit 6 social media posts on facebook.com with different accounts
        for ($i = 1; $i <= 6; $i++) {
            $response = $this->actingAs($this->worker)->post(route('blogwalker.submissions.store'), [
                'target_url' => "https://facebook.com/group/post/{$i}",
                'social_account' => "akun_facebook_{$i}",
                'task_type' => 'social_media',
                'platform' => 'Facebook',
                'published_url' => "https://facebook.com/group/post/{$i}",
                'client_url' => 'https://klien-kami.com',
                'keyword' => 'SEO Facebook',
                'screenshot_base64' => $samplePngBase64,
            ]);

            $response->assertRedirect(route('blogwalker.submissions.index'));
            $response->assertSessionHas('success');
        }

        // Facebook domain should not be locked because social media is exempt
        $domain = Domain::where('root_domain', 'facebook.com')->first();
        $this->assertNotNull($domain);
        $this->assertFalse($domain->is_locked);
    }

    public function test_ip_subnet_detection_and_duplicate_cluster_tracking(): void
    {
        $domainService = app(DomainService::class);

        // Test subnet resolution logic
        $resolved = $domainService->resolveIpAndSubnet('google.com');
        $this->assertNotNull($resolved['ip']);
        $this->assertNotNull($resolved['subnet']);
        $this->assertStringEndsWith('/24', $resolved['subnet']);

        // Create two domains with identical subnet manually to test cluster detection
        $d1 = Domain::create([
            'root_domain' => 'pbn-site1.com',
            'tld' => '.com',
            'ip_address' => '192.168.1.10',
            'ip_subnet' => '192.168.1.0/24',
            'url_count' => 1,
            'max_limit' => 5,
        ]);

        $d2 = Domain::create([
            'root_domain' => 'pbn-site2.com',
            'tld' => '.com',
            'ip_address' => '192.168.1.55',
            'ip_subnet' => '192.168.1.0/24',
            'url_count' => 1,
            'max_limit' => 5,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.domains.index'));
        $response->assertStatus(200);
        $response->assertSee('192.168.1.0/24');
        $response->assertSee('Cluster');
    }

    public function test_admin_can_approve_submission_and_reward_amount_is_retained(): void
    {
        $domain = Domain::create([
            'root_domain' => 'high-authority.edu',
            'tld' => '.edu',
            'url_count' => 1,
            'max_limit' => 5,
        ]);

        $submission = Submission::create([
            'user_id' => $this->worker->id,
            'domain_id' => $domain->id,
            'target_url' => 'https://high-authority.edu/forum/topic-1',
            'task_type' => 'comment_high_dr',
            'domain_rating' => 65,
            'rate_amount' => 1000.00,
            'screenshot_path' => 'screenshots/test.png',
            'review_status' => 'pending',
            'is_paid' => false,
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.reviews.approve', $submission->id));
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $submission->refresh();
        $this->assertEquals('approved', $submission->review_status);
        $this->assertEquals(1000.00, (float) $submission->rate_amount);
    }

    public function test_social_media_account_limit_is_capped_at_two_submissions(): void
    {
        $domainService = app(DomainService::class);

        // First submission with @budi_walker -> Success
        $sub1 = $domainService->recordSubmission(
            user: $this->worker,
            targetUrl: 'https://x.com/budi_walker/status/123456',
            screenshotPath: 'screenshots/test1.png',
            taskType: TaskTypeService::SOCIAL_MEDIA,
            socialAccount: '@budi_walker'
        );

        $this->assertEquals('budi_walker', $sub1->social_account);

        // Second submission with URL format -> Success (normalized to budi_walker)
        $sub2 = $domainService->recordSubmission(
            user: $this->worker,
            targetUrl: 'https://instagram.com/budi_walker/p/789',
            screenshotPath: 'screenshots/test2.png',
            taskType: TaskTypeService::SOCIAL_MEDIA,
            socialAccount: 'https://instagram.com/budi_walker'
        );

        $this->assertEquals('budi_walker', $sub2->social_account);

        // Third submission with same account -> Must throw ValidationException
        $this->expectException(ValidationException::class);
        $domainService->recordSubmission(
            user: $this->worker,
            targetUrl: 'https://threads.net/@budi_walker/post/999',
            screenshotPath: 'screenshots/test3.png',
            taskType: TaskTypeService::SOCIAL_MEDIA,
            socialAccount: 'budi_walker'
        );
    }
}
