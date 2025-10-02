<?php

namespace App\Livewire\Acl;

use App\Models\PermissionCategory;
use App\Models\Role;
use Illuminate\View\View;
use Livewire\Component;

class RoleShow extends Component
{
    public Role $role;

    public string $name = '';

    public array $selectedPermissions = [];

    /**
     * Mount the component with the role instance.
     */
    public function mount(Role $role): void
    {
        $this->role = $role;
        $this->name = $role->name;
        $this->selectedPermissions = $role->permissions->pluck('id')->toArray();
    }

    /**
     * Validate and update the role.
     *
     * @return \Illuminate\Http\RedirectResponse|null
     */
    public function updateRole()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'selectedPermissions' => 'nullable|array',
            'selectedPermissions.*' => 'string|exists:permissions,id',
        ]);

        // Update the role name
        $this->role->name = $this->name;
        $this->role->save();

        // Sync the selected permissions
        $this->role->syncPermissions($this->selectedPermissions);

        session()->flash('success', 'Role updated successfully.');

        return redirect()->route('acl.index');
    }

    /**
     * Delete the role.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteRole()
    {
        // Delete the role
        $this->role->delete();

        session()->flash('success', 'Role deleted successfully.');

        return redirect()->route('acl.index');
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        // Eager-load permission categories with their permissions
        $categories = PermissionCategory::with('permissions')->get();

        return view('livewire.acl.role-show')->with([
            'categories' => $categories,
        ]);
    }
}
