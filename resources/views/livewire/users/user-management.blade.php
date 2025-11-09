<div class="custom-container">
    {{-- Success Message --}}
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i>
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">User Management</h2>
            <p class="text-muted mb-0">Manage system users, roles, and permissions</p>
        </div>
        <button wire:click="createUser" class="btn btn-primary">
            <i class="fa-solid fa-plus me-1"></i> Add New User
        </button>
    </div>

    {{-- Main Card --}}
    <div class="card card-lg">
        {{-- Card Header with Search and Filters --}}
        <div class="card-header border-bottom">
            <div class="row g-3 align-items-center">
                {{-- Search Bar --}}
                <div class="col-12 col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input type="search" wire:model.live.debounce.300ms="search" class="form-control"
                            placeholder="Search users by name, email, username..." />
                        @if ($search)
                            <button wire:click="$set('search', '')" class="btn btn-outline-secondary" type="button">
                                <i class="fa-solid fa-times"></i>
                            </button>
                        @endif
                    </div>
                </div>

                {{-- Actions --}}
                <div class="col-12 col-md-8">
                    <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                        {{-- Filter Toggle Button --}}
                        <button wire:click="toggleFilters" type="button"
                            class="btn {{ $showFilters ? 'btn-primary' : 'btn-white' }}">
                            <i class="fa-solid fa-filter me-1"></i>
                            {{ $showFilters ? 'Hide' : 'Show' }} Filters
                            @if ($role || $gender || ($status !== 'active'))
                                <span class="badge bg-danger ms-1">
                                    {{ collect([$role, $gender, $status !== 'active'])->filter()->count() }}
                                </span>
                            @endif
                        </button>

                        {{-- Reset Filters --}}
                        @if ($search || $role || $gender || ($status !== 'active'))
                            <button wire:click="resetFilters" type="button" class="btn btn-outline-secondary">
                                <i class="fa-solid fa-rotate-left me-1"></i> Reset
                            </button>
                        @endif

                        {{-- Status Toggle --}}
                        <div class="btn-group" role="group">
                            <button type="button" wire:click="$set('status', 'active')"
                                class="btn btn-sm {{ $status === 'active' ? 'btn-primary' : 'btn-outline-primary' }}">
                                Active
                            </button>
                            <button type="button" wire:click="$set('status', 'inactive')"
                                class="btn btn-sm {{ $status === 'inactive' ? 'btn-warning' : 'btn-outline-warning' }}">
                                Disabled
                            </button>
                            <button type="button" wire:click="$set('status', 'all')"
                                class="btn btn-sm {{ $status === 'all' ? 'btn-secondary' : 'btn-outline-secondary' }}">
                                All
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Advanced Filters (Collapsible) --}}
            @if ($showFilters)
                <div class="row g-3 mt-2 pt-3 border-top">
                    <div class="col-12 col-md-6">
                        <label class="form-label small text-muted mb-1">Role</label>
                        <select wire:model.live="role" class="form-select">
                            <option value="">All Roles</option>
                            @foreach ($roles as $r)
                                <option value="{{ $r->name }}">{{ ucfirst($r->name) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label small text-muted mb-1">Gender</label>
                        <select wire:model.live="gender" class="form-select">
                            <option value="">All Genders</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>
            @endif
        </div>

        {{-- Table Section --}}
        <div class="table-responsive" style="min-height: 400px;">
            <table class="table table-hover table-centered align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width: 50px;">#</th>
                        <th style="width: 70px;">Photo</th>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Gender</th>
                        <th>Roles</th>
                        <th>Status</th>
                        <th class="text-end" style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $index => $user)
                        <tr wire:key="user-{{ $user->id }}">
                            <td class="text-center text-muted">
                                {{ $users->firstItem() + $index }}
                            </td>
                            <td>
                                @if ($user->profile_picture)
                                    <img src="{{ asset('storage/' . $user->profile_picture) }}"
                                        alt="{{ $user->full_name }}" class="rounded-circle" width="40"
                                        height="40" style="object-fit: cover;">
                                @else
                                    <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center"
                                        style="width: 40px; height: 40px; font-size: 14px;">
                                        {{ strtoupper(substr($user->first_name ?? 'U', 0, 1)) }}{{ strtoupper(substr($user->surname ?? 'U', 0, 1)) }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-semibold text-dark">{{ $user->full_name }}</span>
                                    @if ($user->reg_number)
                                        <small class="text-muted">{{ $user->reg_number }}</small>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">{{ $user->username }}</span>
                            </td>
                            <td>
                                <a href="mailto:{{ $user->email }}" class="text-decoration-none text-dark">
                                    <i class="fa-solid fa-envelope me-1"></i>{{ $user->email }}
                                </a>
                            </td>
                            <td>
                                @if ($user->phone_number)
                                    <a href="tel:{{ $user->phone_number }}" class="text-decoration-none text-dark">
                                        <i class="fa-solid fa-phone me-1"></i>{{ $user->phone_number }}
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if ($user->gender === 'Male')
                                    <i class="fa-solid fa-mars text-primary me-1"></i>
                                @elseif($user->gender === 'Female')
                                    <i class="fa-solid fa-venus text-danger me-1"></i>
                                @endif
                                {{ $user->gender }}
                            </td>
                            <td>
                                @if ($user->roles->count() > 0)
                                    @foreach ($user->roles as $role)
                                        <span class="badge bg-info text-dark me-1">{{ $role->name }}</span>
                                    @endforeach
                                @else
                                    <span class="text-muted">No roles</span>
                                @endif
                            </td>
                            <td>
                                @if ($user->trashed())
                                    <span class="badge bg-warning">Disabled</span>
                                @else
                                    <span class="badge bg-success">Active</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-1 justify-content-end">
                                    <button wire:click="editUser('{{ $user->id }}')"
                                        class="btn btn-sm btn-ghost-secondary rounded-circle" title="Edit">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>

                                    @if ($user->trashed())
                                        <button wire:click="enableUser('{{ $user->id }}')"
                                            wire:confirm="Are you sure you want to enable this user?"
                                            class="btn btn-sm btn-ghost-success rounded-circle" title="Enable">
                                            <i class="fa-solid fa-check-circle"></i>
                                        </button>
                                    @else
                                        <button wire:click="disableUser('{{ $user->id }}')"
                                            wire:confirm="Are you sure you want to disable this user?"
                                            class="btn btn-sm btn-ghost-warning rounded-circle" title="Disable">
                                            <i class="fa-solid fa-ban"></i>
                                        </button>
                                    @endif

                                    <button wire:click="deleteUserPermanently('{{ $user->id }}')"
                                        wire:confirm="Are you sure you want to permanently delete this user? This action cannot be undone!"
                                        class="btn btn-sm btn-ghost-danger rounded-circle" title="Delete Permanently">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-5">
                                <div class="d-flex flex-column align-items-center justify-content-center">
                                    <i class="fa-solid fa-users text-muted mb-3" style="font-size: 48px;"></i>
                                    <h5 class="text-muted">No Users Found</h5>
                                    <p class="text-muted">
                                        @if ($search || $role || $gender || ($status !== 'active'))
                                            Try adjusting your filters or search query
                                        @else
                                            Start by adding new users to the system
                                        @endif
                                    </p>
                                    @if (!$search && !$role && !$gender && $status === 'active')
                                        <button wire:click="createUser" class="btn btn-primary mt-2">
                                            <i class="fa-solid fa-plus me-1"></i> Add New User
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Card Footer with Pagination --}}
        <div class="card-footer border-top">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                {{-- Results Info --}}
                <div class="text-muted">
                    @if ($users->total() > 0)
                        Showing {{ $users->firstItem() }} to {{ $users->lastItem() }} of {{ $users->total() }}
                        users
                    @else
                        No users found
                    @endif
                </div>

                {{-- Pagination and Per Page --}}
                <div class="d-flex flex-column flex-sm-row align-items-center gap-3">
                    {{-- Per Page Selector --}}
                    <div class="d-flex align-items-center gap-2">
                        <label class="form-label mb-0 text-nowrap small">Rows per page:</label>
                        <select wire:model.live="perPage" class="form-select form-select-sm" style="width: auto;">
                            <option value="5">5</option>
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                    </div>

                    {{-- Pagination Links --}}
                    <div>
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Add/Edit User Modal --}}
    @if ($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fa-solid fa-user me-2"></i>
                            {{ $editMode ? 'Edit User' : 'Add New User' }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="saveUser">
                            {{-- Personal Information --}}
                            <h6 class="text-muted mb-3">
                                <i class="fa-solid fa-user-circle me-1"></i> Personal Information
                            </h6>
                            <div class="row g-3 mb-4">
                                <div class="col-md-3">
                                    <label class="form-label">Salutation</label>
                                    <select wire:model="salutation" class="form-select">
                                        <option value="">Select</option>
                                        <option value="Mr">Mr</option>
                                        <option value="Mrs">Mrs</option>
                                        <option value="Ms">Ms</option>
                                        <option value="Dr">Dr</option>
                                        <option value="Prof">Prof</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">First Name <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="first_name" class="form-control @error('first_name') is-invalid @enderror">
                                    @error('first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Middle Name</label>
                                    <input type="text" wire:model="middle_name" class="form-control">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Surname <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="surname" class="form-control @error('surname') is-invalid @enderror">
                                    @error('surname') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <label class="form-label">Gender <span class="text-danger">*</span></label>
                                    <select wire:model="gender_input" class="form-select @error('gender_input') is-invalid @enderror">
                                        <option value="">Select Gender</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                        <option value="Other">Other</option>
                                    </select>
                                    @error('gender_input') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Date of Birth</label>
                                    <input type="text" wire:model="dob" class="form-control flatpickr @error('dob') is-invalid @enderror" placeholder="Select Date">
                                    @error('dob') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Phone Number</label>
                                    <input type="tel" wire:model="phone_number" class="form-control @error('phone_number') is-invalid @enderror">
                                    @error('phone_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            {{-- Account Information --}}
                            <h6 class="text-muted mb-3 pt-3 border-top">
                                <i class="fa-solid fa-key me-1"></i> Account Information
                            </h6>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" wire:model="email" class="form-control @error('email') is-invalid @enderror">
                                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Username <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="username" class="form-control @error('username') is-invalid @enderror">
                                    @error('username') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label">
                                        Password
                                        @if (!$editMode)
                                            <span class="text-danger">*</span>
                                        @endif
                                    </label>
                                    <input type="password" wire:model="password" class="form-control @error('password') is-invalid @enderror"
                                        placeholder="{{ $editMode ? 'Leave blank to keep current password' : '' }}">
                                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Confirm Password</label>
                                    <input type="password" wire:model="password_confirmation" class="form-control">
                                </div>
                            </div>

                            {{-- Roles --}}
                            <div class="mb-4">
                                <label class="form-label">Assign Roles</label>
                                <div class="row g-2">
                                    @foreach ($roles as $role)
                                        <div class="col-md-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" wire:model="selectedRoles"
                                                    value="{{ $role->id }}" id="role-{{ $role->id }}">
                                                <label class="form-check-label" for="role-{{ $role->id }}">
                                                    {{ ucfirst($role->name) }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Additional Information --}}
                            <h6 class="text-muted mb-3 pt-3 border-top">
                                <i class="fa-solid fa-info-circle me-1"></i> Additional Information
                            </h6>
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Registration Number</label>
                                    <input type="text" wire:model="reg_number" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Qualification</label>
                                    <input type="text" wire:model="qualification" class="form-control">
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-12">
                                    <label class="form-label">Address</label>
                                    <textarea wire:model="address" class="form-control" rows="2"></textarea>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">District</label>
                                    <input type="text" wire:model="district" class="form-control">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Region</label>
                                    <input type="text" wire:model="region" class="form-control">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Country</label>
                                    <input type="text" wire:model="country" class="form-control">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Postal Code</label>
                                    <input type="text" wire:model="postal_code" class="form-control">
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">
                            <i class="fa-solid fa-times me-1"></i> Cancel
                        </button>
                        <button type="button" class="btn btn-primary" wire:click="saveUser">
                            <i class="fa-solid fa-save me-1"></i>
                            {{ $editMode ? 'Update User' : 'Create User' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Loading Indicator --}}
    <div wire:loading class="position-fixed top-50 start-50 translate-middle" style="z-index: 9999;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
</div>
