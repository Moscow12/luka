<?php

namespace App\Livewire\Acl;

use App\Models\Role;
use Illuminate\View\View;
use Livewire\Component;

class RoleIndex extends Component
{
    public ?string $selectedRoleId = null;

    public string $permissionSearch = '';

    public function selectRole(string $roleId): void
    {
        $this->selectedRoleId = $this->selectedRoleId === $roleId ? null : $roleId;
        $this->permissionSearch = '';
    }

    public function render(): View
    {
        $roles = Role::with('permissions.category')->get();

        return view('livewire.acl.role-index')->with([
            'roles' => $roles,
        ]);
    }
}
