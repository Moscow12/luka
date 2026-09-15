<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractAllowance extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'contract_allowances';

    protected $fillable = [
        'contract_id',
        'allowance_id',
        'amount_override',
        'is_active',
        'description',
        'added_by',
    ];

    public function added_by()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function contract()
    {
        return $this->belongsTo(Employeecontracts::class, 'contract_id');
    }

    public function allowance()
    {
        return $this->belongsTo(allowances::class, 'allowance_id');
    }

    /**
     * Calculate the actual allowance amount based on type (fixed or percentage)
     *
     * @param  float  $basicSalary  The basic salary to calculate percentage from
     * @return float The calculated allowance amount
     */
    public function calculateAmount($basicSalary)
    {
        // If there's an amount override, use it directly (assumes it's a fixed amount)
        if ($this->amount_override !== null && $this->amount_override > 0) {
            return $this->amount_override;
        }

        // Get the allowance configuration
        $allowance = $this->allowance;
        if (! $allowance) {
            return 0;
        }

        // Calculate based on type
        if ($allowance->type === 'percentage') {
            // Calculate percentage of basic salary
            return ($allowance->allowance_value / 100) * $basicSalary;
        }

        // Default to fixed amount
        return $allowance->allowance_value;
    }

    /**
     * Get display information about how the allowance is calculated
     *
     * @return array
     */
    public function getCalculationInfo()
    {
        if ($this->amount_override !== null && $this->amount_override > 0) {
            return [
                'type' => 'fixed',
                'value' => $this->amount_override,
                'is_override' => true,
            ];
        }

        $allowance = $this->allowance;
        if (! $allowance) {
            return ['type' => 'fixed', 'value' => 0, 'is_override' => false];
        }

        return [
            'type' => $allowance->type,
            'value' => $allowance->allowance_value,
            'is_override' => false,
        ];
    }
}
