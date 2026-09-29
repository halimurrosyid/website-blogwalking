<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\Period;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use PDO;

class InstallController extends Controller
{
    /**
     * Step 1: System requirements and directory permissions check.
     */
    public function index(): View|RedirectResponse
    {
        if (file_exists(storage_path('installed')) && ! app()->environment('testing')) {
            return redirect()->route('login');
        }

        $phpVersion = PHP_VERSION;
        $phpSatisfied = version_compare($phpVersion, '8.2.0', '>=');

        $requiredExtensions = [
            'pdo' => 'PDO Extension',
            'curl' => 'cURL Extension',
            'mbstring' => 'Mbstring Extension',
            'fileinfo' => 'Fileinfo Extension',
            'gd' => 'GD Image Processing',
            'openssl' => 'OpenSSL Extension',
        ];

        $extensionResults = [];
        $extensionsSatisfied = true;
        foreach ($requiredExtensions as $ext => $label) {
            $loaded = extension_loaded($ext);
            $extensionResults[$ext] = [
                'label' => $label,
                'satisfied' => $loaded,
            ];
            if (! $loaded) {
                $extensionsSatisfied = false;
            }
        }

        // Folder writable checks
        $storageWritable = is_writable(storage_path());
        $cacheWritable = is_writable(base_path('bootstrap/cache'));
        $envWritable = file_exists(base_path('.env')) ? is_writable(base_path('.env')) : is_writable(base_path());

        $allRequirementsMet = $phpSatisfied && $extensionsSatisfied && $storageWritable && $cacheWritable && $envWritable;

        return view('installer.step1_requirements', [
            'phpVersion' => $phpVersion,
            'phpSatisfied' => $phpSatisfied,
            'extensionResults' => $extensionResults,
            'storageWritable' => $storageWritable,
            'cacheWritable' => $cacheWritable,
            'envWritable' => $envWritable,
            'allRequirementsMet' => $allRequirementsMet,
        ]);
    }

    /**
     * Step 2: Database configuration form.
     */
    public function database(): View|RedirectResponse
    {
        if (file_exists(storage_path('installed')) && ! app()->environment('testing')) {
            return redirect()->route('login');
        }

        return view('installer.step2_database', [
            'defaultHost' => env('DB_HOST', '127.0.0.1'),
            'defaultPort' => env('DB_PORT', '3306'),
            'defaultDatabase' => env('DB_DATABASE', ''),
            'defaultUsername' => env('DB_USERNAME', 'root'),
        ]);
    }

    /**
     * Step 2 POST: Test connection, write .env, run migrations.
     */
    public function saveDatabase(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'db_connection' => ['required', 'in:mysql,sqlite'],
            'db_host' => ['required_if:db_connection,mysql', 'nullable', 'string'],
            'db_port' => ['required_if:db_connection,mysql', 'nullable', 'numeric'],
            'db_database' => ['required', 'string'],
            'db_username' => ['required_if:db_connection,mysql', 'nullable', 'string'],
            'db_password' => ['nullable', 'string'],
        ]);

        if ($validated['db_connection'] === 'mysql') {
            $host = $validated['db_host'];
            $port = $validated['db_port'] ?? '3306';
            $database = $validated['db_database'];
            $username = $validated['db_username'];
            $password = $validated['db_password'] ?? '';

            // Test PDO Connection before making any changes
            try {
                $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
                $pdo = new PDO($dsn, $username, $password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_TIMEOUT => 5,
                ]);
            } catch (\Exception $e) {
                return back()->withInput()->withErrors([
                    'db_error' => 'Koneksi ke database gagal: '.$e->getMessage().'. Pastikan database sudah dibuat di cPanel MySQL Databases dan user memiliki semua hak akses (All Privileges).',
                ]);
            }

            // Update .env file
            if (! app()->environment('testing')) {
                $this->updateEnv([
                    'DB_CONNECTION' => 'mysql',
                    'DB_HOST' => $host,
                    'DB_PORT' => $port,
                    'DB_DATABASE' => $database,
                    'DB_USERNAME' => $username,
                    'DB_PASSWORD' => $password,
                    'APP_ENV' => 'production',
                    'APP_DEBUG' => 'false',
                ]);

                // Reconfigure runtime config
                config([
                    'database.default' => 'mysql',
                    'database.connections.mysql.host' => $host,
                    'database.connections.mysql.port' => $port,
                    'database.connections.mysql.database' => $database,
                    'database.connections.mysql.username' => $username,
                    'database.connections.mysql.password' => $password,
                ]);
                DB::purge('mysql');
            }
        } else {
            // SQLite mode
            if (! app()->environment('testing')) {
                $sqlitePath = database_path($validated['db_database']);
                if (! file_exists($sqlitePath)) {
                    touch($sqlitePath);
                }

                $this->updateEnv([
                    'DB_CONNECTION' => 'sqlite',
                    'DB_DATABASE' => $sqlitePath,
                ]);

                config([
                    'database.default' => 'sqlite',
                    'database.connections.sqlite.database' => $sqlitePath,
                ]);
                DB::purge('sqlite');
            }
        }

        // Ensure APP_KEY exists and run migrations
        if (! app()->environment('testing')) {
            if (empty(env('APP_KEY'))) {
                Artisan::call('key:generate', ['--force' => true]);
            }

            try {
                Artisan::call('migrate', ['--force' => true]);
            } catch (\Exception $e) {
                return back()->withInput()->withErrors([
                    'db_error' => 'Koneksi berhasil tetapi migrasi tabel gagal: '.$e->getMessage(),
                ]);
            }
        }

        // Create default active period if not exists
        try {
            $month = (int) date('n');
            $year = (int) date('Y');
            $monthNames = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
            ];
            $periodName = ($monthNames[$month] ?? 'Bulan').' '.$year;

            Period::firstOrCreate(
                ['month' => $month, 'year' => $year],
                [
                    'name' => $periodName,
                    'status' => 'active',
                    'starts_at' => now()->startOfMonth(),
                    'ends_at' => now()->endOfMonth(),
                    'min_target' => 100,
                    'max_urls_per_domain' => 5,
                ]
            );

            // Default App Settings
            AppSetting::set('max_urls_per_domain', 5);
            AppSetting::set('default_monthly_target', 100);

            // Run storage link
            if (! app()->environment('testing')) {
                @Artisan::call('storage:link');
            }
        } catch (\Exception $e) {
            // Ignore if already set
        }

        session(['installer_db_configured' => true]);

        return redirect()->route('install.admin')->with('success', 'Database berhasil terhubung dan tabel sistem berhasil dibuat!');
    }

    /**
     * Step 3: Super Admin account creation form.
     */
    public function admin(): View|RedirectResponse
    {
        if (file_exists(storage_path('installed')) && ! app()->environment('testing')) {
            return redirect()->route('login');
        }

        return view('installer.step3_admin');
    }

    /**
     * Step 3 POST: Save Super Admin account and finalize installation.
     */
    public function saveAdmin(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'alpha_dash', 'min:3', 'max:50'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'confirmed', 'min:8'],
        ]);

        // Create or update Super Admin account
        User::updateOrCreate(
            ['email' => strtolower($validated['email'])],
            [
                'name' => $validated['name'],
                'username' => strtolower($validated['username']),
                'password' => Hash::make($validated['password']),
                'role' => 'admin',
                'is_active' => true,
                'approval_status' => 'approved',
                'approved_at' => now(),
            ]
        );

        // Create lock file to seal the installer
        file_put_contents(storage_path('installed'), json_encode([
            'installed_at' => date('Y-m-d H:i:s'),
            'version' => '1.0.0',
            'admin_email' => $validated['email'],
        ], JSON_PRETTY_PRINT));

        // Clear caches
        if (! app()->environment('testing')) {
            @Artisan::call('optimize:clear');
        }

        session(['installer_finished' => true]);

        return redirect()->route('install.finish');
    }

    /**
     * Step 4: Installation complete screen.
     */
    public function finish(): View
    {
        return view('installer.step4_finish');
    }

    /**
     * Helper to safely update key=value in .env file.
     *
     * @param  array<string, string>  $data
     */
    protected function updateEnv(array $data): void
    {
        $envPath = base_path('.env');
        if (! file_exists($envPath)) {
            if (file_exists(base_path('.env.example'))) {
                copy(base_path('.env.example'), $envPath);
            } else {
                touch($envPath);
            }
        }

        $content = file_get_contents($envPath);

        foreach ($data as $key => $value) {
            $formattedValue = preg_match('/\s/', $value) ? "\"{$value}\"" : $value;

            if (preg_match("/^{$key}=.*/m", $content)) {
                $content = preg_replace("/^{$key}=.*/m", "{$key}={$formattedValue}", $content);
            } else {
                $content .= "\n{$key}={$formattedValue}";
            }
        }

        file_put_contents($envPath, $content);
    }
}
