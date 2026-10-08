<?php

namespace Tests\Feature;

use App\Models\ProfileChangeRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileAndBankApprovalTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $worker;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
            'approval_status' => 'approved',
            'password' => Hash::make('admin12345'),
        ]);

        $this->worker = User::factory()->create([
            'name' => 'Budi Santoso',
            'email' => 'budi@blogwalker.local',
            'role' => 'worker',
            'is_active' => true,
            'approval_status' => 'approved',
            'bank_name' => 'BCA',
            'bank_account_number' => '1234567890',
            'bank_account_name' => 'Budi Santoso',
            'phone' => '081234567890',
            'password' => Hash::make('password123'),
        ]);
    }

    public function test_user_can_view_profile_page_with_bank_list(): void
    {
        $response = $this->actingAs($this->worker)->get(route('profile.edit'));

        $response->assertStatus(200);
        $response->assertSee('Kelola identitas diri');
        $response->assertSee('Budi Santoso');
        $response->assertSee('Bank Central Asia (BCA)');
        $response->assertSee('Bank Syariah Indonesia (BSI)');
        $response->assertSee('Bank Jago');
        $response->assertSee('GoPay');
    }

    public function test_user_can_update_phone_directly_without_approval(): void
    {
        $response = $this->actingAs($this->worker)->put(route('profile.update'), [
            'name' => 'Budi Santoso',
            'phone' => '089876543210',
            'bank_name' => 'BCA',
            'bank_account_number' => '1234567890',
            'bank_account_name' => 'Budi Santoso',
        ]);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $this->worker->id,
            'phone' => '089876543210',
        ]);

        $this->assertDatabaseCount('profile_change_requests', 0);
    }

    public function test_changing_bank_or_name_creates_pending_approval_request(): void
    {
        $bankBookFile = UploadedFile::fake()->image('buku_tabungan_mandiri.jpg', 800, 600);

        $response = $this->actingAs($this->worker)->put(route('profile.update'), [
            'name' => 'Budi Santoso S.Kom',
            'phone' => '081234567890',
            'bank_name' => 'Mandiri',
            'bank_account_number' => '987654321000',
            'bank_account_name' => 'Budi Santoso S.Kom',
            'reason' => 'Pindah rekening gaji utama',
            'bank_book_photo' => $bankBookFile,
        ]);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('success');

        // User table remains unchanged until Super Admin approves
        $this->assertDatabaseHas('users', [
            'id' => $this->worker->id,
            'name' => 'Budi Santoso',
            'bank_name' => 'BCA',
            'bank_account_number' => '1234567890',
            'bank_account_name' => 'Budi Santoso',
        ]);

        // Pending change request created
        $this->assertDatabaseHas('profile_change_requests', [
            'user_id' => $this->worker->id,
            'status' => 'pending',
            'new_name' => 'Budi Santoso S.Kom',
            'new_bank_name' => 'Mandiri',
            'new_bank_account_number' => '987654321000',
            'new_bank_account_name' => 'Budi Santoso S.Kom',
            'reason' => 'Pindah rekening gaji utama',
        ]);
    }

    public function test_blogwalker_can_cancel_pending_request(): void
    {
        $request = ProfileChangeRequest::create([
            'user_id' => $this->worker->id,
            'old_bank_name' => 'BCA',
            'new_bank_name' => 'BRI',
            'old_bank_account_number' => '1234567890',
            'new_bank_account_number' => '555544443333',
            'old_bank_account_name' => 'Budi Santoso',
            'new_bank_account_name' => 'Budi Santoso',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->worker)->delete(route('profile.cancel-request', $request));

        $response->assertRedirect(route('profile.edit'));
        $this->assertDatabaseMissing('profile_change_requests', [
            'id' => $request->id,
        ]);
    }

    public function test_super_admin_can_approve_profile_change_request(): void
    {
        $changeRequest = ProfileChangeRequest::create([
            'user_id' => $this->worker->id,
            'old_name' => 'Budi Santoso',
            'new_name' => 'Budi Santoso S.T.',
            'old_bank_name' => 'BCA',
            'new_bank_name' => 'BSI',
            'old_bank_account_number' => '1234567890',
            'new_bank_account_number' => '71122334455',
            'old_bank_account_name' => 'Budi Santoso',
            'new_bank_account_name' => 'Budi Santoso S.T.',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.profile-requests.approve', $changeRequest));

        $response->assertRedirect(route('admin.profile-requests.index'));
        $response->assertSessionHas('success');

        // Request updated
        $this->assertDatabaseHas('profile_change_requests', [
            'id' => $changeRequest->id,
            'status' => 'approved',
            'reviewed_by' => $this->admin->id,
        ]);

        // User table updated with verified new details
        $this->assertDatabaseHas('users', [
            'id' => $this->worker->id,
            'name' => 'Budi Santoso S.T.',
            'bank_name' => 'BSI',
            'bank_account_number' => '71122334455',
            'bank_account_name' => 'Budi Santoso S.T.',
        ]);
    }

    public function test_super_admin_can_reject_profile_change_request(): void
    {
        $changeRequest = ProfileChangeRequest::create([
            'user_id' => $this->worker->id,
            'old_bank_name' => 'BCA',
            'new_bank_name' => 'DANA',
            'old_bank_account_number' => '1234567890',
            'new_bank_account_number' => '081234567890',
            'old_bank_account_name' => 'Budi Santoso',
            'new_bank_account_name' => 'Budi Santoso',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.profile-requests.reject', $changeRequest), [
            'admin_notes' => 'Nama pada akun e-wallet tidak sesuai dengan nama KTP terdaftar.',
        ]);

        $response->assertRedirect(route('admin.profile-requests.index'));
        $response->assertSessionHas('success');

        // Request updated to rejected with reason
        $this->assertDatabaseHas('profile_change_requests', [
            'id' => $changeRequest->id,
            'status' => 'rejected',
            'admin_notes' => 'Nama pada akun e-wallet tidak sesuai dengan nama KTP terdaftar.',
        ]);

        // User table remains unchanged
        $this->assertDatabaseHas('users', [
            'id' => $this->worker->id,
            'bank_name' => 'BCA',
            'bank_account_number' => '1234567890',
        ]);
    }

    public function test_user_can_update_their_own_password(): void
    {
        $response = $this->actingAs($this->worker)->put(route('profile.password'), [
            'current_password' => 'password123',
            'password' => 'newSecretPassword2026',
            'password_confirmation' => 'newSecretPassword2026',
        ]);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('success');

        $this->worker->refresh();
        $this->assertTrue(Hash::check('newSecretPassword2026', $this->worker->password));
    }

    public function test_super_admin_can_manually_reset_worker_password(): void
    {
        $response = $this->actingAs($this->admin)
            ->from(route('admin.workers.index'))
            ->post(route('admin.workers.reset-password', $this->worker), [
                'password' => 'ManualResetPass999',
            ]);

        $response->assertRedirect(route('admin.workers.index'));
        $response->assertSessionHas('temp_password', 'ManualResetPass999');

        $this->worker->refresh();
        $this->assertTrue(Hash::check('ManualResetPass999', $this->worker->password));
    }

    public function test_forgot_password_and_reset_password_flow(): void
    {
        // 1. Visit forgot password page
        $pageResponse = $this->get(route('password.request'));
        $pageResponse->assertStatus(200);
        $pageResponse->assertSee('Lupa Password?');

        // 2. Request reset link
        $postResponse = $this->post(route('password.email'), [
            'email' => $this->worker->email,
        ]);
        $postResponse->assertSessionHas('status');

        // 3. Create a valid token to test reset
        $token = Password::broker()->createToken($this->worker);

        // 4. Visit reset page
        $resetPageResponse = $this->get(route('password.reset', [
            'token' => $token,
            'email' => $this->worker->email,
        ]));
        $resetPageResponse->assertStatus(200);
        $resetPageResponse->assertSee('Perbarui Password');

        // 5. Submit new password
        $resetResponse = $this->post(route('password.update'), [
            'token' => $token,
            'email' => $this->worker->email,
            'password' => 'brandNewSecurePass2026',
            'password_confirmation' => 'brandNewSecurePass2026',
        ]);

        $resetResponse->assertRedirect(route('login'));
        $resetResponse->assertSessionHas('success');

        $this->worker->refresh();
        $this->assertTrue(Hash::check('brandNewSecurePass2026', $this->worker->password));
    }

    public function test_super_admin_create_worker_form_does_not_contain_rate_field(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.workers.create'));

        $response->assertStatus(200);
        $response->assertSee('Tambah Anggota Blogwalker Baru');
        $response->assertDontSee('Tarif per Komentar Disetujui (Rupiah)');
        $response->assertDontSee('name="default_rate"', false);
    }

    public function test_super_admin_can_create_worker_manually_without_specifying_rate(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.workers.store'), [
            'name' => 'Manual Worker Test',
            'email' => 'manualworker@example.com',
            'password' => 'secret1234',
            'phone' => '081234567890',
        ]);

        $response->assertRedirect(route('admin.workers.index'));
        $this->assertDatabaseHas('users', [
            'name' => 'Manual Worker Test',
            'email' => 'manualworker@example.com',
            'role' => 'blogwalker',
            'default_rate' => 700.00,
            'is_active' => true,
        ]);
    }
}
