<?php

namespace App\Models\Engineering;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\Pivot;

class JobAssistant extends Pivot
{
    use HasUuids;

    protected $table = 'eng_job_assistants';

    public $incrementing = false;

    protected $keyType = 'string';
}
