<?php

namespace App\Models\Engineering;

use App\Models\products;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PpmConsumable extends EngineeringModel
{
    protected $table = 'eng_ppm_consumables';

    protected $casts = ['qty_required' => 'float'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(products::class, 'product_id');
    }
}
