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
                            <li class="breadcrumb-item active" aria-current="page">Locations</li>
                        </ol>
                    </nav>
                </div>
                <div class="mt-3 mt-md-0">
                    <button type="button"
                        class="btn btn-primary"
                        wire:click="syncGeodata"
                        wire:loading.attr="disabled"
                        wire:target="syncGeodata"
                        wire:confirm="Synchronize Tanzania geo-location data from the bundled dataset? Existing locations are kept and missing ones are added — nothing is deleted.">
                        <span wire:loading.remove wire:target="syncGeodata">
                            <i class="fa-solid fa-rotate me-1"></i> Sync Geo Data
                        </span>
                        <span wire:loading wire:target="syncGeodata">
                            <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                            Syncing…
                        </span>
                    </button>
                </div>
            </div>

            @if (session()->has('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if (session()->has('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
        </div>
    </div>
    <!-- row -->

    <div class="row gy-5">

        <div class="col-xxl-12 col-xl-12 col-12">
            <ul class="nav nav-line-bottom mb-6 text-nowrap" id="tab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active py-2" id="country-tab" data-bs-toggle="pill" href="#country" role="tab" aria-controls="country" aria-selected="true">
                        <i class="fa-solid fa-briefcase"></i>
                        Country
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link py-2" id="regions-tab" data-bs-toggle="pill" href="#regions" role="tab" aria-controls="regions" aria-selected="true">
                        <i class="fa-solid fa-building"></i>
                        Regions
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
                <div class="tab-pane fade show active" id="country" role="tabpanel" aria-labelledby="country-tab">
                    <livewire:setup.location.nations />
                </div>

                <div class="tab-pane" id="regions" role="tabpanel" aria-labelledby="regions-tab">
                    <livewire:setup.location.region />
                </div>
                <div class="tab-pane" id="districts" role="tabpanel" aria-labelledby="districts-tab">
                    <livewire:setup.location.district />
                </div>
                <div class="tab-pane fade show" id="wards" role="tabpanel" aria-labelledby="wards-tab">
                   <livewire:setup.location.ward />
                </div>
                <div class="tab-pane fade show" id="streets" role="tabpanel" aria-labelledby="streets-tab">
                    <livewire:setup.location.street />
                </div>
                
            </div>
        </div>
    </div>
</div>