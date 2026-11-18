<?php

namespace App\Livewire\Acl;

use App\Livewire\Concerns\ConfirmsAdminPassword;
use App\Models\PermissionCategory;
use App\Models\Role;
use App\Services\Acl\AccessControl;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

/** Create a role. Needs manage-roles; only permissions the actor holds can be included. */
class RoleCreate extends Component
{
    use ConfirmsAdminPassword;

    public string $name = '';

    public array $selectedPermissions = [];

    public function boot(): void
    {
        abort_unless(Auth::user()?->can(AccessControl::MANAGE_ROLES), 403);
    }

    protected function adminConfirmableActions(): array
    {
        return ['createRole'];
    }

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

        if (! AccessControl::canAssignPermissions(Auth::user(), $this->selectedPermissions)) {
            $this->addError('selectedPermissions', 'You can only include permissions you hold yourself.');

            return null;
        }

        if (! $this->adminPasswordConfirmed()) {
            return $this->requireAdminPassword('createRole');
        }

        $role = Role::create(['name' => $this->name]);

        if (! empty($this->selectedPermissions)) {
            $role->syncPermissions($this->selectedPermissions);
        }

        activity('role')->causedBy(Auth::user())->performedOn($role)->event('created')
            ->withProperties(['permissions' => $role->permissions()->pluck('name')->all()])
            ->log("Role created: {$role->name}");

        session()->flash('success', 'Role created successfully.');

        return redirect()->route('acl.index');
    }

    public function render(): View
    {
        return view('livewire.acl.role-create')->with([
            'categories' => PermissionCategory::with('permissions')->get(),
            'assignable' => AccessControl::permissionNames(Auth::user())->all(),
            'isSuperAdmin' => Auth::user()->isSuperAdmin(),
        ]);
    }
}
