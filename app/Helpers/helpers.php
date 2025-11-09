<?php

use App\Helpers\TanzaniaPAYE;

if (! function_exists('calculate_paye')) {
    /**
     * Calculate monthly PAYE tax for Tanzania
     *
     * @param  float  $monthlyIncome  Gross monthly income in TZS
     * @param  float  $monthlyRelief  Monthly tax relief (default 0)
     * @return array Tax calculation details
     */
    function calculate_paye(float $monthlyIncome, float $monthlyRelief = 0): array
    {
        return TanzaniaPAYE::calculateMonthlyPAYE($monthlyIncome, $monthlyRelief);
    }
}

if (! function_exists('calculate_annual_paye')) {
    /**
     * Calculate annual PAYE tax for Tanzania
     *
     * @param  float  $annualIncome  Gross annual income in TZS
     * @param  float  $annualRelief  Annual tax relief (default 0)
     * @return array Tax calculation details
     */
    function calculate_annual_paye(float $annualIncome, float $annualRelief = 0): array
    {
        return TanzaniaPAYE::calculateAnnualPAYE($annualIncome, $annualRelief);
    }
}

if (! function_exists('paye_breakdown')) {
    /**
     * Get detailed PAYE breakdown showing tax per bracket
     *
     * @param  float  $monthlyIncome  Gross monthly income in TZS
     * @param  float  $monthlyRelief  Monthly tax relief (default 0)
     * @return array Detailed breakdown
     */
    function paye_breakdown(float $monthlyIncome, float $monthlyRelief = 0): array
    {
        return TanzaniaPAYE::getDetailedBreakdown($monthlyIncome, $monthlyRelief);
    }
}

if (! function_exists('format_tzs')) {
    /**
     * Format amount as Tanzania Shillings
     *
     * @param  float  $amount  Amount to format
     * @return string Formatted currency string
     */
    function format_tzs(float $amount): string
    {
        return TanzaniaPAYE::formatCurrency($amount);
    }
}

if (! function_exists('calculate_net_salary')) {
    /**
     * Calculate net salary after PAYE and deductions
     *
     * @param  float  $grossSalary  Gross salary
     * @param  array  $otherDeductions  Other deductions [name => amount]
     * @param  array  $allowances  Allowances [name => amount]
     * @return array Complete salary breakdown
     */
    function calculate_net_salary(
        float $grossSalary,
        array $otherDeductions = [],
        array $allowances = []
    ): array {
        // Calculate PAYE first
        $payeCalculation = calculate_paye($grossSalary);
        $paye = $payeCalculation['tax_amount'];

        return TanzaniaPAYE::calculateNetSalary($grossSalary, $paye, $otherDeductions, $allowances);
    }
}

if (! function_exists('get_tax_brackets')) {
    /**
     * Get all Tanzania PAYE tax brackets
     *
     * @return array Tax brackets
     */
    function get_tax_brackets(): array
    {
        return TanzaniaPAYE::getTaxBrackets();
    }
}
