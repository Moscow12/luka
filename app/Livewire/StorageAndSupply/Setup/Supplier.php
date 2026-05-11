<?php

namespace App\Livewire\StorageAndSupply\Setup;

use App\Models\Supplier as SupplierModel;
use Livewire\{Component, WithPagination};

class Supplier extends Component
{
    use WithPagination;

    public $search = '';
    public $supplier;
    public $filter_status = 'active';

    protected $rules = [
        'supplier.name' => 'required|string|max:200',
        'supplier.contact_person' => 'nullable|string|max:200',
        'supplier.phone' => 'nullable|string|max:20',
        'supplier.email' => 'nullable|email|max:200',
        'supplier.type' => 'nullable|in:local,foreign',
        'supplier.currency' => 'nullable|string|max:10',
        'supplier.supplier_type' => 'nullable|in:vendor,distributor,donor,supplier',
        'supplier.address' => 'nullable|string',
    ];

    public function mount()
    {
        $this->supplier = new SupplierModel;
    }

    public function render()
    {
        $suppliers = SupplierModel::query()
            ->when($this->filter_status === 'active', fn($q) => $q->where('active', true))
            ->when($this->filter_status === 'inactive', fn($q) => $q->where('active', false))
            ->when($this->search, function($query) {
                $query->where(function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('contact_person', 'like', '%' . $this->search . '%')
                      ->orWhere('phone', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('name')
            ->paginate(15);

        return view('livewire.storage-and-supply.setup.supplier', [
            'title' => isset($this->supplier->id) ? 'Edit Supplier' : 'Add Supplier',
            'action' => isset($this->supplier->id) ? 'update' : 'store',
            'suppliers' => $suppliers,
        ]);
    }

    public function store()
    {
        $this->validate();

        if (!$this->supplier->save()) {
            return $this->dispatch('error', 'Failed to add supplier. Please try again.');
        }

        $this->dispatch('success', 'Supplier added successfully.', 'supplierModal');
        $this->resetFields();
    }

    public function edit(SupplierModel $supplier)
    {
        $this->supplier = $supplier;
        $this->dispatch('modal-show', 'supplierModal');
    }

    public function update()
    {
        $this->validate();

        // $this->supplier->updated_by = auth()->id();
        if (!$this->supplier->save()) {
            return $this->dispatch('error', 'Failed to update supplier. Please try again.');
        }

        $this->dispatch('success', 'Supplier updated successfully.', 'supplierModal');
        $this->resetFields();
    }

    public function toggleStatus($id)
    {
        $supplier = SupplierModel::find($id);
        if (!$supplier) {
            return $this->dispatch('error', 'Supplier not found.');
        }

        $supplier->active = !$supplier->active;
        $supplier->save();

        $status = $supplier->active ? 'activated' : 'deactivated';
        $this->dispatch('success', "Supplier {$status} successfully.");
    }

    public function delete($id)
    {
        $supplier = SupplierModel::find($id);
        if (!$supplier) {
            return $this->dispatch('error', 'Supplier not found.');
        }

        try {
            $supplier->delete();
            $this->dispatch('success', "Supplier {$supplier->name} deleted successfully.");
        } catch (\Throwable $th) {
            return $this->dispatch('error', "Failed to delete supplier. It may be in use.");
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterStatus()
    {
        $this->resetPage();
    }

    public function resetFields()
    {
        $this->supplier = new SupplierModel();
        $this->reset(['search']);
    }
}
