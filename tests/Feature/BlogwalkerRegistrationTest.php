<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Period;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BlogwalkerRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        Period::create([
            'name' => 'Oktober 2026',
            'month' => 10,
            'year' => 2026,
            'status' => 'active',
            'starts_at' => now()->startOfMonth(),
            'ends_at' => now()->endOfMonth(),
            'min_target' => 100,
            'max_urls_per_domain' => 5,
        ]);
    }

    public function test_can_view_registration_form(): void
    {
        $response = $this->get(route('register'));
        $response->assertStatus(200);
        $response->assertSee('Form Pendaftaran Blogwalker');
        $response->assertSee('Bank Central Asia (BCA)');
    }

    public function test_blogwalker_can_register_without_ktp(): void
    {
        $bankBookFile = UploadedFile::fake()->image('buku_rekening.png', 800, 600);

        $response = $this->post(route('register.post'), [
            'name' => 'Rizki Pratama',
            'username' => 'rizki_seo',
            'email' => 'rizki@blogwalker.local',
            'password' => 'secret12345',
            'password_confirmation' => 'secret12345',
            'phone' => '081234567890',
            'bank_name' => 'BCA',
            'bank_account_number' => '1234567890',
            'bank_account_name' => 'Rizki Pratama',
            'bank_book_photo' => $bankBookFile,
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'name' => 'Rizki Pratama',
            'username' => 'rizki_seo',
            'email' => 'rizki@blogwalker.local',
            'role' => 'blogwalker',
            'bank_name' => 'BCA',
            'bank_account_number' => '1234567890',
            'id_card_path' => null,
            'is_active' => false,
            'approval_status' => 'pending',
        ]);
    }

    public function test_registration_requires_bank_book_photo_but_not_id_card(): void
    {
        $response = $this->post(route('register.post'), [
            'name' => 'Rizki Pratama',
            'username' => 'rizki_seo2',
            'email' => 'rizki2@blogwalker.local',
            'password' => 'secret12345',
            'password_confirmation' => 'secret12345',
            'phone' => '081234567890',
            'bank_name' => 'BCA',
            'bank_account_number' => '1234567890',
            'bank_account_name' => 'Rizki Pratama',
            // No bank_book_photo and no id_card_photo
        ]);

        $response->assertSessionHasErrors(['bank_book_photo']);
        $response->assertSessionDoesntHaveErrors(['id_card_photo']);
    }

    public function test_pending_blogwalker_cannot_login(): void
    {
        $user = User::factory()->create([
            'username' => 'rizki_pending',
            'email' => 'pending@blogwalker.local',
            'password' => Hash::make('password123'),
            'role' => 'blogwalker',
            'is_active' => false,
            'approval_status' => 'pending',
        ]);

        $response = $this->post(route('login.post'), [
            'login' => 'rizki_pending',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_super_admin_can_approve_and_plot_applicant(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $applicant = User::factory()->create([
            'username' => 'calon_worker',
            'email' => 'calon@blogwalker.local',
            'role' => 'blogwalker',
            'is_active' => false,
            'approval_status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.registrations.approve', $applicant), [
            'default_rate' => 750.00,
            'allowed_tlds' => '.co.id, .web.id',
            'target_keywords' => 'jasa backlink, seo jakarta',
            'target_backlink_url' => 'https://klien.co.id',
            'min_target' => 120,
        ]);

        $response->assertRedirect(route('admin.registrations.index'));
        $response->assertSessionHas('success');

        $applicant->refresh();
        $this->assertTrue($applicant->is_active);
        $this->assertEquals('approved', $applicant->approval_status);
        $this->assertEquals(750.00, $applicant->default_rate);

        $assignment = Assignment::where('user_id', $applicant->id)->first();
        $this->assertNotNull($assignment);
        $this->assertEquals(['.co.id', '.web.id'], $assignment->allowed_tlds);
        $this->assertEquals(120, $assignment->min_target);
        $this->assertTrue($assignment->is_active);
    }

    public function test_super_admin_can_approve_applicant_without_default_rate(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $applicant = User::factory()->create([
            'username' => 'worker_tanpa_tarif',
            'email' => 'tanpatarif@blogwalker.local',
            'role' => 'blogwalker',
            'default_rate' => 700.00,
            'is_active' => false,
            'approval_status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.registrations.approve', $applicant), [
            // No default_rate sent
            'allowed_tlds' => '.co.id',
        ]);

        $response->assertRedirect(route('admin.registrations.index'));
        $response->assertSessionHas('success');

        $applicant->refresh();
        $this->assertTrue($applicant->is_active);
        $this->assertEquals('approved', $applicant->approval_status);
        $this->assertEquals(700.00, $applicant->default_rate);
    }

    public function test_super_admin_can_reject_applicant(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $applicant = User::factory()->create([
            'username' => 'calon_tolak',
            'email' => 'tolak@blogwalker.local',
            'role' => 'blogwalker',
            'is_active' => false,
            'approval_status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.registrations.reject', $applicant), [
            'rejection_reason' => 'Foto KTP tidak jelas dan buram.',
        ]);

        $response->assertRedirect(route('admin.registrations.index'));
        $response->assertSessionHas('success');

        $applicant->refresh();
        $this->assertFalse($applicant->is_active);
        $this->assertEquals('rejected', $applicant->approval_status);
        $this->assertEquals('Foto KTP tidak jelas dan buram.', $applicant->rejection_reason);
    }
}
