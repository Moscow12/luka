<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class payroll_items extends Model
{
    protected $table = 'payroll_items';
    protected $primaryKey = 'id';
    protected $fillable = [
        'payroll_id',
        'name',
        'type',
        'amount',
        'added_by',
    ];

    public function added_by()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function payroll()
    {
        return $this->belongsTo(payrolls::class, 'payroll_id');
    }
}
