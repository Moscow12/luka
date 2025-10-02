<?php

namespace App\Livewire\Acl;

use App\Models\Role;
use Livewire\Component;
use Illuminate\View\View;

class RoleIndex extends Component
{
    public function render(): View
    {
        
        $roles = Role::with('permissions.category')->get();

        return view('livewire.acl.role-index')->with([
            'roles' => $roles,
        ]);
    }
}
