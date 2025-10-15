<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class departments extends Model
{
    use HasFactory, HasUuids;
    // Tell Eloquent the primary key is 'id' (UUID)
    protected $primaryKey = 'id';

    // Primary key is a string
    protected $keyType = 'string';

    // UUIDs are not auto-incrementing
    public $incrementing = false;

    protected $table = 'departments';
    protected $fillable = [
        'name',
        'description',
        'added_by',
    ];

    public function added_by()
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}
