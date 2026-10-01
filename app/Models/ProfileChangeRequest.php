<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfileChangeRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'old_name',
        'new_name',
        'old_phone',
        'new_phone',
        'old_bank_name',
        'new_bank_name',
        'old_bank_account_number',
        'new_bank_account_number',
        'old_bank_account_name',
        'new_bank_account_name',
        'bank_book_path',
        'reason',
        'status',
        'admin_notes',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }
}
