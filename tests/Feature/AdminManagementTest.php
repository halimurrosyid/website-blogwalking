<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin1;

    protected User $admin2;

    protected User $worker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin1 = User::factory()->create([
            'name' => 'Admin Utama',
            'email' => 'admin1@example.com',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->admin2 = User::factory()->create([
            'name' => 'Admin Kedua',
            'email' => 'admin2@example.com',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->worker = User::factory()->create([
            'name' => 'Worker Lapangan',
            'email' => 'worker@example.com',
            'role' => 'blogwalker',
            'is_active' => true,
        ]);
    }

    public function test_worker_cannot_access_super_admin_management(): void
    {
        $response = $this->actingAs($this->worker)->get(route('admin.admins.index'));

        // Should be redirected or forbidden by EnsureAdmin middleware
        $response->assertRedirect(route('login'));
    }

    public function test_super_admin_can_view_admins_list(): void
    {
        $response = $this->actingAs($this->admin1)->get(route('admin.admins.index'));

        $response->assertStatus(200);
        $response->assertSee('Manajemen Akun Super Admin');
        $response->assertSee('Admin Utama');
        $response->assertSee('Admin Kedua');
        $response->assertSee('Akun Anda');
    }

    public function test_super_admin_can_create_new_super_admin(): void
    {
        $response = $this->actingAs($this->admin1)->post(route('admin.admins.store'), [
            'name' => 'Admin Baru Ditambahkan',
            'email' => 'admin.baru@example.com',
            'phone' => '081299998888',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.admins.index'));
        $response->assertSessionHas('success');

        $newAdmin = User::where('email', 'admin.baru@example.com')->first();
        $this->assertNotNull($newAdmin);
        $this->assertEquals('Admin Baru Ditambahkan', $newAdmin->name);
        $this->assertEquals('admin', $newAdmin->role);
        $this->assertTrue($newAdmin->is_active);
        $this->assertTrue(Hash::check('secret123', $newAdmin->password));

        // Test newly created admin can log in and access dashboard
        $loginResponse = $this->actingAs($newAdmin)->get(route('admin.dashboard'));
        $loginResponse->assertStatus(200);
    }

    public function test_super_admin_can_update_another_admin(): void
    {
        $response = $this->actingAs($this->admin1)->put(route('admin.admins.update', $this->admin2), [
            'name' => 'Admin Kedua Diperbarui',
            'email' => 'admin2.updated@example.com',
            'phone' => '08123456789',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.admins.index'));
        $this->admin2->refresh();
        $this->assertEquals('Admin Kedua Diperbarui', $this->admin2->name);
        $this->assertEquals('admin2.updated@example.com', $this->admin2->email);
    }

    public function test_super_admin_can_reset_password_for_another_admin(): void
    {
        $response = $this->actingAs($this->admin1)->post(route('admin.admins.reset-password', $this->admin2), [
            'password' => 'newpassword456',
            'password_confirmation' => 'newpassword456',
        ]);

        $response->assertRedirect();
        $this->admin2->refresh();
        $this->assertTrue(Hash::check('newpassword456', $this->admin2->password));
    }

    public function test_super_admin_can_toggle_status_of_another_admin(): void
    {
        // Deactivate admin2
        $response = $this->actingAs($this->admin1)->post(route('admin.admins.toggle', $this->admin2));
        $response->assertRedirect();

        $this->admin2->refresh();
        $this->assertFalse($this->admin2->is_active);

        // Reactivate admin2
        $response2 = $this->actingAs($this->admin1)->post(route('admin.admins.toggle', $this->admin2));
        $response2->assertRedirect();

        $this->admin2->refresh();
        $this->assertTrue($this->admin2->is_active);
    }

    public function test_super_admin_cannot_deactivate_themselves(): void
    {
        $response = $this->actingAs($this->admin1)->post(route('admin.admins.toggle', $this->admin1));
        $response->assertRedirect();
        $response->assertSessionHas('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');

        $this->admin1->refresh();
        $this->assertTrue($this->admin1->is_active);
    }

    public function test_super_admin_cannot_delete_themselves(): void
    {
        $response = $this->actingAs($this->admin1)->delete(route('admin.admins.destroy', $this->admin1));
        $response->assertRedirect();
        $response->assertSessionHas('error', 'Anda tidak dapat menghapus akun Anda sendiri.');

        $this->assertDatabaseHas('users', ['id' => $this->admin1->id]);
    }

    public function test_super_admin_can_delete_another_admin_when_multiple_exist(): void
    {
        $response = $this->actingAs($this->admin1)->delete(route('admin.admins.destroy', $this->admin2));
        $response->assertRedirect(route('admin.admins.index'));

        $this->assertDatabaseMissing('users', ['id' => $this->admin2->id]);
    }

    public function test_cannot_delete_last_remaining_admin(): void
    {
        // Delete admin2 first
        $this->admin2->delete();

        // Admin1 is now the sole admin; an attempt to delete them should fail
        $response = $this->actingAs($this->admin1)->delete(route('admin.admins.destroy', $this->admin1));
        $response->assertRedirect();

        $this->assertDatabaseHas('users', ['id' => $this->admin1->id]);
    }
}
