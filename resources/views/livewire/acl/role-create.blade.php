<div>
    <div class="row">
        <div class="col-lg-12 col-md-12 col-12">
            <!-- Page header -->
            <div class="mb-8 d-md-flex justify-content-between align-items-center">
                <div>
                    <h1 class="mb-3 h2">New Role</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('acl.index') }}">Roles</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Create Role</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <a href="{{ route('acl.index') }}" class="btn btn-phoenix-primary me-2 px-6">Cancel</a>
                    <button form="role-create-form" type="submit" class="btn btn-primary">Create Role</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Role Create Form -->
    <form id="role-create-form" wire:submit.prevent="createRole">
        <!-- Role Name -->
        <x-forms.input
            name="name"
            label="Role Name"
            placeholder="Role Name"
            wire:model.defer="name"
            colSm="12"
            required
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
        <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-primary">Create Role</button>
        </div>
    </form>
</div>
