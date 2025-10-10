<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class workstations extends Model
{
    use HasFactory, HasUuids;
    protected $table = 'workstations';
    protected $fillable = [
        'Workstation_name',
        'StationLocation',
        'StationPhone_Number',
        'Tin_Number',
        'StationEmail_Address',
        'StationAddress',
        'StationCity',
        'StationProvince',
        'StationCountry',
        'StationPostalCode',
        'added_by',
    ];

    public function added_by()
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}
