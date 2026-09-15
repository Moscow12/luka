<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Testing\Fluent\Concerns\Has;

class activitypersonel extends Model
{
    use HasUuids;
    protected $table = 'activitypersonels';

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'activity_id',
        'title_id',
        'added_by',
    ];

    public function addedBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function activity(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(chopactivities::class);
    }

    public function title(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Jobtitle::class);
    }
}
