<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Period;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReportPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_reports_page(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.reports.index'));
        $response->assertStatus(200);
    }

    public function test_admin_can_view_reports_page_with_active_period_and_assignments(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $worker = User::factory()->create([
            'role' => 'blogwalker',
            'is_active' => true,
        ]);

        $period = Period::create([
            'name' => 'Oktober 2026',
            'month' => 10,
            'year' => 2026,
            'min_target' => 100,
            'max_urls_per_domain' => 5,
            'status' => 'active',
            'starts_at' => '2026-10-01',
            'ends_at' => '2026-10-31',
        ]);

        Assignment::create([
            'period_id' => $period->id,
            'user_id' => $worker->id,
            'target_keywords' => 'jasa seo website, backlink murah',
            'target_backlink_url' => 'https://klien.com',
            'min_target' => 100,
            'status' => 'active',
            'is_eligible_next_period' => true,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.reports.index'));
        $response->assertStatus(200);
        $response->assertSee('jasa seo website');
    }

    public function test_admin_can_view_periods_page(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.periods.index'));
        $response->assertStatus(200);
    }

    public function test_admin_can_export_reports_csv(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.reports.export'));
        $response->assertStatus(200);
        $this->assertTrue(str_contains($response->headers->get('Content-Disposition') ?? '', 'attachment'));
    }

    public function test_blogwalker_can_view_dashboard(): void
    {
        $worker = User::factory()->create([
            'role' => 'blogwalker',
            'is_active' => true,
        ]);

        $response = $this->actingAs($worker)->get(route('blogwalker.dashboard'));
        $response->assertStatus(200);
    }

    public function test_admin_can_view_targets_page(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.targets.index'));
        $response->assertStatus(200);
    }

    public function test_admin_can_view_targets_create_page(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.targets.create'));
        $response->assertStatus(200);
    }

    public function test_admin_can_view_reviews_page(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.reviews.index'));
        $response->assertStatus(200);
    }

    public function test_blogwalker_can_view_submissions_page(): void
    {
        $worker = User::factory()->create([
            'role' => 'blogwalker',
            'is_active' => true,
        ]);

        $response = $this->actingAs($worker)->get(route('blogwalker.submissions.index'));
        $response->assertStatus(200);
    }

    public function test_blogwalker_can_view_submissions_create_page(): void
    {
        $worker = User::factory()->create([
            'role' => 'blogwalker',
            'is_active' => true,
        ]);

        $response = $this->actingAs($worker)->get(route('blogwalker.submissions.create'));
        $response->assertStatus(200);
    }

    public function test_storage_fallback_serves_public_file(): void
    {
        Storage::disk('public')->put('tests/demo.txt', 'test content');

        $response = $this->get('/storage/tests/demo.txt');
        $response->assertStatus(200);
        $this->assertTrue(str_contains($response->headers->get('Content-Type') ?? '', 'text/plain'));

        Storage::disk('public')->delete('tests/demo.txt');
    }
}
