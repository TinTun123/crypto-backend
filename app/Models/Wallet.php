<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    use HasFactory;

    protected $table = 'wallets';
    protected $fillable = [
        'wallet_type',
        'usd_equivalent',
        'QR_image_url',
        'LOGO_image_url',
        'address',
        'normal_fee',
        'pro_fee'
    ];

    public function swappingFee() {
        return $this->hasMany(SwapFee::class, 'from_wallet_id', 'id');
    }

    public function balance() {
        return $this->hasMany(UserBalance::class, 'wallet_id', 'id');
    }
}
