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
    <table class="table table-hover table-striped">
        <thead>
        <tr>
            <th scope="col">#</th>
            <th scope="col">Role Name</th>
            <th scope="col">Permissions</th>
        </tr>
        </thead>
        <tbody>
        @foreach($roles as $index => $role)
        <tr>
            <th>{{ $index + 1 }}</th>
            <td><a href="{{ route('acl.show',$role) }}">{{ $role->name }}</a></td>
            <td>
                @php
                    $groupedPermissions = $role->permissions->groupBy(function ($permission) {
                        return $permission->category->name ?? 'Uncategorized';
                    });
                @endphp

                @foreach($groupedPermissions as $category => $permissions)
                    <h6>{{ $category }}</h6>
                    <div class="row mb-3">
                        @foreach($permissions as $permission)
                            <x-forms.checkbox
                                name="permission_display"
                                :label="$permission->name"
                                :id="'flexCheckChecked-' . $permission->id"
                                checked="true"
                                disabled="true"
                                colSm="3"
                            />
                        @endforeach
                    </div>
                    @if (! $loop->last)<hr>@endif
                @endforeach
            </td>

        </tr>
        @endforeach
        </tbody>
    </table>
    @else
        <p class="text-muted mx-n3 px-2 mx-lg-n6 px-lg-6">No roles found.</p>
    @endif
</div>
