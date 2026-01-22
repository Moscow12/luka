<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class assetclass extends Model
{
    protected $table = 'assetclasses';
    protected $fillable = ['name', 'depreciation', 'added_by'];

    public function added_by()
    {
        return $this->belongsTo(employee::class, 'added_by');
    }

}
