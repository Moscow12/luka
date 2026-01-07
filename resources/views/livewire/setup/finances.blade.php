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
                    <a class="nav-link py-2" id="financial-years-tab" data-bs-toggle="pill" href="#financial-years" role="tab" aria-controls="financial-years" aria-selected="true">
                        <i class="fa-solid fa-calendar-days"></i>
                        Financial Years
                    </a>
                </li>
                <!-- loan items -->
                 <li class="nav-item">
                    <a class="nav-link py-2" id="loan-items-tab" data-bs-toggle="pill" href="#loan-items" role="tab" aria-controls="loan-items" aria-selected="true">
                        <i class="fa-solid fa-money-bill"></i>
                        Loan Items
                    </a>
                </li>
            </ul>

            <div class="tab-content" id="tabContent">
                <div class="tab-pane fade show active" id="allowances" role="tabpanel" aria-labelledby="allowances-tab">
                    <livewire:setup.finance.allowance />
                </div>

                <div class="tab-pane" id="deductions" role="tabpanel" aria-labelledby="deductions-tab">
                    <livewire:setup.finance.deductions />
                </div>
                <div class="tab-pane" id="financial-years" role="tabpanel" aria-labelledby="financial-years-tab">
                    <livewire:setup.finance.financial-years />
                </div>
                <div class="tab-pane" id="loan-items" role="tabpanel" aria-labelledby="loan-items-tab">
                    <livewire:hr.loan.loanitems />
                </div>
            </div>
        </div>
    </div>
</div>