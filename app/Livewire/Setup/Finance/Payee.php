<?php

namespace App\Livewire\Setup\Finance;

use App\Models\paye_brackets;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Payee extends Component
{
    use WithPagination;

    public $search = '';

    // Form fields for brackets
    public $bracketId;
    public $modalMode = 'create';
    public $showModal = false;
    public $min_amount;
    public $max_amount;
    public $rate;
    public $description;
    public $order;
    public $is_active = true;

    // Calculator fields
    public $salary_input = '';
    public $calculation_result = null;

    protected $paginationTheme = 'bootstrap';

    protected function rules()
    {
        return [
            'min_amount' => ['required', 'numeric', 'min:0'],
            'max_amount' => ['nullable', 'numeric', 'min:0', 'gt:min_amount'],
            'rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'order' => ['required', 'integer', 'min:1'],
            'is_active' => ['boolean'],
        ];
    }

    protected $messages = [
        'max_amount.gt' => 'Maximum amount must be greater than minimum amount.',
        'rate.max' => 'Tax rate cannot exceed 100%.',
    ];

    public function mount()
    {
        // Set default salary for demonstration
        $this->salary_input = 1500000;
        $this->calculatePaye();
    }

    public function openModal($mode = 'create', $id = null)
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->modalMode = $mode;
        $this->showModal = true;

        if ($mode === 'edit' && $id) {
            $bracket = paye_brackets::findOrFail($id);
            $this->bracketId = $id;
            $this->min_amount = $bracket->min_amount;
            $this->max_amount = $bracket->max_amount;
            $this->rate = $bracket->rate;
            $this->description = $bracket->description;
            $this->order = $bracket->order;
            $this->is_active = $bracket->is_active;
        } else {
            $this->resetForm();
            // Auto-set next order number
            $maxOrder = paye_brackets::max('order') ?? 0;
            $this->order = $maxOrder + 1;
        }
    }

    public function resetForm()
    {
        $this->reset([
            'bracketId',
            'min_amount',
            'max_amount',
            'rate',
            'description',
            'order',
        ]);
        $this->is_active = true;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'min_amount' => $this->min_amount,
            'max_amount' => $this->max_amount ?: null,
            'rate' => $this->rate,
            'fixed_amount' => 0,
            'description' => $this->description,
            'order' => $this->order,
            'is_active' => $this->is_active,
        ];

        if ($this->modalMode === 'edit' && $this->bracketId) {
            $bracket = paye_brackets::findOrFail($this->bracketId);
            $bracket->update($data);
            session()->flash('success', 'PAYE bracket updated successfully!');
        } else {
            $data['added_by'] = Auth::id();
            paye_brackets::create($data);
            session()->flash('success', 'PAYE bracket added successfully!');
        }

        $this->showModal = false;
        $this->resetForm();

        // Recalculate if salary is entered
        if ($this->salary_input) {
            $this->calculatePaye();
        }
    }

    public function delete($id)
    {
        $bracket = paye_brackets::findOrFail($id);
        $bracket->delete();
        session()->flash('success', 'PAYE bracket deleted successfully!');

        // Recalculate if salary is entered
        if ($this->salary_input) {
            $this->calculatePaye();
        }
    }

    public function toggleActive($id)
    {
        $bracket = paye_brackets::findOrFail($id);
        $bracket->update(['is_active' => !$bracket->is_active]);

        // Recalculate if salary is entered
        if ($this->salary_input) {
            $this->calculatePaye();
        }
    }

    public function calculatePaye()
    {
        if (!$this->salary_input || !is_numeric($this->salary_input)) {
            $this->calculation_result = null;
            return;
        }

        $salary = (float) $this->salary_input;
        $this->calculation_result = paye_brackets::calculatePaye($salary);
    }

    public function updatedSalaryInput()
    {
        $this->calculatePaye();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $brackets = paye_brackets::query()
            ->when($this->search, function ($query) {
                $query->where('description', 'like', '%' . $this->search . '%');
            })
            ->orderBy('order')
            ->paginate(10);

        return view('livewire.setup.finance.payee', [
            'brackets' => $brackets,
        ]);
    }
}
