<?php

namespace App\Livewire\Chop;

use App\Models\sourceoffunds;
use App\Models\FinancialYear;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class FundSources extends Component
{
    use WithPagination;

    public $search = '';
    public $source_id;
    public $modalMode = 'create';
    public $showModal = false;

    // Required fields
    public $name;
    public $slug;
    public $is_active = true;
    public $financial_year_id;

    // Optional fields
    public $description;
    public $colorcode;
    public $estimated_cost;

    public function updatedName()
    {
        $this->slug = Str::slug($this->name);
    }

    public function openModal($mode = 'create', $id = null)
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->modalMode = $mode;
        $this->showModal = true;

        if ($mode === 'edit' && $id) {
            $source = sourceoffunds::findOrFail($id);
            $this->source_id = $id;
            $this->name = $source->name;
            $this->slug = $source->slug;
            $this->is_active = $source->is_active;
            $this->financial_year_id = $source->financial_year_id;
            $this->description = $source->description;
            $this->colorcode = $source->colorcode;
            $this->estimated_cost = $source->estimated_cost;
        } else {
            $this->reset(['source_id', 'name', 'slug', 'is_active', 'financial_year_id', 'description', 'colorcode', 'estimated_cost']);
            $this->is_active = true;

            // Set default to current financial year
            $currentFY = FinancialYear::where('is_current', true)->first();
            if (!$currentFY) {
                // If no current FY, get the most recent active one
                $currentFY = FinancialYear::where('status', 'active')
                    ->orderBy('start_date', 'desc')
                    ->first();
            }

            if ($currentFY) {
                $this->financial_year_id = (string) $currentFY->id;
            }
        }
    }

    public function save()
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255'],
            'financial_year_id' => ['required'],
            'is_active' => ['boolean'],
            'description' => ['nullable', 'string'],
            'colorcode' => ['nullable', 'string', 'max:7'],
            'estimated_cost' => ['nullable', 'string', 'max:255'],
        ]);

        // Additional validation to ensure financial year exists
        if (!FinancialYear::where('id', $this->financial_year_id)->exists()) {
            $this->addError('financial_year_id', 'The selected financial year is invalid.');
            return;
        }

        if ($this->modalMode === 'edit' && $this->source_id) {
            $source = sourceoffunds::findOrFail($this->source_id);
            $source->update([
                'name' => $this->name,
                'slug' => $this->slug,
                'is_active' => $this->is_active,
                'financial_year_id' => $this->financial_year_id,
                'description' => $this->description,
                'colorcode' => $this->colorcode,
                'estimated_cost' => $this->estimated_cost,
            ]);
            session()->flash('success', 'Source of Funds updated successfully!');
        } else {
            sourceoffunds::create([
                'name' => $this->name,
                'slug' => $this->slug,
                'is_active' => $this->is_active,
                'financial_year_id' => $this->financial_year_id,
                'description' => $this->description,
                'colorcode' => $this->colorcode,
                'estimated_cost' => $this->estimated_cost,
                'added_by' => Auth::id(),
            ]);
            session()->flash('success', 'Source of Funds added successfully!');
        }

        $this->showModal = false;
        $this->reset(['source_id', 'name', 'slug', 'is_active', 'financial_year_id', 'description', 'colorcode', 'estimated_cost']);
    }

    public function update()
    {
        $this->save();
    }

    public function delete($id)
    {
        $source = sourceoffunds::findOrFail($id);
        $source->delete();
        session()->flash('success', 'Source of Funds deleted successfully!');
    }

    public function mount()
    {
        //
    }

    public function render()
    {
        $sources = sourceoffunds::query()
            ->with(['financialYear'])
            ->where(function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('slug', 'like', '%' . $this->search . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $financialYears = FinancialYear::active()->orderBy('start_date', 'desc')->get();

        return view('livewire.chop.fund-sources', [
            'sources' => $sources,
            'financialYears' => $financialYears
        ]);
    }
}
