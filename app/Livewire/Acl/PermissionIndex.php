<?php

namespace App\Livewire\Acl;

use App\Models\Permission;
use App\Models\PermissionCategory;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class PermissionIndex extends Component
{
    use WithPagination;

    // Category properties
    public $showCategoryModal = false;

    public $categoryModalMode = 'create';

    public $category_id;

    public $category_name;

    // Permission properties
    public $showPermissionModal = false;

    public $permissionModalMode = 'create';

    public $permission_id;

    public $permission_name;

    public $permission_description;

    public $permission_category_id;

    // Delete confirmation
    public $showDeleteModal = false;

    public $deleteType;

    public $deleteId;

    public $deleteName;

    // Search
    public $search = '';

    protected $queryString = ['search'];

    protected function rules()
    {
        return [
            'category_name' => 'required|string|min:2|max:100|unique:permission_categories,name,'.$this->category_id,
            'permission_name' => 'required|string|min:2|max:100',
            'permission_description' => 'nullable|string|max:500',
            'permission_category_id' => 'required|exists:permission_categories,id',
        ];
    }

    protected $messages = [
        'category_name.required' => 'Category name is required.',
        'category_name.unique' => 'This category name already exists.',
        'permission_name.required' => 'Permission name is required.',
        'permission_category_id.required' => 'Please select a category.',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    // Category Methods
    public function openCategoryModal($mode = 'create', $id = null)
    {
        $this->resetValidation();
        $this->categoryModalMode = $mode;
        $this->category_id = null;
        $this->category_name = '';

        if ($mode === 'edit' && $id) {
            $category = PermissionCategory::findOrFail($id);
            $this->category_id = $category->id;
            $this->category_name = $category->name;
        }

        $this->showCategoryModal = true;
    }

    public function closeCategoryModal()
    {
        $this->showCategoryModal = false;
        $this->resetValidation();
    }

    public function saveCategory()
    {
        $this->validate([
            'category_name' => 'required|string|min:2|max:100|unique:permission_categories,name,'.$this->category_id,
        ]);

        if ($this->categoryModalMode === 'edit' && $this->category_id) {
            $category = PermissionCategory::findOrFail($this->category_id);
            $category->update(['name' => $this->category_name]);
            session()->flash('success', 'Category updated successfully!');
        } else {
            PermissionCategory::create(['name' => $this->category_name]);
            session()->flash('success', 'Category created successfully!');
        }

        $this->closeCategoryModal();
    }

    // Permission Methods
    public function openPermissionModal($mode = 'create', $id = null, $categoryId = null)
    {
        $this->resetValidation();
        $this->permissionModalMode = $mode;
        $this->permission_id = null;
        $this->permission_name = '';
        $this->permission_description = '';
        $this->permission_category_id = $categoryId;

        if ($mode === 'edit' && $id) {
            $permission = Permission::findOrFail($id);
            $this->permission_id = $permission->id;
            $this->permission_name = $permission->name;
            $this->permission_description = $permission->description;
            $this->permission_category_id = $permission->category_id;
        }

        $this->showPermissionModal = true;
    }

    public function closePermissionModal()
    {
        $this->showPermissionModal = false;
        $this->resetValidation();
    }

    public function savePermission()
    {
        $this->validate([
            'permission_name' => 'required|string|min:2|max:100',
            'permission_description' => 'nullable|string|max:500',
            'permission_category_id' => 'required|exists:permission_categories,id',
        ]);

        if ($this->permissionModalMode === 'edit' && $this->permission_id) {
            $permission = Permission::findOrFail($this->permission_id);
            $permission->update([
                'name' => $this->permission_name,
                'description' => $this->permission_description,
                'category_id' => $this->permission_category_id,
            ]);
            session()->flash('success', 'Permission updated successfully!');
        } else {
            Permission::create([
                'name' => $this->permission_name,
                'guard_name' => 'web',
                'description' => $this->permission_description,
                'category_id' => $this->permission_category_id,
            ]);
            session()->flash('success', 'Permission created successfully!');
        }

        $this->closePermissionModal();
    }

    // Delete Methods
    public function confirmDelete($type, $id, $name)
    {
        $this->deleteType = $type;
        $this->deleteId = $id;
        $this->deleteName = $name;
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->deleteType = null;
        $this->deleteId = null;
        $this->deleteName = null;
    }

    public function delete()
    {
        if ($this->deleteType === 'category') {
            $category = PermissionCategory::findOrFail($this->deleteId);
            $category->delete();
            session()->flash('success', 'Category deleted successfully!');
        } elseif ($this->deleteType === 'permission') {
            $permission = Permission::findOrFail($this->deleteId);
            $permission->delete();
            session()->flash('success', 'Permission deleted successfully!');
        }

        $this->closeDeleteModal();
    }

    public function render(): View
    {
        $categories = PermissionCategory::with(['permissions' => function ($query) {
            if ($this->search) {
                $query->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%');
            }
        }])
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%'.$this->search.'%')
                    ->orWhereHas('permissions', function ($q) {
                        $q->where('name', 'like', '%'.$this->search.'%')
                            ->orWhere('description', 'like', '%'.$this->search.'%');
                    });
            })
            ->orderBy('name')
            ->get();

        $allCategories = PermissionCategory::orderBy('name')->get();

        return view('livewire.acl.permission-index', [
            'categories' => $categories,
            'allCategories' => $allCategories,
        ]);
    }
}
