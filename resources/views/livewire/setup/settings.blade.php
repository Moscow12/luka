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
                    <form class="row g-6 justify-content-center needs-validation" novalidate>
                        <div class="col-xl-8 col-12">
                           

                            <!-- card -->
                            <div class="card mb-6 card-lg">
                                <div class="card-header border-bottom border-dashed">
                                    <h5>Properties</h5>
                                    <p class="mb-0 text-secondary">Additional functions and attributes...</p>
                                </div>
                                <!-- card body -->
                                <div class="card-body px-6 py-5">
                                    <!-- input -->
                                    <div class="form-check form-switch mb-5">
                                        <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchStock" checked />
                                        <label class="form-check-label" for="flexSwitchStock">In Stock</label>
                                    </div>
                                    <!-- input -->
                                    <div class="row g-4">
                                        <div class="col-lg-6">
                                            <label class="form-label visually-hidden" for="productCode">Product Code</label>
                                            <input type="text" id="productCode" class="form-control" placeholder="Product Code" required />
                                            <div class="invalid-feedback">Please enter code.</div>
                                        </div>
                                        <!-- input -->
                                        <div class="col-lg-6">
                                            <label class="form-label visually-hidden" for="productSKU">Product SKU</label>
                                            <input type="text" id="productSKU" class="form-control" placeholder="Product SKU" required />
                                            <div class="invalid-feedback">Please enter SKU.</div>
                                        </div>
                                        <!-- input -->
                                        <div>
                                            <h4 class="mb-3 fs-6">Gender</h4>

                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox" name="inlineCheckboxOptions" id="inlineCheckbox1" value="option1" />
                                                <label class="form-check-label" for="inlineCheckbox1">Male</label>
                                            </div>
                                            <!-- input -->
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox" name="inlineCheckboxOptions" id="inlineCheckbox2" value="option2" />
                                                <label class="form-check-label" for="inlineCheckbox2">Female</label>
                                            </div>
                                            <!-- input -->
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox" name="inlineCheckboxOptions" id="inlineCheckbox3" value="option2" />
                                                <label class="form-check-label" for="inlineCheckbox3">Kids</label>
                                            </div>
                                        </div>
                                        <!-- input -->

                                        <!-- select menu -->
                                        <div>
                                            <label class="form-label visually-hidden" for="categorySelect">Category</label>
                                            <select class="form-select" id="categorySelect" data-choices="">
                                                <option selected>Shoe</option>
                                                <option value="Sunglasses">Sunglasses</option>
                                                <option value="Handbag">Handbag</option>
                                                <option value="Slingbag">Slingbag</option>
                                            </select>
                                        </div>

                                        <!-- tag -->

                                        <div>
                                            <label class="form-label visually-hidden">Status</label>
                                            <select class="form-select" data-choices="">
                                                <option selected>Published</option>
                                                <option value="Unpublished">Unpublished</option>
                                                <option value="Draft">Draft</option>
                                            </select>
                                        </div>
                                        <!-- date -->
                                        <div>
                                            <div class="input-group me-3 rounded">
                                                <input class="form-control flatpickr" type="text" value="Select Date" placeholder="Select Date" aria-describedby="basic-addon2" />

                                                <span class="input-group-text text-secondary" id="basic-addon2">
                                                    <svg
                                                        xmlns="http://www.w3.org/2000/svg"
                                                        class="icon icon-tabler icon-tabler-calendar"
                                                        width="16"
                                                        height="16"
                                                        viewBox="0 0 24 24"
                                                        stroke-width="1.5"
                                                        stroke="currentColor"
                                                        fill="none"
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                        <path d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z" />
                                                        <path d="M16 3v4" />
                                                        <path d="M8 3v4" />
                                                        <path d="M4 11h16" />
                                                        <path d="M11 15h1" />
                                                        <path d="M12 15v3" />
                                                    </svg>
                                                </span>
                                            </div>
                                        </div>

                                        <div>
                                            <label class="form-label visually-hidden" for="tagsInput"></label>
                                            <input type="text" id="tagsInput" class="form-control" value="" placeholder="Add Tags" data-choices="" data-choices-removeitembutton="true" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                           
                            <!-- button -->
                            <div>
                                <button type="submit" class="btn btn-primary">Create Product</button>
                            </div>
                        </div>
                    </form>
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