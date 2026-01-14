<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class paye_brackets extends Model
{
    use HasUuids;

    protected $table = 'paye_brackets';

    protected $fillable = [
        'min_amount',
        'max_amount',
        'rate',
        'fixed_amount',
        'description',
        'order',
        'is_active',
        'added_by',
    ];

    protected $casts = [
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'rate' => 'decimal:2',
        'fixed_amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    /**
     * Calculate PAYE tax for a given taxable income
     * Uses Tanzania mainland progressive tax calculation
     */
    public static function calculatePaye(float $taxableIncome): array
    {
        $brackets = self::where('is_active', true)
            ->orderBy('order')
            ->get();

        if ($brackets->isEmpty()) {
            return [
                'taxable_income' => $taxableIncome,
                'total_tax' => 0,
                'net_income' => $taxableIncome,
                'effective_rate' => 0,
                'breakdown' => [],
            ];
        }

        $totalTax = 0;
        $remainingIncome = $taxableIncome;
        $breakdown = [];

        foreach ($brackets as $bracket) {
            if ($remainingIncome <= 0) {
                break;
            }

            $bracketMin = (float) $bracket->min_amount;
            $bracketMax = $bracket->max_amount ? (float) $bracket->max_amount : PHP_FLOAT_MAX;
            $rate = (float) $bracket->rate;

            // Skip if income hasn't reached this bracket
            if ($taxableIncome < $bracketMin) {
                continue;
            }

            // Calculate taxable amount in this bracket
            $bracketRange = $bracketMax - $bracketMin;
            $incomeInBracket = min($taxableIncome - $bracketMin, $bracketRange);

            if ($incomeInBracket <= 0) {
                continue;
            }

            // For the first bracket (0%), no tax
            if ($rate == 0) {
                $taxInBracket = 0;
            } else {
                $taxInBracket = $incomeInBracket * ($rate / 100);
            }

            $totalTax += $taxInBracket;

            $breakdown[] = [
                'bracket' => $bracket->description ?? "TZS " . number_format($bracketMin) . " - " . ($bracket->max_amount ? "TZS " . number_format($bracketMax) : "Above"),
                'min' => $bracketMin,
                'max' => $bracketMax,
                'rate' => $rate,
                'taxable_in_bracket' => $incomeInBracket,
                'tax_amount' => $taxInBracket,
            ];
        }

        $netIncome = $taxableIncome - $totalTax;
        $effectiveRate = $taxableIncome > 0 ? ($totalTax / $taxableIncome) * 100 : 0;

        return [
            'taxable_income' => $taxableIncome,
            'total_tax' => round($totalTax, 2),
            'net_income' => round($netIncome, 2),
            'effective_rate' => round($effectiveRate, 2),
            'breakdown' => $breakdown,
        ];
    }
}
