<?php

namespace App\Models\Engineering;

use Illuminate\Database\Eloquent\Model;

class Sequence extends Model
{
    protected $table = 'eng_sequences';

    public $timestamps = false;

    protected $guarded = [];
}
