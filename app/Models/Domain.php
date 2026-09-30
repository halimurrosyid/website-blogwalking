<?php

namespace App\Models;

use Database\Factories\DomainFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Domain extends Model
{
    /** @use HasFactory<DomainFactory> */
    use HasFactory;

    protected $fillable = [
        'root_domain',
        'tld',
        'ip_address',
        'ip_subnet',
        'da',
        'pa',
        'dr',
        'pr',
        'seo_updated_at',
        'url_count',
        'max_limit',
        'is_locked',
        'last_reset_at',
    ];

    protected function casts(): array
    {
        return [
            'da' => 'integer',
            'pa' => 'integer',
            'dr' => 'integer',
            'seo_updated_at' => 'datetime',
            'url_count' => 'integer',
            'max_limit' => 'integer',
            'is_locked' => 'boolean',
            'last_reset_at' => 'datetime',
        ];
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }

    public function targetUrls()
    {
        return $this->hasMany(TargetUrl::class);
    }

    public function isAvailable(): bool
    {
        return ! $this->is_locked && $this->url_count < $this->max_limit;
    }

    public function remainingSlots(): int
    {
        return max(0, $this->max_limit - $this->url_count);
    }

    /**
     * Recalculate domain URL count from submissions and adjust locked status.
     */
    public function recalculateCount(): void
    {
        // Only active/valid submissions (pending or approved) count towards the limit
        $count = $this->submissions()->whereIn('review_status', ['pending', 'approved'])->count();
        $this->url_count = $count;
        $this->is_locked = $count >= $this->max_limit;
        $this->save();
    }
}
