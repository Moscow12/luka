<div>
    <!-- Breadcrumb Navigation -->
    <div class="row">
        <div class="col-lg-12 col-md-12 col-12">
            <!-- Page header -->
            <div class="mb-8 d-md-flex justify-content-between align-items-center">
                <div>
                    <h1 class="mb-3 h2">Edit Role</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('acl.index') }}">Roles</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Edit Role</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <a href="{{ route('acl.index') }}" class="btn btn-phoenix-primary me-2 px-6">Cancel</a>
                    <button form="role-edit-form" type="submit" class="btn btn-primary">Update Role</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Role Edit Form -->
    <form id="role-edit-form" wire:submit.prevent="updateRole">
        <!-- Role Name -->
        <x-forms.input
            name="name"
            label="Role Name"
            placeholder="Role Name"
            wire:model.defer="name"
            colSm="12"
        />

        <!-- Permissions Grouped by Category -->
        <h4 class="mb-3">Permissions</h4>
        @foreach($categories as $category)
            <h6>{{ $category->name }}</h6>
            <div class="row mb-3">
                @foreach($category->permissions as $permission)
                    <x-forms.checkbox
                        name="selectedPermissions"
                        :label="$permission->name"
                        :value="$permission->id"
                        :id="'permission-' . $permission->id"
                        wire:model="selectedPermissions"
                        colSm="3"
                    />
                @endforeach
            </div>
            @if (! $loop->last)<hr>@endif
        @endforeach

        <!-- Form Submit Button -->
        <div class="d-flex justify-content-between">
            <button type="button" wire:click="deleteRole" onclick="return confirm('Are you sure you want to delete this role? This action cannot be undone.')" class="btn btn-danger me-2">Delete Role</button>
            <button type="submit" class="btn btn-primary">Update Role</button>
        </div>
    </form>
</div>
