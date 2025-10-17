<div>
    <div>
        <h5 class="mb-5">Workstation</h5>
    </div>
    <div class="d-flex flex-column gap-6">
        <div class="d-flex flex-md-row flex-column gap-2 justify-content-between">
            <div class="d-flex flex-row gap-3 align-items-center">
                <div>
                    <form>
                        <input class="form-control" type="search" value="" placeholder="Search" />
                    </form>
                </div>
                <a href="#!" class="text-inherit">
                    <i class="fa-solid fa-filter"></i>
                    <span>Filter</span>
                </a>
            </div>
            <div>
                <button class="btn btn-primary d-flex flex-row gap-1 align-items-center" wire:click="$set('showModal', true)">
                    <i class="fa-solid fa-plus"></i>
                    ADD WORKSTATION
                </button>
            </div>
        </div>
        <div>
            <div class="card card-lg overflow-hidden" id="taskTable" data-list="name">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        @if (session()->has('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                        @endif

                        <table class="table text-nowrap mb-0 table-centered table-hover" data-check-container="">
                            <thead>
                                <tr>
                                    <th>
                                        #
                                    </th>
                                    <th class="listjs-sorter" data-sort="task_title"> name</th>
                                    <th class="listjs-sorter" data-sort="task_type">Description</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody class="list">
                                @php
                                $number = 1;
                                @endphp
                                @foreach($workstations as $station)
                                <tr>
                                    <td>
                                        {{ $number++ }}
                                    </td>
                                    <td class="name">{{ $station->workstation_name }}</td>
                                    <td class="task_type">{{ $station->tin_number }}</td>
                                    <td class="task_type">{{ $station->email_address }}</td>
                                    <td class="task_type">{{ $station->ward->name }}</td>
                                    <td class="task_type">{{ $station->region->name }}</td>
                                    <td class="task_type">{{ $station->district->name }}</td>
                                    <td class="task_type">{{ $station->country->name }}</td>
                                    <td class="task_type">{{ $station->postal_code }}</td>
                                    <td class="task_type">{{ $station->physical_address }}</td>
                                    <td class="task_type">{{ $station->phone_number }}</td>
                                    <td class="task_type">{{ $station->address }}</td>

                                    <td>
                                        <button class="btn btn-sm btn-warning" wire:click="openModal('edit','{{ $station->id }}')">Edit</button>
                                        <button class="btn btn-sm btn-danger" wire:click="delete('{{ $station->id }}')"
                                            onclick="return confirm('Delete this department?')">Delete</button>
                                    </td>

                                </tr>
                                @endforeach


                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <!-- Modal -->
    <div class="modal fade @if($showModal) show d-block @endif" tabindex="-1"
        @if($showModal) style="background: rgba(0,0,0,0.5);" @endif>
        <div class="modal-dialog  modal-xl">
            <div class="modal-content">
                <form wire:submit.prevent="save">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            {{ $modalMode === 'edit' ? 'Edit Workstation' : 'Add Workstation' }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label>Workstation Name</label>
                                    <input type="text" class="form-control" wire:model="name" placeholder="Enter Workstation Name">
                                    @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label>Address</label>
                                    <input type="text" class="form-control" wire:model="address" placeholder="Enter Address">
                                    @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label>Phone Number</label>
                                    <input type="tel" class="form-control" wire:model="phone_number" placeholder="Enter Phone Number">
                                    @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label>TIN Number</label>
                                    <input type="text" class="form-control" wire:model="tin_number" placeholder="Enter TIN Number">
                                    @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label>Email Address</label>
                                    <input type="text" class="form-control" wire:model="email_address" placeholder="Enter Email Address">
                                    @error('email_address') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <!-- postal code -->
                                <div class="mb-3">
                                    <label>Postal Code</label>
                                    <input type="text" class="form-control" wire:model="postal_code" placeholder="Postal Code" required>
                                    @error('postal_code') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label>Country</label>
                                    <select class="form-select" wire:model="country_id" placeholder="Country" required>
                                        <option selected disabled value="">Choose...</option>
                                        @foreach($countries as $country)
                                        @if($country->code == 'TZ')
                                        <option value="{{ $country->id }}" selected>{{ $country->name }}</option>
                                        @else
                                        <option value="{{ $country->id }}">{{ $country->name }}</option>
                                        @endif
                                        @endforeach

                                    </select>
                                    @error('country_id') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label>Region</label>
                                    <select class="form-select" wire:model="region_id"  placeholder="Region"  wire:change="updateDistricts" required>
                                        <option selected disabled value="">Choose...</option>
                                        @foreach($regions as $region)
                                        @if($region->code == 'TZ')
                                        <option value="{{ $region->id }}" selected>{{ $region->name }}</option>
                                        @else
                                        <option value="{{ $region->id }}">{{ $region->name }}</option>
                                        @endif
                                        @endforeach

                                    </select>
                                    @error('region_id') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label>District</label>
                                    <select class="form-select" wire:model="district_id" placeholder="District" wire:change="updatewards" required>
                                        <option selected disabled value="">Choose...</option>
                                        @foreach($districts as $district)
                                        <option value="{{ $district->id }}">{{ $district->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('district_id') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <!--ward -->
                                <div class="mb-3">
                                    <label>Ward</label>
                                    <select class="form-select" wire:model="ward_id" placeholder="Ward" required>
                                        <option selected disabled value="">Choose...</option>
                                        @foreach ($wards as $ward)
                                            <option value="{{ $ward->id }}">{{ $ward->name }}</option>                                            
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- physical address -->
                        <div class="mb-3">
                            <label>Physical Address</label>
                            <input type="text" class="form-control" wire:model="physical_address" placeholder="Physical Address" required>
                            @error('physical_address') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>


                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="$set('showModal', false)">close</button>
                            <button type="submit" class="btn btn-primary">
                                {{ $modalMode === 'edit' ? 'Update' : 'Save' }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>