<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\Assignment;
use App\Models\Domain;
use App\Models\Period;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PeriodService
{
    /**
     * Get or automatically initialize the active period for current month.
     */
    public function getActivePeriod(): Period
    {
        $active = Period::where('status', 'active')->first();

        if ($active) {
            return $active;
        }

        // Auto create period for current month if none active
        $now = Carbon::now();
        $indonesianMonths = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        $name = ($indonesianMonths[$now->month] ?? $now->format('F')).' '.$now->year;

        $period = Period::create([
            'name' => $name,
            'month' => $now->month,
            'year' => $now->year,
            'min_target' => (int) AppSetting::get('default_monthly_target', 100),
            'max_target' => null,
            'max_urls_per_domain' => (int) AppSetting::get('max_urls_per_domain', 5),
            'status' => 'active',
            'starts_at' => $now->copy()->startOfMonth()->toDateString(),
            'ends_at' => $now->copy()->endOfMonth()->toDateString(),
        ]);

        // Auto assign existing active blogwalkers to this initial period
        $blogwalkers = User::where('role', 'blogwalker')->where('is_active', true)->get();
        foreach ($blogwalkers as $bw) {
            $lastAssignment = Assignment::where('user_id', $bw->id)->latest()->first();

            Assignment::create([
                'period_id' => $period->id,
                'user_id' => $bw->id,
                'allowed_tlds' => $lastAssignment?->allowed_tlds,
                'target_keywords' => $lastAssignment?->target_keywords ?? 'jasa seo website',
                'target_backlink_url' => $lastAssignment?->target_backlink_url ?? 'https://klien-kami.com',
                'custom_instructions' => $lastAssignment?->custom_instructions,
                'min_target' => $lastAssignment?->min_target ?? $period->min_target,
                'max_target' => $lastAssignment?->max_target ?? $period->max_target,
                'status' => 'active',
                'is_eligible_next_period' => true,
                'is_active' => true,
            ]);
        }

        return $period;
    }

    /**
     * Update period and global system configuration.
     *
     * @param  array{min_target: int, max_target?: int|null, max_urls_per_domain: int, starts_at?: string, ends_at?: string, notes?: string|null}  $data
     */
    public function updatePeriodConfiguration(Period $period, array $data): void
    {
        $period->update([
            'min_target' => (int) $data['min_target'],
            'max_target' => ! empty($data['max_target']) ? (int) $data['max_target'] : null,
            'max_urls_per_domain' => (int) $data['max_urls_per_domain'],
            'starts_at' => $data['starts_at'] ?? $period->starts_at,
            'ends_at' => $data['ends_at'] ?? $period->ends_at,
            'notes' => $data['notes'] ?? $period->notes,
        ]);

        // Persist as global app defaults
        AppSetting::set('default_monthly_target', $data['min_target']);
        AppSetting::set('max_urls_per_domain', $data['max_urls_per_domain']);

        // Update default max_limit for all non-locked domains
        Domain::where('is_locked', false)->update([
            'max_limit' => (int) $data['max_urls_per_domain'],
        ]);
    }

    /**
     * Evaluate all blogwalkers in a period (identifies who reached target and who is disqualified).
     */
    public function evaluateParticipants(Period $period): Collection
    {
        $assignments = $period->assignments()->with('user')->get();

        foreach ($assignments as $assignment) {
            $stats = $period->progressForUser($assignment->user, $assignment);

            if ($stats['is_qualified']) {
                $assignment->update([
                    'status' => 'qualified',
                    'is_eligible_next_period' => true,
                    'qualification_notes' => "Lolos! Mencapai {$stats['approved']} dari target {$stats['target']} komentar.",
                ]);
            } else {
                $assignment->update([
                    'status' => 'disqualified',
                    'is_eligible_next_period' => false,
                    'qualification_notes' => "Tidak mencapai target minimal. Hanya menyelesaikan {$stats['approved']} dari target {$stats['target']} approved komentar.",
                ]);
            }
        }

        return $assignments->fresh();
    }

    /**
     * Close the current active period, perform evaluation, and initialize the new period.
     */
    public function closeAndRollOver(Period $currentPeriod, array $nextPeriodData): Period
    {
        return DB::transaction(function () use ($currentPeriod, $nextPeriodData) {
            // 1. Evaluate current participants
            $this->evaluateParticipants($currentPeriod);

            // 2. Close current period
            $currentPeriod->update([
                'status' => 'closed',
                'closed_at' => now(),
            ]);

            // 3. Create the new period
            $newPeriod = Period::create([
                'name' => $nextPeriodData['name'],
                'month' => (int) $nextPeriodData['month'],
                'year' => (int) $nextPeriodData['year'],
                'min_target' => (int) ($nextPeriodData['min_target'] ?? $currentPeriod->min_target),
                'max_target' => ! empty($nextPeriodData['max_target']) ? (int) $nextPeriodData['max_target'] : null,
                'max_urls_per_domain' => (int) ($nextPeriodData['max_urls_per_domain'] ?? $currentPeriod->max_urls_per_domain),
                'status' => 'active',
                'starts_at' => $nextPeriodData['starts_at'],
                'ends_at' => $nextPeriodData['ends_at'],
                'notes' => $nextPeriodData['notes'] ?? null,
            ]);

            // 4. Enroll participants based on qualification
            $prevAssignments = $currentPeriod->assignments()->get();

            foreach ($prevAssignments as $prev) {
                if ($prev->is_eligible_next_period) {
                    // Qualified: Enrolled as active with plotting
                    Assignment::create([
                        'period_id' => $newPeriod->id,
                        'user_id' => $prev->user_id,
                        'allowed_tlds' => $prev->allowed_tlds,
                        'target_keywords' => $prev->target_keywords,
                        'target_backlink_url' => $prev->target_backlink_url,
                        'custom_instructions' => $prev->custom_instructions,
                        'min_target' => $newPeriod->min_target,
                        'max_target' => $newPeriod->max_target,
                        'status' => 'active',
                        'is_eligible_next_period' => true,
                        'is_active' => true,
                    ]);
                } else {
                    // Disqualified: Suspended for this period
                    Assignment::create([
                        'period_id' => $newPeriod->id,
                        'user_id' => $prev->user_id,
                        'allowed_tlds' => $prev->allowed_tlds,
                        'target_keywords' => $prev->target_keywords,
                        'target_backlink_url' => $prev->target_backlink_url,
                        'custom_instructions' => 'Ditangguhkan karena tidak mencapai target bulan lalu.',
                        'min_target' => $newPeriod->min_target,
                        'max_target' => $newPeriod->max_target,
                        'status' => 'disqualified',
                        'is_eligible_next_period' => false,
                        'qualification_notes' => 'Tidak boleh ikut periode ini karena tidak mencapai target minimal periode sebelumnya.',
                        'is_active' => false,
                    ]);
                }
            }

            return $newPeriod;
        });
    }

    /**
     * Grant dispensation to a disqualified blogwalker so they can submit again.
     */
    public function grantDispensation(Assignment $assignment, string $reason): void
    {
        $assignment->update([
            'status' => 'dispensed',
            'is_eligible_next_period' => true,
            'is_active' => true,
            'qualification_notes' => "Diberikan dispensasi admin: {$reason}",
        ]);
    }
}
