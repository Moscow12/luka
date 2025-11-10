<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class shifts extends Model
{
    use HasFactory, HasUuids;
    protected $table = 'shifts';
    protected $fillable = [
        'name',
        'description',
        'start_time',
        'end_time',
        'status',
        'count_early',
        'count_late',
        'added_by',
    ];

    public function added_by()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function rosters()
    {
        return $this->hasMany(employeeroster::class, 'shift_id');
    }
}
