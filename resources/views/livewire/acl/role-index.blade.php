<div>
    <div class="row">
        <div class="col-lg-12 col-md-12 col-12">
            <!-- Page header -->
            <div class="mb-8 d-md-flex justify-content-between align-items-center">
                <div>
                    <h1 class="mb-3 h2">Roles and Permissions</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item"><a href="#">Access Controls</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Roles</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <a href="{{ route('acl.create') }}" class="btn btn-primary">New Role</a>
                </div>
            </div>
        </div>
    </div>

    @if($roles->count())
    <div class="row">
        <div class="col-lg-4 col-md-5 col-12 mb-4 mb-md-0">
            <div class="list-group list-group-flush border rounded">
                @foreach($roles as $role)
                    <div wire:key="role-{{ $role->id }}"
                         wire:click="selectRole('{{ $role->id }}')"
                         class="list-group-item list-group-item-action d-flex justify-content-between align-items-center {{ $selectedRoleId === $role->id ? 'active' : '' }}"
                         role="button">
                        <span>{{ $role->name }}</span>
                        <span class="badge {{ $selectedRoleId === $role->id ? 'bg-light text-dark' : 'bg-primary' }} rounded-pill">
                            {{ $role->permissions->count() }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="col-lg-8 col-md-7 col-12">
            @if($selectedRoleId)
                @php
                    $selectedRole = $roles->firstWhere('id', $selectedRoleId);
                @endphp
                @if($selectedRole)
                    <div class="card shadow-sm">
                        <div class="card-header bg-light">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0 fw-semibold">{{ $selectedRole->name }}</h5>
                                <a href="{{ route('acl.show', $selectedRole) }}" class="btn btn-sm btn-outline-primary">Edit Role</a>
                            </div>
                            @if($selectedRole->permissions->isNotEmpty())
                                <input type="text"
                                       wire:model.live.debounce.300ms="permissionSearch"
                                       class="form-control form-control-sm"
                                       placeholder="Search permissions...">
                            @endif
                        </div>
                        <div class="card-body" style="max-height: 28rem; overflow-y: auto;">
                            @if($selectedRole->permissions->isEmpty())
                                <p class="text-muted mb-0">No permissions assigned to this role.</p>
                            @else
                                <x-acl.permission-checklist :permissions="$selectedRole->permissions" :search="$permissionSearch" />
                            @endif
                        </div>
                    </div>
                @endif
            @else
                <div class="d-flex align-items-center justify-content-center text-muted border rounded h-100 py-8">
                    Select a role to view its permissions.
                </div>
            @endif
        </div>
    </div>
    @else
        <p class="text-muted mx-n3 px-2 mx-lg-n6 px-lg-6">No roles found.</p>
    @endif
</div>
