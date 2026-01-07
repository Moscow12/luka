<?php

namespace App\Livewire\Hr\Loan;

use App\Models\loan_items;
use App\Models\workstations;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Loanitems extends Component
{
    use WithPagination;

    public $search = '';

    public $loanItemId;

    public $modalMode = 'create';

    public $showModal = false;

    // Form fields
    public $name;

    public $min_amount;

    public $max_amount;

    public $interest_rate;

    public $repayment_period_months;

    public $workstation_id;

    // Dropdown data
    public $workstations = [];

    protected $paginationTheme = 'bootstrap';

    protected function rules()
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                $this->modalMode === 'create'
                    ? 'unique:loan_items,name'
                    : 'unique:loan_items,name,'.$this->loanItemId,
            ],
            'min_amount' => ['required', 'numeric', 'min:0'],
            'max_amount' => ['required', 'numeric', 'min:0', 'gte:min_amount'],
            'interest_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'repayment_period_months' => ['required', 'integer', 'min:1'],
            'workstation_id' => ['required', 'exists:workstations,id'],
        ];
    }

    protected $messages = [
        'max_amount.gte' => 'Maximum amount must be greater than or equal to minimum amount.',
        'interest_rate.max' => 'Interest rate cannot exceed 100%.',
        'workstation_id.required' => 'Please select a workstation.',
    ];

    public function mount()
    {
        $this->workstations = workstations::orderBy('workstation_name')->get();

        // Set default workstation from session if available
        if (session()->has('workstation_id')) {
            $this->workstation_id = session('workstation_id');
        }
    }

    public function openModal($mode = 'create', $id = null)
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->modalMode = $mode;
        $this->showModal = true;

        if ($mode === 'edit' && $id) {
            $loanItem = loan_items::findOrFail($id);
            $this->loanItemId = $id;
            $this->name = $loanItem->name;
            $this->min_amount = $loanItem->min_amount;
            $this->max_amount = $loanItem->max_amount;
            $this->interest_rate = $loanItem->interest_rate;
            $this->repayment_period_months = $loanItem->repayment_period_months;
            $this->workstation_id = $loanItem->workstation_id;
        } else {
            $this->resetForm();
            // Set default workstation from session
            if (session()->has('workstation_id')) {
                $this->workstation_id = session('workstation_id');
            }
        }
    }

    public function resetForm()
    {
        $this->reset([
            'loanItemId',
            'name',
            'min_amount',
            'max_amount',
            'interest_rate',
            'repayment_period_months',
            'workstation_id',
        ]);
    }

    public function save()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'min_amount' => $this->min_amount,
            'max_amount' => $this->max_amount,
            'interest_rate' => $this->interest_rate,
            'repayment_period_months' => $this->repayment_period_months,
            'workstation_id' => $this->workstation_id,
        ];

        if ($this->modalMode === 'edit' && $this->loanItemId) {
            $loanItem = loan_items::findOrFail($this->loanItemId);
            $loanItem->update($data);
            session()->flash('success', 'Loan item updated successfully!');
        } else {
            $data['added_by'] = Auth::id();
            loan_items::create($data);
            session()->flash('success', 'Loan item added successfully!');
        }

        // Store selected workstation in session for next time
        session(['workstation_id' => $this->workstation_id]);

        $this->showModal = false;
        $this->resetForm();
    }

    public function update()
    {
        $this->save();
    }

    public function delete($id)
    {
        $loanItem = loan_items::findOrFail($id);
        $loanItem->delete();
        session()->flash('success', 'Loan item deleted successfully!');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $loanItems = loan_items::query()
            ->with('workstation')
            ->where('name', 'like', '%'.$this->search.'%')
            ->latest()
            ->paginate(10);

        return view('livewire.hr.loan.loanitems', [
            'loanItems' => $loanItems,
        ]);
    }
}
