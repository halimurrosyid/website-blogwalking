<?php

namespace App\Models;

use Database\Factories\SubmissionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Submission extends Model
{
    /** @use HasFactory<SubmissionFactory> */
    use HasFactory;

    protected $fillable = [
        'period_id',
        'user_id',
        'domain_id',
        'target_url_id',
        'target_url',
        'screenshot_path',
        'comment_type',
        'review_status',
        'rejection_reason',
        'rate_amount',
        'is_paid',
        'payout_id',
        'paid_at',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'rate_amount' => 'decimal:2',
            'is_paid' => 'boolean',
            'paid_at' => 'datetime',
            'reviewed_at' => 'datetime',
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

    public function domain()
    {
        return $this->belongsTo(Domain::class);
    }

    public function targetUrl()
    {
        return $this->belongsTo(TargetUrl::class);
    }

    public function payout()
    {
        return $this->belongsTo(Payout::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function getScreenshotUrlAttribute(): string
    {
        if (! $this->screenshot_path) {
            return '';
        }

        return Storage::disk('public')->url($this->screenshot_path);
    }
}
