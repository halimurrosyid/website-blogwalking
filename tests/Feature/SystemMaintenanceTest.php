<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class SystemMaintenanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_blogwalker_cannot_access_system_maintenance_page(): void
    {
        $worker = User::factory()->create([
            'role' => 'blogwalker',
            'is_active' => true,
            'approval_status' => 'approved',
        ]);

        $response = $this->actingAs($worker)->get(route('admin.system.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_super_admin_can_view_system_maintenance_page(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
            'approval_status' => 'approved',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.system.index'));
        $response->assertStatus(200);
        $response->assertSee('Pemeliharaan Sistem & Hosting');
        $response->assertSee('Bersihkan Seluruh Cache');
        $response->assertSee('Update Struktur Database');
        $response->assertSee('Perbaiki Storage Link');
        $response->assertSee('Backup & Migrasi Data');
    }

    public function test_super_admin_can_clear_cache_via_web(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
            'approval_status' => 'approved',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.system.clear-cache'));
        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    public function test_super_admin_can_run_migrate_via_web(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
            'approval_status' => 'approved',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.system.run-migrate'));
        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    public function test_super_admin_can_fix_storage_link_via_web(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
            'approval_status' => 'approved',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.system.fix-storage-link'));
        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    public function test_super_admin_can_download_database_backup(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
            'approval_status' => 'approved',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.system.backup'));
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/sql');
    }

    public function test_super_admin_can_download_full_backup_zip(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
            'approval_status' => 'approved',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.system.backup-full'));

        if (class_exists(\ZipArchive::class)) {
            $response->assertStatus(200);
            $response->assertHeader('Content-Type', 'application/zip');
        } else {
            $response->assertRedirect();
            $response->assertSessionHas('error');
        }
    }

    public function test_super_admin_can_restore_database_from_sql_file(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
            'approval_status' => 'approved',
        ]);

        $sqlContent = "-- Test Backup\nSELECT 1;\n";
        $file = UploadedFile::fake()->createWithContent('backup.sql', $sqlContent);

        $response = $this->actingAs($admin)->post(route('admin.system.restore'), [
            'backup_file' => $file,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    public function test_super_admin_can_reset_installer(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
            'approval_status' => 'approved',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.system.reset-installer'));
        $response->assertRedirect(route('install.index'));
        $this->assertGuest();
    }

    public function test_super_admin_can_update_domain_url(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
            'approval_status' => 'approved',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.system.update-domain'), [
            'app_url' => 'https://newdomain-blogwalker.com',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }
}
