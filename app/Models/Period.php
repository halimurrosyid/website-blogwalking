<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Period extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'month',
        'year',
        'min_target',
        'max_target',
        'max_urls_per_domain',
        'status',
        'starts_at',
        'ends_at',
        'closed_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'date',
            'ends_at' => 'date',
            'closed_at' => 'datetime',
            'min_target' => 'integer',
            'max_target' => 'integer',
            'max_urls_per_domain' => 'integer',
        ];
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    public function remainingDays(): int
    {
        if ($this->isClosed()) {
            return 0;
        }

        $now = now()->startOfDay();
        $end = $this->ends_at->startOfDay();

        return $now->diffInDays($end, false) >= 0 ? (int) $now->diffInDays($end) : 0;
    }

    /**
     * Get statistics and progress for a specific blogwalker in this period.
     *
     * @return array{approved: int, pending: int, total: int, target: int, percentage: float, is_qualified: bool, remaining_needed: int}
     */
    public function progressForUser(User $user, ?Assignment $assignment = null): array
    {
        $assignment = $assignment ?? $this->assignments()->where('user_id', $user->id)->first();
        $minTarget = $assignment?->min_target ?? $this->min_target ?? 100;

        $approvedCount = $this->submissions()
            ->where('user_id', $user->id)
            ->where('review_status', 'approved')
            ->count();

        $pendingCount = $this->submissions()
            ->where('user_id', $user->id)
            ->where('review_status', 'pending')
            ->count();

        $percentage = $minTarget > 0 ? min(100.0, round(($approvedCount / $minTarget) * 100, 1)) : 100.0;
        $isQualified = $approvedCount >= $minTarget;
        $remainingNeeded = max(0, $minTarget - $approvedCount);

        return [
            'approved' => $approvedCount,
            'pending' => $pendingCount,
            'total' => $approvedCount + $pendingCount,
            'target' => $minTarget,
            'percentage' => $percentage,
            'is_qualified' => $isQualified,
            'remaining_needed' => $remainingNeeded,
        ];
    }
}
