<?php

namespace Tests\Feature;

use App\Models\Period;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class InstallationWizardTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        // Cleanup test lockfile if left behind
        if (file_exists(storage_path('installed_test'))) {
            @unlink(storage_path('installed_test'));
        }
        parent::tearDown();
    }

    public function test_installer_step1_requirements_page_loads_successfully(): void
    {
        $response = $this->get(route('install.index'));

        $response->assertStatus(200);
        $response->assertSee('Pemeriksaan Persyaratan Server');
        $response->assertSee('Versi PHP');
    }

    public function test_installer_step2_database_page_loads_successfully(): void
    {
        $response = $this->get(route('install.database'));

        $response->assertStatus(200);
        $response->assertSee('Pengaturan Database');
        $response->assertSee('Nama Database');
    }

    public function test_installer_step2_fails_with_invalid_mysql_credentials(): void
    {
        $response = $this->post(route('install.database.post'), [
            'db_connection' => 'mysql',
            'db_host' => 'invalid-host-dns-failure.invalid',
            'db_port' => '3306',
            'db_database' => 'non_existent_db_123',
            'db_username' => 'invalid_user',
            'db_password' => 'invalid_pass',
        ]);

        $response->assertSessionHasErrors(['db_error']);
    }

    public function test_installer_step2_sqlite_mode_creates_period_and_redirects(): void
    {
        $response = $this->post(route('install.database.post'), [
            'db_connection' => 'sqlite',
            'db_database' => ':memory:',
        ]);

        $response->assertRedirect(route('install.admin'));
        $this->assertTrue(Period::where('status', 'active')->exists());
    }

    public function test_installer_step3_admin_page_loads_successfully(): void
    {
        $response = $this->get(route('install.admin'));

        $response->assertStatus(200);
        $response->assertSee('Buat Akun Super Administrator');
    }

    public function test_installer_step3_creates_super_admin_and_locks_installer(): void
    {
        $installedFile = storage_path('installed');
        $backupInstalled = file_exists($installedFile) ? file_get_contents($installedFile) : null;
        if (file_exists($installedFile)) {
            @unlink($installedFile);
        }

        try {
            $response = $this->post(route('install.admin.post'), [
                'name' => 'Root Super Admin',
                'username' => 'superadmin',
                'email' => 'admin@portalblogwalker.com',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
            ]);

            $response->assertRedirect(route('install.finish'));

            // Verify admin user is created in database
            $this->assertDatabaseHas('users', [
                'username' => 'superadmin',
                'email' => 'admin@portalblogwalker.com',
                'role' => 'admin',
                'is_active' => true,
                'approval_status' => 'approved',
            ]);

            $admin = User::where('email', 'admin@portalblogwalker.com')->first();
            $this->assertTrue(Hash::check('Password123!', $admin->password));

            // Verify lock file exists
            $this->assertFileExists($installedFile);
        } finally {
            if ($backupInstalled !== null) {
                file_put_contents($installedFile, $backupInstalled);
            }
        }
    }

    public function test_installer_step4_finish_page_renders_congratulations(): void
    {
        $response = $this->get(route('install.finish'));

        $response->assertStatus(200);
        $response->assertSee('Selamat! Instalasi Berhasil');
        $response->assertSee(route('login'));
    }

    public function test_ensure_installed_middleware_redirects_uninstalled_site_to_installer(): void
    {
        config(['app.enforce_installed_middleware' => true]);

        $installedFile = storage_path('installed');
        $backupInstalled = file_exists($installedFile) ? file_get_contents($installedFile) : null;
        if (file_exists($installedFile)) {
            @unlink($installedFile);
        }

        try {
            $response = $this->get(route('login'));
            $response->assertRedirect(route('install.index'));
        } finally {
            if ($backupInstalled !== null) {
                file_put_contents($installedFile, $backupInstalled);
            }
        }
    }

    public function test_ensure_installed_middleware_blocks_installer_routes_when_installed(): void
    {
        config(['app.enforce_installed_middleware' => true]);

        $installedFile = storage_path('installed');
        file_put_contents($installedFile, json_encode(['test' => true]));

        try {
            $response = $this->get(route('install.index'));
            $response->assertRedirect(route('login'));
        } finally {
            // Keep file or restore
        }
    }
}
