<div class="custom-container">
    <x-pages.breadcrumn title="ADD NEW STAFF"
        :breadcrumbs="[
        ['label' => 'Home', 'url' => route('dashboard')], 
        ['label' => 'Staff List', 'url' => route('hr.stafflist')],
        ['label' => 'Add New Staff']  
        ]">
        <a href="{{ route('hr.stafflist') }}" class="btn btn-sm btn-primary">Back to List</a>
    </x-pages.breadcrumn>
    <div>
        <!-- row -->
        <div class="row mb-6">
            <form class="row g-6 justify-content-center needs-validation" novalidate wire:submit.prevent="save">
                @csrf
                <div class="col-xxl-9 col-xl-8 col-md-12 col-12">
                    <!-- card -->
                    <div class="card card-lg mb-6">
                        <div class="card-header">
                            <h5 class="mb-0">Employee information</h5>
                        </div>
                        <!-- card body -->
                        <div class="card-body px-6 py-5">
                            <!-- form -->
                            <div class="row g-3">
                                <!-- form group -->
                                <div class="col-md-4 col-12">
                                    <label class="form-label">
                                        First Name
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" placeholder="Enter First Name" wire:model="first_name" required />
                                </div>
                                <!-- form group -->
                                <div class="col-md-4 col-12">
                                    <label class="form-label">
                                        Middle Name
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" placeholder="Enter Middle Name" wire:model="middle_name" required />
                                </div>
                                <!-- form group -->
                                <div class="col-md-4 col-12">
                                    <label class="form-label">
                                        Last Name
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" placeholder="Enter Last Name" wire:model="last_name" required />
                                </div>
                                <!-- form group -->

                                <!-- form group -->
                                <div class="col-md-4 col-12">
                                    <label class="form-label">
                                        Date of Birth
                                        <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group me-3 flatpickr rounded">
                                        <input class="form-control" type="date" placeholder="Select Date" aria-describedby="basic-addon2" wire:model="dob" />

                                        <span class="input-group-text text-secondary" id="basic-addon2">
                                            <i class="fa-solid fa-calendar"></i>
                                        </span>
                                    </div>
                                </div>
                                <!-- form group -->
                                <div class="col-md-4 col-12">
                                    <label class="form-label">
                                        Gender
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" wire:model="gender">
                                        <option selected>Select Gender</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <!-- form group -->

                                <!-- form group -->
                                <div class="col-md-4 col-12">
                                    <label class="form-label">Marital Status</label>
                                    <select class="form-select" wire:model="marital_status">
                                        <option value="">Marital Status</option>
                                        <option value="Single">Single</option>
                                        <option value="Married">Married</option>
                                        <option value="Divorced">Divorced</option>
                                        <option value="Widowed">Widowed</option>
                                        <option value="Separated">Separated</option>
                                        <option value="Never married">Never married</option>
                                        <option value="Not applicable">Not applicable</option>
                                    </select>
                                </div>
                                <!-- form group -->
                                <div class="col-md-4 col-12">
                                    <label class="form-label">Phone number</label>
                                    <input type="text" class="form-control" placeholder="Enter Phone number" wire:model="phone" required />
                                </div>

                                <div class="col-md-4 col-12">
                                    <label class="form-label">Tin number</label>
                                    <input type="text" class="form-control" placeholder="Enter Tin number" wire:model="tin_number" required />
                                </div>
                                <div class="col-md-4 col-12">
                                    <label class="form-label">National ID</label>
                                    <input type="text" class="form-control" placeholder="Enter National ID" wire:model="national_id" required />
                                </div>
                                <div class="col-md-4 col-12">
                                    <label class="form-label">Email</label>
                                    <input type="text" class="form-control" placeholder="Enter Email" wire:model="email" required />
                                </div>
                                <div class="col-md-4 col-12">
                                    <label class="form-label">Employment Type</label>
                                    <select class="form-select" wire:model="employment_type">
                                        <option value="">Employment Type</option>
                                        <option value="Full-time">Full-time</option>
                                        <option value="Part-time">Part-time</option>
                                        <option value="Contract">Contract</option>
                                    </select>
                                </div>
                                <div class="col-md-4 col-12">
                                    <label class="form-label">Hire Date</label>
                                    <div class="input-group me-3 flatpickr rounded">
                                        <input class="form-control" type="date" placeholder="Select Date" aria-describedby="basic-addon2" wire:model="hired_date" />

                                        <span class="input-group-text text-secondary" id="basic-addon2">
                                            <i class="fa-solid fa-calendar"></i>
                                        </span>
                                    </div>
                                </div>
                                <!-- form group -->
                                <div class="col-md-4 col-12">
                                    <label class="form-label">Education Level</label>
                                    <select class="form-select" wire:model="education_level">
                                        <option value="">Education Level</option>
                                        <option value="Primary">Primary</option>
                                        <option value="Diploma">Diploma</option>
                                        <option value="Certificate">Certificate</option>
                                        <option value="Degree">Degree</option>
                                        <option value="Masters">Masters</option>
                                        <option value="PhD">PhD</option>
                                    </select>
                                </div>
                                <!-- form group -->

                                <!-- form group -->
                                <div class="col-md-4 col-12">
                                    <label class="form-label">Fingerprint ID</label>
                                    <input type="text" class="form-control" placeholder="Enter Fingerprint ID" wire:model="fpid" required />
                                </div>
                                <!-- form group -->
                                <div class="col-md-4 col-12">
                                    <label class="form-label">Denomination</label>
                                    <select name="denomination_id" id="denomination_id" class="form-select" wire:model="denomination_id">
                                        <option value="">Select denomination</option>
                                        @foreach($denominations as $denomination)
                                        <option value="{{ $denomination->id }}">{{ $denomination->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <!-- form group -->

                            </div>
                        </div>
                    </div>
                    <div class="card card-lg  mb-6">
                        <div class="card-header">
                            <h5 class="mb-0">Employee Workstations</h5>
                        </div>
                        <!-- card body -->
                        <div class="card-body px-6 py-5">
                            <div class="row g-3">
                                <div class="col-md-4 col-12">
                                    <div class="mb-2">Workstation Name</div>
                                    <select name="workstation_id" id="workstation_id" class="form-select" wire:model="workstation_id">
                                        <option value="">Workstation Name</option>
                                        @foreach($workstations as $workstation)
                                        <option value="{{ $workstation->id }}">{{ $workstation->workstation_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <!-- form group -->
                                <!-- department -->
                                <div class="col-md-4 col-12">
                                    <div class="mb-2">Department</div>
                                    <select name="department_id" id="department_id" class="form-select" wire:model="department_id">
                                        <option value="">Department</option>
                                        @foreach($departments as $department)
                                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <!-- form group -->
                                <!-- title -->
                                <div class="col-md-4 col-12">
                                    <div class="mb-2">Title</div>
                                    <select name="title_id" id="title_id" class="form-select" wire:model="title_id">
                                        <option value="">Title</option>
                                        @foreach($jobtitles as $jobtitle)
                                        <option value="{{ $jobtitle->id }}">{{ $jobtitle->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <!-- form group -->
                                <div class="col-md-4 col-12">
                                    <div class="mb-2">Designation</div>
                                    <select name="designation_id" id="designation_id" class="form-select" wire:model="designation_id">
                                        <option value="">Designation</option>
                                        @foreach($designations as $designation)
                                        <option value="{{ $designation->id }}">{{ $designation->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <!-- form group -->
                                <!-- employee_no -->
                                <div class="col-md-4 col-12">
                                    <div class="mb-2">Employee No</div>
                                    <input type="text" class="form-control" placeholder="Enter Employee No" wire:model="employee_no" required />
                                </div>

                                <!-- login as -->
                                <div class="col-md-4 col-12">
                                    <div class="mb-2">Login As</div>
                                    <select name="user_id" id="user_id" class="form-select" wire:model="user_id">
                                        <option value="">Login As</option>
                                        @foreach ($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->first_name }} {{ $user->last_name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="mt-4 d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">Create</button>
                    </div>
                </div>
                <div class="col-xxl-3 col-xl-4">
                    <div class="card mb-5 card-lg">
                        <div class="card-body px-6 py-5">
                            <div class="mb-5">
                                <h5 class="mb-0">Place of Birth</h5>
                            </div>
                            <div class="mb-2">Country</div>
                            <select class="form-select" wire:model="country_id">
                                <option value="">Country</option>
                                @foreach($countries as $country)
                                <option value="{{ $country->id }}">{{ $country->name }}</option>
                                @endforeach
                            </select>
                            <div class="mb-2">Region</div>
                            <select class="form-select" wire:model="region_id" wire:change="updateDistricts">
                                <option value="">Region</option>
                                @foreach($regions as $region)
                                <option value="{{ $region->id }}">{{ $region->name }}</option>
                                @endforeach
                            </select>
                            <div class="mb-2">District</div>
                            <select class="form-select" wire:model="district_id" wire:change="updatewards">
                                <option value="">District</option>
                                @foreach($districts as $district)
                                <option value="{{ $district->id }}">{{ $district->name }}</option>
                                @endforeach
                            </select>
                            <div class="mb-2">Ward</div>
                            <select class="form-select" wire:model="ward_id" wire:change="updatestreet">
                                <option value="">Ward</option>
                                @foreach($wards as $ward)
                                <option value="{{ $ward->id }}">{{ $ward->name }}</option>
                                @endforeach
                            </select>
                            <div class="mb-2">Village</div>
                            <select class="form-select" wire:model="vilstreet_id">
                                <option value="">Village</option>
                                @foreach($villages as $village)
                                <option value="{{ $village->id }}">{{ $village->name }}</option>
                                @endforeach
                            </select>

                        </div>
                    </div>
                    <div class="card card-lg">
                        <div class="card-body px-6 py-5">
                            <div class="mb-5">
                                <h5 class="mb-0">Photo</h5>
                            </div>
                            <label class="form-label">Upload Photo</label>
                            <input type="file" class="form-control" wire:model="photo">
                            {{-- Show loading indicator while uploading --}}
                            <div wire:loading wire:target="photo" class="text-muted mt-2">
                                Uploading...
                            </div>

                            {{-- Show uploaded photo preview --}}
                            @if ($photo)
                            <div class="mt-3">
                                <p class="mb-1">Preview:</p>
                                <img src="{{ $photo->temporaryUrl() }}" alt="Preview" class="img-thumbnail" width="150">
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>