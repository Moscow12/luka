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
        'Pay_Grade',
        'Job_Title',
        'Minimum_Salary',
        'Mid_Point_Salary',
        'Maximum_Salary',
        'Description',
        'added_by',
    ];

    public function added_by()
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}
