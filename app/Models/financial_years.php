<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class financial_years extends Model
{
    use HasFactory, HasUuids;
    protected $table = 'financial_years';
    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'status',
        'added_by',
    ];

    public function added_by()
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}
