<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Submission;
use App\Models\User;
use App\Services\DomainService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkerSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_worker_can_submit_comment_proof_with_base64_screenshot(): void
    {
        $worker = User::factory()->create([
            'role' => 'blogwalker',
            'default_rate' => 700.00,
            'is_active' => true,
        ]);

        Assignment::create([
            'user_id' => $worker->id,
            'allowed_tlds' => null, // Bebas semua domain
            'target_keywords' => 'jasa seo backlink',
            'target_backlink_url' => 'https://jasaseo.co.id',
            'is_active' => true,
        ]);

        // 1x1 transparent PNG data URI (simulating clipboard paste)
        $samplePngBase64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

        $response = $this->actingAs($worker)->post(route('blogwalker.submissions.store'), [
            'target_url' => 'https://blog.digital.co.id/belajar-backlink-2026',
            'comment_type' => 'approved_live',
            'screenshot_base64' => $samplePngBase64,
        ]);

        $response->assertRedirect(route('blogwalker.submissions.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('submissions', [
            'user_id' => $worker->id,
            'target_url' => 'https://blog.digital.co.id/belajar-backlink-2026',
            'comment_type' => 'approved_live',
            'review_status' => 'pending',
            'rate_amount' => 700.00,
            'is_paid' => false,
        ]);

        $this->assertDatabaseHas('domains', [
            'root_domain' => 'digital.co.id',
            'tld' => '.co.id',
            'url_count' => 1,
            'is_locked' => false,
        ]);
    }

    public function test_rejected_comment_decrements_domain_count_and_frees_slot(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $worker = User::factory()->create(['role' => 'worker']);

        $service = new DomainService;
        $submission = $service->recordSubmission(
            $worker,
            'https://testdomain.co.id/post-1',
            'screenshots/dummy.webp',
            'approved_live'
        );

        $domain = $submission->domain;
        $this->assertEquals(1, $domain->url_count);

        // Admin rejects the submission
        $response = $this->actingAs($admin)->post(route('admin.reviews.reject', $submission), [
            'rejection_reason' => 'Komentar tidak ditemukan di halaman.',
        ]);

        $response->assertSessionHas('success');

        $submission->refresh();
        $domain->refresh();

        $this->assertEquals('rejected', $submission->review_status);
        $this->assertEquals(0, $domain->url_count); // Slot freed!
    }

    public function test_admin_can_export_csv_report(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('admin.reviews.export-csv'));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }
}
