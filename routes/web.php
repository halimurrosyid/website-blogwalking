<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DomainController;
use App\Http\Controllers\Admin\PayoutController;
use App\Http\Controllers\Admin\PeriodController;
use App\Http\Controllers\Admin\RegistrationApprovalController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\SystemMaintenanceController;
use App\Http\Controllers\Admin\WorkerController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\InstallController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\Worker\DashboardController as WorkerDashboardController;
use App\Http\Controllers\Worker\SubmissionController;
use App\Http\Controllers\Worker\TargetUrlController;
use Illuminate\Support\Facades\Route;

// Web-Based Installation Wizard (cPanel / Self-Hosted Setup)
Route::prefix('install')->name('install.')->group(function () {
    Route::get('/', [InstallController::class, 'index'])->name('index');
    Route::get('/database', [InstallController::class, 'database'])->name('database');
    Route::post('/database', [InstallController::class, 'saveDatabase'])->name('database.post');
    Route::get('/admin', [InstallController::class, 'admin'])->name('admin');
    Route::post('/admin', [InstallController::class, 'saveAdmin'])->name('admin.post');
    Route::get('/finish', [InstallController::class, 'finish'])->name('finish');
});

// Redirect root to login or appropriate dashboard
Route::get('/', function () {
    if (auth()->check()) {
        return auth()->user()->isAdmin()
            ? redirect()->route('admin.dashboard')
            : redirect()->route('blogwalker.dashboard');
    }

    return redirect()->route('login');
});

// Authentication & Registration Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1')->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register'])->middleware('throttle:10,1')->name('register.post');

// Blogwalker Routes
Route::middleware(['auth', 'blogwalker'])->prefix('blogwalker')->name('blogwalker.')->group(function () {
    Route::get('/', [WorkerDashboardController::class, 'index'])->name('dashboard');
    Route::post('/check-domain', [WorkerDashboardController::class, 'checkDomain'])->middleware('throttle:60,1')->name('check-domain');
    Route::get('/targets', [TargetUrlController::class, 'index'])->name('targets.index');
    Route::post('/targets/{target}/claim', [TargetUrlController::class, 'claim'])->name('targets.claim');
    Route::post('/targets/{target}/skip', [TargetUrlController::class, 'skip'])->name('targets.skip');
    Route::get('/submissions', [SubmissionController::class, 'index'])->name('submissions.index');
    Route::get('/submissions/create', [SubmissionController::class, 'create'])->name('submissions.create');
    Route::post('/submissions', [SubmissionController::class, 'store'])->middleware('throttle:20,1')->name('submissions.store');
});

// Backward compatibility redirect for worker
Route::get('/worker', fn () => redirect()->route('blogwalker.dashboard'));
Route::get('/worker/submissions', fn () => redirect()->route('blogwalker.submissions.index'));
Route::get('/worker/submissions/create', fn () => redirect()->route('blogwalker.submissions.create'));

// Admin Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Review & Verification Queue
    Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews.index');
    Route::get('/reviews/export-csv', [ReviewController::class, 'exportCsv'])->name('reviews.export-csv');
    Route::post('/reviews/{submission}/approve', [ReviewController::class, 'approve'])->name('reviews.approve');
    Route::post('/reviews/{submission}/reject', [ReviewController::class, 'reject'])->name('reviews.reject');
    Route::post('/reviews/bulk-approve', [ReviewController::class, 'bulkApprove'])->name('reviews.bulk-approve');

    // Target URL Pool / Queue Management
    Route::get('/targets', [App\Http\Controllers\Admin\TargetUrlController::class, 'index'])->name('targets.index');
    Route::get('/targets/create', [App\Http\Controllers\Admin\TargetUrlController::class, 'create'])->name('targets.create');
    Route::post('/targets', [App\Http\Controllers\Admin\TargetUrlController::class, 'store'])->name('targets.store');
    Route::delete('/targets/{target}', [App\Http\Controllers\Admin\TargetUrlController::class, 'destroy'])->name('targets.destroy');
    Route::post('/targets/clear-completed', [App\Http\Controllers\Admin\TargetUrlController::class, 'clearCompleted'])->name('targets.clear-completed');

    // Verifikasi Pendaftaran Calon Blogwalker
    Route::get('/registrations', [RegistrationApprovalController::class, 'index'])->name('registrations.index');
    Route::post('/registrations/{applicant}/approve', [RegistrationApprovalController::class, 'approve'])->name('registrations.approve');
    Route::post('/registrations/{applicant}/reject', [RegistrationApprovalController::class, 'reject'])->name('registrations.reject');

    // Worker Management & Plotting
    Route::resource('workers', WorkerController::class)->except(['show', 'destroy']);
    Route::post('/workers/{worker}/toggle', [WorkerController::class, 'toggleStatus'])->name('workers.toggle');

    // Domain & Quota Manager
    Route::get('/domains', [DomainController::class, 'index'])->name('domains.index');
    Route::get('/domains/{domain}', [DomainController::class, 'show'])->name('domains.show');
    Route::post('/domains/{domain}/reset', [DomainController::class, 'reset'])->name('domains.reset');
    Route::post('/domains/bulk-reset', [DomainController::class, 'bulkReset'])->name('domains.bulk-reset');

    // Payout / Payroll
    Route::get('/payouts', [PayoutController::class, 'index'])->name('payouts.index');
    Route::post('/payouts/{worker}/process', [PayoutController::class, 'process'])->name('payouts.process');

    // Laporan Kinerja Tim Blogwalker
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');

    // Periode Bulanan, Evaluasi & Target Global
    Route::get('/periods', [PeriodController::class, 'index'])->name('periods.index');
    Route::post('/periods/{period}/config', [PeriodController::class, 'updateConfig'])->name('periods.config');
    Route::post('/periods/assignment/{assignment}', [PeriodController::class, 'updateAssignment'])->name('periods.assignment');
    Route::post('/periods/{period}/rollover', [PeriodController::class, 'closeAndRollOver'])->name('periods.rollover');
    Route::post('/periods/assignment/{assignment}/dispense', [PeriodController::class, 'dispense'])->name('periods.dispense');

    // Pemeliharaan Sistem (Zero Terminal / Shared Hosting Friendly)
    Route::get('/system', [SystemMaintenanceController::class, 'index'])->name('system.index');
    Route::post('/system/clear-cache', [SystemMaintenanceController::class, 'clearCache'])->name('system.clear-cache');
    Route::post('/system/run-migrate', [SystemMaintenanceController::class, 'runMigrate'])->name('system.run-migrate');
    Route::post('/system/fix-storage-link', [SystemMaintenanceController::class, 'fixStorageLink'])->name('system.fix-storage-link');
    Route::get('/system/backup', [SystemMaintenanceController::class, 'backupDatabase'])->name('system.backup');
    Route::post('/system/update-domain', [SystemMaintenanceController::class, 'updateAppUrl'])->name('system.update-domain');
    Route::post('/system/reset-installer', [SystemMaintenanceController::class, 'resetInstaller'])->name('system.reset-installer');
});
