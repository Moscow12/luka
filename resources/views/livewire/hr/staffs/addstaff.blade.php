<div class="custom-container">
    <x-pages.breadcrumn title="Staff Details"
        :breadcrumbs="[
        ['label' => 'Home', 'url' => route('dashboard')],
        ['label' => 'Staff List', 'url' => route('hr.stafflist')],
        ['label' =>  $modalMode === 'edit' ? 'Update Info' : 'Add New Staff']
        ]">
        <a href="{{ route('hr.stafflist') }}" class="btn btn-sm btn-primary">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to List
        </a>
    </x-pages.breadcrumn>

    <form class="needs-validation" novalidate wire:submit.prevent="save">
        @csrf
        <div class="row g-4">
            <!-- Main Content Column -->
            <div class="col-xxl-9 col-xl-8 col-12">

                <!-- Personal Information Card -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary bg-opacity-10 border-bottom">
                        <div class="d-flex align-items-center">
                            <div class="bg-primary rounded-circle p-2 me-3">
                                <i class="fa-solid fa-user text-white"></i>
                            </div>
                            <div>
                                <h5 class="mb-0 fw-semibold">Personal Information</h5>
                                <small class="text-muted">Basic details about the staff member</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        @if (session()->has('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fa-solid fa-check-circle me-2"></i>
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                        @if (session()->has('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fa-solid fa-exclamation-circle me-2"></i>
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <!-- Name Fields -->
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <h6 class="text-uppercase text-muted fw-semibold small mb-3">
                                    <i class="fa-solid fa-id-card me-1"></i> Full Name
                                </h6>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-medium">
                                    First Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control @error('first_name') is-invalid @enderror"
                                    placeholder="Enter First Name" wire:model="first_name" required />
                                @error('first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-medium">
                                    Middle Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control @error('middle_name') is-invalid @enderror"
                                    placeholder="Enter Middle Name" wire:model="middle_name" required />
                                @error('middle_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-medium">
                                    Last Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control @error('last_name') is-invalid @enderror"
                                    placeholder="Enter Last Name" wire:model="last_name" required />
                                @error('last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Personal Details -->
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <h6 class="text-uppercase text-muted fw-semibold small mb-3">
                                    <i class="fa-solid fa-info-circle me-1"></i> Personal Details
                                </h6>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-medium">
                                    Date of Birth <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input class="form-control flatpickr @error('dob') is-invalid @enderror"
                                        type="text" placeholder="Select Date" wire:model="dob" />
                                    <span class="input-group-text bg-light">
                                        <i class="fa-solid fa-calendar text-muted"></i>
                                    </span>
                                </div>
                                @error('dob') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-medium">
                                    Gender <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('gender') is-invalid @enderror" wire:model="gender">
                                    <option value="">Select Gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Other">Other</option>
                                </select>
                                @error('gender') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-medium">Marital Status</label>
                                <select class="form-select @error('marital_status') is-invalid @enderror" wire:model="marital_status">
                                    <option value="">Select Status</option>
                                    <option value="Single">Single</option>
                                    <option value="Married">Married</option>
                                    <option value="Divorced">Divorced</option>
                                    <option value="Widowed">Widowed</option>
                                    <option value="Separated">Separated</option>
                                    <option value="Never married">Never married</option>
                                    <option value="Not applicable">Not applicable</option>
                                </select>
                                @error('marital_status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Contact & Identification -->
                        <div class="row g-3">
                            <div class="col-12">
                                <h6 class="text-uppercase text-muted fw-semibold small mb-3">
                                    <i class="fa-solid fa-address-book me-1"></i> Contact & Identification
                                </h6>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-medium">Phone Number</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">
                                        <i class="fa-solid fa-phone text-muted"></i>
                                    </span>
                                    <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                        placeholder="Enter Phone Number" wire:model="phone" />
                                </div>
                                @error('phone') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-medium">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">
                                        <i class="fa-solid fa-envelope text-muted"></i>
                                    </span>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                                        placeholder="Enter Email" wire:model="email" />
                                </div>
                                @error('email') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-medium">TIN Number</label>
                                <input type="text" class="form-control @error('tin_number') is-invalid @enderror"
                                    placeholder="Enter TIN Number" wire:model="tin_number" />
                                @error('tin_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-medium">National ID</label>
                                <input type="text" class="form-control @error('national_id') is-invalid @enderror"
                                    placeholder="Enter National ID" wire:model="national_id" />
                                @error('national_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-medium">Fingerprint ID</label>
                                <input type="text" class="form-control @error('fpid') is-invalid @enderror"
                                    placeholder="Enter Fingerprint ID" wire:model="fpid" />
                                @error('fpid') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-medium">Denomination</label>
                                <select class="form-select @error('denomination_id') is-invalid @enderror"
                                    wire:model="denomination_id" wire:change="getDenominations()">
                                    <option value="">Select Denomination</option>
                                    @foreach($denominations as $denomination)
                                        <option value="{{ $denomination->id }}">{{ $denomination->name }}</option>
                                    @endforeach
                                </select>
                                @error('denomination_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Employment Information Card -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-success bg-opacity-10 border-bottom">
                        <div class="d-flex align-items-center">
                            <div class="bg-success rounded-circle p-2 me-3">
                                <i class="fa-solid fa-briefcase text-white"></i>
                            </div>
                            <div>
                                <h5 class="mb-0 fw-semibold">Employment Information</h5>
                                <small class="text-muted">Work-related details and assignments</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <!-- Employment Details -->
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <h6 class="text-uppercase text-muted fw-semibold small mb-3">
                                    <i class="fa-solid fa-file-contract me-1"></i> Employment Details
                                </h6>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-medium">Employment Type</label>
                                <select class="form-select @error('employment_type') is-invalid @enderror" wire:model="employment_type">
                                    <option value="">Select Type</option>
                                    <option value="Full-time">Full-time</option>
                                    <option value="Part-time">Part-time</option>
                                    <option value="Contract">Contract</option>
                                </select>
                                @error('employment_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-medium">Hire Date</label>
                                <div class="input-group">
                                    <input class="form-control flatpickr @error('hired_date') is-invalid @enderror"
                                        type="text" placeholder="Select Date" wire:model="hired_date" />
                                    <span class="input-group-text bg-light">
                                        <i class="fa-solid fa-calendar text-muted"></i>
                                    </span>
                                </div>
                                @error('hired_date') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-medium">Education Level</label>
                                <select class="form-select @error('education_level') is-invalid @enderror" wire:model="education_level">
                                    <option value="">Select Level</option>
                                    <option value="Primary">Primary</option>
                                    <option value="Diploma">Diploma</option>
                                    <option value="Certificate">Certificate</option>
                                    <option value="Degree">Degree</option>
                                    <option value="Masters">Masters</option>
                                    <option value="PhD">PhD</option>
                                </select>
                                @error('education_level') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Workstation Assignment -->
                        <div class="row g-3">
                            <div class="col-12">
                                <h6 class="text-uppercase text-muted fw-semibold small mb-3">
                                    <i class="fa-solid fa-building me-1"></i> Workstation Assignment
                                </h6>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-medium">Workstation</label>
                                <select class="form-select @error('workstation_id') is-invalid @enderror" wire:model="workstation_id">
                                    <option value="">Select Workstation</option>
                                    @foreach($workstations as $workstation)
                                        <option value="{{ $workstation->id }}">{{ $workstation->workstation_name }}</option>
                                    @endforeach
                                </select>
                                @error('workstation_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-medium">Department</label>
                                <select class="form-select @error('department_id') is-invalid @enderror" wire:model="department_id">
                                    <option value="">Select Department</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                                    @endforeach
                                </select>
                                @error('department_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-medium">Job Title</label>
                                <select class="form-select @error('title_id') is-invalid @enderror" wire:model="title_id">
                                    <option value="">Select Title</option>
                                    @foreach($jobtitles as $jobtitle)
                                        <option value="{{ $jobtitle->id }}">{{ $jobtitle->name }}</option>
                                    @endforeach
                                </select>
                                @error('title_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-medium">Designation</label>
                                <select class="form-select @error('designation_id') is-invalid @enderror" wire:model="designation_id">
                                    <option value="">Select Designation</option>
                                    @foreach($designations as $designation)
                                        <option value="{{ $designation->id }}">{{ $designation->name }}</option>
                                    @endforeach
                                </select>
                                @error('designation_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-medium">Employee No</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">
                                        <i class="fa-solid fa-hashtag text-muted"></i>
                                    </span>
                                    <input type="text" class="form-control @error('employee_no') is-invalid @enderror"
                                        placeholder="Enter Employee No" wire:model="employee_no" />
                                </div>
                                @error('employee_no') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            @if (!$createUserAccount)
                            <div class="col-md-4">
                                <label class="form-label fw-medium">Link to Existing User</label>
                                <select class="form-select @error('user_id') is-invalid @enderror" wire:model="user_id">
                                    <option value="">Select User</option>
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->first_name }} {{ $user->surname }}</option>
                                    @endforeach
                                </select>
                                @error('user_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <small class="text-muted">Or create a new account in the sidebar</small>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Submit Button (Mobile) -->
                <div class="d-xl-none mb-4">
                    <button type="submit" class="btn btn-primary btn-lg w-100" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="save">
                            <i class="fa-solid fa-{{ $modalMode === 'edit' ? 'save' : 'plus' }} me-2"></i>
                            {{ $modalMode === 'edit' ? 'Update Staff Info' : 'Save New Staff' }}
                        </span>
                        <span wire:loading wire:target="save">
                            <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                            Processing...
                        </span>
                    </button>
                </div>
            </div>

            <!-- Sidebar Column -->
            <div class="col-xxl-3 col-xl-4 col-12">
                <!-- Place of Birth Card -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-info bg-opacity-10 border-bottom">
                        <div class="d-flex align-items-center">
                            <div class="bg-info rounded-circle p-2 me-3">
                                <i class="fa-solid fa-location-dot text-white"></i>
                            </div>
                            <div>
                                <h5 class="mb-0 fw-semibold">Place of Birth</h5>
                                <small class="text-muted">Location details</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="d-flex flex-column gap-3">
                            <div>
                                <label class="form-label fw-medium small text-muted mb-1">Country</label>
                                <select class="form-select form-select-sm @error('country_id') is-invalid @enderror" wire:model="country_id">
                                    <option value="">Select Country</option>
                                    @foreach($countries as $country)
                                        <option value="{{ $country->id }}">{{ $country->name }}</option>
                                    @endforeach
                                </select>
                                @error('country_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div>
                                <label class="form-label fw-medium small text-muted mb-1">Region</label>
                                <select class="form-select form-select-sm @error('region_id') is-invalid @enderror"
                                    wire:model="region_id" wire:change="updateDistricts">
                                    <option value="">Select Region</option>
                                    @foreach($regions as $region)
                                        <option value="{{ $region->id }}">{{ $region->name }}</option>
                                    @endforeach
                                </select>
                                @error('region_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div>
                                <label class="form-label fw-medium small text-muted mb-1">District</label>
                                <select class="form-select form-select-sm @error('district_id') is-invalid @enderror"
                                    wire:model="district_id" wire:change="updatewards">
                                    <option value="">Select District</option>
                                    @foreach($districts as $district)
                                        <option value="{{ $district->id }}">{{ $district->name }}</option>
                                    @endforeach
                                </select>
                                @error('district_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div>
                                <label class="form-label fw-medium small text-muted mb-1">Ward</label>
                                <select class="form-select form-select-sm @error('ward_id') is-invalid @enderror"
                                    wire:model="ward_id" wire:change="updatestreet">
                                    <option value="">Select Ward</option>
                                    @foreach($wards as $ward)
                                        <option value="{{ $ward->id }}">{{ $ward->name }}</option>
                                    @endforeach
                                </select>
                                @error('ward_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div>
                                <label class="form-label fw-medium small text-muted mb-1">Village</label>
                                <select class="form-select form-select-sm @error('vilstreet_id') is-invalid @enderror" wire:model="vilstreet_id">
                                    <option value="">Select Village</option>
                                    @foreach($villages as $village)
                                        <option value="{{ $village->id }}">{{ $village->name }}</option>
                                    @endforeach
                                </select>
                                @error('vilstreet_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Photo Upload Card -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-warning bg-opacity-10 border-bottom">
                        <div class="d-flex align-items-center">
                            <div class="bg-warning rounded-circle p-2 me-3">
                                <i class="fa-solid fa-camera text-white"></i>
                            </div>
                            <div>
                                <h5 class="mb-0 fw-semibold">Staff Photo</h5>
                                <small class="text-muted">Upload profile picture</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <!-- Photo Preview Area -->
                        <div class="text-center mb-3">
                            @if ($photo)
                                @if (is_object($photo))
                                    <img src="{{ $photo->temporaryUrl() }}" alt="Preview"
                                        class="rounded-circle border shadow-sm"
                                        style="width: 120px; height: 120px; object-fit: cover;">
                                @else
                                    <img src="{{ asset('storage/' . $photo) }}" alt="Preview"
                                        class="rounded-circle border shadow-sm"
                                        style="width: 120px; height: 120px; object-fit: cover;">
                                @endif
                            @else
                                <div class="bg-light rounded-circle mx-auto d-flex align-items-center justify-content-center border"
                                    style="width: 120px; height: 120px;">
                                    <i class="fa-solid fa-user fa-3x text-muted"></i>
                                </div>
                            @endif
                        </div>

                        <!-- Upload Input -->
                        <div class="mb-2">
                            <label for="photo-upload" class="form-label small text-muted fw-medium">
                                Choose Image
                            </label>
                            <input type="file" id="photo-upload"
                                class="form-control form-control-sm @error('photo') is-invalid @enderror"
                                wire:model="photo" accept="image/*">
                            @error('photo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <!-- Loading Indicator -->
                        <div wire:loading wire:target="photo" class="text-center">
                            <div class="spinner-border spinner-border-sm text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <small class="text-muted ms-2">Uploading...</small>
                        </div>

                        <small class="text-muted d-block mt-2">
                            <i class="fa-solid fa-info-circle me-1"></i>
                            Recommended: Square image, max 2MB
                        </small>
                    </div>
                </div>

                <!-- User Account Card -->
                @if ($modalMode === 'create' || ($modalMode === 'edit' && !$hasExistingUser))
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-secondary bg-opacity-10 border-bottom">
                        <div class="d-flex align-items-center">
                            <div class="bg-secondary rounded-circle p-2 me-3">
                                <i class="fa-solid fa-user-shield text-white"></i>
                            </div>
                            <div>
                                <h5 class="mb-0 fw-semibold">User Account</h5>
                                <small class="text-muted">System login credentials</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        @if ($modalMode === 'edit' && !$hasExistingUser)
                            <div class="alert alert-info small mb-3">
                                <i class="fa-solid fa-info-circle me-1"></i>
                                This staff member does not have a user account. You can create one below.
                            </div>
                        @endif

                        <!-- Create User Account Toggle -->
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="createUserAccount"
                                wire:model.live="createUserAccount">
                            <label class="form-check-label fw-medium" for="createUserAccount">
                                {{ $modalMode === 'edit' ? 'Create user account for this staff' : 'Create user account' }}
                            </label>
                        </div>

                        @if ($createUserAccount)
                        <div class="d-flex flex-column gap-3">
                            <div>
                                <label class="form-label fw-medium small text-muted mb-1">
                                    Username <span class="text-danger">*</span>
                                </label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light">
                                        <i class="fa-solid fa-at text-muted"></i>
                                    </span>
                                    <input type="text" class="form-control form-control-sm @error('username') is-invalid @enderror"
                                        placeholder="Enter username" wire:model="username" />
                                </div>
                                @error('username') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            <div>
                                <label class="form-label fw-medium small text-muted mb-1">
                                    Password <span class="text-danger">*</span>
                                </label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light">
                                        <i class="fa-solid fa-lock text-muted"></i>
                                    </span>
                                    <input type="password" class="form-control form-control-sm @error('user_password') is-invalid @enderror"
                                        placeholder="Enter password" wire:model="user_password" />
                                </div>
                                @error('user_password') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            <div>
                                <label class="form-label fw-medium small text-muted mb-1">
                                    Confirm Password <span class="text-danger">*</span>
                                </label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light">
                                        <i class="fa-solid fa-lock text-muted"></i>
                                    </span>
                                    <input type="password" class="form-control form-control-sm @error('user_password_confirmation') is-invalid @enderror"
                                        placeholder="Confirm password" wire:model="user_password_confirmation" />
                                </div>
                                @error('user_password_confirmation') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            <div>
                                <label class="form-label fw-medium small text-muted mb-1">
                                    User Role <span class="text-danger">*</span>
                                </label>
                                <select class="form-select form-select-sm @error('selected_role') is-invalid @enderror"
                                    wire:model="selected_role">
                                    <option value="">Select Role</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}">{{ ucfirst($role->name) }}</option>
                                    @endforeach
                                </select>
                                @error('selected_role') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            @if($hasSmsProvider)
                            <div class="border-top pt-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="sendSmsNotification"
                                        wire:model="sendSmsNotification">
                                    <label class="form-check-label fw-medium" for="sendSmsNotification">
                                        <i class="fa-solid fa-mobile-screen me-1 text-success"></i>
                                        Send credentials via SMS
                                    </label>
                                </div>
                                <small class="text-muted d-block mt-2">
                                    <i class="fa-solid fa-info-circle me-1"></i>
                                    Username and password will be sent to the staff's phone number
                                </small>
                            </div>
                            @endif

                            <small class="text-muted">
                                <i class="fa-solid fa-info-circle me-1"></i>
                                Password must be at least 8 characters
                            </small>
                        </div>
                        @endif
                    </div>
                </div>
                @elseif ($modalMode === 'edit' && $hasExistingUser)
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-success bg-opacity-10 border-bottom">
                        <div class="d-flex align-items-center">
                            <div class="bg-success rounded-circle p-2 me-3">
                                <i class="fa-solid fa-user-check text-white"></i>
                            </div>
                            <div>
                                <h5 class="mb-0 fw-semibold">User Account</h5>
                                <small class="text-muted">System login credentials</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="alert alert-success small mb-0">
                            <i class="fa-solid fa-check-circle me-1"></i>
                            This staff member already has a user account linked.
                        </div>
                    </div>
                </div>
                @endif

                <!-- Submit Button (Desktop) -->
                <div class="d-none d-xl-block">
                    <div class="card shadow-sm border-primary">
                        <div class="card-body p-3">
                            <button type="submit" class="btn btn-primary w-100" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="save">
                                    <i class="fa-solid fa-{{ $modalMode === 'edit' ? 'save' : 'plus' }} me-2"></i>
                                    {{ $modalMode === 'edit' ? 'Update Staff Info' : 'Save New Staff' }}
                                </span>
                                <span wire:loading wire:target="save">
                                    <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                    Processing...
                                </span>
                            </button>
                            <a href="{{ route('hr.stafflist') }}" class="btn btn-outline-secondary w-100 mt-2">
                                <i class="fa-solid fa-times me-2"></i> Cancel
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
