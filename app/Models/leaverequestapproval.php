<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class leaverequestapproval extends Model
{
    use HasUuids;

    protected $table = 'leaverequestapprovals';
    protected $fillable = [
        'leave_request_id',
        'approval_level_id',
        'approver_id',
        'status',
        'comments',
        'approved_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function leave_request()
    {
        return $this->belongsTo(Employeeleaves::class, 'leave_request_id');
    }

    public function approval_level()
    {
        return $this->belongsTo(approvallevel::class, 'approval_level_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
