<?php

namespace App\Livewire\Setup\Asset;

use App\Models\asset;
use App\Models\assetclass;
use App\Models\building;
use App\Models\facilitylocation;
use App\Models\workstations;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Assetconf extends Component
{
    use WithPagination;

    public $activeTab = 'buildings';

    // Building fields
    public $building_name;
    public $building_workstation_id;
    public $editingBuildingId = null;

    // Facility Location fields
    public $location_name;
    public $location_building_id;
    public $location_workstation_id;
    public $editingLocationId = null;

    // Asset Class fields
    public $class_name;
    public $class_depreciation;
    public $class_depreciation_method = 'straight_line';
    public $class_useful_life_years;
    public $class_depreciation_rate;
    public $editingClassId = null;

    // Asset fields
    public $asset_name;
    public $asset_type;
    public $asset_class_id;
    public $editingAssetId = null;

    // Search
    public $searchBuilding = '';
    public $searchLocation = '';
    public $searchClass = '';
    public $searchAsset = '';

    // Modal states
    public $showBuildingModal = false;
    public $showLocationModal = false;
    public $showClassModal = false;
    public $showAssetModal = false;

    // Delete confirmation
    public $confirmingDelete = false;
    public $deleteType = '';
    public $deleteId = '';

    protected $paginationTheme = 'bootstrap';

    protected function rules()
    {
        return [
            // Building rules
            'building_name' => 'required|string|max:255',
            'building_workstation_id' => 'required|exists:workstations,id',

            // Location rules
            'location_name' => 'required|string|max:255',
            'location_building_id' => 'required|exists:buildings,id',
            'location_workstation_id' => 'required|exists:workstations,id',

            // Asset Class rules
            'class_name' => 'required|string|max:255',
            'class_depreciation' => 'required|string|max:255',

            // Asset rules
            'asset_name' => 'required|string|max:255|unique:assets,name,' . $this->editingAssetId,
            'asset_type' => 'required|string|in:current,non-current,intangible,physical,operating,non-operating',
            'asset_class_id' => 'required|exists:assetclasses,id',
        ];
    }

    public function updatedActiveTab()
    {
        $this->resetPage();
    }

    public function updatedSearchBuilding()
    {
        $this->resetPage();
    }

    public function updatedSearchLocation()
    {
        $this->resetPage();
    }

    public function updatedSearchClass()
    {
        $this->resetPage();
    }

    public function updatedSearchAsset()
    {
        $this->resetPage();
    }

    // Building Methods
    public function openBuildingModal($id = null)
    {
        $this->resetBuildingForm();
        if ($id) {
            $building = building::findOrFail($id);
            $this->editingBuildingId = $id;
            $this->building_name = $building->name;
            $this->building_workstation_id = $building->workstation_id;
        }
        $this->showBuildingModal = true;
    }

    public function resetBuildingForm()
    {
        $this->editingBuildingId = null;
        $this->building_name = '';
        $this->building_workstation_id = '';
        $this->resetValidation(['building_name', 'building_workstation_id']);
    }

    public function saveBuilding()
    {
        $this->validate([
            'building_name' => 'required|string|max:255',
            'building_workstation_id' => 'required|exists:workstations,id',
        ]);

        $data = [
            'name' => $this->building_name,
            'workstation_id' => $this->building_workstation_id,
            'added_by' => Auth::id(),
        ];

        if ($this->editingBuildingId) {
            building::findOrFail($this->editingBuildingId)->update($data);
            session()->flash('success', 'Building updated successfully.');
        } else {
            building::create($data);
            session()->flash('success', 'Building created successfully.');
        }

        $this->showBuildingModal = false;
        $this->resetBuildingForm();
    }

    // Facility Location Methods
    public function openLocationModal($id = null)
    {
        $this->resetLocationForm();
        if ($id) {
            $location = facilitylocation::findOrFail($id);
            $this->editingLocationId = $id;
            $this->location_name = $location->name;
            $this->location_building_id = $location->building_id;
            $this->location_workstation_id = $location->workstation_id;
        }
        $this->showLocationModal = true;
    }

    public function resetLocationForm()
    {
        $this->editingLocationId = null;
        $this->location_name = '';
        $this->location_building_id = '';
        $this->location_workstation_id = '';
        $this->resetValidation(['location_name', 'location_building_id', 'location_workstation_id']);
    }

    public function saveLocation()
    {
        $this->validate([
            'location_name' => 'required|string|max:255',
            'location_building_id' => 'required|exists:buildings,id',
            'location_workstation_id' => 'required|exists:workstations,id',
        ]);

        $data = [
            'name' => $this->location_name,
            'building_id' => $this->location_building_id,
            'workstation_id' => $this->location_workstation_id,
            'added_by' => Auth::id(),
        ];

        if ($this->editingLocationId) {
            facilitylocation::findOrFail($this->editingLocationId)->update($data);
            session()->flash('success', 'Facility location updated successfully.');
        } else {
            facilitylocation::create($data);
            session()->flash('success', 'Facility location created successfully.');
        }

        $this->showLocationModal = false;
        $this->resetLocationForm();
    }

    // Asset Class Methods
    public function openClassModal($id = null)
    {
        $this->resetClassForm();
        if ($id) {
            $class = assetclass::findOrFail($id);
            $this->editingClassId = $id;
            $this->class_name = $class->name;
            $this->class_depreciation = $class->depreciation;
            $this->class_depreciation_method = $class->depreciation_method;
            $this->class_useful_life_years = $class->useful_life_years;
            $this->class_depreciation_rate = $class->depreciation_rate;
        }
        $this->showClassModal = true;
    }

    public function resetClassForm()
    {
        $this->editingClassId = null;
        $this->class_name = '';
        $this->class_depreciation = '';
        $this->class_depreciation_method = 'straight_line';
        $this->class_useful_life_years = '';
        $this->class_depreciation_rate = '';
        $this->resetValidation(['class_name', 'class_depreciation', 'class_depreciation_method', 'class_useful_life_years', 'class_depreciation_rate']);
    }

    public function saveClass()
    {
        $this->validate([
            'class_name' => 'required|string|max:255',
            'class_depreciation' => 'nullable|string|max:255',
            'class_depreciation_method' => 'required|in:straight_line,reducing_balance',
            'class_useful_life_years' => 'nullable|integer|min:1',
            'class_depreciation_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        $data = [
            'name' => $this->class_name,
            'depreciation' => $this->class_depreciation,
            'depreciation_method' => $this->class_depreciation_method,
            'useful_life_years' => $this->class_useful_life_years ?: null,
            'depreciation_rate' => $this->class_depreciation_rate ?: null,
            'added_by' => Auth::id(),
        ];

        if ($this->editingClassId) {
            assetclass::findOrFail($this->editingClassId)->update($data);
            session()->flash('success', 'Asset class updated successfully.');
        } else {
            assetclass::create($data);
            session()->flash('success', 'Asset class created successfully.');
        }

        $this->showClassModal = false;
        $this->resetClassForm();
    }

    // Asset Methods
    public function openAssetModal($id = null)
    {
        $this->resetAssetForm();
        if ($id) {
            $asset = asset::findOrFail($id);
            $this->editingAssetId = $id;
            $this->asset_name = $asset->name;
            $this->asset_type = $asset->type;
            $this->asset_class_id = $asset->asset_class_id;
        }
        $this->showAssetModal = true;
    }

    public function resetAssetForm()
    {
        $this->editingAssetId = null;
        $this->asset_name = '';
        $this->asset_type = '';
        $this->asset_class_id = '';
        $this->resetValidation(['asset_name', 'asset_type', 'asset_class_id']);
    }

    public function saveAsset()
    {
        $this->validate([
            'asset_name' => 'required|string|max:255|unique:assets,name,' . $this->editingAssetId,
            'asset_type' => 'required|string|in:current,non-current,intangible,physical,operating,non-operating',
            'asset_class_id' => 'required|exists:assetclasses,id',
        ]);

        $data = [
            'name' => $this->asset_name,
            'type' => $this->asset_type,
            'asset_class_id' => $this->asset_class_id,
            'added_by' => Auth::id(),
        ];

        if ($this->editingAssetId) {
            asset::findOrFail($this->editingAssetId)->update($data);
            session()->flash('success', 'Asset updated successfully.');
        } else {
            asset::create($data);
            session()->flash('success', 'Asset created successfully.');
        }

        $this->showAssetModal = false;
        $this->resetAssetForm();
    }

    // Delete Methods
    public function confirmDelete($type, $id)
    {
        $this->deleteType = $type;
        $this->deleteId = $id;
        $this->confirmingDelete = true;
    }

    public function cancelDelete()
    {
        $this->confirmingDelete = false;
        $this->deleteType = '';
        $this->deleteId = '';
    }

    public function delete()
    {
        switch ($this->deleteType) {
            case 'building':
                building::findOrFail($this->deleteId)->delete();
                session()->flash('success', 'Building deleted successfully.');
                break;
            case 'location':
                facilitylocation::findOrFail($this->deleteId)->delete();
                session()->flash('success', 'Facility location deleted successfully.');
                break;
            case 'class':
                assetclass::findOrFail($this->deleteId)->delete();
                session()->flash('success', 'Asset class deleted successfully.');
                break;
            case 'asset':
                asset::findOrFail($this->deleteId)->delete();
                session()->flash('success', 'Asset deleted successfully.');
                break;
        }

        $this->cancelDelete();
    }

    // Close modals
    public function closeModal()
    {
        $this->showBuildingModal = false;
        $this->showLocationModal = false;
        $this->showClassModal = false;
        $this->showAssetModal = false;
        $this->resetBuildingForm();
        $this->resetLocationForm();
        $this->resetClassForm();
        $this->resetAssetForm();
    }

    public function render()
    {
        $buildings = building::with('workstation')
            ->when($this->searchBuilding, fn($q) => $q->where('name', 'like', '%' . $this->searchBuilding . '%'))
            ->latest()
            ->paginate(10, ['*'], 'buildingsPage');

        $locations = facilitylocation::with(['building', 'workstation'])
            ->when($this->searchLocation, fn($q) => $q->where('name', 'like', '%' . $this->searchLocation . '%'))
            ->latest()
            ->paginate(10, ['*'], 'locationsPage');

        $assetClasses = assetclass::withCount('assets')
            ->when($this->searchClass, fn($q) => $q->where('name', 'like', '%' . $this->searchClass . '%'))
            ->latest()
            ->paginate(10, ['*'], 'classesPage');

        $assets = asset::with('asset_class')
            ->when($this->searchAsset, fn($q) => $q->where('name', 'like', '%' . $this->searchAsset . '%'))
            ->latest()
            ->paginate(10, ['*'], 'assetsPage');

        return view('livewire.setup.asset.assetconf', [
            'buildings' => $buildings,
            'locations' => $locations,
            'assetClasses' => $assetClasses,
            'assets' => $assets,
            'workstations' => workstations::orderBy('workstation_name')->get(),
            'buildingsForSelect' => building::orderBy('name')->get(),
            'assetClassesForSelect' => assetclass::orderBy('name')->get(),
            'assetTypes' => [
                'current' => 'Current Asset',
                'non-current' => 'Non-Current Asset',
                'intangible' => 'Intangible Asset',
                'physical' => 'Physical Asset',
                'operating' => 'Operating Asset',
                'non-operating' => 'Non-Operating Asset',
            ],
            'depreciationMethods' => [
                'straight_line' => 'Straight Line',
                'reducing_balance' => 'Reducing Balance',
            ],
        ]);
    }
}
