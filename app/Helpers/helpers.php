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

if (! function_exists('send_sms')) {
    /**
     * Send SMS to single or multiple phone numbers
     *
     * @param  string|array  $phoneNumber  Phone number(s) to send SMS to
     * @param  string  $message  Message content to send
     * @param  string|null  $senderId  Optional sender ID
     * @param  string|null  $providerId  Optional specific provider UUID
     * @return array Response with success status and details
     */
    function send_sms(string|array $phoneNumber, string $message, ?string $senderId = null, ?string $providerId = null): array
    {
        try {
            $smsService = new \App\Services\SmsService($providerId);

            return $smsService->send($phoneNumber, $message, $senderId);
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to send SMS: '.$e->getMessage(),
                'error' => $e->getMessage(),
            ];
        }
    }
}

if (! function_exists('send_sms_to_staff')) {
    /**
     * Send SMS to staff members by their IDs
     *
     * @param  array  $staffIds  Array of staff IDs
     * @param  string  $message  Message content
     * @param  string|null  $providerId  Optional specific provider UUID
     * @return array Results for each staff member
     */
    function send_sms_to_staff(array $staffIds, string $message, ?string $providerId = null): array
    {
        try {
            $smsService = new \App\Services\SmsService($providerId);

            return $smsService->sendToStaff($staffIds, $message);
        } catch (Exception $e) {
            return [
                [
                    'success' => false,
                    'message' => 'Failed to send SMS: '.$e->getMessage(),
                    'error' => $e->getMessage(),
                ],
            ];
        }
    }
}

if (! function_exists('format_phone_number')) {
    /**
     * Format phone number to international format (255...)
     *
     * @param  string  $phoneNumber  Phone number to format
     * @return string Formatted phone number
     */
    function format_phone_number(string $phoneNumber): string
    {
        // Remove any spaces, dashes, or special characters
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);

        // Check if it already starts with country code (255)
        if (strlen($phoneNumber) > 10 && substr($phoneNumber, 0, 3) === '255') {
            return $phoneNumber;
        }

        // Replace leading 0 with 255 (Tanzania country code)
        if (substr($phoneNumber, 0, 1) === '0') {
            return '255'.substr($phoneNumber, 1);
        }

        // If it's just 9 digits, add 255
        if (strlen($phoneNumber) === 9) {
            return '255'.$phoneNumber;
        }

        return $phoneNumber;
    }
}

if (! function_exists('get_sms_providers')) {
    /**
     * Get all active SMS providers
     */
    function get_sms_providers(): \Illuminate\Database\Eloquent\Collection
    {
        return \App\Services\SmsService::getAvailableProviders();
    }
}

if (! function_exists('get_default_sms_provider')) {
    /**
     * Get the default SMS provider
     */
    function get_default_sms_provider(): ?\App\Models\SmsApiSetting
    {
        return \App\Services\SmsService::getDefaultProvider();
    }
}
