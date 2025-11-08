<div>
    <x-pages.breadcrumn title="Request Leave"
        :breadcrumbs="[
        ['label' => 'Home', 'url' => route('dashboard')], 
        ['label' => 'Leave Overview', 'url' => route('leave.leavebalance')],
        ['label' => 'Leave Request']
        ]">
        <x-forms.button-model name="ADD LEAVE"/> 
    </x-pages.breadcrumn>
    <div>
        <!-- row -->
        <div class="row">
            <div class="col-12">
                <div class="card card-lg" id="productList"
                    data-list="product_name,product_category,product_date,product_price,product_quantity,product_status">
                    <div class="card-header border-bottom-0">
                        <div class="row g-4">
                            <div class="col-lg-4">
                                <input type="search" class="form-control listjs-search" placeholder="Search" />
                            </div>
                            <div class="col-lg-8 d-flex justify-content-end">
                                <div class="d-flex align-items-center gap-2">
                                    <div>
                                        <button type="button" class="btn btn-white">More Filter</button>
                                    </div>
                                    <div class="dropdown">
                                        <a class="btn btn-white dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                                            aria-expanded="false"> Category </a>

                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#">Accessories</a></li>
                                            <li><a class="dropdown-item" href="#">Bags</a></li>
                                            <li><a class="dropdown-item" href="#">Men's Fashion</a></li>
                                            <li><a class="dropdown-item" href="#">Accessories</a></li>
                                        </ul>
                                    </div>
                                    <div class="dropdown">
                                        <a class="btn btn-white dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                                            aria-expanded="false"> Export </a>

                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#">Download as CSV</a></li>
                                            <li><a class="dropdown-item" href="#">Print</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


    </div>