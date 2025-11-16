<div>
    <div class="row">
        <div class="col-lg-12 col-md-12 col-12">
            <!-- Page header -->
            <div class="mb-8 d-md-flex justify-content-between align-items-center">
                <div>
                    <h1 class="mb-3 h2">Chop Management</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="#">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="#">Settings</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Chop Settings</li>
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
                    <a class="nav-link active py-2" id="category-areas-tab" data-bs-toggle="pill" href="#category-areas" role="tab" aria-controls="category-areas" aria-selected="true">
                        <i class="fa-solid fa-layer-group"></i>
                        Category Areas
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link py-2" id="source-funds-tab" data-bs-toggle="pill" href="#source-funds" role="tab" aria-controls="source-funds" aria-selected="false">
                        <i class="fa-solid fa-hand-holding-dollar"></i>
                        Source of Funds
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link py-2" id="chop-items-tab" data-bs-toggle="pill" href="#chop-items" role="tab" aria-controls="chop-items" aria-selected="false">
                        <i class="fa-solid fa-box"></i>
                        Chop Items
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link py-2" id="activities-tab" data-bs-toggle="pill" href="#activities" role="tab" aria-controls="activities" aria-selected="false">
                        <i class="fa-solid fa-list-check"></i>
                        Activities
                    </a>
                </li>
            </ul>

            <div class="tab-content" id="tabContent">
                <div class="tab-pane fade show active" id="category-areas" role="tabpanel" aria-labelledby="category-areas-tab">
                    <livewire:chop.category-areas />
                </div>

                <div class="tab-pane fade" id="source-funds" role="tabpanel" aria-labelledby="source-funds-tab">
                    <livewire:chop.fund-sources />
                </div>

                <div class="tab-pane fade" id="chop-items" role="tabpanel" aria-labelledby="chop-items-tab">
                    <livewire:chop.items-management />
                </div>

                <div class="tab-pane fade" id="activities" role="tabpanel" aria-labelledby="activities-tab">
                    <livewire:chop.activities-management />
                </div>
            </div>
        </div>
    </div>
</div>
