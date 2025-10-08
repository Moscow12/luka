<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class deductions extends Model
{
    protected $table = 'deductions';
    protected $fillable = [
        'Deduction_Type',
        'Amount',
        'Description',
        'added_by',
    ];

    public function added_by()
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}
