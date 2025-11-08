<div>
    <div class="row">
        <div class="col-lg-12 col-md-12 col-12">
            <!-- Page header -->
            <div class="mb-8 d-md-flex justify-content-between align-items-center">
                <div>
                    <h1 class="mb-3 h2">Approval Configurations</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="#">Settings</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Approval Configurations</li>
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
                    <a class="nav-link active py-2" id="approvallevels-tab" data-bs-toggle="pill" href="#approvallevels" role="tab" aria-controls="approvallevels" aria-selected="true">
                        <i class="fa-solid fa-layer-group"></i>
                        Approval Levels
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link py-2" id="documents-tab" data-bs-toggle="pill" href="#documents" role="tab" aria-controls="documents" aria-selected="false">
                        <i class="fa-solid fa-file-lines"></i>
                        Document Mappings
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link py-2" id="employees-tab" data-bs-toggle="pill" href="#employees" role="tab" aria-controls="employees" aria-selected="false">
                        <i class="fa-solid fa-users"></i>
                        Employee Mappings
                    </a>
                </li>
            </ul>

            <div class="tab-content" id="tabContent">
                <div class="tab-pane fade show active" id="approvallevels" role="tabpanel" aria-labelledby="approvallevels-tab">
                    <livewire:setup.approval.approvallevels />
                </div>

                <div class="tab-pane fade" id="documents" role="tabpanel" aria-labelledby="documents-tab">
                    <livewire:setup.approval.documentmappings />
                </div>

                <div class="tab-pane fade" id="employees" role="tabpanel" aria-labelledby="employees-tab">
                    <livewire:setup.approval.employeemappings />
                </div>
            </div>
        </div>
    </div>
</div>
