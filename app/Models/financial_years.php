<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class financial_years extends Model
{
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
