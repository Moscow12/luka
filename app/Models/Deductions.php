<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deductions extends Model
{
    /** @use HasFactory<\Database\Factories\DeductionsFactory> */
    use HasFactory, HasUuids;
    protected $table = 'deductions';
    protected $fillable = [
        'name',
        'modepercentage',
        'Deduction_Type',
        'Mode',
        'Amount',
        'Description',
        'added_by',
    ];

    public function added_by()
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}
