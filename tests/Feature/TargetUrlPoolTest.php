<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Domain;
use App\Models\Submission;
use App\Models\TargetUrl;
use App\Models\User;
use App\Services\PeriodService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TargetUrlPoolTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $blogwalker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->blogwalker = User::factory()->create([
            'role' => 'blogwalker',
            'default_rate' => 700.00,
        ]);
    }

    public function test_admin_can_bulk_import_target_urls(): void
    {
        $payload = [
            'urls' => "https://beritaviral.co.id/artikel-1\nhttps://beritaviral.co.id/artikel-2\nportalekonomi.id/info-bisnis",
            'keyword' => 'jasa seo google',
            'notes' => 'Tulis komentar bermutu',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.targets.store'), $payload);
        $response->assertRedirect(route('admin.targets.index'));

        $this->assertDatabaseCount('target_urls', 3);
        $this->assertDatabaseHas('target_urls', [
            'url' => 'https://beritaviral.co.id/artikel-1',
            'root_domain' => 'beritaviral.co.id',
            'status' => 'available',
        ]);
        $this->assertDatabaseHas('target_urls', [
            'url' => 'https://portalekonomi.id/info-bisnis',
            'root_domain' => 'portalekonomi.id',
            'status' => 'available',
        ]);
    }

    public function test_target_is_flagged_domain_full_if_domain_already_locked(): void
    {
        $domain = Domain::create([
            'root_domain' => 'sudahpenuh.com',
            'tld' => '.com',
            'url_count' => 5,
            'max_limit' => 5,
            'is_locked' => true,
        ]);

        $payload = [
            'urls' => 'https://sudahpenuh.com/artikel-ke-6',
        ];

        $this->actingAs($this->admin)->post(route('admin.targets.store'), $payload);

        $this->assertDatabaseHas('target_urls', [
            'url' => 'https://sudahpenuh.com/artikel-ke-6',
            'status' => 'domain_full',
        ]);
    }

    public function test_blogwalker_can_claim_and_complete_target_url(): void
    {
        Storage::fake('public');

        $target = TargetUrl::create([
            'url' => 'https://websitetarget.id/tips-belajar-seo/',
            'root_domain' => 'websitetarget.id',
            'keyword' => 'belajar backlink',
            'status' => 'available',
        ]);

        // Blogwalker claims the target
        $claimResponse = $this->actingAs($this->blogwalker)->post(route('blogwalker.targets.claim', $target->id));
        $claimResponse->assertRedirect(route('blogwalker.submissions.create', ['target_id' => $target->id]));

        $target->refresh();
        $this->assertEquals('in_progress', $target->status);
        $this->assertEquals($this->blogwalker->id, $target->taken_by_user_id);

        // Blogwalker submits proof
        $file = UploadedFile::fake()->image('screenshot.png', 800, 600);
        $submitResponse = $this->actingAs($this->blogwalker)->post(route('blogwalker.submissions.store'), [
            'target_url' => $target->url,
            'comment_type' => 'approved_live',
            'screenshot_file' => $file,
            'target_id' => $target->id,
        ]);

        $submitResponse->assertRedirect(route('blogwalker.submissions.index'));

        $target->refresh();
        $this->assertEquals('completed', $target->status);
        $this->assertNotNull($target->submission_id);
    }

    public function test_blogwalker_can_skip_target_if_comment_closed(): void
    {
        $target = TargetUrl::create([
            'url' => 'https://webtutup.com/post-tanpa-komentar',
            'root_domain' => 'webtutup.com',
            'status' => 'available',
        ]);

        $response = $this->actingAs($this->blogwalker)->post(route('blogwalker.targets.skip', $target->id), [
            'reason' => 'Kolom komentar dimatikan oleh pemilik web',
        ]);

        $response->assertRedirect(route('blogwalker.targets.index'));

        $target->refresh();
        $this->assertEquals('skipped', $target->status);
        $this->assertStringContainsString('Kolom komentar dimatikan', $target->notes);
    }

    public function test_dork_generator_locks_to_worker_assigned_plotting(): void
    {
        Assignment::create([
            'user_id' => $this->blogwalker->id,
            'allowed_tlds' => ['.co.id', '.web.id'],
            'target_keywords' => 'paket wifi, internet murah',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->blogwalker)->get(route('blogwalker.targets.index'));
        $response->assertStatus(200);
        $response->assertSee('🎯 Sesuai Plotting Super Admin');
        $response->assertSee('.co.id (Plotting Tugas Anda)');
        $response->assertSee('.web.id (Plotting Tugas Anda)');
        $response->assertSee('paket wifi');
        // Unassigned TLDs must NOT be present in options
        $response->assertDontSee('.com.my (Malaysia)');
        $response->assertDontSee('.de (Jerman)');
    }

    public function test_dork_generator_shows_all_options_for_admin(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.targets.create'));
        $response->assertStatus(200);
        $response->assertSee('.com.my (Malaysia)');
        $response->assertSee('.de (Jerman)');
    }

    public function test_blogwalker_can_release_claimed_target_back_to_pool(): void
    {
        $target = TargetUrl::create([
            'url' => 'https://websitetarget.id/tips-dilepas/',
            'root_domain' => 'websitetarget.id',
            'status' => 'available',
        ]);

        // Claim first
        $this->actingAs($this->blogwalker)->post(route('blogwalker.targets.claim', $target->id));
        $target->refresh();
        $this->assertEquals('in_progress', $target->status);
        $this->assertEquals($this->blogwalker->id, $target->taken_by_user_id);

        // Worker releases the target back to pool
        $releaseResponse = $this->actingAs($this->blogwalker)->post(route('blogwalker.targets.release', $target->id));
        $releaseResponse->assertRedirect(route('blogwalker.targets.index'));

        $target->refresh();
        $this->assertEquals('available', $target->status);
        $this->assertNull($target->taken_by_user_id);
        $this->assertNull($target->taken_at);
    }

    public function test_stale_in_progress_targets_are_auto_released_after_two_hours(): void
    {
        $staleTarget = TargetUrl::create([
            'url' => 'https://websitetarget.id/tips-terlantar/',
            'root_domain' => 'websitetarget.id',
            'status' => 'in_progress',
            'taken_by_user_id' => $this->blogwalker->id,
            'taken_at' => now()->subHours(3),
        ]);

        $freshTarget = TargetUrl::create([
            'url' => 'https://websitetarget.id/tips-baru-diambil/',
            'root_domain' => 'websitetarget.id',
            'status' => 'in_progress',
            'taken_by_user_id' => $this->blogwalker->id,
            'taken_at' => now()->subMinutes(30),
        ]);

        // Accessing targets index triggers auto-release
        $this->actingAs($this->blogwalker)->get(route('blogwalker.targets.index'));

        $staleTarget->refresh();
        $freshTarget->refresh();

        $this->assertEquals('available', $staleTarget->status);
        $this->assertNull($staleTarget->taken_by_user_id);

        $this->assertEquals('in_progress', $freshTarget->status);
        $this->assertEquals($this->blogwalker->id, $freshTarget->taken_by_user_id);
    }

    public function test_admin_can_requeue_skipped_target(): void
    {
        $target = TargetUrl::create([
            'url' => 'https://webtutup.com/post-requeue',
            'root_domain' => 'webtutup.com',
            'status' => 'skipped',
            'notes' => 'Skip: Kolom komentar ditutup',
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.targets.requeue', $target->id));
        $response->assertRedirect(route('admin.targets.index'));

        $target->refresh();
        $this->assertEquals('available', $target->status);
        $this->assertNull($target->taken_by_user_id);
    }

    public function test_payout_index_displays_worker_bank_details(): void
    {
        $worker = User::factory()->create([
            'name' => 'Budi Santoso',
            'role' => 'blogwalker',
            'bank_name' => 'BCA',
            'bank_account_number' => '1234567890',
            'bank_account_name' => 'Budi Santoso',
            'phone' => '08123456789',
        ]);

        $domain = Domain::create([
            'root_domain' => 'domainku.com',
            'tld' => '.com',
            'url_count' => 1,
            'max_limit' => 5,
        ]);

        Submission::create([
            'user_id' => $worker->id,
            'domain_id' => $domain->id,
            'target_url' => 'https://domainku.com/blog/1',
            'root_domain' => 'domainku.com',
            'live_url' => 'https://domainku.com/blog/1#comment-1',
            'screenshot_path' => 'screenshots/test.png',
            'review_status' => 'approved',
            'is_paid' => false,
            'rate_amount' => 1000.00,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.payouts.index'));
        $response->assertStatus(200);
        $response->assertSee('Budi Santoso');
        $response->assertSee('Bank Central Asia (BCA)');
        $response->assertSee('1234567890');
        $response->assertSee('Salin');
    }

    public function test_worker_dashboard_shows_countdown_banner_when_deadline_is_near(): void
    {
        $periodService = app(PeriodService::class);
        $period = $periodService->getActivePeriod();
        $period->update([
            'starts_at' => now()->subDays(25)->toDateString(),
            'ends_at' => now()->addDays(3)->toDateString(),
            'min_target' => 50,
        ]);

        $response = $this->actingAs($this->blogwalker)->get(route('blogwalker.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Sisa 3 Hari Lagi!');
        $response->assertSee('Perhatian: Target Periode');
    }
}
