<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractDeduction extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'contract_deductions';

    protected $fillable = [
        'contract_id',
        'deduction_id',
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

    public function deduction()
    {
        return $this->belongsTo(Deduction::class, 'deduction_id');
    }

    /**
     * Calculate the actual deduction amount based on type (fixed or percentage)
     *
     * @param  float  $basicSalary  The basic salary to calculate percentage from
     * @param  float  $grossSalary  The gross salary (for deductions applied to gross)
     * @return float The calculated deduction amount
     */
    public function calculateAmount($basicSalary, $grossSalary = null)
    {
        // If there's an amount override, use it directly (assumes it's a fixed amount)
        if ($this->amount_override !== null && $this->amount_override > 0) {
            return $this->amount_override;
        }

        // Get the deduction configuration
        $deduction = $this->deduction;
        if (! $deduction) {
            return 0;
        }

        // Calculate based on type
        if ($deduction->type === 'percentage') {
            // Determine what to apply percentage to based on 'applies_to' field
            $baseAmount = $basicSalary;
            if ($grossSalary !== null && $deduction->applies_to === 'gross_salary') {
                $baseAmount = $grossSalary;
            }

            // Calculate percentage of base amount
            return ($deduction->deduction_value / 100) * $baseAmount;
        }

        // Default to fixed amount
        return $deduction->deduction_value;
    }

    /**
     * Get display information about how the deduction is calculated
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
                'applies_to' => null,
            ];
        }

        $deduction = $this->deduction;
        if (! $deduction) {
            return ['type' => 'fixed', 'value' => 0, 'is_override' => false, 'applies_to' => null];
        }

        return [
            'type' => $deduction->type,
            'value' => $deduction->deduction_value,
            'is_override' => false,
            'applies_to' => $deduction->applies_to ?? 'basic_salary',
        ];
    }
}
