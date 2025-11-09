<?php

namespace App\Helpers;

/**
 * Tanzania PAYE (Pay As You Earn) Tax Calculator
 *
 * This class calculates PAYE tax for Tanzania Mainland (Bara) based on
 * the official tax brackets as per Tanzania Revenue Authority (TRA).
 *
 * Tax Year: 2024/2025
 * Currency: TZS (Tanzania Shillings)
 */
class TanzaniaPAYE
{
    /**
     * Monthly tax brackets for Tanzania PAYE
     * Format: [max_income, tax_rate, cumulative_tax_from_previous_brackets]
     */
    private const TAX_BRACKETS = [
        ['min' => 0, 'max' => 270000, 'rate' => 0, 'cumulative' => 0],
        ['min' => 270001, 'max' => 520000, 'rate' => 0.08, 'cumulative' => 0],
        ['min' => 520001, 'max' => 760000, 'rate' => 0.20, 'cumulative' => 20000],
        ['min' => 760001, 'max' => 1000000, 'rate' => 0.25, 'cumulative' => 68000],
        ['min' => 1000001, 'max' => PHP_INT_MAX, 'rate' => 0.30, 'cumulative' => 128000],
    ];

    /**
     * Calculate monthly PAYE tax
     *
     * @param  float  $monthlyIncome  Gross monthly income in TZS
     * @param  float  $monthlyRelief  Monthly tax relief (default 0)
     * @return array Returns calculated tax details
     */
    public static function calculateMonthlyPAYE(float $monthlyIncome, float $monthlyRelief = 0): array
    {
        // Apply relief to get taxable income
        $taxableIncome = max(0, $monthlyIncome - $monthlyRelief);

        if ($taxableIncome <= 0) {
            return [
                'gross_income' => $monthlyIncome,
                'relief' => $monthlyRelief,
                'taxable_income' => 0,
                'tax_amount' => 0,
                'net_income' => $monthlyIncome,
                'effective_tax_rate' => 0,
                'bracket' => self::TAX_BRACKETS[0],
            ];
        }

        // Find applicable bracket and calculate tax
        $taxAmount = self::calculateTax($taxableIncome);
        $netIncome = $monthlyIncome - $taxAmount;
        $effectiveTaxRate = $monthlyIncome > 0 ? ($taxAmount / $monthlyIncome) * 100 : 0;

        return [
            'gross_income' => round($monthlyIncome, 2),
            'relief' => round($monthlyRelief, 2),
            'taxable_income' => round($taxableIncome, 2),
            'tax_amount' => round($taxAmount, 2),
            'net_income' => round($netIncome, 2),
            'effective_tax_rate' => round($effectiveTaxRate, 2),
            'bracket' => self::getCurrentBracket($taxableIncome),
        ];
    }

    /**
     * Calculate annual PAYE tax
     *
     * @param  float  $annualIncome  Gross annual income in TZS
     * @param  float  $annualRelief  Annual tax relief (default 0)
     * @return array Returns calculated tax details
     */
    public static function calculateAnnualPAYE(float $annualIncome, float $annualRelief = 0): array
    {
        $monthlyIncome = $annualIncome / 12;
        $monthlyRelief = $annualRelief / 12;

        $monthlyCalculation = self::calculateMonthlyPAYE($monthlyIncome, $monthlyRelief);

        return [
            'gross_income' => round($annualIncome, 2),
            'relief' => round($annualRelief, 2),
            'taxable_income' => round($monthlyCalculation['taxable_income'] * 12, 2),
            'tax_amount' => round($monthlyCalculation['tax_amount'] * 12, 2),
            'net_income' => round($monthlyCalculation['net_income'] * 12, 2),
            'effective_tax_rate' => $monthlyCalculation['effective_tax_rate'],
            'monthly_breakdown' => $monthlyCalculation,
        ];
    }

    /**
     * Calculate tax amount based on taxable income
     *
     * @param  float  $taxableIncome  Taxable income amount
     * @return float Calculated tax amount
     */
    private static function calculateTax(float $taxableIncome): float
    {
        if ($taxableIncome <= 270000) {
            return 0;
        }

        $bracket = self::getCurrentBracket($taxableIncome);

        // Calculate tax: cumulative from previous brackets + (income above bracket min * rate)
        $taxAmount = $bracket['cumulative'] + (($taxableIncome - $bracket['min'] + 1) * $bracket['rate']);

        return max(0, $taxAmount);
    }

    /**
     * Get the current tax bracket for given income
     *
     * @param  float  $income  Taxable income
     * @return array Current tax bracket details
     */
    private static function getCurrentBracket(float $income): array
    {
        foreach (self::TAX_BRACKETS as $bracket) {
            if ($income >= $bracket['min'] && $income <= $bracket['max']) {
                return $bracket;
            }
        }

        // Return highest bracket if income exceeds all brackets
        return end(self::TAX_BRACKETS);
    }

    /**
     * Get all tax brackets
     *
     * @return array All tax brackets
     */
    public static function getTaxBrackets(): array
    {
        return self::TAX_BRACKETS;
    }

    /**
     * Format currency for Tanzania (TZS)
     *
     * @param  float  $amount  Amount to format
     * @return string Formatted currency string
     */
    public static function formatCurrency(float $amount): string
    {
        return 'TZS '.number_format($amount, 2, '.', ',');
    }

    /**
     * Calculate PAYE breakdown showing tax per bracket
     *
     * @param  float  $monthlyIncome  Gross monthly income
     * @param  float  $monthlyRelief  Monthly tax relief
     * @return array Detailed breakdown of tax per bracket
     */
    public static function getDetailedBreakdown(float $monthlyIncome, float $monthlyRelief = 0): array
    {
        $taxableIncome = max(0, $monthlyIncome - $monthlyRelief);
        $breakdown = [];
        $remainingIncome = $taxableIncome;
        $totalTax = 0;

        foreach (self::TAX_BRACKETS as $bracket) {
            if ($remainingIncome <= 0) {
                break;
            }

            $bracketSize = $bracket['max'] - $bracket['min'] + 1;
            $incomeInBracket = min($remainingIncome, $bracketSize);

            // For first bracket (min > 0), adjust calculation
            if ($bracket['min'] > 0 && $remainingIncome >= $bracket['min']) {
                $incomeInBracket = min($remainingIncome - ($bracket['min'] - 1), $bracketSize);
            }

            $taxInBracket = $incomeInBracket * $bracket['rate'];
            $totalTax += $taxInBracket;

            $breakdown[] = [
                'bracket' => self::formatCurrency($bracket['min']).' - '.($bracket['max'] === PHP_INT_MAX ? 'Above' : self::formatCurrency($bracket['max'])),
                'rate' => ($bracket['rate'] * 100).'%',
                'income_in_bracket' => round($incomeInBracket, 2),
                'tax_in_bracket' => round($taxInBracket, 2),
            ];

            $remainingIncome -= $incomeInBracket;
        }

        return [
            'gross_income' => $monthlyIncome,
            'relief' => $monthlyRelief,
            'taxable_income' => $taxableIncome,
            'total_tax' => round($totalTax, 2),
            'net_income' => round($monthlyIncome - $totalTax, 2),
            'breakdown' => $breakdown,
        ];
    }

    /**
     * Calculate net salary after PAYE and other deductions
     *
     * @param  float  $grossSalary  Gross salary
     * @param  float  $paye  PAYE tax amount
     * @param  array  $otherDeductions  Other deductions [name => amount]
     * @param  array  $allowances  Allowances [name => amount]
     * @return array Complete salary breakdown
     */
    public static function calculateNetSalary(
        float $grossSalary,
        float $paye,
        array $otherDeductions = [],
        array $allowances = []
    ): array {
        $totalAllowances = array_sum($otherDeductions);
        $totalDeductions = $paye + array_sum($otherDeductions);
        $totalAllowancesAmount = array_sum($allowances);

        $netSalary = $grossSalary + $totalAllowancesAmount - $totalDeductions;

        return [
            'gross_salary' => round($grossSalary, 2),
            'allowances' => $allowances,
            'total_allowances' => round($totalAllowancesAmount, 2),
            'paye_tax' => round($paye, 2),
            'other_deductions' => $otherDeductions,
            'total_deductions' => round($totalDeductions, 2),
            'net_salary' => round($netSalary, 2),
        ];
    }
}
