<?php

namespace App\Models;

use Database\Factories\AssignmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    /** @use HasFactory<AssignmentFactory> */
    use HasFactory;

    protected $fillable = [
        'period_id',
        'user_id',
        'allowed_tlds',
        'target_keywords',
        'target_backlink_url',
        'custom_instructions',
        'min_target',
        'max_target',
        'status',
        'is_eligible_next_period',
        'qualification_notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'allowed_tlds' => 'array',
            'min_target' => 'integer',
            'max_target' => 'integer',
            'is_eligible_next_period' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function period()
    {
        return $this->belongsTo(Period::class);
    }

    public function effectiveMinTarget(): int
    {
        return $this->min_target ?? $this->period?->min_target ?? 100;
    }

    public function isDisqualified(): bool
    {
        return $this->status === 'disqualified' || ! $this->is_eligible_next_period;
    }
}
