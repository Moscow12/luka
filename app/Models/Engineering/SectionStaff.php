<?php

namespace App\Models\Engineering;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\Pivot;

class SectionStaff extends Pivot
{
    use HasUuids;

    protected $table = 'eng_section_staff';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $casts = ['is_head' => 'boolean'];
}
