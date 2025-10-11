<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class Leaves extends Model
{
    use HasFactory, HasUuids;
    protected $table = 'leaves';
    protected $fillable = [
        'name',
        'description',
        'days',
        'gender',
        'status',
        'added_by',
    ];

    public function added_by()
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}
