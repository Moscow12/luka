<?php

namespace App\Livewire\Assets;

use App\Models\assetclass;
use App\Models\assetregistry;
use App\Models\building;
use App\Models\departments;
use App\Models\workstations;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Response;
use Livewire\Component;
use Livewire\WithPagination;

class Assetreports extends Component
{
    use WithPagination;

    // Filters
    public $filterDepartment = '';
    public $filterAssetClass = '';
    public $filterWorkstation = '';
    public $filterBuilding = '';
    public $filterStatus = '';
    public $filterCondition = '';
    public $filterDateFrom = '';
    public $filterDateTo = '';
    public $search = '';

    // Report type
    public $reportType = 'registry'; // registry, depreciation, summary, by_department, by_class

    // Sorting
    public $sortField = 'created_at';
    public $sortDirection = 'desc';

    protected $paginationTheme = 'bootstrap';

    protected $queryString = [
        'filterDepartment' => ['except' => ''],
        'filterAssetClass' => ['except' => ''],
        'filterWorkstation' => ['except' => ''],
        'filterStatus' => ['except' => ''],
        'search' => ['except' => ''],
        'reportType' => ['except' => 'registry'],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterDepartment()
    {
        $this->resetPage();
    }

    public function updatingFilterAssetClass()
    {
        $this->resetPage();
    }

    public function updatingReportType()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function clearFilters()
    {
        $this->reset([
            'filterDepartment',
            'filterAssetClass',
            'filterWorkstation',
            'filterBuilding',
            'filterStatus',
            'filterCondition',
            'filterDateFrom',
            'filterDateTo',
            'search',
        ]);
        $this->resetPage();
    }

    protected function getBaseQuery()
    {
        return assetregistry::with(['asset', 'assetClass', 'building', 'facilityLocation', 'department', 'workstation', 'addedBy'])
            ->when($this->search, fn($q) => $q->where(function($query) {
                $query->where('serial_number', 'like', '%' . $this->search . '%')
                    ->orWhere('codeno', 'like', '%' . $this->search . '%')
                    ->orWhere('model', 'like', '%' . $this->search . '%')
                    ->orWhereHas('asset', fn($q) => $q->where('name', 'like', '%' . $this->search . '%'));
            }))
            ->when($this->filterDepartment, fn($q) => $q->where('department_id', $this->filterDepartment))
            ->when($this->filterAssetClass, fn($q) => $q->where('asset_class_id', $this->filterAssetClass))
            ->when($this->filterWorkstation, fn($q) => $q->where('workstation_id', $this->filterWorkstation))
            ->when($this->filterBuilding, fn($q) => $q->where('building_id', $this->filterBuilding))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterCondition, fn($q) => $q->where('condition', $this->filterCondition))
            ->when($this->filterDateFrom, fn($q) => $q->whereDate('purchase_date', '>=', $this->filterDateFrom))
            ->when($this->filterDateTo, fn($q) => $q->whereDate('purchase_date', '<=', $this->filterDateTo))
            ->orderBy($this->sortField, $this->sortDirection);
    }

    protected function getStatistics()
    {
        $baseQuery = $this->getBaseQuery();
        $assets = (clone $baseQuery)->get();

        // Calculate total depreciation dynamically
        $totalDepreciation = 0;
        foreach ($assets as $asset) {
            $totalDepreciation += $this->calculateDepreciation($asset);
        }

        $totalValue = $assets->sum('purchase_cost') ?? 0;

        return [
            'total_assets' => $assets->count(),
            'total_value' => $totalValue,
            'total_depreciation' => $totalDepreciation,
            'net_book_value' => $totalValue - $totalDepreciation,
            'active_assets' => $assets->where('status', 'active')->count(),
            'disposed_assets' => $assets->where('status', 'disposed')->count(),
            'under_maintenance' => $assets->where('status', 'under_maintenance')->count(),
            'by_condition' => [
                'new' => $assets->where('condition', 'new')->count(),
                'good' => $assets->where('condition', 'good')->count(),
                'fair' => $assets->where('condition', 'fair')->count(),
                'poor' => $assets->where('condition', 'poor')->count(),
            ],
        ];
    }

    protected function getDepartmentSummary()
    {
        return departments::withCount(['assetregistries' => function($q) {
                $this->applyFilters($q);
            }])
            ->withSum(['assetregistries' => function($q) {
                $this->applyFilters($q);
            }], 'purchase_cost')
            ->withSum(['assetregistries' => function($q) {
                $this->applyFilters($q);
            }], 'depreciation')
            ->having('assetregistries_count', '>', 0)
            ->orderBy('assetregistries_count', 'desc')
            ->get();
    }

    protected function getClassSummary()
    {
        return assetclass::withCount(['assets as registry_count' => function($q) {
                $q->whereHas('assetregistries', function($sub) {
                    $this->applyFilters($sub);
                });
            }])
            ->get()
            ->filter(fn($class) => $class->registry_count > 0);
    }

    protected function applyFilters($query)
    {
        return $query
            ->when($this->filterDepartment, fn($q) => $q->where('department_id', $this->filterDepartment))
            ->when($this->filterAssetClass, fn($q) => $q->where('asset_class_id', $this->filterAssetClass))
            ->when($this->filterWorkstation, fn($q) => $q->where('workstation_id', $this->filterWorkstation))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterDateFrom, fn($q) => $q->whereDate('purchase_date', '>=', $this->filterDateFrom))
            ->when($this->filterDateTo, fn($q) => $q->whereDate('purchase_date', '<=', $this->filterDateTo));
    }

    /**
     * Calculate depreciation for an asset
     * Uses asset-level settings if available, otherwise falls back to asset class settings
     */
    public function calculateDepreciation($asset)
    {
        // Get depreciation settings - asset level overrides class level
        $method = $asset->depreciation_method ?? $asset->assetClass?->depreciation_method ?? 'straight_line';
        $usefulLife = $asset->useful_life_years ?? $asset->assetClass?->useful_life_years;
        $depreciationRate = $asset->assetClass?->depreciation_rate;

        $purchaseCost = $asset->purchase_cost ?? 0;
        $purchaseDate = $asset->purchase_date;

        if (!$purchaseDate || !$purchaseCost || $purchaseCost <= 0) {
            return 0;
        }

        // Calculate years since purchase
        $yearsUsed = Carbon::parse($purchaseDate)->diffInYears(now());
        if ($yearsUsed < 1) {
            // Pro-rate for first year
            $yearsUsed = Carbon::parse($purchaseDate)->diffInDays(now()) / 365;
        }

        $accumulatedDepreciation = 0;

        if ($method === 'straight_line') {
            // Straight Line: (Cost - Salvage) / Useful Life per year
            if ($usefulLife && $usefulLife > 0) {
                $annualDepreciation = $purchaseCost / $usefulLife;
                $accumulatedDepreciation = min($annualDepreciation * $yearsUsed, $purchaseCost);
            }
        } elseif ($method === 'reducing_balance') {
            // Reducing Balance: Apply rate to remaining value each year
            $rate = $depreciationRate ?? ($usefulLife > 0 ? (2 / $usefulLife) * 100 : 20); // Default to double declining
            $rate = $rate / 100; // Convert percentage to decimal

            $remainingValue = $purchaseCost;
            for ($year = 1; $year <= floor($yearsUsed); $year++) {
                $yearDepreciation = $remainingValue * $rate;
                $accumulatedDepreciation += $yearDepreciation;
                $remainingValue -= $yearDepreciation;
            }

            // Pro-rate partial year
            $partialYear = $yearsUsed - floor($yearsUsed);
            if ($partialYear > 0) {
                $accumulatedDepreciation += $remainingValue * $rate * $partialYear;
            }

            // Cap at purchase cost
            $accumulatedDepreciation = min($accumulatedDepreciation, $purchaseCost);
        }

        return round($accumulatedDepreciation, 2);
    }

    /**
     * Get effective depreciation method for display (asset override or class default)
     */
    public function getEffectiveDepreciationMethod($asset)
    {
        return $asset->depreciation_method ?? $asset->assetClass?->depreciation_method ?? 'straight_line';
    }

    /**
     * Get effective useful life for display (asset override or class default)
     */
    public function getEffectiveUsefulLife($asset)
    {
        return $asset->useful_life_years ?? $asset->assetClass?->useful_life_years ?? '-';
    }

    public function exportPdf()
    {
        $assets = $this->getBaseQuery()->get();

        // Calculate depreciation for each asset
        $assetsWithDepreciation = $assets->map(function($asset) {
            $asset->calculated_depreciation = $this->calculateDepreciation($asset);
            $asset->effective_method = $this->getEffectiveDepreciationMethod($asset);
            $asset->effective_life = $this->getEffectiveUsefulLife($asset);
            $asset->has_override = $asset->depreciation_method !== null || $asset->useful_life_years !== null;
            return $asset;
        });

        $statistics = $this->getStatistics();
        $filters = $this->getActiveFilters();

        $pdf = Pdf::loadView('livewire.assets.reports.pdf-export', [
            'assets' => $assetsWithDepreciation,
            'statistics' => $statistics,
            'filters' => $filters,
            'reportType' => $this->reportType,
            'generatedAt' => now(),
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn() => print($pdf->output()),
            'fixed-asset-report-' . now()->format('Y-m-d-His') . '.pdf'
        );
    }

    public function exportExcel()
    {
        $assets = $this->getBaseQuery()->get();

        $csvData = [];
        $csvData[] = [
            'No',
            'Asset Code',
            'Asset Name',
            'Asset Class',
            'Serial Number',
            'Department',
            'Location',
            'Building',
            'Status',
            'Condition',
            'Purchase Date',
            'Purchase Cost',
            'Depreciation Method',
            'Method Source',
            'Useful Life (Years)',
            'Life Source',
            'Accumulated Depreciation',
            'Net Book Value',
            'Vendor',
            'Model',
            'Make',
        ];

        foreach ($assets as $index => $asset) {
            $effectiveMethod = $this->getEffectiveDepreciationMethod($asset);
            $effectiveLife = $this->getEffectiveUsefulLife($asset);
            $calculatedDepreciation = $this->calculateDepreciation($asset);
            $hasMethodOverride = $asset->depreciation_method !== null;
            $hasLifeOverride = $asset->useful_life_years !== null;

            $csvData[] = [
                $index + 1,
                $asset->codeno ?? '-',
                $asset->asset?->name ?? '-',
                $asset->assetClass?->name ?? '-',
                $asset->serial_number ?? '-',
                $asset->department?->name ?? '-',
                $asset->facilityLocation?->name ?? '-',
                $asset->building?->name ?? '-',
                ucfirst($asset->status ?? '-'),
                ucfirst($asset->condition ?? '-'),
                $asset->purchase_date?->format('Y-m-d') ?? '-',
                number_format($asset->purchase_cost ?? 0, 2),
                ucwords(str_replace('_', ' ', $effectiveMethod)),
                $hasMethodOverride ? 'Asset Override' : 'Asset Class',
                $effectiveLife,
                $hasLifeOverride ? 'Asset Override' : 'Asset Class',
                number_format($calculatedDepreciation, 2),
                number_format(($asset->purchase_cost ?? 0) - $calculatedDepreciation, 2),
                $asset->vendor ?? ($asset->vendorRelation?->name ?? '-'),
                $asset->model ?? '-',
                $asset->make ?? '-',
            ];
        }

        $filename = 'fixed-asset-report-' . now()->format('Y-m-d-His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($csvData) {
            $file = fopen('php://output', 'w');
            foreach ($csvData as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    protected function getActiveFilters()
    {
        $filters = [];

        if ($this->filterDepartment) {
            $dept = departments::find($this->filterDepartment);
            $filters['Department'] = $dept?->name ?? 'Unknown';
        }
        if ($this->filterAssetClass) {
            $class = assetclass::find($this->filterAssetClass);
            $filters['Asset Class'] = $class?->name ?? 'Unknown';
        }
        if ($this->filterWorkstation) {
            $ws = workstations::find($this->filterWorkstation);
            $filters['Workstation'] = $ws?->workstation_name ?? 'Unknown';
        }
        if ($this->filterStatus) {
            $filters['Status'] = ucfirst($this->filterStatus);
        }
        if ($this->filterCondition) {
            $filters['Condition'] = ucfirst($this->filterCondition);
        }
        if ($this->filterDateFrom) {
            $filters['From Date'] = $this->filterDateFrom;
        }
        if ($this->filterDateTo) {
            $filters['To Date'] = $this->filterDateTo;
        }

        return $filters;
    }

    public function render()
    {
        $assets = $this->getBaseQuery()->paginate(20);
        $statistics = $this->getStatistics();

        return view('livewire.assets.assetreports', [
            'assets' => $assets,
            'statistics' => $statistics,
            'departments' => departments::orderBy('name')->get(),
            'assetClasses' => assetclass::orderBy('name')->get(),
            'workstations' => workstations::orderBy('workstation_name')->get(),
            'buildings' => building::orderBy('name')->get(),
            'statuses' => [
                'active' => 'Active',
                'inactive' => 'Inactive',
                'disposed' => 'Disposed',
                'under_maintenance' => 'Under Maintenance',
            ],
            'conditions' => [
                'new' => 'New',
                'good' => 'Good',
                'fair' => 'Fair',
                'bad' => 'Bad',
                'poor' => 'Poor',
                'worse' => 'Worse',
            ],
            'activeFilters' => $this->getActiveFilters(),
        ]);
    }
}
