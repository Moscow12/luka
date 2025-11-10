<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class contracts extends Model
{
    use HasUuids;

    protected $table = 'contracts';

    protected $fillable = [
        'contract_number',
        'title',
        'department_id',
        'vendor_id',
        'type',
        'start_date',
        'end_date',
        'contract_value',
        'status',
        'description',
        'notification_time',
        'added_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'contract_value' => 'decimal:2',
    ];

    public function department()
    {
        return $this->belongsTo(departments::class, 'department_id');
    }

    public function vendor()
    {
        return $this->belongsTo(vendors::class, 'vendor_id');
    }

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function parties()
    {
        return $this->hasMany(ContractParty::class, 'contract_id');
    }

    public function documents()
    {
        return $this->hasMany(contract_documents::class, 'contract_id');
    }

    public function deliverables()
    {
        return $this->hasMany(contract_deliverables::class, 'contract_id');
    }

    public function renewals()
    {
        return $this->hasMany(contract_renewals::class, 'contract_id');
    }

    public function approvals()
    {
        return $this->hasMany(contract_approvals::class, 'contract_id');
    }

    // Helper methods
    public function getDaysUntilExpiryAttribute()
    {
        return now()->diffInDays($this->end_date, false);
    }

    public function getIsExpiringSoonAttribute()
    {
        $daysUntilExpiry = $this->days_until_expiry;

        return $daysUntilExpiry > 0 && $daysUntilExpiry <= $this->notification_time;
    }

    public function getLatestDocumentAttribute()
    {
        return $this->documents()->where('status', 'active')->latest()->first();
    }
}
