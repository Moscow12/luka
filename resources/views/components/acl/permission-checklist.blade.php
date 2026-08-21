@props([
    'permissions',
    'search' => '',
])

@php
    $filteredPermissions = $search !== ''
        ? $permissions->filter(fn ($permission) => str_contains(strtolower($permission->name), strtolower($search)))
        : $permissions;

    $groupedPermissions = $filteredPermissions->groupBy(function ($permission) {
        return $permission->category->name ?? 'Uncategorized';
    });
@endphp

@if($groupedPermissions->isEmpty())
    <p class="text-muted mb-0">No permissions match "{{ $search }}".</p>
@else
    @foreach($groupedPermissions as $category => $categoryPermissions)
        <h6>{{ $category }}</h6>
        <div class="row mb-3">
            @foreach($categoryPermissions as $permission)
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
@endif
