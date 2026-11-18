<?php

namespace App\Livewire\Acl;

use App\Livewire\Concerns\ConfirmsAdminPassword;
use App\Models\PermissionCategory;
use App\Models\Role;
use App\Services\Acl\AccessControl;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Edit or delete a role. Needs manage-roles, the role must be within the actor's own
 * access, a role the actor holds cannot be edited or deleted by them, and only permissions
 * the actor holds can be added.
 */
class RoleShow extends Component
{
    use ConfirmsAdminPassword;

    public Role $role;

    public string $name = '';

    public array $selectedPermissions = [];

    public function boot(): void
    {
        abort_unless(Auth::user()?->can(AccessControl::MANAGE_ROLES), 403);
    }

    protected function adminConfirmableActions(): array
    {
        return ['updateRole', 'deleteRole'];
    }

    public function mount(Role $role): void
    {
        $this->role = $role;
        $this->name = $role->name;
        $this->selectedPermissions = $role->permissions->pluck('id')->toArray();
    }

    public function updateRole()
    {
        if ($message = $this->refusal()) {
            session()->flash('error', $message);

            return null;
        }

        $this->validate([
            'name' => 'required|string|max:255|unique:roles,name,'.$this->role->id,
            'selectedPermissions' => 'nullable|array',
            'selectedPermissions.*' => 'string|exists:permissions,id',
        ]);

        if (! AccessControl::canAssignPermissions(Auth::user(), $this->selectedPermissions)) {
            $this->addError('selectedPermissions', 'You can only include permissions you hold yourself.');

            return null;
        }

        if (! $this->adminPasswordConfirmed()) {
            return $this->requireAdminPassword('updateRole');
        }

        $before = ['name' => $this->role->name, 'permissions' => $this->role->permissions()->pluck('name')->sort()->values()->all()];

        $this->role->name = $this->name;
        $this->role->save();
        $this->role->syncPermissions($this->selectedPermissions);

        activity('role')->causedBy(Auth::user())->performedOn($this->role)->event('updated')
            ->withProperties([
                'old' => $before,
                'attributes' => ['name' => $this->role->name, 'permissions' => $this->role->permissions()->pluck('name')->sort()->values()->all()],
            ])
            ->log("Role updated: {$this->role->name}");

        session()->flash('success', 'Role updated successfully.');

        return redirect()->route('acl.index');
    }

    public function deleteRole()
    {
        if ($message = $this->refusal()) {
            session()->flash('error', $message);

            return null;
        }

        if (! $this->adminPasswordConfirmed()) {
            return $this->requireAdminPassword('deleteRole');
        }

        $name = $this->role->name;
        activity('role')->causedBy(Auth::user())->performedOn($this->role)->event('deleted')
            ->withProperties(['permissions' => $this->role->permissions()->pluck('name')->all()])
            ->log("Role deleted: {$name}");

        $this->role->delete();

        session()->flash('success', 'Role deleted successfully.');

        return redirect()->route('acl.index');
    }

    /** Why the actor may not change this role, or null when they may. */
    protected function refusal(): ?string
    {
        $actor = Auth::user();

        if (AccessControl::holdsRole($actor, $this->role) && ! $actor->isSuperAdmin()) {
            return 'You cannot change a role you hold yourself. Ask another administrator.';
        }

        if (! AccessControl::canGrantRole($actor, $this->role)) {
            return 'This role includes permissions you do not hold, so you cannot change it.';
        }

        return null;
    }

    public function render(): View
    {
        return view('livewire.acl.role-show')->with([
            'categories' => PermissionCategory::with('permissions')->get(),
            'assignable' => AccessControl::permissionNames(Auth::user())->all(),
            'isSuperAdmin' => Auth::user()->isSuperAdmin(),
            'readOnlyReason' => $this->refusal(),
        ]);
    }
}
