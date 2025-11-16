<?php

namespace App\Livewire\Chop;

use App\Models\chopcategoryarea;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class CategoryAreas extends Component
{
    use WithPagination;

    public $search = '';
    public $category_id;
    public $modalMode = 'create';
    public $showModal = false;
    public $name, $slug, $description;

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
            $category = chopcategoryarea::findOrFail($id);
            $this->category_id = $id;
            $this->name = $category->name;
            $this->slug = $category->slug;
            $this->description = $category->description;
        } else {
            $this->reset(['category_id', 'name', 'slug', 'description']);
        }
    }

    public function save()
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255', $this->modalMode === 'create' ? 'unique:chopcategoryareas,name' : 'unique:chopcategoryareas,name,' . $this->category_id],
            'slug' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        if ($this->modalMode === 'edit' && $this->category_id) {
            $category = chopcategoryarea::findOrFail($this->category_id);
            $category->update([
                'name' => $this->name,
                'slug' => $this->slug,
                'description' => $this->description,
            ]);
            session()->flash('success', 'Category Area updated successfully!');
        } else {
            chopcategoryarea::create([
                'name' => $this->name,
                'slug' => $this->slug,
                'description' => $this->description,
                'added_by' => Auth::id(),
            ]);
            session()->flash('success', 'Category Area added successfully!');
        }

        $this->showModal = false;
        $this->reset(['category_id', 'name', 'slug', 'description']);
    }

    public function update()
    {
        $this->save();
    }

    public function delete($id)
    {
        $category = chopcategoryarea::findOrFail($id);

        // Check if category has chop items
        if ($category->chopItems()->count() > 0) {
            session()->flash('error', 'Cannot delete category with existing chop items!');
            return;
        }

        $category->delete();
        session()->flash('success', 'Category Area deleted successfully!');
    }

    public function mount()
    {
        //
    }

    public function render()
    {
        $categories = chopcategoryarea::query()
            ->where('name', 'like', '%' . $this->search . '%')
            ->withCount('chopItems')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.chop.category-areas', [
            'categories' => $categories
        ]);
    }
}
