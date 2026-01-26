<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class Leaves extends Model
{
    use HasFactory, HasUuids;
    protected $table = 'leaves';
    protected $fillable = [
        'name',
        'description',
        'days',
        'gender',
        'require_document',
        'paid',
        'status',
        'added_by',
    ];

    protected $casts = [
        'require_document' => 'boolean',
        'paid' => 'boolean',
    ];

    public function added_by()
    {
        return $this->belongsTo(User::class, 'added_by');
    }
    public function employee()
    {
        return $this->hasMany(Employeeleaves::class, 'leave_id');
    }
}
