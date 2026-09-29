<?php

namespace App\Models;

use Database\Factories\PayoutFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payout extends Model
{
    /** @use HasFactory<PayoutFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'total_submissions',
        'total_amount',
        'payment_method',
        'notes',
        'processed_by',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'total_submissions' => 'integer',
            'total_amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }
}
