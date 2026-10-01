<?php

namespace App\Models;

use App\Services\TaskTypeService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class TargetUrl extends Model
{
    use HasFactory;

    protected $fillable = [
        'url',
        'task_type',
        'client_url',
        'domain_id',
        'root_domain',
        'keyword',
        'reward_amount',
        'notes',
        'status',
        'taken_by_user_id',
        'taken_at',
        'submission_id',
        'created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'taken_at' => 'datetime',
            'reward_amount' => 'decimal:2',
        ];
    }

    /**
     * Get effective reward rate for this target.
     */
    public function getEffectiveRate(): float
    {
        if ($this->reward_amount !== null && (float) $this->reward_amount > 0) {
            return (float) $this->reward_amount;
        }

        return TaskTypeService::getRate($this->task_type ?? 'comment');
    }

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class);
    }

    public function takenBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'taken_by_user_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    /**
     * Scope for available targets that are not locked.
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available')
            ->where(function ($q) {
                $q->whereNull('domain_id')
                    ->orWhereHas('domain', function ($sub) {
                        $sub->where('is_locked', false)
                            ->whereColumn('url_count', '<', 'max_limit');
                    });
            });
    }

    /**
     * Mark target as taken by a blogwalker.
     */
    public function claimBy(User $user): void
    {
        $this->update([
            'status' => 'in_progress',
            'taken_by_user_id' => $user->id,
            'taken_at' => now(),
        ]);
    }

    /**
     * Mark target as completed upon submission.
     */
    public function markCompleted(Submission $submission): void
    {
        $this->update([
            'status' => 'completed',
            'submission_id' => $submission->id,
        ]);
    }

    /**
     * Mark target as skipped (broken link / no comment field).
     */
    public function markSkipped(?string $reason = null): void
    {
        $this->update([
            'status' => 'skipped',
            'notes' => $reason ? ($this->notes ? $this->notes." | Skip: {$reason}" : "Skip: {$reason}") : $this->notes,
        ]);
    }

    /**
     * Release a claimed target back to available pool.
     */
    public function releaseToPool(): void
    {
        $this->update([
            'status' => 'available',
            'taken_by_user_id' => null,
            'taken_at' => null,
        ]);
    }

    /**
     * Re-queue a skipped or inactive target back to available.
     */
    public function requeue(): void
    {
        $this->releaseToPool();
    }

    /**
     * Release all stale in-progress claims older than the specified hours.
     */
    public static function releaseStaleClaims(int $hours = 2): int
    {
        return self::where('status', 'in_progress')
            ->where('taken_at', '<', now()->subHours($hours))
            ->update([
                'status' => 'available',
                'taken_by_user_id' => null,
                'taken_at' => null,
            ]);
    }

    /**
     * Get when this claim will expire (2 hours after taken_at).
     */
    public function getClaimExpiresAtAttribute(): ?Carbon
    {
        return $this->taken_at ? $this->taken_at->copy()->addHours(2) : null;
    }
}
