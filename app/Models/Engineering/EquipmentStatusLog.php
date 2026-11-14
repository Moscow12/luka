<?php

namespace App\Models\Engineering;

use App\Models\Concerns\WriteOnce;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipmentStatusLog extends EngineeringModel
{
    use WriteOnce;

    public const UPDATED_AT = null;

    protected $table = 'eng_equipment_status_logs';

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
