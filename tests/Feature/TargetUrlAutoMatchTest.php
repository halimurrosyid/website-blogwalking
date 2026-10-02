<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Period;
use App\Models\Submission;
use App\Models\TargetUrl;
use App\Models\User;
use App\Services\TaskTypeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TargetUrlAutoMatchTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $worker1;

    protected User $worker2;

    protected Period $activePeriod;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->activePeriod = Period::create([
            'name' => 'Oktober 2026',
            'month' => 10,
            'year' => 2026,
            'status' => 'active',
            'starts_at' => now()->startOfMonth(),
            'ends_at' => now()->endOfMonth(),
            'min_target' => 100,
            'max_urls_per_domain' => 5,
        ]);

        $this->admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->worker1 = User::factory()->create([
            'role' => 'blogwalker',
            'default_rate' => 700.00,
        ]);

        $this->worker2 = User::factory()->create([
            'role' => 'blogwalker',
            'default_rate' => 700.00,
        ]);

        Assignment::create([
            'user_id' => $this->worker1->id,
            'period_id' => $this->activePeriod->id,
            'allowed_tlds' => ['.co.id', '.id', '.com'],
            'is_active' => true,
        ]);

        Assignment::create([
            'user_id' => $this->worker2->id,
            'period_id' => $this->activePeriod->id,
            'allowed_tlds' => ['.co.id', '.id', '.com'],
            'is_active' => true,
        ]);
    }

    public function test_manual_submission_auto_matches_available_target_url(): void
    {
        // Admin creates a target URL in queue
        $target = TargetUrl::create([
            'url' => 'https://beritabisnis.co.id/ekonomi-digital',
            'root_domain' => 'beritabisnis.co.id',
            'client_url' => 'https://klien-seo.com/jasa-backlink',
            'keyword' => 'backlink berkualitas',
            'reward_amount' => 1200.00,
            'task_type' => TaskTypeService::COMMENT,
            'status' => 'available',
        ]);

        // Worker1 finds this URL independently and submits without clicking "claim" / without target_id
        $file = UploadedFile::fake()->image('bukti.png', 800, 600);

        $response = $this->actingAs($this->worker1)->post(route('blogwalker.submissions.store'), [
            'target_url' => 'https://beritabisnis.co.id/ekonomi-digital',
            'comment_type' => 'approved_live',
            'screenshot_file' => $file,
            // Notice: no target_id passed
        ]);

        $response->assertRedirect(route('blogwalker.submissions.index'));
        $response->assertSessionHas('success');

        // Verify target status changed to completed and linked to submission
        $target->refresh();
        $this->assertEquals('completed', $target->status);
        $this->assertEquals($this->worker1->id, $target->taken_by_user_id);
        $this->assertNotNull($target->submission_id);

        // Verify submission is linked and inherited custom rate and client info
        $submission = Submission::where('target_url_id', $target->id)->first();
        $this->assertNotNull($submission);
        $this->assertEquals($this->worker1->id, $submission->user_id);
        $this->assertEquals(1200.00, (float) $submission->rate_amount);
        $this->assertEquals('https://klien-seo.com/jasa-backlink', $submission->client_url);
        $this->assertEquals('backlink berkualitas', $submission->keyword);
    }

    public function test_manual_submission_matches_target_with_trailing_slash_difference(): void
    {
        // Target stored with trailing slash
        $target = TargetUrl::create([
            'url' => 'https://portalmedia.id/review-gadget/',
            'root_domain' => 'portalmedia.id',
            'status' => 'available',
            'reward_amount' => 900.00,
        ]);

        // Worker submits without trailing slash
        $file = UploadedFile::fake()->image('bukti.png', 800, 600);

        $response = $this->actingAs($this->worker1)->post(route('blogwalker.submissions.store'), [
            'target_url' => 'https://portalmedia.id/review-gadget',
            'comment_type' => 'approved_live',
            'screenshot_file' => $file,
        ]);

        $response->assertRedirect(route('blogwalker.submissions.index'));

        $target->refresh();
        $this->assertEquals('completed', $target->status);
        $this->assertEquals($this->worker1->id, $target->taken_by_user_id);
        $this->assertNotNull($target->submission_id);
    }

    public function test_manual_submission_matches_target_previously_claimed_by_same_worker(): void
    {
        // Target claimed by worker1 earlier
        $target = TargetUrl::create([
            'url' => 'https://teknoinfo.com/ai-terbaru',
            'root_domain' => 'teknoinfo.com',
            'status' => 'in_progress',
            'taken_by_user_id' => $this->worker1->id,
            'taken_at' => now()->subMinutes(15),
        ]);

        // Worker1 submits manually without passing target_id in request
        $file = UploadedFile::fake()->image('bukti.png', 800, 600);

        $response = $this->actingAs($this->worker1)->post(route('blogwalker.submissions.store'), [
            'target_url' => 'https://teknoinfo.com/ai-terbaru',
            'comment_type' => 'approved_live',
            'screenshot_file' => $file,
        ]);

        $response->assertRedirect(route('blogwalker.submissions.index'));

        $target->refresh();
        $this->assertEquals('completed', $target->status);
        $this->assertEquals($this->worker1->id, $target->taken_by_user_id);
        $this->assertNotNull($target->submission_id);
    }

    public function test_target_completed_via_auto_match_cannot_be_claimed_by_another_worker(): void
    {
        $target = TargetUrl::create([
            'url' => 'https://beritaviral.co.id/artikel-eksklusif',
            'root_domain' => 'beritaviral.co.id',
            'status' => 'available',
        ]);

        // Worker1 submits it manually
        $file = UploadedFile::fake()->image('bukti.png', 800, 600);
        $this->actingAs($this->worker1)->post(route('blogwalker.submissions.store'), [
            'target_url' => 'https://beritaviral.co.id/artikel-eksklusif',
            'comment_type' => 'approved_live',
            'screenshot_file' => $file,
        ]);

        $target->refresh();
        $this->assertEquals('completed', $target->status);

        // Worker2 tries to claim this target -> must be rejected
        $claimResponse = $this->actingAs($this->worker2)->post(route('blogwalker.targets.claim', $target->id));
        $claimResponse->assertRedirect(route('blogwalker.targets.index'));
        $claimResponse->assertSessionHas('error');

        // Worker2 also cannot submit the same URL manually -> duplicate validation triggers
        $duplicateResponse = $this->actingAs($this->worker2)->post(route('blogwalker.submissions.store'), [
            'target_url' => 'https://beritaviral.co.id/artikel-eksklusif',
            'comment_type' => 'approved_live',
            'screenshot_file' => UploadedFile::fake()->image('bukti2.png', 800, 600),
        ]);

        $duplicateResponse->assertSessionHasErrors(['target_url']);
    }

    public function test_regular_manual_submission_works_independently_when_no_target_exists(): void
    {
        $file = UploadedFile::fake()->image('bukti.png', 800, 600);

        $response = $this->actingAs($this->worker1)->post(route('blogwalker.submissions.store'), [
            'target_url' => 'https://websitemandiri.co.id/artikel-baru',
            'comment_type' => 'approved_live',
            'screenshot_file' => $file,
            'client_url' => 'https://klienmanual.com',
            'keyword' => 'kata kunci manual',
        ]);

        $response->assertRedirect(route('blogwalker.submissions.index'));

        $this->assertDatabaseHas('submissions', [
            'user_id' => $this->worker1->id,
            'target_url' => 'https://websitemandiri.co.id/artikel-baru',
            'target_url_id' => null,
            'client_url' => 'https://klienmanual.com',
            'keyword' => 'kata kunci manual',
        ]);
    }
}
