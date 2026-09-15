<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class salary_scales extends Model
{
    use HasFactory, HasUuids;
    protected $table = 'salary_scales';
    protected $fillable = [
        'name',
        'description',
        'added_by',
    ];

    public function added_by()
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}
