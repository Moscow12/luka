<?php

namespace App\Livewire\Acl;

use App\Models\PermissionCategory;
use App\Models\Role;
use Illuminate\View\View;
use Livewire\Component;

class RoleCreate extends Component
{
    public string $name = '';

    public array $selectedPermissions = [];

    /**
     * Create a new role with the provided name and permissions.
     */
    public function createRole()
    {
        $this->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'selectedPermissions' => 'nullable|array',
            'selectedPermissions.*' => 'string|exists:permissions,id',
        ]);

        // Create the role
        $role = Role::create(['name' => $this->name]);

        // Sync the provided permissions (if any)
        if (! empty($this->selectedPermissions)) {
            $role->syncPermissions($this->selectedPermissions);
        }

        session()->flash('success', 'Role created successfully.');

        return redirect()->route('acl.index');
    }

    /**
     * Render the component view.
     */
    public function render(): View
    {
        // Load permission categories along with their permissions
        $categories = PermissionCategory::with('permissions')->get();

        return view('livewire.acl.role-create')->with([
            'categories' => $categories,
        ]);
    }
}
