<?php

namespace App\Livewire\Assets;

use App\Models\asset;
use App\Models\assetclass;
use App\Models\assetregistry;
use App\Models\building;
use App\Models\departments;
use App\Models\Employee;
use App\Models\facilitylocation;
use App\Models\vendors;
use App\Models\workstations;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class DepartmentAssets extends Component
{
    use WithPagination;

    // Form fields
    public $asset_class_id;

    public $facility_location_id;

    public $asset_id;

    public $workstation_id;

    public $building_id;

    public $department_id;

    public $description;

    public $status = 'active';

    public $serial_number;

    public $purchase_date;

    public $purchase_cost;

    public $warranty_expiry_date;

    public $vendor;

    public $vendor_id;

    public $condition;

    public $model;

    public $make;

    public $codeno;

    public $depreciation_method;

    public $useful_life_years;

    // UI State
    public $editingId = null;

    public $showModal = false;

    public $confirmingDelete = false;

    public $deleteId = '';

    public $search = '';

    // Filters
    public $filterDepartment = '';

    public $filterStatus = '';

    public $filterAssetClass = '';

    // Searchable asset picker (inside the modal)
    public $assetSearch = '';

    public $showAssetDropdown = false;

    // Inline "create new asset" state
    public $newAssetMode = false;

    public $newAssetName = '';

    public $newAssetType = '';

    public $newAssetDuplicates = [];

    protected $paginationTheme = 'bootstrap';

    protected function rules()
    {
        return [
            'asset_class_id' => 'required|exists:assetclasses,id',
            'asset_id' => 'required|exists:assets,id',
            'workstation_id' => 'required|exists:workstations,id',
            'building_id' => 'required|exists:buildings,id',
            'facility_location_id' => 'required|exists:facilitylocations,id',
            'department_id' => 'required|exists:departments,id',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive,disposed,under_maintenance',
            'serial_number' => 'nullable|string|unique:assetregistries,serial_number,'.$this->editingId,
            'purchase_date' => 'nullable|date',
            'purchase_cost' => 'nullable|numeric|min:0',
            'warranty_expiry_date' => 'nullable|date',
            'vendor' => 'nullable|string|max:255',
            'vendor_id' => 'nullable|exists:vendors,id',
            'condition' => 'nullable|in:new,good,fair,bad,poor,worse',
            'model' => 'nullable|string|max:255',
            'make' => 'nullable|string|max:255',
            'codeno' => 'nullable|string|max:255',
            'depreciation_method' => 'nullable|in:straight_line,reducing_balance',
            'useful_life_years' => 'nullable|integer|min:1',
        ];
    }

    public function updatedWorkstationId($value)
    {
        $this->building_id = '';
        $this->facility_location_id = '';
    }

    public function updatedBuildingId($value)
    {
        $this->facility_location_id = '';
    }

    public function updatedAssetClassId($value)
    {
        $this->asset_id = '';
        $this->assetSearch = '';
        $this->showAssetDropdown = false;
        $this->cancelNewAsset();
    }

    public function getFilteredAssetsProperty()
    {
        if (! $this->asset_class_id) {
            return collect();
        }

        $term = trim($this->assetSearch);

        return asset::where('asset_class_id', $this->asset_class_id)
            ->when($term !== '', fn ($q) => $q->where(function ($q) use ($term) {
                $q->where('name', 'like', '%'.$term.'%')
                    ->orWhere('type', 'like', '%'.$term.'%');
            }))
            ->orderBy('name')
            ->limit(50)
            ->get();
    }

    public function getSelectedAssetProperty()
    {
        if (! $this->asset_id) {
            return null;
        }

        return asset::find($this->asset_id);
    }

    public function selectAsset($id)
    {
        $this->asset_id = $id;
        $this->showAssetDropdown = false;
        $this->assetSearch = '';
        $this->cancelNewAsset();
    }

    public function clearAsset()
    {
        $this->asset_id = '';
        $this->assetSearch = '';
        $this->showAssetDropdown = true;
    }

    public function startNewAsset()
    {
        $this->newAssetMode = true;
        $this->newAssetName = trim($this->assetSearch);
        $this->newAssetType = '';
        $this->newAssetDuplicates = [];
        $this->checkAssetDuplicates();
    }

    public function cancelNewAsset()
    {
        $this->newAssetMode = false;
        $this->newAssetName = '';
        $this->newAssetType = '';
        $this->newAssetDuplicates = [];
    }

    public function updatedNewAssetName()
    {
        $this->checkAssetDuplicates();
    }

    protected function checkAssetDuplicates()
    {
        $name = trim($this->newAssetName);

        if ($name === '' || ! $this->asset_class_id) {
            $this->newAssetDuplicates = [];

            return;
        }

        $this->newAssetDuplicates = asset::where('asset_class_id', $this->asset_class_id)
            ->where('name', 'like', '%'.$name.'%')
            ->orderBy('name')
            ->limit(5)
            ->get()
            ->map(fn ($a) => ['id' => $a->id, 'name' => $a->name, 'type' => $a->type])
            ->toArray();
    }

    public function createAsset()
    {
        $this->validate([
            'asset_class_id' => 'required|exists:assetclasses,id',
            'newAssetName' => 'required|string|max:255',
            'newAssetType' => 'required|in:current,non-current,intangible,physical,operating,non-operating',
        ], [], [
            'newAssetName' => 'asset name',
            'newAssetType' => 'asset type',
        ]);

        $name = trim($this->newAssetName);

        $existing = asset::where('asset_class_id', $this->asset_class_id)
            ->whereRaw('LOWER(name) = ?', [strtolower($name)])
            ->first();

        if ($existing) {
            $this->addError('newAssetName', 'An asset with this name already exists in the selected class.');
            $this->asset_id = $existing->id;

            return;
        }

        $asset = asset::create([
            'name' => $name,
            'type' => $this->newAssetType,
            'asset_class_id' => $this->asset_class_id,
            'added_by' => Auth::id(),
        ]);

        $this->asset_id = $asset->id;
        $this->cancelNewAsset();
        $this->assetSearch = '';
        $this->showAssetDropdown = false;
    }

    public function openModal($id = null)
    {
        $this->resetForm();
        if ($id) {
            $registry = assetregistry::findOrFail($id);
            $this->editingId = $id;
            $this->asset_class_id = $registry->asset_class_id;
            $this->facility_location_id = $registry->facility_location_id;
            $this->asset_id = $registry->asset_id;
            $this->workstation_id = $registry->workstation_id;
            $this->building_id = $registry->building_id;
            $this->department_id = $registry->department_id;
            $this->description = $registry->description;
            $this->status = $registry->status;
            $this->serial_number = $registry->serial_number;
            $this->purchase_date = $registry->purchase_date?->format('Y-m-d');
            $this->purchase_cost = $registry->purchase_cost;
            $this->warranty_expiry_date = $registry->warranty_expiry_date?->format('Y-m-d');
            $this->vendor = $registry->vendor;
            $this->vendor_id = $registry->vendor_id;
            $this->condition = $registry->condition;
            $this->model = $registry->model;
            $this->make = $registry->make;
            $this->codeno = $registry->codeno;
            $this->depreciation_method = $registry->depreciation_method;
            $this->useful_life_years = $registry->useful_life_years;
        } else {
            // Set default department if user has one
            $employee = Employee::where('user_id', Auth::id())->first();
            if ($employee && $employee->department_id) {
                $this->department_id = $employee->department_id;
                $this->workstation_id = $employee->workstation_id;
            }
        }
        $this->showModal = true;
    }

    public function resetForm()
    {
        $this->editingId = null;
        $this->asset_class_id = '';
        $this->facility_location_id = '';
        $this->asset_id = '';
        $this->assetSearch = '';
        $this->showAssetDropdown = false;
        $this->cancelNewAsset();
        $this->workstation_id = '';
        $this->building_id = '';
        $this->department_id = '';
        $this->description = '';
        $this->status = 'active';
        $this->serial_number = '';
        $this->purchase_date = '';
        $this->purchase_cost = '';
        $this->warranty_expiry_date = '';
        $this->vendor = '';
        $this->vendor_id = '';
        $this->condition = '';
        $this->model = '';
        $this->make = '';
        $this->codeno = '';
        $this->depreciation_method = '';
        $this->useful_life_years = '';
        $this->resetValidation();
    }

    public function save()
    {
        $this->validate();

        $data = [
            'asset_class_id' => $this->asset_class_id,
            'facility_location_id' => $this->facility_location_id,
            'asset_id' => $this->asset_id,
            'workstation_id' => $this->workstation_id,
            'building_id' => $this->building_id,
            'department_id' => $this->department_id,
            'description' => $this->description,
            'status' => $this->status,
            'serial_number' => $this->serial_number ?: null,
            'purchase_date' => $this->purchase_date ?: null,
            'purchase_cost' => $this->purchase_cost ?: null,
            'warranty_expiry_date' => $this->warranty_expiry_date ?: null,
            'vendor' => $this->vendor ?: null,
            'vendor_id' => $this->vendor_id ?: null,
            'condition' => $this->condition ?: null,
            'model' => $this->model ?: null,
            'make' => $this->make ?: null,
            'codeno' => $this->codeno ?: null,
            'depreciation_method' => $this->depreciation_method ?: null,
            'useful_life_years' => $this->useful_life_years ?: null,
            'added_by' => Auth::id(),
        ];

        if ($this->editingId) {
            assetregistry::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Asset updated successfully.');
        } else {
            assetregistry::create($data);
            session()->flash('success', 'Asset registered successfully.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function confirmDelete($id)
    {
        $this->deleteId = $id;
        $this->confirmingDelete = true;
    }

    public function cancelDelete()
    {
        $this->confirmingDelete = false;
        $this->deleteId = '';
    }

    public function delete()
    {
        $user = Auth::user();

        if (! $user->can('manage-assets') && ! $user->isSuperAdmin()) {
            session()->flash('error', 'You do not have permission to delete assets.');
            $this->cancelDelete();

            return;
        }

        assetregistry::findOrFail($this->deleteId)->delete();
        session()->flash('success', 'Asset deleted successfully.');
        $this->cancelDelete();
    }

    public function render()
    {
        $query = assetregistry::with(['asset', 'assetClass', 'building', 'facilityLocation', 'department', 'workstation'])
            ->when($this->search, fn ($q) => $q->where(function ($query) {
                $query->where('serial_number', 'like', '%'.$this->search.'%')
                    ->orWhere('codeno', 'like', '%'.$this->search.'%')
                    ->orWhere('model', 'like', '%'.$this->search.'%')
                    ->orWhereHas('asset', fn ($q) => $q->where('name', 'like', '%'.$this->search.'%'));
            }))
            ->when($this->filterDepartment, fn ($q) => $q->where('department_id', $this->filterDepartment))
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterAssetClass, fn ($q) => $q->where('asset_class_id', $this->filterAssetClass))
            ->latest();

        // Get buildings filtered by workstation
        $buildings = $this->workstation_id
            ? building::where('workstation_id', $this->workstation_id)->orderBy('name')->get()
            : collect();

        // Get facility locations filtered by building
        $facilityLocations = $this->building_id
            ? facilitylocation::where('building_id', $this->building_id)->orderBy('name')->get()
            : collect();

        // Get assets filtered by asset class
        $assets = $this->asset_class_id
            ? asset::where('asset_class_id', $this->asset_class_id)->orderBy('name')->get()
            : collect();

        return view('livewire.assets.department-assets', [
            'registries' => $query->paginate(10),
            'workstations' => workstations::orderBy('workstation_name')->get(),
            'buildings' => $buildings,
            'facilityLocations' => $facilityLocations,
            'departments' => departments::orderBy('name')->get(),
            'assetClasses' => assetclass::orderBy('name')->get(),
            'assets' => $assets,
            'vendors' => vendors::orderBy('name')->get(),
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
            'depreciationMethods' => [
                'straight_line' => 'Straight Line',
                'reducing_balance' => 'Reducing Balance',
            ],
            'assetTypes' => [
                'current' => 'Current',
                'non-current' => 'Non-current',
                'intangible' => 'Intangible',
                'physical' => 'Physical',
                'operating' => 'Operating',
                'non-operating' => 'Non-operating',
            ],
        ]);
    }
}
