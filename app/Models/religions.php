<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class religions extends Model
{
    protected $table = 'religions';
    protected $fillable = [
        'name',
        'description',
    ];
}
