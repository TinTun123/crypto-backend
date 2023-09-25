<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoinswapTransaction extends Model
{
    use HasFactory;

    protected $table = 'coinswap_transactions';

    protected $cast = [
        'transfer_amount' => 'string',
        'received_amount' => 'string',
    ];

    protected $fillable = [
        'user_id',
        'from_wallet_id',
        'to_wallet_id',
        'transfer_amount',
        'received_amount',
        'status',
    ];

    public function fromWallet() {
        return $this->belongsTo(Wallet::class, 'from_wallet_id');
    }

    public function toWallet() {
        return $this->belongsTo(Wallet::class, 'to_wallet_id');
    }

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }

}
