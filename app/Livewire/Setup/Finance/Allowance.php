<?php

namespace App\Livewire\Setup\Finance;

use App\Models\allowances;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Allowance extends Component
{
    public $allowance;
    public $Pay_Grade;
    public $Job_Title;
    public $Minimum_Salary;
    public $Mid_Point_Salary;
    public $Maximum_Salary;
    public $search = '';
    public $allowance_id;
    public $modalMode = 'create'; // or 'edit'
    public $showModal = false;
    public function openModal($mode = 'create', $id = null)
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->modalMode = $mode;
        $this->showModal = true;
        if ($mode === 'edit' && $id) {
            $allowance = allowances::findOrFail($id);
            $this->allowance_id = $id;
            $this->Pay_Grade = $allowance->Pay_Grade;
            $this->Job_Title = $allowance->Job_Title;
            $this->Minimum_Salary = $allowance->Minimum_Salary;
            $this->Mid_Point_Salary = $allowance->Mid_Point_Salary;
            $this->Maximum_Salary = $allowance->Maximum_Salary;


        } else {
            $this->reset(['Pay_Grade', 'allowance_id', 'Job_Title', 'Minimum_Salary', 'Mid_Point_Salary', 'Maximum_Salary']);
        }
    }

    public function save()
    {
        $this->validate([
            'Pay_Grade' => ['required', 'string', 'max:255', 'unique:allowances,Pay_Grade'],
            'Job_Title' => ['required', 'string', 'max:255'],
            'Minimum_Salary' => ['required', 'string', 'max:255'],
            'Mid_Point_Salary' => ['required', 'string', 'max:255'],
            'Maximum_Salary' => ['required', 'string', 'max:255'],

        ]);

        if ($this->modalMode === 'edit' && $this->allowance_id) {
            $allowance = allowances::findOrFail($this->allowance_id);
            $allowance->update(['Pay_Grade' => $this->Pay_Grade, 'Job_Title' => $this->Job_Title, 'Minimum_Salary' => $this->Minimum_Salary, 'Mid_Point_Salary' => $this->Mid_Point_Salary, 'Maximum_Salary' => $this->Maximum_Salary]);
            $this->listdata();
            session()->flash('success', 'Allowance updated successfully!');
        } else {
            allowances::create([
                'Pay_Grade' => $this->Pay_Grade,
                'Job_Title' => $this->Job_Title,
                'Minimum_Salary' => $this->Minimum_Salary,
                'Mid_Point_Salary' => $this->Mid_Point_Salary,
                'Maximum_Salary' => $this->Maximum_Salary,                
                'added_by' => Auth::user()->id
            ]);
            $this->listdata();
            session()->flash('success', 'Allowance added successfully!');
        }

        $this->showModal = false;
        $this->reset(['Pay_Grade', 'allowance_id', 'Job_Title', 'Minimum_Salary', 'Mid_Point_Salary', 'Maximum_Salary']);
    }

    public function delete($uuid)
    {
        $allowance = allowances::findOrFail($uuid);
        $allowance->delete();
        $this->listdata();
        session()->flash('success', 'Allowance deleted successfully!');
    }
    public function mount()
    {
        $this->listdata();
    }

    public function listdata()
    {
        
    }
    public function render()
    {
        $allowances = allowances::query()
            ->where('Pay_Grade', 'like', '%' . $this->search . '%')
            ->paginate(10);
        return view('livewire.setup.finance.allowance', ['allowances' => $allowances]);
    }
}
