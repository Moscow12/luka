<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class allowances extends Model
{
    Use HasFactory, HasUuids;
    protected $table = 'allowances';
    protected $fillable = [
        'name', 'type', 'allowance_value', 'taxable', 'is_active', 'description',  'added_by',
    ];

    public function added_by()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    
}
