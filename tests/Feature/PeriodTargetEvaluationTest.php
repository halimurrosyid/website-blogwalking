<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Domain;
use App\Models\Period;
use App\Models\User;
use App\Services\PeriodService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PeriodTargetEvaluationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $blogwalkerA;

    protected User $blogwalkerB;

    protected Period $period;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);

        $this->blogwalkerA = User::factory()->create([
            'name' => 'Blogwalker A',
            'role' => 'blogwalker',
            'default_rate' => 700.00,
        ]);

        $this->blogwalkerB = User::factory()->create([
            'name' => 'Blogwalker B',
            'role' => 'blogwalker',
            'default_rate' => 700.00,
        ]);

        $periodService = app(PeriodService::class);
        $this->period = $periodService->getActivePeriod();

        // Configure active period: min 5 approved URLs, max 3 URLs per domain
        $periodService->updatePeriodConfiguration($this->period, [
            'min_target' => 5,
            'max_urls_per_domain' => 3,
            'max_target' => 50,
            'starts_at' => now()->startOfMonth()->toDateString(),
            'ends_at' => now()->endOfMonth()->toDateString(),
        ]);

        // Plotting Blogwalker A: Only .co.id and .web.id
        Assignment::updateOrCreate(
            ['user_id' => $this->blogwalkerA->id, 'period_id' => $this->period->id],
            [
                'allowed_tlds' => ['.co.id', '.web.id'],
                'target_keywords' => 'jasa seo website',
                'target_backlink_url' => 'https://klien-a.com',
                'min_target' => 5,
                'status' => 'active',
                'is_eligible_next_period' => true,
                'is_active' => true,
            ]
        );

        // Plotting Blogwalker B: Only .com
        Assignment::updateOrCreate(
            ['user_id' => $this->blogwalkerB->id, 'period_id' => $this->period->id],
            [
                'allowed_tlds' => ['.com'],
                'target_keywords' => 'backlink premium',
                'target_backlink_url' => 'https://klien-b.com',
                'min_target' => 5,
                'status' => 'active',
                'is_eligible_next_period' => true,
                'is_active' => true,
            ]
        );
    }

    public function test_admin_can_update_period_and_domain_limits(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.periods.config', $this->period->id), [
            'min_target' => 150,
            'max_urls_per_domain' => 8,
            'max_target' => 500,
            'starts_at' => now()->startOfMonth()->toDateString(),
            'ends_at' => now()->endOfMonth()->toDateString(),
        ]);

        $response->assertRedirect(route('admin.periods.index'));

        $this->period->refresh();
        $this->assertEquals(150, $this->period->min_target);
        $this->assertEquals(8, $this->period->max_urls_per_domain);
        $this->assertEquals(500, $this->period->max_target);
    }

    public function test_blogwalker_cannot_submit_domain_outside_assigned_tlds(): void
    {
        Storage::fake('public');

        // Blogwalker A is only assigned .co.id and .web.id
        // Trying to submit a .com domain must be rejected
        $response = $this->actingAs($this->blogwalkerA)->post(route('blogwalker.submissions.store'), [
            'target_url' => 'https://portalbisnis.com/artikel-seo',
            'comment_type' => 'approved_live',
            'screenshot_file' => UploadedFile::fake()->image('proof.png'),
        ]);

        $response->assertSessionHasErrors(['target_url']);
        $this->assertDatabaseMissing('submissions', [
            'target_url' => 'https://portalbisnis.com/artikel-seo',
        ]);

        // Submitting assigned .co.id must succeed
        $responseValid = $this->actingAs($this->blogwalkerA)->post(route('blogwalker.submissions.store'), [
            'target_url' => 'https://portalbisnis.co.id/artikel-seo',
            'comment_type' => 'approved_live',
            'screenshot_file' => UploadedFile::fake()->image('proof.png'),
        ]);

        $responseValid->assertRedirect(route('blogwalker.submissions.index'));
        $this->assertDatabaseHas('submissions', [
            'target_url' => 'https://portalbisnis.co.id/artikel-seo',
            'user_id' => $this->blogwalkerA->id,
            'period_id' => $this->period->id,
        ]);
    }

    public function test_dynamic_domain_limit_enforced(): void
    {
        Storage::fake('public');

        // Max URLs per domain was configured to 3
        for ($i = 1; $i <= 3; $i++) {
            $res = $this->actingAs($this->blogwalkerA)->post(route('blogwalker.submissions.store'), [
                'target_url' => 'https://beritaviral.co.id/post-'.$i,
                'comment_type' => 'approved_live',
                'screenshot_file' => UploadedFile::fake()->image('proof.png'),
            ]);
            $res->assertRedirect(route('blogwalker.submissions.index'));
        }

        $domain = Domain::where('root_domain', 'beritaviral.co.id')->first();
        $this->assertEquals(3, $domain->url_count);
        $this->assertTrue($domain->is_locked);

        // 4th submission must be rejected
        $res4 = $this->actingAs($this->blogwalkerA)->post(route('blogwalker.submissions.store'), [
            'target_url' => 'https://beritaviral.co.id/post-4',
            'comment_type' => 'approved_live',
            'screenshot_file' => UploadedFile::fake()->image('proof.png'),
        ]);
        $res4->assertSessionHasErrors(['target_url']);
    }

    public function test_period_rollover_disqualifies_participants_who_fail_target(): void
    {
        Storage::fake('public');

        $domainA = Domain::create([
            'root_domain' => 'site-test.co.id',
            'tld' => '.co.id',
            'max_limit' => 10,
        ]);

        $domainB = Domain::create([
            'root_domain' => 'site-b.com',
            'tld' => '.com',
            'max_limit' => 10,
        ]);

        // Blogwalker A submits 5 approved comments (meets min_target of 5)
        for ($i = 1; $i <= 5; $i++) {
            $sub = $this->period->submissions()->create([
                'user_id' => $this->blogwalkerA->id,
                'domain_id' => $domainA->id,
                'target_url' => 'https://site-'.$i.'.co.id',
                'screenshot_path' => 'screenshots/test.png',
                'comment_type' => 'approved_live',
                'review_status' => 'approved',
                'rate_amount' => 700.00,
            ]);
        }

        // Blogwalker B only submits 2 approved comments (fails min_target of 5)
        for ($i = 1; $i <= 2; $i++) {
            $sub = $this->period->submissions()->create([
                'user_id' => $this->blogwalkerB->id,
                'domain_id' => $domainB->id,
                'target_url' => 'https://site-b-'.$i.'.com',
                'screenshot_path' => 'screenshots/test.png',
                'comment_type' => 'approved_live',
                'review_status' => 'approved',
                'rate_amount' => 700.00,
            ]);
        }

        // Admin rolls over to next month
        $response = $this->actingAs($this->admin)->post(route('admin.periods.rollover', $this->period->id), [
            'next_month' => now()->addMonth()->month,
            'next_year' => now()->addMonth()->year,
            'next_min_target' => 5,
            'next_max_urls_per_domain' => 3,
        ]);

        $response->assertRedirect(route('admin.periods.index'));

        // Current period must be closed
        $this->period->refresh();
        $this->assertEquals('closed', $this->period->status);

        // Next period must be active
        $periodService = app(PeriodService::class);
        $nextPeriod = $periodService->getActivePeriod();
        $this->assertNotEquals($this->period->id, $nextPeriod->id);

        // Check enrollment:
        // Blogwalker A reached target -> active in new period
        $assignA = Assignment::where('user_id', $this->blogwalkerA->id)
            ->where('period_id', $nextPeriod->id)
            ->first();
        $this->assertNotNull($assignA);
        $this->assertEquals('active', $assignA->status);
        $this->assertTrue($assignA->is_eligible_next_period);

        // Blogwalker B failed target -> disqualified & suspended in new period
        $assignB = Assignment::where('user_id', $this->blogwalkerB->id)
            ->where('period_id', $nextPeriod->id)
            ->first();
        $this->assertNotNull($assignB);
        $this->assertEquals('disqualified', $assignB->status);
        $this->assertFalse($assignB->is_eligible_next_period);

        // Blogwalker B should be blocked from submitting in new period
        $blockedResponse = $this->actingAs($this->blogwalkerB)->post(route('blogwalker.submissions.store'), [
            'target_url' => 'https://some-web.com/article',
            'comment_type' => 'approved_live',
            'screenshot_file' => UploadedFile::fake()->image('proof.png'),
        ]);
        $blockedResponse->assertSessionHasErrors(['target_url']);

        // Admin grants dispensation to Blogwalker B
        $dispenseResponse = $this->actingAs($this->admin)->post(route('admin.periods.dispense', $assignB->id), [
            'reason' => 'Izin sakit dengan surat dokter',
        ]);
        $dispenseResponse->assertRedirect(route('admin.periods.index'));

        $assignB->refresh();
        $this->assertEquals('dispensed', $assignB->status);
        $this->assertTrue($assignB->is_eligible_next_period);

        // Blogwalker B can now submit again
        $allowedResponse = $this->actingAs($this->blogwalkerB)->post(route('blogwalker.submissions.store'), [
            'target_url' => 'https://some-web.com/article',
            'comment_type' => 'approved_live',
            'screenshot_file' => UploadedFile::fake()->image('proof.png'),
        ]);
        $allowedResponse->assertRedirect(route('blogwalker.submissions.index'));
    }
}
