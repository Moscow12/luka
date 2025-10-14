<div>
    <div class="row">
        <div class="col-lg-12 col-md-12 col-12">
            <!-- Page header -->
            <div class="mb-8 d-md-flex justify-content-between align-items-center">
                <div>
                    <h1 class="mb-3 h2">Setup & Configuration</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="#">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="#">Settings</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Configuration</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <!-- row -->

    <div class="row gy-5">

        <div class="col-xxl-12 col-xl-12 col-12">
            <ul class="nav nav-line-bottom mb-6 text-nowrap" id="tab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active py-2" id="workstations-tab" data-bs-toggle="pill" href="#workstations" role="tab" aria-controls="workstations" aria-selected="true">
                        <i class="fa-solid fa-briefcase"></i>
                        Work Station
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link py-2" id="departments-tab" data-bs-toggle="pill" href="#departments" role="tab" aria-controls="departments" aria-selected="true">
                        <i class="fa-solid fa-building"></i>
                        Departments
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-2" id="designations-tab" data-bs-toggle="pill" href="#designations" role="tab" aria-controls="designations" aria-selected="true">
                        <i class="fa-solid fa-person"></i>
                        Designation
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-2" id="jobtitle-tab" data-bs-toggle="pill" href="#jobtitle" role="tab" aria-controls="jobtitle" aria-selected="true">
                        <i class="fa-solid fa-user-doctor"></i>
                        Job Title
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-2" id="shift-tab" data-bs-toggle="pill" href="#shift" role="tab" aria-controls="shift" aria-selected="true">
                        <i class="fa-solid fa-clock"></i>
                        Shift
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-2" id="leavetype-tab" data-bs-toggle="pill" href="#leavetype" role="tab" aria-controls="leavetype" aria-selected="true">
                        <i class="fa-solid fa-clock"></i>
                        Leave Type
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-2" id="violations-tab" data-bs-toggle="pill" href="#violations" role="tab" aria-controls="violations" aria-selected="true">
                        <i class="fa-solid fa-check"></i>
                        Violations
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-2" id="religions-tab" data-bs-toggle="pill" href="#religions" role="tab" aria-controls="religions" aria-selected="true">
                        <i class="fa-solid fa-church"></i>
                        Religions
                    </a>
                </li>
            </ul>

            <div class="tab-content" id="tabContent">
                <div class="tab-pane fade show active" id="workstations" role="tabpanel" aria-labelledby="workstations-tab">
                    <div class="card card-md">
                        <!-- card header -->
                        <div class="card-header border-0 d-flex flex-wrap justify-content-between align-items-center py-3 pt-4 pb-0">
                            <h2>Workstations</h2>
                            <div class="d-flex align-items-center">
                                <a href="#" class="btn btn-primary">Add Workstation</a>
                            </div>
                        </div>
                        <form class="row g-6 justify-content-center" wire:submit.prevent="storeWorkstation">
                            @csrf
                            <div class="row col-xl-12 col-12">
                                <div class="row g-6 justify-content-center">
                                    <div class="col-xl-6 col-6">
                                        <div class="mb-3">
                                            <label for="validationCustom01" class="form-label">Workstation Name</label>
                                            <input type="text" class="form-control" wire:model="workstation.name"  placeholder="Workstation Name" required>
                                            <div class="valid-feedback">
                                                Looks good!
                                            </div>
                                        </div>                                    
                                    </div>
                                    <div class="col-xl-6 col-6">
                                        <div class="mb-3">
                                            <label for="validationCustom04" class="form-label">Tin Number</label>
                                            <input type="text" class="form-control" wire:model="workstation.tin_number"  placeholder="Tin Number" required>
                                            <div class="valid-feedback">
                                                Looks good!
                                            </div>
                                        </div>                                    
                                    </div>
                                    <div class="col-xl-6 col-6">
                                        <div class="mb-3">
                                            <label for="validationCustom05" class="form-label">Station Address</label>
                                            <input type="text" class="form-control" wire:model="workstation.physical_address"  placeholder="Station Address" required>
                                            <div class="valid-feedback">
                                                Looks good!
                                            </div>
                                        </div>                                    
                                    </div>
                                    <div class="col-xl-6 col-6">
                                        <div class="mb-3">
                                            <label for="validationCustom02" class="form-label">Station Location</label>
                                            <input type="text" class="form-control" wire:model="workstation.location"  placeholder="Station Location" required>
                                            <div class="valid-feedback">
                                                Looks good!
                                            </div>
                                        </div>                                    
                                    </div>
                                    <div class="col-xl-6 col-6">
                                        <div class="mb-3">
                                            <label for="validationCustom03" class="form-label">Phone Number</label>
                                            <input type="text" class="form-control" wire:model="workstation.phone_number"  placeholder="Station Phone Number" required>
                                            <div class="valid-feedback">
                                                Looks good!
                                            </div>
                                        </div>                                    
                                    </div>
                                    <!-- country -->
                                     <div class="col-xl-6 col-6">
                                        <div class="mb-3">
                                            <label for="validationCustom07" class="form-label">Country</label>
                                            <select class="form-select" wire:model="workstation.country_id"  placeholder="Country" required>
                                                <option selected disabled value="">Choose...</option>
                                                @foreach($countries as $country)
                                                    @if($country->code == 'TZ')
                                                        <option value="{{ $country->id }}" selected>{{ $country->name }}</option>
                                                    @else
                                                        <option value="{{ $country->id }}">{{ $country->name }}</option>
                                                    @endif
                                                @endforeach
                                                        
                                            </select>
                                            <div class="valid-feedback">
                                                Looks good!
                                            </div>
                                        </div>                                    
                                    </div>
                                    
                                    <div class="col-xl-6 col-6">
                                        <div class="mb-3">
                                            <label for="validationCustom06" class="form-label">Region</label>
                                            <select class="form-select" wire:model="workstation.region_id"  placeholder="Region" required>
                                                <option selected disabled value="">Choose...</option>
                                                <option>...</option>
                                            </select>                                            
                                        </div>                                    
                                    </div>
                                    <div class="col-xl-6 col-6">
                                        <div class="mb-3">
                                            <label for="validationCustom07" class="form-label">District</label>
                                            <select class="form-select" wire:model="workstation.district_id"  placeholder="District" required>
                                                <option selected disabled value="">Choose...</option>
                                                <option>...</option>
                                            </select>                                            
                                        </div>                                    
                                    </div>
                                    <div class="col-xl-6 col-6">
                                        <div class="mb-3">
                                            <label for="validationCustom08" class="form-label">Ward</label>
                                            <select class="form-select" wire:model="workstation.ward_id"  placeholder="Ward" required>
                                                <option selected disabled value="">Choose...</option>
                                                <option>...</option>
                                            </select>                                            
                                        </div>                                    
                                    </div>
                                    <div class="col-xl-6 col-6">
                                        <div class="mb-3">
                                            <label for="validationCustom09" class="form-label">Postal Code</label>
                                            <input type="text" class="form-control" wire:model="workstation.postal_code"  placeholder="Postal Code" required>
                                            <div class="valid-feedback">
                                                Looks good!
                                            </div>
                                        </div>                                    
                                    </div>
                                    
                                    
                                    <div class="col-xl-6 col-6">
                                        <div class="mb-3">
                                           <!-- button -->
                                            <div>
                                                <button type="submit" class="btn btn-primary btn-block btn-left">SUBMIT</button>
                                            </div>
                                        </div>                                    
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="tab-pane" id="departments" role="tabpanel" aria-labelledby="departments-tab">
                    <livewire:setup.departments />
                </div>
                <div class="tab-pane" id="designations" role="tabpanel" aria-labelledby="designations-tab">
                    <livewire:setup.designations />
                </div>
                <div class="tab-pane fade show" id="jobtitle" role="tabpanel" aria-labelledby="jobtitle-tab">
                    <livewire:setup.jobtitles />
                </div>
                <div class="tab-pane fade show" id="shift" role="tabpanel" aria-labelledby="shift-tab">
                    <livewire:setup.shiftmngts />
                </div>
                <div class="tab-pane fade show" id="leavetype" role="tabpanel" aria-labelledby="leavetype-tab">
                    <livewire:setup.leavemngts />
                </div>
                <div class="tab-pane fade show" id="violations" role="tabpanel" aria-labelledby="violations-tab">
                    <livewire:setup.violation-managemet />
                </div>
                <div class="tab-pane fade show" id="religions" role="tabpanel" aria-labelledby="religions-tab">
                    <livewire:setup.religion-management />
                </div>
            </div>
        </div>
    </div>
</div>