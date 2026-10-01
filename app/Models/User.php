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
     * Standard list of conventional and digital banks in Indonesia.
     *
     * @var array<string, string>
     */
    public static array $conventionalBanks = [
        // BUMN & Syariah Utama
        'BCA' => 'Bank Central Asia (BCA)',
        'Mandiri' => 'Bank Mandiri',
        'BRI' => 'Bank Rakyat Indonesia (BRI)',
        'BNI' => 'Bank Negara Indonesia (BNI)',
        'BSI' => 'Bank Syariah Indonesia (BSI)',
        'BTN' => 'Bank Tabungan Negara (BTN)',
        // Digital Banks
        'Seabank' => 'SeaBank Indonesia',
        'Jago' => 'Bank Jago',
        'BNC' => 'Bank Neo Commerce (BNC / Neobank)',
        'Blu' => 'Blu by BCA Digital',
        'BTPN_Jenius' => 'Bank BTPN / Jenius',
        'Allo' => 'Allo Bank Indonesia',
        'Superbank' => 'Superbank',
        'Raya' => 'Bank Raya Indonesia',
        'TMRW' => 'TMRW by UOB Indonesia',
        // Swasta Nasional
        'CIMB' => 'Bank CIMB Niaga',
        'Permata' => 'Bank Permata',
        'Danamon' => 'Bank Danamon',
        'Panin' => 'Bank Panin',
        'OCBC' => 'Bank OCBC NISP',
        'Mega' => 'Bank Mega',
        'Maybank' => 'Bank Maybank Indonesia',
        'Sinarmas' => 'Bank Sinarmas',
        'Muamalat' => 'Bank Muamalat',
        'Bukopin' => 'KB Bukopin',
        'MNC' => 'Bank MNC',
        'Victoria' => 'Bank Victoria',
        'Maspion' => 'Bank Maspion',
        'Ina' => 'Bank Ina Perdana',
        'ArthaGraha' => 'Bank Artha Graha Internasional',
        'Ganesha' => 'Bank Ganesha',
        'Nobu' => 'Bank Nationalnobu (Nobu Bank)',
        'Mayapada' => 'Bank Mayapada',
        'Mestika' => 'Bank Mestika Dharma',
        'Shinhan' => 'Bank Shinhan Indonesia',
        'Woori' => 'Bank Woori Saudara',
        'IBK' => 'Bank IBK Indonesia',
        'Commonwealth' => 'Bank Commonwealth',
        'UOB' => 'Bank UOB Indonesia',
        'StandardChartered' => 'Standard Chartered Bank',
        'HSBC' => 'HSBC Indonesia',
        // BPD (Bank Pembangunan Daerah)
        'BPD_DKI' => 'Bank DKI',
        'BPD_BJB' => 'Bank BJB (Jawa Barat & Banten)',
        'BPD_Jateng' => 'Bank Jateng',
        'BPD_Jatim' => 'Bank Jatim',
        'BPD_DIY' => 'Bank BPD DIY (Yogyakarta)',
        'BPD_Bali' => 'Bank BPD Bali',
        'BPD_Sumut' => 'Bank Sumut',
        'BPD_Nagari' => 'Bank Nagari (BPD Sumatera Barat)',
        'BPD_RiauKepri' => 'Bank Riau Kepri Syariah',
        'BPD_SumselBabel' => 'Bank Sumsel Babel',
        'BPD_Lampung' => 'Bank Lampung',
        'BPD_Jambi' => 'Bank Jambi',
        'BPD_Bengkulu' => 'Bank Bengkulu',
        'BPD_Kalsel' => 'Bank Kalsel',
        'BPD_Kalbar' => 'Bank Kalbar',
        'BPD_Kaltimtara' => 'Bank Kaltimtara',
        'BPD_Kalteng' => 'Bank Kalteng',
        'BPD_Sulselbar' => 'Bank Sulselbar',
        'BPD_SulutGo' => 'Bank SulutGo',
        'BPD_Sulteng' => 'Bank Sulteng',
        'BPD_Sultra' => 'Bank Sultra',
        'BPD_NTB' => 'Bank NTB Syariah',
        'BPD_NTT' => 'Bank NTT',
        'BPD_MalukuMalut' => 'Bank Maluku Malut',
        'BPD_Papua' => 'Bank Papua',
        // E-Wallet
        'DANA' => 'DANA (Dompet Digital)',
        'GOPAY' => 'GoPay (Gojek)',
        'OVO' => 'OVO',
        'SHOPEEPAY' => 'ShopeePay',
        'LINKAJA' => 'LinkAja',
        // Lainnya
        'LAINNYA' => 'Bank Lain / Koperasi / BPR Lainnya',
    ];

    /**
     * Get all supported banks in Indonesia.
     *
     * @return array<string, string>
     */
    public static function getBanks(): array
    {
        return self::$conventionalBanks;
    }

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

    public function profileChangeRequests()
    {
        return $this->hasMany(ProfileChangeRequest::class);
    }

    public function pendingProfileChangeRequest()
    {
        return $this->hasOne(ProfileChangeRequest::class)->where('status', 'pending')->latestOfMany();
    }
}
