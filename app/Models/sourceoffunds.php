<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class sourceoffunds extends Model
{
    use HasUuids;

    protected $table = 'sourceoffunds';

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'colorcode',
        'is_active',
        'estimated_cost',
        'financial_year_id',
        'added_by',
    ];

    public function financialYear(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(FinancialYear::class);
    }

    public function addedBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
