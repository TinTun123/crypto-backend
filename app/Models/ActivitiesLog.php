<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivitiesLog extends Model
{
    use HasFactory;

    protected $table = 'activities_log';

    protected $fillable = ['user_ip', 'action', 'country', 'city'];

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }
}
