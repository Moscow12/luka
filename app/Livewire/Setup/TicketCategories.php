<?php

namespace App\Livewire\Setup;

use App\Models\departments;
use App\Models\TicketCategory;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class TicketCategories extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $name = '';

    public $description = '';

    public $default_department_id = '';

    public $default_sla_hours = '';

    public $status = 'active';

    public $editingId = null;

    public $showModal = false;

    public $search = '';

    public function mount()
    {
        abort_unless(Auth::user()->isSuperAdmin() || Auth::user()->can('manage-ticket-categories'), 403);
    }

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255|unique:ticket_categories,name,'.$this->editingId,
            'description' => 'nullable|string|max:1000',
            'default_department_id' => 'nullable|exists:departments,id',
            'default_sla_hours' => 'nullable|integer|min:1|max:8760',
            'status' => 'required|in:active,inactive',
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openModal($id = null)
    {
        $this->resetErrorBag();
        $this->reset(['name', 'description', 'default_department_id', 'default_sla_hours', 'status', 'editingId']);

        if ($id) {
            $category = TicketCategory::findOrFail($id);
            $this->editingId = $id;
            $this->name = $category->name;
            $this->description = $category->description;
            $this->default_department_id = $category->default_department_id ?? '';
            $this->default_sla_hours = $category->default_sla_hours ?? '';
            $this->status = $category->status;
        }

        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'description' => $this->description ?: null,
            'default_department_id' => $this->default_department_id ?: null,
            'default_sla_hours' => $this->default_sla_hours ?: null,
            'status' => $this->status,
        ];

        if ($this->editingId) {
            TicketCategory::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Category updated successfully.');
        } else {
            TicketCategory::create($data + ['added_by' => Auth::id()]);
            session()->flash('success', 'Category created successfully.');
        }

        $this->closeModal();
    }

    public function toggleStatus($id)
    {
        $category = TicketCategory::findOrFail($id);
        $category->update(['status' => $category->status === 'active' ? 'inactive' : 'active']);
    }

    public function render()
    {
        return view('livewire.setup.ticket-categories', [
            'categories' => TicketCategory::with('defaultDepartment:id,name')
                ->withCount('tickets')
                ->when($this->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%"))
                ->orderBy('name')
                ->paginate(15),
            'departments' => departments::orderBy('name')->get(['id', 'name']),
        ]);
    }
}
