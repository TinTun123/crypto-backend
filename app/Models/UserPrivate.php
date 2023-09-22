<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPrivate extends Model
{
    use HasFactory;

    protected $table = 'user_private_keys';

    protected $fillable = [
        'private_key',
        'state',
        'user_id',
        'isVerified'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
}
