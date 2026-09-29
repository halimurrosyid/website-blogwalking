<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\Domain;
use App\Models\Submission;
use App\Models\TargetUrl;
use App\Models\User;
use App\Services\PeriodService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Admin Account
        $admin = User::firstOrCreate(
            ['email' => 'admin@blogwalker.local'],
            [
                'name' => 'Administrator Utama',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'default_rate' => 0.00,
                'phone' => '081234567890',
                'is_active' => true,
            ]
        );

        // 2. Blogwalker 1 Account (Plotting: .co.id, .web.id)
        $blogwalker1 = User::firstOrCreate(
            ['email' => 'blogwalker@blogwalker.local'],
            [
                'name' => 'Budi Blogwalker (Indo)',
                'password' => Hash::make('password123'),
                'role' => 'blogwalker',
                'default_rate' => 700.00,
                'phone' => '082198765432',
                'is_active' => true,
            ]
        );

        // 3. Blogwalker 2 Account (Plotting: .com, .net)
        $blogwalker2 = User::firstOrCreate(
            ['email' => 'blogwalker2@blogwalker.local'],
            [
                'name' => 'Siti Blogwalker (Global)',
                'password' => Hash::make('password123'),
                'role' => 'blogwalker',
                'default_rate' => 700.00,
                'phone' => '082199887766',
                'is_active' => true,
            ]
        );

        // 4. Initialize Active Monthly Period
        $periodService = app(PeriodService::class);
        $period = $periodService->getActivePeriod();

        // Configure Period: min target 100, max 5 URLs per domain
        $periodService->updatePeriodConfiguration($period, [
            'min_target' => 100,
            'max_urls_per_domain' => 5,
            'max_target' => null,
            'starts_at' => now()->startOfMonth()->toDateString(),
            'ends_at' => now()->endOfMonth()->toDateString(),
            'notes' => 'Periode bulanan aktif. Pastikan setiap domain maksimal 5 URL dan capai minimal 100 approved komentar.',
        ]);

        // 5. Plotting Blogwalker 1 (.co.id & .web.id)
        Assignment::updateOrCreate(
            ['user_id' => $blogwalker1->id, 'period_id' => $period->id],
            [
                'allowed_tlds' => ['.co.id', '.web.id'],
                'target_keywords' => 'jasa seo website, pakar seo indonesia',
                'target_backlink_url' => 'https://jasaseoberkualitas.co.id',
                'custom_instructions' => 'Khusus domain lokal Indonesia (.co.id dan .web.id). Tulis komentar minimal 2 kalimat bermutu.',
                'min_target' => 100,
                'status' => 'active',
                'is_eligible_next_period' => true,
                'is_active' => true,
            ]
        );

        // 6. Plotting Blogwalker 2 (.com & .net)
        Assignment::updateOrCreate(
            ['user_id' => $blogwalker2->id, 'period_id' => $period->id],
            [
                'allowed_tlds' => ['.com', '.net'],
                'target_keywords' => 'best seo agency, backlink outreach service',
                'target_backlink_url' => 'https://globalserviceseo.com',
                'custom_instructions' => 'Fokus di blog/web berekstensi .com dan .net internasional.',
                'min_target' => 100,
                'status' => 'active',
                'is_eligible_next_period' => true,
                'is_active' => true,
            ]
        );

        // 7. Sample Domains
        $domainIndo = Domain::firstOrCreate(
            ['root_domain' => 'portalberita.co.id'],
            [
                'tld' => '.co.id',
                'url_count' => 3,
                'max_limit' => 5,
                'is_locked' => false,
            ]
        );

        $domainGlobal = Domain::firstOrCreate(
            ['root_domain' => 'techportal.com'],
            [
                'tld' => '.com',
                'url_count' => 5,
                'max_limit' => 5,
                'is_locked' => true, // Reached limit 5
            ]
        );

        // 8. Sample Submissions for Blogwalker 1
        for ($i = 1; $i <= 3; $i++) {
            Submission::firstOrCreate(
                ['target_url' => 'https://portalberita.co.id/artikel-'.$i],
                [
                    'period_id' => $period->id,
                    'user_id' => $blogwalker1->id,
                    'domain_id' => $domainIndo->id,
                    'screenshot_path' => 'screenshots/sample.png',
                    'comment_type' => 'approved_live',
                    'review_status' => 'approved',
                    'rate_amount' => 700.00,
                    'is_paid' => false,
                ]
            );
        }

        // 1 pending submission
        Submission::firstOrCreate(
            ['target_url' => 'https://portalberita.co.id/artikel-pending-review'],
            [
                'period_id' => $period->id,
                'user_id' => $blogwalker1->id,
                'domain_id' => $domainIndo->id,
                'screenshot_path' => 'screenshots/sample.png',
                'comment_type' => 'pending_moderation',
                'review_status' => 'pending',
                'rate_amount' => 700.00,
                'is_paid' => false,
            ]
        );

        // 9. Sample Target URLs in Pool
        TargetUrl::firstOrCreate(
            ['url' => 'https://portalberita.co.id/strategi-digital-marketing/'],
            [
                'domain_id' => $domainIndo->id,
                'root_domain' => 'portalberita.co.id',
                'keyword' => 'jasa seo website',
                'notes' => 'Tulis komentar bermutu di kolom komentar bawah artikel',
                'status' => 'available',
                'created_by_user_id' => $admin->id,
            ]
        );
    }
}
