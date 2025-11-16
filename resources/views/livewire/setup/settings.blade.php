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
                <li class="nav-item">
                    <a class="nav-link py-2" id="financial-years-tab" data-bs-toggle="pill" href="#financial-years" role="tab" aria-controls="financial-years" aria-selected="false">
                        <i class="fa-solid fa-calendar-days"></i>
                        Financial Years
                    </a>
                </li>
            </ul>

            <div class="tab-content" id="tabContent">
                <div class="tab-pane fade show active" id="workstations" role="tabpanel" aria-labelledby="workstations-tab">
                    <livewire:setup.workstation />
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
                <div class="tab-pane fade" id="financial-years" role="tabpanel" aria-labelledby="financial-years-tab">
                    <livewire:setup.financial-year-management />
                </div>
            </div>
        </div>
    </div>
</div>