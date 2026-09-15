<?php

namespace App\Livewire\Chop;

use App\Models\chopcategoryarea;
use App\Models\chopitems;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class ItemsManagement extends Component
{
    use WithPagination;

    public $search = '';

    public $item_id;

    public $modalMode = 'create';

    public $showModal = false;

    public $name;

    public $slug;

    public $is_active = true;

    public $is_asset = false;

    public $can_be_stocked = true;

    public $gfc_code;

    public $unit;

    public $quantity;

    public $price;

    public $description;

    public $category_id;

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
            $item = chopitems::findOrFail($id);
            $this->item_id = $id;
            $this->name = $item->name;
            $this->slug = $item->slug;
            $this->is_active = $item->is_active;
            $this->is_asset = $item->is_asset;
            $this->can_be_stocked = $item->can_be_stocked;
            $this->gfc_code = $item->gfc_code;
            $this->unit = $item->unit;
            $this->quantity = $item->quantity;
            $this->price = $item->price;
            $this->description = $item->description;
            $this->category_id = $item->category_id;
        } else {
            $this->reset(['item_id', 'name', 'slug', 'is_active', 'is_asset', 'can_be_stocked', 'gfc_code', 'unit', 'quantity', 'price', 'description', 'category_id']);
            $this->is_active = true;
            $this->is_asset = false;
            $this->can_be_stocked = true;
        }
    }

    public function save()
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255', $this->modalMode === 'create' ? 'unique:chopitems,name' : 'unique:chopitems,name,'.$this->item_id],
            'slug' => ['required', 'string', 'max:255'],
            'is_active' => ['boolean'],
            'is_asset' => ['boolean'],
            'can_be_stocked' => ['boolean'],
            'gfc_code' => ['nullable', 'string', 'max:255'],
            'unit' => ['nullable', 'string', 'max:255'],
            'quantity' => ['nullable', 'integer', 'min:0'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'category_id' => ['nullable', 'exists:chopcategoryareas,id'],
        ]);

        if ($this->modalMode === 'edit' && $this->item_id) {
            $item = chopitems::findOrFail($this->item_id);
            $item->update([
                'name' => $this->name,
                'slug' => $this->slug,
                'is_active' => $this->is_active,
                'is_asset' => $this->is_asset,
                'can_be_stocked' => $this->can_be_stocked,
                'gfc_code' => $this->gfc_code,
                'unit' => $this->unit,
                'quantity' => $this->quantity,
                'price' => $this->price,
                'description' => $this->description,
                'category_id' => $this->category_id,
            ]);
            session()->flash('success', 'Chop Item updated successfully!');
        } else {
            chopitems::create([
                'name' => $this->name,
                'slug' => $this->slug,
                'is_active' => $this->is_active,
                'is_asset' => $this->is_asset,
                'can_be_stocked' => $this->can_be_stocked,
                'gfc_code' => $this->gfc_code,
                'unit' => $this->unit,
                'quantity' => $this->quantity,
                'price' => $this->price,
                'description' => $this->description,
                'category_id' => $this->category_id,
                'added_by' => Auth::id(),
            ]);
            session()->flash('success', 'Chop Item added successfully!');
        }

        $this->showModal = false;
        $this->reset(['item_id', 'name', 'slug', 'is_active', 'is_asset', 'can_be_stocked', 'gfc_code', 'unit', 'quantity', 'price', 'description', 'category_id']);
    }

    public function update()
    {
        $this->save();
    }

    public function mount()
    {
        //
    }

    public function render()
    {
        $items = chopitems::query()
            ->with('category')
            ->where('name', 'like', '%'.$this->search.'%')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $categories = chopcategoryarea::orderBy('name')->get();

        return view('livewire.chop.items-management', [
            'items' => $items,
            'categories' => $categories,
        ]);
    }
}
