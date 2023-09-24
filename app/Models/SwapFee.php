<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SwapFee extends Model
{
    use HasFactory;

    protected $table = 'swap_fee';
    protected $fillable = [
        'fee',
        'from_wallet_id',
        'to_wallet_id'
    ];

    public function toWallet() {
        return $this->belongsTo(Wallet::class, 'to_wallet_id', 'id');
    }
}
