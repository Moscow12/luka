<?php

namespace App\Livewire\Acl;

use App\Livewire\Concerns\ConfirmsAdminPassword;
use App\Models\Permission;
use App\Models\PermissionCategory;
use App\Services\Acl\AccessControl;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Exceptions\PermissionAlreadyExists;

/**
 * Permission catalogue. Needs manage-permissions on every request; every change needs a
 * fresh password confirmation; a non-super-admin cannot rename, move or delete a
 * permission they do not hold themselves.
 */
class PermissionIndex extends Component
{
    use ConfirmsAdminPassword, WithPagination;

    // Category properties
    public $showCategoryModal = false;

    public $categoryModalMode = 'create';

    #[Locked]
    public $category_id;

    public $category_name;

    // Permission properties
    public $showPermissionModal = false;

    public $permissionModalMode = 'create';

    #[Locked]
    public $permission_id;

    public $permission_name;

    public $permission_description;

    public $permission_category_id;

    // Delete confirmation (target set server-side only)
    public $showDeleteModal = false;

    #[Locked]
    public $deleteType;

    #[Locked]
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

    public function boot(): void
    {
        abort_unless(Auth::user()?->can(AccessControl::MANAGE_PERMISSIONS), 403);
    }

    protected function adminConfirmableActions(): array
    {
        return ['saveCategory', 'savePermission', 'delete'];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    /** A non-super-admin may only alter permissions they hold. */
    protected function mayAlter(Permission $permission): bool
    {
        return AccessControl::holdsAll(Auth::user(), [$permission->name]);
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

        if (! $this->adminPasswordConfirmed()) {
            return $this->requireAdminPassword('saveCategory');
        }

        if ($this->categoryModalMode === 'edit' && $this->category_id) {
            $category = PermissionCategory::findOrFail($this->category_id);
            $category->update(['name' => $this->category_name]);
            session()->flash('success', 'Category updated successfully!');
        } else {
            PermissionCategory::create(['name' => $this->category_name]);
            session()->flash('success', 'Category created successfully!');
        }

        $this->closeCategoryModal();

        return null;
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

        $existing = $this->permissionModalMode === 'edit' && $this->permission_id
            ? Permission::findOrFail($this->permission_id)
            : null;

        if ($existing && ! $this->mayAlter($existing)) {
            $this->addError('permission_name', 'You cannot change a permission you do not hold.');

            return null;
        }

        if (! $this->adminPasswordConfirmed()) {
            return $this->requireAdminPassword('savePermission');
        }

        try {
            if ($existing) {
                $permission = $existing;
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
        } catch (PermissionAlreadyExists $e) {
            $this->addError('permission_name', "The permission '{$this->permission_name}' already exists. Please choose a different name.");
        }

        return null;
    }

    // Delete Methods
    /** The name shown in the dialog is looked up here, never passed in from the page. */
    public function confirmDelete($type, $id)
    {
        $target = match ($type) {
            'category' => PermissionCategory::findOrFail($id),
            'permission' => Permission::findOrFail($id),
            default => abort(404),
        };

        $this->deleteType = $type;
        $this->deleteId = $target->getKey();
        $this->deleteName = $target->name;
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
        if ($this->deleteType === 'permission' && ! $this->mayAlter(Permission::findOrFail($this->deleteId))) {
            session()->flash('error', 'You cannot delete a permission you do not hold.');
            $this->closeDeleteModal();

            return null;
        }

        if ($this->deleteType === 'category' && PermissionCategory::findOrFail($this->deleteId)->permissions()
            ->pluck('name')->contains(fn ($name) => ! AccessControl::holdsAll(Auth::user(), [$name]))) {
            session()->flash('error', 'This category contains permissions you do not hold.');
            $this->closeDeleteModal();

            return null;
        }

        if (! $this->adminPasswordConfirmed()) {
            $this->showDeleteModal = false;

            return $this->requireAdminPassword('delete');
        }

        if ($this->deleteType === 'category') {
            $category = PermissionCategory::findOrFail($this->deleteId);
            $category->delete();
            session()->flash('success', 'Category deleted successfully!');
        } elseif ($this->deleteType === 'permission') {
            $permission = Permission::findOrFail($this->deleteId);
            $permission->delete();
            session()->flash('success', 'Permission deleted successfully!');
        }

        activity('permission')->causedBy(Auth::user())->event('deleted')
            ->withProperties(['type' => $this->deleteType, 'name' => $this->deleteName])
            ->log("Deleted {$this->deleteType}: {$this->deleteName}");

        $this->closeDeleteModal();

        return null;
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
