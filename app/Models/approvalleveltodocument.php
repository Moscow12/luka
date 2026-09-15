<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class approvalleveltodocument extends Model
{
    use HasUuids;
    protected $table = 'approvalleveltodocuments';
    protected $fillable = [
        'approval_level_id',
        'document_type',
        'document_sub_type',
        'is_active',
        'added_by',
    ];

    public function added_by()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function approval_level()
    {
        return $this->belongsTo(approvallevel::class, 'approval_level_id');
    }
}
