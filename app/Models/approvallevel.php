<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class approvallevel extends Model
{
    use HasUuids;
    protected $table = 'approvallevels';
    protected $fillable = [
        'name',
        'description',
        'level_order',
        'is_active',
        'added_by',
    ];
    public function added_by()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function approvalleveltodocuments()
    {
        return $this->hasMany(approvalleveltodocument::class, 'approval_level_id');
    }

    public function approvalleveltoemployees()
    {
        return $this->hasMany(approvalleveltoemployee::class, 'approval_level_id');
    }
}
