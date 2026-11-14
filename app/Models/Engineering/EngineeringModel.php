<?php

namespace App\Models\Engineering;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * Base for engineering models. Attributes are unguarded because every write goes through
 * a service or a validated component, never straight from request input.
 */
abstract class EngineeringModel extends Model
{
    use HasUuids;

    protected $guarded = [];
}
