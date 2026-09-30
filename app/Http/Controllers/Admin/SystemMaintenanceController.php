<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

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

        $zipSupported = class_exists(ZipArchive::class);

        return view('admin.system.index', [
            'serverInfo' => $serverInfo,
            'publicStorageExists' => $publicStorageExists,
            'publicStorageIsLink' => $publicStorageIsLink,
            'dbDriver' => $dbDriver,
            'dbTablesCount' => $dbTablesCount,
            'appUrl' => config('app.url', url('/')),
            'appName' => config('app.name', 'Blogwalker Pro'),
            'zipSupported' => $zipSupported,
        ]);
    }

    /**
     * Clear all application caches (config, cache, route, view).
     */
    public function clearCache(): RedirectResponse
    {
        try {
            Artisan::call('optimize:clear');

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
    public function backupDatabase(): BinaryFileResponse|RedirectResponse
    {
        try {
            $filename = 'backup_blogwalker_db_'.date('Y-m-d_His').'.sql';
            $tempSqlPath = storage_path('app/backup_temp_'.uniqid().'.sql');

            $this->writeSqlDumpToFile($tempSqlPath);

            return response()->download($tempSqlPath, $filename, [
                'Content-Type' => 'application/sql',
            ])->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengekspor database: '.$e->getMessage());
        }
    }

    /**
     * Export complete migration package (Database SQL + Uploaded Files) as a ZIP archive.
     */
    public function backupFullZip(): BinaryFileResponse|RedirectResponse
    {
        if (! class_exists(ZipArchive::class)) {
            return back()->with('error', 'Ekstensi PHP ZipArchive tidak aktif pada server hosting ini. Silakan gunakan Download Backup SQL.');
        }

        try {
            $tempZipPath = storage_path('app/backup_full_'.uniqid().'.zip');
            $tempSqlPath = storage_path('app/backup_temp_sql_'.uniqid().'.sql');

            $zip = new ZipArchive();
            if ($zip->open($tempZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                return back()->with('error', 'Gagal membuat file arsip ZIP cadangan.');
            }

            // 1. Generate SQL dump and add to ZIP
            $this->writeSqlDumpToFile($tempSqlPath);
            $zip->addFile($tempSqlPath, 'database.sql');

            // 2. Add files from storage/app/public
            $storagePublicPath = storage_path('app/public');
            if (file_exists($storagePublicPath)) {
                $files = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($storagePublicPath, RecursiveDirectoryIterator::SKIP_DOTS),
                    RecursiveIteratorIterator::LEAVES_ONLY
                );

                foreach ($files as $file) {
                    if (! $file->isDir()) {
                        $filePath = $file->getRealPath();
                        $relativePath = 'storage/'.substr($filePath, strlen($storagePublicPath) + 1);
                        $relativePath = str_replace('\\', '/', $relativePath);
                        $zip->addFile($filePath, $relativePath);
                    }
                }
            }

            $zip->close();

            // Clean up temp SQL
            if (file_exists($tempSqlPath)) {
                @unlink($tempSqlPath);
            }

            $downloadFilename = 'backup_blogwalker_lengkap_'.date('Y-m-d_His').'.zip';

            return response()->download($tempZipPath, $downloadFilename, [
                'Content-Type' => 'application/zip',
            ])->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membuat paket cadangan lengkap: '.$e->getMessage());
        }
    }

    /**
     * Restore database and uploaded files from uploaded .sql or .zip backup file.
     */
    public function restoreBackup(Request $request): RedirectResponse
    {
        $request->validate([
            'backup_file' => ['required', 'file', 'max:102400'], // 100MB
        ], [
            'backup_file.required' => 'Silakan pilih file cadangan (.sql atau .zip) terlebih dahulu.',
            'backup_file.max' => 'Ukuran file cadangan maksimal 100 MB.',
        ]);

        $uploadedFile = $request->file('backup_file');
        $extension = strtolower($uploadedFile->getClientOriginalExtension());

        if (! in_array($extension, ['sql', 'zip', 'txt'])) {
            return back()->with('error', 'Format file tidak didukung. Harap unggah file cadangan berekstensi .sql atau .zip.');
        }

        @ini_set('memory_limit', '512M');
        @set_time_limit(300);

        try {
            if ($extension === 'zip') {
                if (! class_exists(ZipArchive::class)) {
                    return back()->with('error', 'Ekstensi PHP ZipArchive tidak aktif pada server hosting ini.');
                }

                $zip = new ZipArchive();
                if ($zip->open($uploadedFile->getRealPath()) !== true) {
                    return back()->with('error', 'Gagal membuka file arsip ZIP cadangan.');
                }

                // 1. Restore database if database.sql exists
                $sqlContent = $zip->getFromName('database.sql');
                if ($sqlContent !== false && ! empty($sqlContent)) {
                    $this->executeSqlContent($sqlContent);
                }

                // 2. Restore files to storage/app/public
                $targetPublicPath = storage_path('app/public');
                if (! file_exists($targetPublicPath)) {
                    @mkdir($targetPublicPath, 0755, true);
                }

                $restoredFilesCount = 0;
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $entryName = $zip->getNameIndex($i);
                    if (str_contains($entryName, '..')) {
                        continue; // Protect against path traversal
                    }

                    if (str_starts_with($entryName, 'storage/')) {
                        $subPath = substr($entryName, strlen('storage/'));
                        if (! empty($subPath) && ! str_ends_with($subPath, '/')) {
                            $targetFilePath = $targetPublicPath.'/'.$subPath;
                            $targetDir = dirname($targetFilePath);
                            if (! file_exists($targetDir)) {
                                @mkdir($targetDir, 0755, true);
                            }
                            file_put_contents($targetFilePath, $zip->getFromIndex($i));
                            $restoredFilesCount++;
                        }
                    }
                }

                $zip->close();

                try {
                    Artisan::call('storage:link');
                    Artisan::call('optimize:clear');
                } catch (\Exception $e) {
                    // Ignore
                }

                return back()->with('success', "Pemulihan paket lengkap berhasil! Database diperbarui dan {$restoredFilesCount} file media dipulihkan.");
            } else {
                $sqlContent = file_get_contents($uploadedFile->getRealPath());
                if (empty($sqlContent)) {
                    return back()->with('error', 'File SQL yang diunggah kosong.');
                }

                $this->executeSqlContent($sqlContent);

                try {
                    Artisan::call('optimize:clear');
                } catch (\Exception $e) {
                    // Ignore
                }

                return back()->with('success', 'Database berhasil dipulihkan dari file SQL!');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memulihkan cadangan: '.$e->getMessage());
        }
    }

    /**
     * Write complete SQL database backup to a file on disk.
     */
    protected function writeSqlDumpToFile(string $filePath): void
    {
        $handle = fopen($filePath, 'w');
        if (! $handle) {
            throw new \RuntimeException("Tidak dapat membuat file dump SQL di {$filePath}");
        }

        $dbDriver = config('database.default');

        fwrite($handle, "-- =====================================================\n");
        fwrite($handle, "-- Blogwalker Pro Database Backup\n");
        fwrite($handle, "-- Waktu Backup: ".date('Y-m-d H:i:s')."\n");
        fwrite($handle, "-- Tipe Driver : {$dbDriver}\n");
        fwrite($handle, "-- =====================================================\n\n");

        if ($dbDriver === 'sqlite') {
            fwrite($handle, "PRAGMA foreign_keys = OFF;\n\n");

            $tables = DB::select("SELECT name, sql FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
            foreach ($tables as $t) {
                $tableName = $t->name;
                $createSql = $t->sql;

                fwrite($handle, "-- -----------------------------------------------------\n");
                fwrite($handle, "-- Struktur Tabel `{$tableName}`\n");
                fwrite($handle, "-- -----------------------------------------------------\n");
                fwrite($handle, "DROP TABLE IF EXISTS `{$tableName}`;\n");
                fwrite($handle, "{$createSql};\n\n");

                fwrite($handle, "-- Data Tabel `{$tableName}`\n");
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

                    fwrite($handle, "INSERT INTO `{$tableName}` (".implode(', ', $escapedColumns).') VALUES ('.implode(', ', $values).");\n");
                }
                fwrite($handle, "\n");
            }

            fwrite($handle, "PRAGMA foreign_keys = ON;\n");
        } else {
            fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n");
            fwrite($handle, "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n\n");

            $tables = DB::select('SHOW TABLES');
            foreach ($tables as $tableObj) {
                $tableArray = (array) $tableObj;
                $tableName = reset($tableArray);

                fwrite($handle, "-- -----------------------------------------------------\n");
                fwrite($handle, "-- Struktur Tabel `{$tableName}`\n");
                fwrite($handle, "-- -----------------------------------------------------\n");
                fwrite($handle, "DROP TABLE IF EXISTS `{$tableName}`;\n");

                $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`");
                $createTableArray = (array) $createTable[0];
                fwrite($handle, $createTableArray['Create Table'].";\n\n");

                fwrite($handle, "-- Data Tabel `{$tableName}`\n");
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

                    fwrite($handle, "INSERT INTO `{$tableName}` (".implode(', ', $escapedColumns).') VALUES ('.implode(', ', $values).");\n");
                }
                fwrite($handle, "\n");
            }

            fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
        }

        fclose($handle);
    }

    /**
     * Execute SQL statements safely into database.
     */
    protected function executeSqlContent(string $sqlContent): void
    {
        $dbDriver = config('database.default');

        if ($dbDriver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        } elseif ($dbDriver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF;');
        }

        DB::unprepared($sqlContent);

        if ($dbDriver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        } elseif ($dbDriver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON;');
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
