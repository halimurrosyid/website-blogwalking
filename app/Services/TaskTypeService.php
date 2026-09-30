<?php

namespace App\Services;

use App\Models\AppSetting;

class TaskTypeService
{
    public const COMMENT = 'comment';

    public const GUESTPOST = 'guestpost';

    public const SOCIAL_MEDIA = 'social_media';

    public const COMMENT_HIGH_DR = 'comment_high_dr';

    public const INTERNAL_ARTICLE = 'internal_article';

    /**
     * Get all task types with their metadata and current default rates.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function all(): array
    {
        return [
            self::COMMENT => [
                'key' => self::COMMENT,
                'name' => 'Backlink Komentar',
                'label' => 'Backlink Komentar',
                'description' => 'Komentar di artikel blog/website orang lain dengan memasukkan anchor text dan link target.',
                'default_rate' => (float) AppSetting::get('rate_comment', 750),
                'badge' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                'badge_color' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                'pill_bg' => 'bg-emerald-600',
                'output_label' => 'URL Komentar Langsung',
            ],
            self::GUESTPOST => [
                'key' => self::GUESTPOST,
                'name' => 'Guest Post',
                'label' => 'Guest Post',
                'description' => 'Membuat artikel di website orang lain dan menyematkan target keyword & URL backlink.',
                'default_rate' => (float) AppSetting::get('rate_guestpost', 2000),
                'badge' => 'bg-blue-100 text-blue-800 border-blue-200',
                'badge_color' => 'bg-blue-100 text-blue-800 border-blue-200',
                'pill_bg' => 'bg-blue-600',
                'output_label' => 'URL Artikel Terbit di Web Luar',
            ],
            self::SOCIAL_MEDIA => [
                'key' => self::SOCIAL_MEDIA,
                'name' => 'Evergreen Konten Medsos',
                'label' => 'Evergreen Konten Medsos',
                'description' => 'Posting di media sosial (FB, X, LinkedIn, IG, Pinterest, dll.) dengan menyematkan keyword & URL target.',
                'default_rate' => (float) AppSetting::get('rate_social_media', 1500),
                'badge' => 'bg-purple-100 text-purple-800 border-purple-200',
                'badge_color' => 'bg-purple-100 text-purple-800 border-purple-200',
                'pill_bg' => 'bg-purple-600',
                'output_label' => 'URL Postingan Sosial Media',
            ],
            self::COMMENT_HIGH_DR => [
                'key' => self::COMMENT_HIGH_DR,
                'name' => 'Komentar Domain Tinggi (DR > 40)',
                'label' => 'Komentar Domain Tinggi (DR > 40)',
                'description' => 'Komentar pada website berotoritas tinggi dengan nilai Domain Rating (DR) di atas 40.',
                'default_rate' => (float) AppSetting::get('rate_comment_high_dr', 1000),
                'badge' => 'bg-amber-100 text-amber-800 border-amber-200',
                'badge_color' => 'bg-amber-100 text-amber-800 border-amber-200',
                'pill_bg' => 'bg-amber-600',
                'output_label' => 'URL Komentar di Web DR > 40',
            ],
            self::INTERNAL_ARTICLE => [
                'key' => self::INTERNAL_ARTICLE,
                'name' => 'Post Artikel Internal',
                'label' => 'Post Artikel Internal',
                'description' => 'Menulis dan memposting artikel pada jaringan blog / PBN internal kita sendiri.',
                'default_rate' => (float) AppSetting::get('rate_internal_article', 10000),
                'tier_rates' => [10000, 25000],
                'badge' => 'bg-rose-100 text-rose-800 border-rose-200',
                'badge_color' => 'bg-rose-100 text-rose-800 border-rose-200',
                'pill_bg' => 'bg-rose-600',
                'output_label' => 'URL Artikel di Web Internal Kita',
            ],
        ];
    }

    /**
     * Get default rate for a task type.
     */
    public static function getRate(string $taskType): float
    {
        $all = self::all();

        return $all[$taskType]['default_rate'] ?? 750.0;
    }

    /**
     * Get human-readable name for a task type.
     */
    public static function getName(?string $taskType): string
    {
        $all = self::all();

        return $all[$taskType]['name'] ?? 'Backlink Komentar';
    }

    /**
     * Get badge styling class for a task type.
     */
    public static function getBadgeColor(?string $taskType): string
    {
        $all = self::all();

        return $all[$taskType]['badge_color'] ?? 'bg-slate-100 text-slate-800 border-slate-200';
    }

    /**
     * Update master default rates in app_settings.
     *
     * @param  array<string, float>  $rates
     */
    public static function updateDefaultRates(array $rates): void
    {
        foreach ($rates as $key => $rate) {
            if (is_numeric($rate) && $rate >= 0) {
                AppSetting::set("rate_{$key}", (float) $rate);
            }
        }
    }
}
