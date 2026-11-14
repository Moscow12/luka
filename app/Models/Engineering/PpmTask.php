<?php

namespace App\Models\Engineering;

class PpmTask extends EngineeringModel
{
    protected $table = 'eng_ppm_tasks';

    protected $casts = ['mandatory' => 'boolean'];
}
