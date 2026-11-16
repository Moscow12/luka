<?php

namespace App\Livewire\Setup\Asset;

use App\Livewire\Concerns\ManagesRecordStatus;
use App\Models\asset;
use App\Models\assetclass;
use App\Models\building;
use App\Models\departments;
use App\Models\Engineering\Section;
use App\Models\facilitylocation;
use App\Models\workstations;
use App\Support\RecordUsage;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class Assetconf extends Component
{
    use ManagesRecordStatus, WithPagination;

    public $activeTab = 'buildings';

    // Building fields
    public $building_name;

    public $building_workstation_id;

    public $editingBuildingId = null;

    // Facility Location fields
    public $location_name;

    public $location_building_id;

    public $location_workstation_id;

    public $location_department_id;

    public $location_code;

    public $location_floor;

    public $editingLocationId = null;

    /** '' = all, 'none' = rooms without a department, else a department id */
    public $filterLocationDepartment = '';

    // Asset Class fields
    public $class_name;

    public $class_depreciation;

    public $class_depreciation_method = 'straight_line';

    public $class_useful_life_years;

    public $class_depreciation_rate;

    public $class_eng_section_id;

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
            'location_department_id' => 'required|exists:departments,id',

            // Asset Class rules
            'class_name' => 'required|string|max:255',
            'class_depreciation' => 'required|string|max:255',

            // Asset rules
            'asset_name' => 'required|string|max:255|unique:assets,name,'.$this->editingAssetId,
            'asset_type' => 'required|string|in:current,non-current,intangible,physical,operating,non-operating',
            'asset_class_id' => 'required|exists:assetclasses,id',
        ];
    }

    /**
     * Translate a database integrity error into a message safe to show the user.
     */
    protected function friendlyDbErrorMessage(QueryException $e): string
    {
        if (str_contains($e->getMessage(), 'Duplicate entry')) {
            return 'This record already exists. Please use a different name.';
        }

        return 'A database error occurred while saving. Please try again or contact support if the problem persists.';
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
        $this->resetPage('locationsPage');
    }

    public function updatedFilterLocationDepartment()
    {
        $this->resetPage('locationsPage');
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
        ];

        try {
            if ($this->editingBuildingId) {
                $building = building::findOrFail($this->editingBuildingId);
                $building->update($data);
                // Rooms in the building follow it to its workstation
                facilitylocation::where('building_id', $building->id)->update(['workstation_id' => $building->workstation_id]);
                session()->flash('success', 'Building updated successfully.');
            } else {
                building::create($data + ['added_by' => Auth::id()]);
                session()->flash('success', 'Building created successfully.');
            }
        } catch (QueryException $e) {
            session()->flash('error', $this->friendlyDbErrorMessage($e));

            return;
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
            $this->location_department_id = $location->department_id;
            $this->location_code = $location->code;
            $this->location_floor = $location->floor;
        }
        $this->showLocationModal = true;
    }

    public function resetLocationForm()
    {
        $this->editingLocationId = null;
        $this->location_name = '';
        $this->location_building_id = '';
        $this->location_workstation_id = '';
        $this->location_department_id = '';
        $this->location_code = '';
        $this->location_floor = '';
        $this->resetValidation(['location_name', 'location_building_id', 'location_workstation_id', 'location_department_id', 'location_code', 'location_floor']);
    }

    public function saveLocation()
    {
        $this->location_code = $this->location_code ? strtoupper(trim($this->location_code)) : null;

        $this->validate([
            'location_name' => 'required|string|max:255',
            'location_building_id' => ['required', Rule::exists('buildings', 'id')->whereNull('deleted_at')],
            'location_department_id' => ['required', Rule::exists('departments', 'id')],
            'location_code' => ['nullable', 'string', 'max:30', Rule::unique('facilitylocations', 'code')->where('department_id', $this->location_department_id)->ignore($this->editingLocationId)],
            'location_floor' => 'nullable|string|max:60',
        ], [], [
            'location_building_id' => 'building',
            'location_department_id' => 'department',
            'location_code' => 'room code',
        ]);

        // The workstation always follows the building
        $building = building::findOrFail($this->location_building_id);

        $data = [
            'name' => $this->location_name,
            'building_id' => $building->id,
            'workstation_id' => $building->workstation_id,
            'department_id' => $this->location_department_id,
            'code' => $this->location_code ?: null,
            'floor' => $this->location_floor ?: null,
        ];

        try {
            if ($this->editingLocationId) {
                facilitylocation::findOrFail($this->editingLocationId)->update($data);
                session()->flash('success', 'Facility location updated successfully.');
            } else {
                facilitylocation::create($data + ['added_by' => Auth::id()]);
                session()->flash('success', 'Facility location created successfully.');
            }
        } catch (QueryException $e) {
            session()->flash('error', $this->friendlyDbErrorMessage($e));

            return;
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
            $this->class_eng_section_id = $class->eng_section_id;
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
        $this->class_eng_section_id = '';
        $this->resetValidation(['class_name', 'class_depreciation', 'class_depreciation_method', 'class_useful_life_years', 'class_depreciation_rate', 'class_eng_section_id']);
    }

    public function saveClass()
    {
        $this->validate([
            'class_name' => 'required|string|max:255',
            'class_depreciation' => 'nullable|string|max:255',
            'class_depreciation_method' => 'required|in:straight_line,reducing_balance',
            'class_useful_life_years' => 'nullable|integer|min:1',
            'class_depreciation_rate' => 'nullable|numeric|min:0|max:100',
            'class_eng_section_id' => 'nullable|exists:eng_sections,id',
        ]);

        $data = [
            'name' => $this->class_name,
            'depreciation' => $this->class_depreciation,
            'depreciation_method' => $this->class_depreciation_method,
            'useful_life_years' => $this->class_useful_life_years ?: null,
            'depreciation_rate' => $this->class_depreciation_rate ?: null,
            'eng_section_id' => $this->class_eng_section_id ?: null,
        ];

        try {
            if ($this->editingClassId) {
                assetclass::findOrFail($this->editingClassId)->update($data);
                session()->flash('success', 'Asset class updated successfully.');
            } else {
                assetclass::create($data + ['added_by' => Auth::id()]);
                session()->flash('success', 'Asset class created successfully.');
            }
        } catch (QueryException $e) {
            session()->flash('error', $this->friendlyDbErrorMessage($e));

            return;
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
            'asset_name' => 'required|string|max:255|unique:assets,name,'.$this->editingAssetId,
            'asset_type' => 'required|string|in:current,non-current,intangible,physical,operating,non-operating',
            'asset_class_id' => 'required|exists:assetclasses,id',
        ]);

        $data = [
            'name' => $this->asset_name,
            'type' => $this->asset_type,
            'asset_class_id' => $this->asset_class_id,
        ];

        try {
            if ($this->editingAssetId) {
                asset::findOrFail($this->editingAssetId)->update($data);
                session()->flash('success', 'Asset updated successfully.');
            } else {
                asset::create($data + ['added_by' => Auth::id()]);
                session()->flash('success', 'Asset created successfully.');
            }
        } catch (QueryException $e) {
            session()->flash('error', $this->friendlyDbErrorMessage($e));

            return;
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
        $user = Auth::user();

        if (! $user->can('delete-asset') && ! $user->isSuperAdmin()) {
            session()->flash('error', 'You do not have permission to delete asset configuration.');
            $this->cancelDelete();

            return;
        }

        $record = $this->findConfigRecord($this->deleteType, $this->deleteId);

        if ($record) {
            $this->deleteUnlessInUse($record, $this->configLabel($this->deleteType, $record));
        }

        $this->cancelDelete();
    }

    /** Records in use are deactivated instead of deleted; inactive ones are hidden from pickers. */
    public function toggleStatus($type, $id)
    {
        $record = $this->findConfigRecord($type, $id) ?? abort(404);

        $this->toggleRecordStatus($record, $this->configLabel($type, $record));
    }

    protected function findConfigRecord(string $type, string $id)
    {
        return match ($type) {
            'building' => building::findOrFail($id),
            'location' => facilitylocation::findOrFail($id),
            'class' => assetclass::findOrFail($id),
            'asset' => asset::findOrFail($id),
            default => null,
        };
    }

    protected function configLabel(string $type, $record): string
    {
        return match ($type) {
            'building' => "Building {$record->name}",
            'location' => "Location {$record->name}",
            'class' => "Asset class {$record->name}",
            default => "Asset {$record->name}",
        };
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
            ->when($this->searchBuilding, fn ($q) => $q->where('name', 'like', '%'.$this->searchBuilding.'%'))
            ->latest()
            ->paginate(10, ['*'], 'buildingsPage');

        $locations = facilitylocation::with(['building', 'workstation', 'department:id,name'])
            ->when($this->searchLocation, fn ($q) => $q->where(fn ($w) => $w
                ->where('name', 'like', '%'.$this->searchLocation.'%')
                ->orWhere('code', 'like', '%'.$this->searchLocation.'%')))
            ->when($this->filterLocationDepartment === 'none', fn ($q) => $q->whereNull('department_id'))
            ->when($this->filterLocationDepartment && $this->filterLocationDepartment !== 'none', fn ($q) => $q->where('department_id', $this->filterLocationDepartment))
            ->latest()
            ->paginate(10, ['*'], 'locationsPage');

        $assetClasses = assetclass::withCount('assets')->with('engSection:id,name')
            ->when($this->searchClass, fn ($q) => $q->where('name', 'like', '%'.$this->searchClass.'%'))
            ->latest()
            ->paginate(10, ['*'], 'classesPage');

        $assets = asset::with('asset_class')
            ->when($this->searchAsset, fn ($q) => $q->where('name', 'like', '%'.$this->searchAsset.'%'))
            ->latest()
            ->paginate(10, ['*'], 'assetsPage');

        return view('livewire.setup.asset.assetconf', [
            'buildings' => $buildings,
            'locations' => $locations,
            'assetClasses' => $assetClasses,
            'assets' => $assets,
            'usedIds' => [
                'building' => RecordUsage::usedIds('buildings', $buildings->pluck('id')),
                'location' => RecordUsage::usedIds('facilitylocations', $locations->pluck('id')),
                'class' => RecordUsage::usedIds('assetclasses', $assetClasses->pluck('id')),
                'asset' => RecordUsage::usedIds('assets', $assets->pluck('id')),
            ],
            'workstations' => workstations::activeOr($this->building_workstation_id ?: null)->orderBy('workstation_name')->get(),
            'buildingsForSelect' => building::activeOr($this->location_building_id ?: null)->with('workstation:id,workstation_name')->orderBy('name')->get(),
            'assetClassesForSelect' => assetclass::activeOr($this->asset_class_id ?: null)->orderBy('name')->get(),
            'departmentsForSelect' => departments::activeOr($this->location_department_id ?: null)->orderBy('name')->get(['id', 'name']),
            'departmentsForFilter' => departments::orderBy('name')->get(['id', 'name']),
            'engSections' => Section::active()->get(['id', 'name']),
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
