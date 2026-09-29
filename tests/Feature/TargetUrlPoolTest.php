<?php

namespace Tests\Feature;

use App\Models\Domain;
use App\Models\TargetUrl;
use App\Models\User;
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
}
