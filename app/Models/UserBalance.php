<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserBalance extends Model
{
    use HasFactory;
    
    protected $table = 'user_balances';

    protected $fillable = [
        'user_id',
        'wallet_id',
        'balance_amount',
    ];

    protected $cast = [
        'balance_amount' => 'string'
    ];

    public function wallet() {
        return $this->belongsTo(Wallet::class, 'wallet_id');
    }

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }

}
