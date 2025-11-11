<?php

namespace App\Livewire\Setup;

use App\Models\countries;
use App\Models\districts;
use App\Models\regions;
use App\Models\street;
use App\Models\vendors;
use App\Models\wards;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class VendorManagement extends Component
{
    use WithPagination;

    public $search = '';

    public $statusFilter = '';

    public $typeFilter = '';

    public $showModal = false;

    public $modalMode = 'create';

    public $vendorId;

    // Vendor fields
    public $name;

    public $vendor_type;

    public $vendor_number;

    public $email;

    public $phone;

    public $address;

    public $status = 'active';

    public $contact_person;

    public $contact_email;

    public $contact_phone;

    public $description;

    // Location fields
    public $country_id;

    public $region_id;

    public $district_id;

    public $ward_id;

    public $vilstreet_id;

    // Location options
    public $countries = [];

    public $regions = [];

    public $districts = [];

    public $wards = [];

    public $streets = [];

    protected $paginationTheme = 'bootstrap';

    public function mount()
    {
        $this->countries = countries::all();
    }

    public function updatedCountryId($value)
    {
        $this->regions = regions::where('country_id', $value)->get();
        $this->region_id = null;
        $this->district_id = null;
        $this->ward_id = null;
        $this->vilstreet_id = null;
    }

    public function updatedRegionId($value)
    {
        $this->districts = districts::where('region_id', $value)->get();
        $this->district_id = null;
        $this->ward_id = null;
        $this->vilstreet_id = null;
    }

    public function updatedDistrictId($value)
    {
        $this->wards = wards::where('district_id', $value)->get();
        $this->ward_id = null;
        $this->vilstreet_id = null;
    }

    public function updatedWardId($value)
    {
        $this->streets = street::where('ward_id', $value)->get();
        $this->vilstreet_id = null;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openModal($mode = 'create', $id = null)
    {
        $this->resetForm();
        $this->modalMode = $mode;
        $this->showModal = true;

        if ($mode === 'edit' && $id) {
            $vendor = vendors::findOrFail($id);
            $this->vendorId = $vendor->id;
            $this->name = $vendor->name;
            $this->vendor_type = $vendor->vendor_type;
            $this->vendor_number = $vendor->vendor_number;
            $this->email = $vendor->email;
            $this->phone = $vendor->phone;
            $this->address = $vendor->address;
            $this->status = $vendor->status;
            $this->contact_person = $vendor->contact_person;
            $this->contact_email = $vendor->contact_email;
            $this->contact_phone = $vendor->contact_phone;
            $this->description = $vendor->description;
            $this->country_id = $vendor->country_id;
            $this->region_id = $vendor->region_id;
            $this->district_id = $vendor->district_id;
            $this->ward_id = $vendor->ward_id;
            $this->vilstreet_id = $vendor->vilstreet_id;

            // Load cascading dropdowns
            if ($this->country_id) {
                $this->regions = regions::where('country_id', $this->country_id)->get();
            }
            if ($this->region_id) {
                $this->districts = districts::where('region_id', $this->region_id)->get();
            }
            if ($this->district_id) {
                $this->wards = wards::where('district_id', $this->district_id)->get();
            }
            if ($this->ward_id) {
                $this->streets = street::where('ward_id', $this->ward_id)->get();
            }
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->reset([
            'vendorId',
            'name',
            'vendor_type',
            'vendor_number',
            'email',
            'phone',
            'address',
            'status',
            'contact_person',
            'contact_email',
            'contact_phone',
            'description',
            'country_id',
            'region_id',
            'district_id',
            'ward_id',
            'vilstreet_id',
        ]);
        $this->resetErrorBag();
    }

    public function save()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'vendor_type' => 'required|string|max:255',
            'vendor_number' => 'required|string|max:255|unique:vendors,vendor_number'.($this->modalMode === 'edit' ? ','.$this->vendorId : ''),
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'contact_person' => 'required|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:255',
            'country_id' => 'required|exists:countries,id',
            'region_id' => 'required|exists:regions,id',
            'district_id' => 'required|exists:districts,id',
        ];

        $this->validate($rules);

        try {
            $data = [
                'name' => $this->name,
                'vendor_type' => $this->vendor_type,
                'vendor_number' => $this->vendor_number,
                'email' => $this->email,
                'phone' => $this->phone,
                'address' => $this->address,
                'status' => $this->status ?? 'active',
                'contact_person' => $this->contact_person,
                'contact_email' => $this->contact_email,
                'contact_phone' => $this->contact_phone,
                'description' => $this->description,
                'country_id' => $this->country_id,
                'region_id' => $this->region_id,
                'district_id' => $this->district_id,
                'ward_id' => $this->ward_id,
                'vilstreet_id' => $this->vilstreet_id,
                'added_by' => Auth::id(),
            ];

            if ($this->modalMode === 'create') {
                vendors::create($data);
                $message = 'Vendor created successfully';
            } else {
                vendors::findOrFail($this->vendorId)->update($data);
                $message = 'Vendor updated successfully';
            }

            $this->dispatch('toaster', ['type' => 'success', 'message' => $message]);
            $this->closeModal();
        } catch (\Exception) {
            $this->dispatch('toaster', ['type' => 'error', 'message' => 'Error saving vendor']);
        }
    }

    public function deleteVendor($id)
    {
        try {
            vendors::findOrFail($id)->delete();
            $this->dispatch('toaster', ['type' => 'success', 'message' => 'Vendor deleted successfully']);
        } catch (\Exception) {
            $this->dispatch('toaster', ['type' => 'error', 'message' => 'Error deleting vendor']);
        }
    }

    public function render()
    {
        $vendors = vendors::query()
            ->with(['country', 'region', 'district', 'ward', 'vilstreet', 'added_by'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('vendor_number', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%')
                        ->orWhere('phone', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->when($this->typeFilter, function ($query) {
                $query->where('vendor_type', $this->typeFilter);
            })
            ->latest()
            ->paginate(15);

        $vendorTypes = vendors::distinct()->pluck('vendor_type');

        return view('livewire.setup.vendors', [
            'vendors' => $vendors,
            'vendorTypes' => $vendorTypes,
        ]);
    }
}
