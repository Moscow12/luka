<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class contract_documents extends Model
{
    use HasUuids;

    protected $table = 'contract_documents';

    protected $fillable = [
        'contract_id',
        'document_name',
        'document_number',
        'file_path',
        'version',
        'description',
        'status',
        'added_by',
    ];

    public function contract()
    {
        return $this->belongsTo(contracts::class, 'contract_id');
    }

    public function added_by()
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}
