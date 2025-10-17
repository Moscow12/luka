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
                            <li class="breadcrumb-item active" aria-current="page">Finances</li>
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
                    <a class="nav-link active py-2" id="allowances-tab" data-bs-toggle="pill" href="#allowances" role="tab" aria-controls="allowances" aria-selected="true">
                        <i class="fa-solid fa-briefcase"></i>
                        Allowances
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link py-2" id="deductions-tab" data-bs-toggle="pill" href="#deductions" role="tab" aria-controls="deductions" aria-selected="true">
                        <i class="fa-solid fa-building"></i>
                        deductions
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-2" id="districts-tab" data-bs-toggle="pill" href="#districts" role="tab" aria-controls="districts" aria-selected="true">
                        <i class="fa-solid fa-person"></i>
                        Districts
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-2" id="wards-tab" data-bs-toggle="pill" href="#wards" role="tab" aria-controls="wards" aria-selected="true">
                        <i class="fa-solid fa-user-doctor"></i>
                        Wards
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-2" id="streets-tab" data-bs-toggle="pill" href="#streets" role="tab" aria-controls="streets" aria-selected="true">
                        <i class="fa-solid fa-clock"></i>
                        Streets
                    </a>
                </li>
               
            </ul>

            <div class="tab-content" id="tabContent">
                <div class="tab-pane fade show active" id="allowances" role="tabpanel" aria-labelledby="allowances-tab">
                    <livewire:setup.finance.allowance />
                </div>

                <div class="tab-pane" id="deductions" role="tabpanel" aria-labelledby="deductions-tab">
                    <livewire:setup.location.region />
                </div>
                <div class="tab-pane" id="districts" role="tabpanel" aria-labelledby="districts-tab">
                    <livewire:setup.location.district />
                </div>
                <div class="tab-pane fade show" id="wards" role="tabpanel" aria-labelledby="wards-tab">
                   <livewire:setup.location.ward />
                </div>
                <div class="tab-pane fade show" id="streets" role="tabpanel" aria-labelledby="streets-tab">
                    
                </div>
                
            </div>
        </div>
    </div>
</div>