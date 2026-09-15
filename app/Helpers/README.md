# Tanzania PAYE Tax Calculator

A comprehensive helper for calculating PAYE (Pay As You Earn) tax in Tanzania Mainland (Bara) based on Tanzania Revenue Authority (TRA) tax brackets.

## Tax Brackets (2024/2025)

Monthly income tax brackets:

| Monthly Income (TZS) | Tax Rate | Cumulative Tax |
|---------------------|----------|----------------|
| 0 - 270,000 | 0% | 0 |
| 270,001 - 520,000 | 8% | 0 |
| 520,001 - 760,000 | 20% | 20,000 |
| 760,001 - 1,000,000 | 25% | 68,000 |
| Above 1,000,000 | 30% | 128,000 |

## Installation

The helper is already registered in `composer.json` and auto-loaded. No additional setup required.

## Available Functions

### 1. Calculate Monthly PAYE

```php
calculate_paye(float $monthlyIncome, float $monthlyRelief = 0): array
```

Calculate PAYE tax for monthly income.

**Example:**
```php
$result = calculate_paye(1500000);

// Returns:
// [
//     'gross_income' => 1500000.00,
//     'relief' => 0.00,
//     'taxable_income' => 1500000.00,
//     'tax_amount' => 278000.00,
//     'net_income' => 1222000.00,
//     'effective_tax_rate' => 18.53,
//     'bracket' => [...]
// ]
```

### 2. Calculate Annual PAYE

```php
calculate_annual_paye(float $annualIncome, float $annualRelief = 0): array
```

Calculate PAYE tax for annual income with monthly breakdown.

**Example:**
```php
$result = calculate_annual_paye(18000000);

// Returns annual totals plus monthly breakdown
```

### 3. Get Detailed Breakdown

```php
paye_breakdown(float $monthlyIncome, float $monthlyRelief = 0): array
```

Get detailed tax calculation showing how much tax is paid in each bracket.

**Example:**
```php
$breakdown = paye_breakdown(1500000);

// Shows tax calculation per bracket:
// - TZS 270,001 - 520,000 @ 8%: TZS 20,000
// - TZS 520,001 - 760,000 @ 20%: TZS 48,000
// - TZS 760,001 - 1,000,000 @ 25%: TZS 60,000
// - Above 1,000,000 @ 30%: TZS 149,999.70
```

### 4. Calculate Net Salary

```php
calculate_net_salary(
    float $grossSalary,
    array $otherDeductions = [],
    array $allowances = []
): array
```

Calculate net salary after PAYE, deductions, and allowances.

**Example:**
```php
$salary = calculate_net_salary(
    grossSalary: 1200000,
    deductions: [
        'NSSF' => 60000,
        'NHIF' => 30000,
        'Loan' => 100000,
    ],
    allowances: [
        'Transport' => 150000,
        'Housing' => 300000,
    ]
);

// Returns complete salary breakdown
```

### 5. Format Currency

```php
format_tzs(float $amount): string
```

Format amount as Tanzania Shillings.

**Example:**
```php
echo format_tzs(1500000);
// Output: TZS 1,500,000.00
```

### 6. Get Tax Brackets

```php
get_tax_brackets(): array
```

Get all tax bracket information.

**Example:**
```php
$brackets = get_tax_brackets();
```

## Usage in Livewire Components

```php
use Livewire\Component;

class SalaryCalculator extends Component
{
    public $grossSalary = 0;
    public $payeDetails = [];

    public function calculatePaye()
    {
        $this->payeDetails = calculate_paye($this->grossSalary);
    }

    public function render()
    {
        return view('livewire.salary-calculator');
    }
}
```

## Usage in Blade Templates

```blade
@php
    $paye = calculate_paye($employee->salary);
@endphp

<div>
    <p>Gross Salary: {{ format_tzs($paye['gross_income']) }}</p>
    <p>PAYE Tax: {{ format_tzs($paye['tax_amount']) }}</p>
    <p>Net Salary: {{ format_tzs($paye['net_income']) }}</p>
    <p>Tax Rate: {{ $paye['effective_tax_rate'] }}%</p>
</div>
```

## Usage in Models

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employeesalaries extends Model
{
    public function getPayeAttribute()
    {
        return calculate_paye($this->base_salary);
    }

    public function getNetSalaryAttribute()
    {
        $allowances = $this->allowances->pluck('allowance_amount', 'allowance.name')->toArray();
        $deductions = $this->deductions->pluck('deductions_amount', 'deduction.name')->toArray();

        return calculate_net_salary($this->base_salary, $deductions, $allowances);
    }
}
```

## Direct Class Usage

If you prefer using the class directly:

```php
use App\Helpers\TanzaniaPAYE;

$result = TanzaniaPAYE::calculateMonthlyPAYE(1500000);
$annual = TanzaniaPAYE::calculateAnnualPAYE(18000000);
$breakdown = TanzaniaPAYE::getDetailedBreakdown(1500000);
```

## Testing Examples

```php
// Test 1: Low salary (no tax)
$result = calculate_paye(250000);
// Tax: TZS 0.00

// Test 2: Middle salary
$result = calculate_paye(700000);
// Tax: TZS 56,000.00
// Effective Rate: 8%

// Test 3: High salary
$result = calculate_paye(2000000);
// Tax: TZS 428,000.00
// Effective Rate: 21.4%
```

## Notes

- All amounts are in Tanzania Shillings (TZS)
- Tax rates are based on Tanzania Revenue Authority (TRA) guidelines
- The calculator uses progressive taxation (tax is calculated per bracket)
- Relief/deductions are subtracted before calculating taxable income
- Update tax brackets in `TanzaniaPAYE::TAX_BRACKETS` if rates change

## Files

- **Helper Class**: `app/Helpers/TanzaniaPAYE.php`
- **Helper Functions**: `app/Helpers/helpers.php`
- **Registration**: `composer.json` (autoload.files)

## Support

For tax rate updates or questions, refer to:
- Tanzania Revenue Authority (TRA): https://www.tra.go.tz/
- PAYE tax tables and guidelines

## License

This helper is part of the Dasher HR Management System.
