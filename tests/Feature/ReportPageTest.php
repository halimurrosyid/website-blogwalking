<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
