<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name',
    'username',
    'email',
    'password',
    'role',
    'default_rate',
    'phone',
    'is_active',
    'bank_name',
    'bank_account_number',
    'bank_account_name',
    'id_card_path',
    'bank_book_path',
    'approval_status',
    'rejection_reason',
    'approved_at',
    'approved_by',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Standard list of conventional banks in Indonesia.
     *
     * @var array<string, string>
     */
    public static array $conventionalBanks = [
        'BCA' => 'Bank Central Asia (BCA)',
        'Mandiri' => 'Bank Mandiri',
        'BRI' => 'Bank Rakyat Indonesia (BRI)',
        'BNI' => 'Bank Negara Indonesia (BNI)',
        'CIMB' => 'Bank CIMB Niaga',
        'Permata' => 'Bank Permata',
        'Danamon' => 'Bank Danamon',
        'BTN' => 'Bank Tabungan Negara (BTN)',
        'Panin' => 'Bank Panin',
        'OCBC' => 'Bank OCBC NISP',
        'BTPN' => 'Bank BTPN / Jenius',
        'Maybank' => 'Bank Maybank Indonesia',
        'Mega' => 'Bank Mega',
        'Sinarmas' => 'Bank Sinarmas',
        'BSI' => 'Bank Syariah Indonesia (BSI)',
        'Muamalat' => 'Bank Muamalat',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'default_rate' => 'decimal:2',
            'is_active' => 'boolean',
            'approved_at' => 'datetime',
        ];
    }

    /**
     * Get default rate with fallback.
     */
    public function getDefaultRateAttribute($value): float
    {
        return (float) ($value ?? 750);
    }

    public function isPendingApproval(): bool
    {
        return $this->approval_status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->approval_status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->approval_status === 'rejected';
    }

    public function getIdCardUrlAttribute(): ?string
    {
        if (! $this->id_card_path) {
            return null;
        }

        if (preg_match('/^https?:\/\/[^\/]+(\/storage\/.*)$/i', $this->id_card_path, $matches)) {
            return $matches[1];
        }

        if (str_starts_with($this->id_card_path, 'http://') || str_starts_with($this->id_card_path, 'https://')) {
            return $this->id_card_path;
        }

        return '/storage/'.ltrim($this->id_card_path, '/');
    }

    public function getBankBookUrlAttribute(): ?string
    {
        if (! $this->bank_book_path) {
            return null;
        }

        if (preg_match('/^https?:\/\/[^\/]+(\/storage\/.*)$/i', $this->bank_book_path, $matches)) {
            return $matches[1];
        }

        if (str_starts_with($this->bank_book_path, 'http://') || str_starts_with($this->bank_book_path, 'https://')) {
            return $this->bank_book_path;
        }

        return '/storage/'.ltrim($this->bank_book_path, '/');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isBlogwalker(): bool
    {
        return $this->role === 'blogwalker' || $this->role === 'worker';
    }

    public function isWorker(): bool
    {
        return $this->isBlogwalker();
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }

    public function activeAssignment()
    {
        return $this->hasOne(Assignment::class)->where('is_active', true)->latestOfMany();
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }

    public function payouts()
    {
        return $this->hasMany(Payout::class);
    }
}
