<?php

namespace App\Livewire\Setup\Finance;

use App\Models\FinancialYear;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class FinancialYears extends Component
{
    use WithPagination;

    public $search = '';
    public $financial_year_id;
    public $modalMode = 'create';
    public $showModal = false;
    public $name, $start_date, $end_date, $is_current = false, $status = 'active', $description;

    public function openModal($mode = 'create', $id = null)
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->modalMode = $mode;
        $this->showModal = true;

        if ($mode === 'edit' && $id) {
            $financialYear = FinancialYear::findOrFail($id);
            $this->financial_year_id = $id;
            $this->name = $financialYear->name;
            $this->start_date = $financialYear->start_date->format('Y-m-d');
            $this->end_date = $financialYear->end_date->format('Y-m-d');
            $this->is_current = $financialYear->is_current;
            $this->status = $financialYear->status;
            $this->description = $financialYear->description;
        } else {
            $this->reset(['financial_year_id', 'name', 'start_date', 'end_date', 'is_current', 'status', 'description']);
            $this->is_current = false;
            $this->status = 'active';
        }
    }

    public function save()
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255', $this->modalMode === 'create' ? 'unique:financial_years,name' : 'unique:financial_years,name,' . $this->financial_year_id],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'is_current' => ['boolean'],
            'status' => ['required', 'in:active,inactive'],
            'description' => ['nullable', 'string'],
        ]);

        if ($this->modalMode === 'edit' && $this->financial_year_id) {
            $financialYear = FinancialYear::findOrFail($this->financial_year_id);
            $financialYear->update([
                'name' => $this->name,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'is_current' => $this->is_current,
                'status' => $this->status,
                'description' => $this->description,
            ]);
            session()->flash('success', 'Financial Year updated successfully!');
        } else {
            FinancialYear::create([
                'name' => $this->name,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'is_current' => $this->is_current,
                'status' => $this->status,
                'description' => $this->description,
            ]);
            session()->flash('success', 'Financial Year added successfully!');
        }

        $this->showModal = false;
        $this->reset(['financial_year_id', 'name', 'start_date', 'end_date', 'is_current', 'status', 'description']);
    }

    public function update()
    {
        $this->save();
    }

    public function setAsCurrent($id)
    {
        $financialYear = FinancialYear::findOrFail($id);
        $financialYear->setAsCurrent();
        session()->flash('success', 'Financial Year set as current successfully!');
    }

    public function delete($id)
    {
        $financialYear = FinancialYear::findOrFail($id);

        if ($financialYear->is_current) {
            session()->flash('error', 'Cannot delete the current financial year!');
            return;
        }

        $financialYear->delete();
        session()->flash('success', 'Financial Year deleted successfully!');
    }

    public function updateCurrentYear()
    {
        FinancialYear::updateCurrentFinancialYear();
        session()->flash('success', 'Current financial year updated based on today\'s date!');
    }

    public function mount()
    {
        //
    }

    public function render()
    {
        $financialYears = FinancialYear::query()
            ->where('name', 'like', '%' . $this->search . '%')
            ->orderBy('start_date', 'desc')
            ->paginate(10);

        return view('livewire.setup.finance.financial-years', [
            'financialYears' => $financialYears,
            'currentYear' => FinancialYear::current()
        ]);
    }
}
