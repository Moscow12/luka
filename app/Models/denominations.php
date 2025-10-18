<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class denominations extends Model
{
    use HasFactory, HasUuids;
    protected $table = 'denominations';
    protected $fillable = [
        'name',
        'region_id',
    ];

    public function religion()
    {
        return $this->belongsTo(religions::class, 'religion_id');
    }

    public function employees()
    {
        return $this->hasMany(Employee::class, 'denomination_id');
    }
}
