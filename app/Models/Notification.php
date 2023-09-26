<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'notifications';

    protected $fillable = [
        'type',         // The notification type (e.g., App\Notifications\NewTransactionNotification)
        'notifiable_id',// ID of the notifiable entity (e.g., user_id)
        'notifiable_type', // Type of the notifiable entity (e.g., User)
        'data',         // JSON-encoded data containing the notification content
        'read_at',      // Timestamp when the notification was read
    ];


    public function setDataAttribute($data) 
    { 
        $this->attributes['data'] = json_encode($data); 
    } 
}
