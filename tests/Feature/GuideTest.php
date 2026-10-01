<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuideTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_accessing_guide(): void
    {
        $response = $this->get(route('guide'));

        $response->assertRedirect(route('login'));
    }

    public function test_admin_can_access_guide_with_admin_tab(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('guide'));

        $response->assertStatus(200);
        $response->assertSee('Panduan Penggunaan BlogwalkerPro');
        $response->assertSee('Panduan Super Admin');
        $response->assertSee('Panduan Blogwalker (Worker)');
        $response->assertSee('Tanya Jawab (FAQ)');
    }

    public function test_blogwalker_can_access_guide_with_worker_tab(): void
    {
        $worker = User::factory()->create(['role' => 'blogwalker']);

        $response = $this->actingAs($worker)->get(route('guide'));

        $response->assertStatus(200);
        $response->assertSee('Panduan Penggunaan BlogwalkerPro');
        $response->assertSee('Panduan Blogwalker (Worker)');
        $response->assertSee('Tanya Jawab (FAQ)');
        $response->assertDontSee('👑 Panduan Super Admin');
    }
}
