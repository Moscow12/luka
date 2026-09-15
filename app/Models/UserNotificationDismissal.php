<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class UserNotificationDismissal extends Model
{
    use HasUuids;

    protected $table = 'user_notification_dismissals';

    protected $fillable = ['user_id', 'notification_key', 'dismissed_at'];

    protected $casts = [
        'dismissed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
