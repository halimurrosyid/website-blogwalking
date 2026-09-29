<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SystemMaintenanceController extends Controller
{
    /**
     * Display system diagnostic info and maintenance tools.
     */
    public function index(): View
    {
        $serverInfo = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Web Server',
            'memory_limit' => ini_get('memory_limit'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'max_execution_time' => ini_get('max_execution_time').'s',
        ];

        // Storage link status
        $publicStorageExists = file_exists(public_path('storage'));
        $publicStorageIsLink = is_link(public_path('storage'));

        // Database stats
        $dbDriver = config('database.default');
        $dbTablesCount = 0;
        try {
            if ($dbDriver === 'sqlite') {
                $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
                $dbTablesCount = count($tables);
            } else {
                $tables = DB::select('SHOW TABLES');
                $dbTablesCount = count($tables);
            }
        } catch (\Exception $e) {
            $dbTablesCount = 0;
        }

        return view('admin.system.index', [
            'serverInfo' => $serverInfo,
            'publicStorageExists' => $publicStorageExists,
            'publicStorageIsLink' => $publicStorageIsLink,
            'dbDriver' => $dbDriver,
            'dbTablesCount' => $dbTablesCount,
            'appUrl' => config('app.url', url('/')),
            'appName' => config('app.name', 'Blogwalker Pro'),
        ]);
    }

    /**
     * Clear all application caches (config, cache, route, view).
     */
    public function clearCache(): RedirectResponse
    {
        try {
            Artisan::call('optimize:clear');
            $output = Artisan::output();

            return back()->with('success', 'Cache sistem berhasil dibersihkan! (Config, Route, View, Application Cache)');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membersihkan cache: '.$e->getMessage());
        }
    }

    /**
     * Execute database migrations via web button (no terminal needed).
     */
    public function runMigrate(): RedirectResponse
    {
        try {
            Artisan::call('migrate', ['--force' => true]);
            $output = trim(Artisan::output());

            $message = ! empty($output) ? $output : 'Pembaruan struktur database berhasil dijalankan!';

            return back()->with('success', 'Migrasi Database: '.$message);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menjalankan migrasi database: '.$e->getMessage());
        }
    }

    /**
     * Create or repair storage symlink via web.
     */
    public function fixStorageLink(): RedirectResponse
    {
        try {
            Artisan::call('storage:link');

            return back()->with('success', 'Storage link berhasil diperbarui! File upload siap diakses publik.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membuat storage link: '.$e->getMessage());
        }
    }

    /**
     * Export complete SQL database backup directly as a browser download.
     */
    public function backupDatabase(): StreamedResponse|RedirectResponse
    {
        try {
            $dbDriver = config('database.default');
            $filename = 'backup_blogwalker_'.date('Y-m-d_His').'.sql';

            return response()->streamDownload(function () use ($dbDriver) {
                echo "-- Blogwalker Database Backup\n";
                echo '-- Generated At: '.date('Y-m-d H:i:s')."\n";
                echo "-- Driver: {$dbDriver}\n\n";

                if ($dbDriver === 'sqlite') {
                    $sqlitePath = config('database.connections.sqlite.database');
                    if (file_exists($sqlitePath)) {
                        echo file_get_contents($sqlitePath);
                    }
                } else {
                    // MySQL Table Export
                    $tables = DB::select('SHOW TABLES');
                    $dbKey = 'Tables_in_'.config('database.connections.mysql.database');

                    foreach ($tables as $tableObj) {
                        $tableArray = (array) $tableObj;
                        $tableName = reset($tableArray);

                        echo "\n-- Table structure for `{$tableName}`\n";
                        echo "DROP TABLE IF EXISTS `{$tableName}`;\n";

                        $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`");
                        $createTableArray = (array) $createTable[0];
                        echo $createTableArray['Create Table'].";\n\n";

                        echo "-- Dumping data for `{$tableName}`\n";
                        $rows = DB::table($tableName)->get();
                        foreach ($rows as $row) {
                            $rowArray = (array) $row;
                            $columns = array_keys($rowArray);
                            $escapedColumns = array_map(fn ($c) => "`{$c}`", $columns);

                            $values = array_map(function ($val) {
                                if (is_null($val)) {
                                    return 'NULL';
                                }

                                return "'".addslashes((string) $val)."'";
                            }, array_values($rowArray));

                            echo 'INSERT INTO `'.$tableName.'` ('.implode(', ', $escapedColumns).') VALUES ('.implode(', ', $values).");\n";
                        }
                    }
                }
            }, $filename, [
                'Content-Type' => 'application/sql',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ]);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengekspor database: '.$e->getMessage());
        }
    }

    /**
     * Update application domain URL and name in .env without opening terminal.
     */
    public function updateAppUrl(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'app_url' => ['required', 'url'],
            'app_name' => ['nullable', 'string', 'max:100'],
        ]);

        $updates = [
            'APP_URL' => rtrim($validated['app_url'], '/'),
        ];

        if (! empty($validated['app_name'])) {
            $updates['APP_NAME'] = $validated['app_name'];
        }

        if (! app()->environment('testing')) {
            $this->updateEnv($updates);
            try {
                Artisan::call('config:clear');
            } catch (\Exception $e) {
                // Ignore if in restricted hosting
            }
        }

        return back()->with('success', 'URL Domain aplikasi berhasil diperbarui ke: '.$validated['app_url']);
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
            return;
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

    /**
     * Unlock installer to allow switching database connection (e.g. SQLite to MySQL).
     */
    public function resetInstaller(Request $request): RedirectResponse
    {
        $lockFile = storage_path('installed');
        if (file_exists($lockFile)) {
            @unlink($lockFile);
        }

        try {
            Artisan::call('config:clear');
            Artisan::call('cache:clear');
        } catch (\Exception $e) {
            // Ignore if restricted
        }

        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('install.index')->with('info', 'Kunci instalasi telah dibuka. Silakan masukkan data database MySQL Anda.');
    }
}
