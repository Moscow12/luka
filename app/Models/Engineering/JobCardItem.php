<?php

namespace App\Models\Engineering;

class JobCardItem extends EngineeringModel
{
    protected $table = 'eng_job_card_items';

    protected $casts = [
        'quantity' => 'float',
        'price' => 'float',
    ];

    public function total(): float
    {
        return round($this->quantity * $this->price, 2);
    }
}
