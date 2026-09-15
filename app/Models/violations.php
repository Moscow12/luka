<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class violations extends Model
{
    use HasFactory, HasUuids;
    protected $table = 'violations';
    protected $fillable = [
        'violation_type',
        'description',
        'added_by',
    ];

    public function added_by()
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}
