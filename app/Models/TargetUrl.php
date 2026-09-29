<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TargetUrl extends Model
{
    use HasFactory;

    protected $fillable = [
        'url',
        'domain_id',
        'root_domain',
        'keyword',
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
        ];
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
}
