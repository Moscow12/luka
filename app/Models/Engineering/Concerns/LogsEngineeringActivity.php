<?php

namespace App\Models\Engineering\Concerns;

use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Writes every create/update of the model to the system audit log (actor, before, after).
 */
trait LogsEngineeringActivity
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('engineering')
            ->logAll()
            ->logExcept(['created_at', 'updated_at', 'last_activity_at'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
