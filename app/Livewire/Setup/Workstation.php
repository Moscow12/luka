<?php

namespace App\Livewire\Setup;

use App\Models\countries;
use App\Models\districts;
use App\Models\regions;
use App\Models\wards;
use App\Models\workstations;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Workstation extends Component
{
    use WithFileUploads, WithPagination;

    // Form fields
    public $workstation_id;
    public $workstation_name;
    public $location;
    public $postal_code;
    public $physical_address;
    public $phone_number;
    public $tin_number;
    public $email_address;
    public $country_id;
    public $region_id;
    public $district_id;
    public $ward_id;

    // File uploads
    public $logo;
    public $official_stamp;
    public $letter_head;

    // Existing file paths (for edit mode)
    public $existing_logo;
    public $existing_stamp;
    public $existing_letterhead;

    // Dropdown data
    public $countries = [];
    public $regions = [];
    public $districts = [];
    public $wards = [];

    // UI state
    public $modalMode = 'create';
    public $showModal = false;
    public $search = '';
    public $confirmingDelete = null;

    protected $paginationTheme = 'bootstrap';

    protected function rules()
    {
        $uniqueRule = $this->modalMode === 'edit' && $this->workstation_id
            ? 'unique:workstations,workstation_name,' . $this->workstation_id
            : 'unique:workstations,workstation_name';

        return [
            'workstation_name' => ['required', 'string', 'max:255', $uniqueRule],
            'location' => ['nullable', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'max:20'],
            'tin_number' => ['required', 'string', 'max:50'],
            'email_address' => ['required', 'email', 'max:255'],
            'country_id' => ['required', 'exists:countries,id'],
            'region_id' => ['required', 'exists:regions,id'],
            'district_id' => ['required', 'exists:districts,id'],
            'ward_id' => ['required', 'exists:wards,id'],
            'postal_code' => ['required', 'string', 'max:20'],
            'physical_address' => ['required', 'string', 'max:500'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'official_stamp' => ['nullable', 'image', 'max:2048'],
            'letter_head' => ['nullable', 'image', 'max:2048'],
        ];
    }

    protected $messages = [
        'workstation_name.required' => 'Workstation name is required.',
        'workstation_name.unique' => 'This workstation name already exists.',
        'phone_number.required' => 'Phone number is required.',
        'tin_number.required' => 'TIN number is required.',
        'email_address.required' => 'Email address is required.',
        'email_address.email' => 'Please enter a valid email address.',
        'country_id.required' => 'Please select a country.',
        'region_id.required' => 'Please select a region.',
        'district_id.required' => 'Please select a district.',
        'ward_id.required' => 'Please select a ward.',
        'postal_code.required' => 'Postal code is required.',
        'physical_address.required' => 'Physical address is required.',
        'logo.image' => 'Logo must be an image file.',
        'logo.max' => 'Logo must not exceed 2MB.',
        'official_stamp.image' => 'Official stamp must be an image file.',
        'official_stamp.max' => 'Official stamp must not exceed 2MB.',
        'letter_head.image' => 'Letter head must be an image file.',
        'letter_head.max' => 'Letter head must not exceed 2MB.',
    ];

    public function mount()
    {
        $this->countries = countries::orderBy('name')->get();
        $this->regions = regions::orderBy('name')->get();
    }

    public function updatedRegionId($value)
    {
        $this->districts = $value
            ? districts::where('region_id', $value)->orderBy('name')->get()
            : collect();
        $this->district_id = null;
        $this->wards = collect();
        $this->ward_id = null;
    }

    public function updatedDistrictId($value)
    {
        $this->wards = $value
            ? wards::where('district_id', $value)->orderBy('name')->get()
            : collect();
        $this->ward_id = null;
    }

    public function openModal($mode = 'create', $id = null)
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->modalMode = $mode;

        if ($mode === 'edit' && $id) {
            $workstation = workstations::findOrFail($id);
            $this->workstation_id = $id;
            $this->workstation_name = $workstation->workstation_name;
            $this->location = $workstation->location;
            $this->phone_number = $workstation->phone_number;
            $this->tin_number = $workstation->tin_number;
            $this->email_address = $workstation->email_address;
            $this->country_id = $workstation->country_id;
            $this->region_id = $workstation->region_id;
            $this->postal_code = $workstation->postal_code;
            $this->physical_address = $workstation->physical_address;

            // Load districts and wards for edit mode
            $this->districts = districts::where('region_id', $workstation->region_id)->orderBy('name')->get();
            $this->district_id = $workstation->district_id;
            $this->wards = wards::where('district_id', $workstation->district_id)->orderBy('name')->get();
            $this->ward_id = $workstation->ward_id;

            // Store existing file paths
            $this->existing_logo = $workstation->logo;
            $this->existing_stamp = $workstation->official_stamp;
            $this->existing_letterhead = $workstation->letter_head;
        } else {
            $this->resetForm();
        }

        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->reset([
            'workstation_id',
            'workstation_name',
            'location',
            'phone_number',
            'tin_number',
            'email_address',
            'country_id',
            'region_id',
            'district_id',
            'ward_id',
            'postal_code',
            'physical_address',
            'logo',
            'official_stamp',
            'letter_head',
            'existing_logo',
            'existing_stamp',
            'existing_letterhead',
        ]);
        $this->districts = collect();
        $this->wards = collect();
    }

    public function save()
    {
        $this->validate();

        $data = [
            'workstation_name' => $this->workstation_name,
            'location' => $this->location,
            'phone_number' => $this->phone_number,
            'tin_number' => $this->tin_number,
            'email_address' => $this->email_address,
            'country_id' => $this->country_id,
            'region_id' => $this->region_id,
            'district_id' => $this->district_id,
            'ward_id' => $this->ward_id,
            'postal_code' => $this->postal_code,
            'physical_address' => $this->physical_address,
        ];

        // Handle file uploads
        if ($this->logo) {
            if ($this->existing_logo) {
                Storage::disk('public')->delete($this->existing_logo);
            }
            $data['logo'] = $this->logo->store('workstations/logos', 'public');
        }

        if ($this->official_stamp) {
            if ($this->existing_stamp) {
                Storage::disk('public')->delete($this->existing_stamp);
            }
            $data['official_stamp'] = $this->official_stamp->store('workstations/stamps', 'public');
        }

        if ($this->letter_head) {
            if ($this->existing_letterhead) {
                Storage::disk('public')->delete($this->existing_letterhead);
            }
            $data['letter_head'] = $this->letter_head->store('workstations/letterheads', 'public');
        }

        if ($this->modalMode === 'edit' && $this->workstation_id) {
            $workstation = workstations::findOrFail($this->workstation_id);
            $workstation->update($data);
            session()->flash('success', 'Workstation updated successfully!');
        } else {
            $data['added_by'] = Auth::id();
            workstations::create($data);
            session()->flash('success', 'Workstation created successfully!');
        }

        $this->closeModal();
    }

    public function confirmDelete($id)
    {
        $this->confirmingDelete = $id;
    }

    public function delete()
    {
        if ($this->confirmingDelete) {
            $workstation = workstations::findOrFail($this->confirmingDelete);

            // Delete associated files
            if ($workstation->logo) {
                Storage::disk('public')->delete($workstation->logo);
            }
            if ($workstation->official_stamp) {
                Storage::disk('public')->delete($workstation->official_stamp);
            }
            if ($workstation->letter_head) {
                Storage::disk('public')->delete($workstation->letter_head);
            }

            $workstation->delete();
            $this->confirmingDelete = null;
            session()->flash('success', 'Workstation deleted successfully!');
        }
    }

    public function cancelDelete()
    {
        $this->confirmingDelete = null;
    }

    public function removeFile($type)
    {
        if ($type === 'logo') {
            $this->logo = null;
        } elseif ($type === 'stamp') {
            $this->official_stamp = null;
        } elseif ($type === 'letterhead') {
            $this->letter_head = null;
        }
    }

    public function render()
    {
        $workstations = workstations::with(['country', 'region', 'district', 'ward', 'added_by'])
            ->when($this->search, function ($query) {
                $query->where('workstation_name', 'like', '%' . $this->search . '%')
                    ->orWhere('email_address', 'like', '%' . $this->search . '%')
                    ->orWhere('tin_number', 'like', '%' . $this->search . '%')
                    ->orWhere('phone_number', 'like', '%' . $this->search . '%');
            })
            ->orderBy('workstation_name')
            ->paginate(10);

        return view('livewire.setup.workstation', compact('workstations'));
    }
}
