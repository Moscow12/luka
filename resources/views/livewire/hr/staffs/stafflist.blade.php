 <div class="custom-container">

     <x-pages.breadcrumn title="STAFF LIST"
         :breadcrumbs="[
        ['label' => 'Home', 'url' => route('dashboard')], 
        ['label' => 'HR Overview', 'url' => route('hr.index')],
        ['label' => 'Staff List', 'url' => route('hr.stafflist')],
        ]">
         <a class='btn btn-primary d-md-flex align-items-center gap-2' href="{{ route('hr.addstaff') }}"> <i class="fa-solid fa-plus"></i> ADD NEW STAFF</a>
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

                     <div class="table-responsive table-checkbox" data-simplebar style="height: 600px">
                         <table class="table text-nowrap table-centered table-hover mb-0" data-check-container>
                             <thead class="sticky-top">
                                 <tr>
                                     <th class="pe-0">
                                         #
                                     </th>
                                     <th>Photo</th>
                                     <th class="listjs-sorter ps-0" data-sort="product_name">Name</th>
                                     <th class="listjs-sorter" data-sort="product_category">Registration No</th>
                                     <th class="listjs-sorter" data-sort="product_category">Gender</th>
                                     <th class="listjs-sorter" data-sort="product_date">Age</th>
                                     <th class="listjs-sorter" data-sort="product_category">Designation</th>
                                     <th class="listjs-sorter" data-sort="product_date">Phone Number</th>
                                     <th class="listjs-sorter" data-sort="product_price">Contact Type</th>
                                     <th>Action</th>
                                 </tr>
                             </thead>
                             <tbody class="list">
                                @php
                                    $num=1;
                                @endphp
                                 @foreach ($employees as $employee)
                                 <tr>
                                    <td class="pe-0">
                                        {{ $num++ }}
                                    </td>
                                     <td class="product_name ps-0">
                                         <div class="d-flex align-items-center">
                                             <img src="{{ asset('storage/'.$employee->photo) }}" alt="" class="rounded-3" width="56" />
                                         </div>
                                     </td>
                                     <td class="pe-0">
                                         {{ $employee->first_name }} {{ $employee->middle_name }} {{ $employee->last_name }}
                                     </td>
                                     <td class="product_price"> {{ $employee->employee_no }}</td>
                                     <td class="product_category">{{ $employee->gender }}</td>
                                     <td class="product_date">{{ $employee->dob }}</td>
                                     <td class="product_category">{{ $employee->designation->name }}</td>
                                     <td class="product_date">{{ $employee->hired_date }}</td>
                                     <td>{{ $employee->employment_type }}</td>
                                     <td>
                                         <a href=" {{ route('hr.staffdetails',$employee->id) }}" class="btn btn-ghost btn-icon btn-sm rounded-circle texttooltip" data-template="eyeFour">
                                             <svg
                                                 xmlns="http://www.w3.org/2000/svg"
                                                 class="icon icon-tabler icon-tabler-eye"
                                                 width="16"
                                                 height="16"
                                                 viewBox="0 0 24 24"
                                                 stroke-width="1.5"
                                                 stroke="currentColor"
                                                 fill="none"
                                                 stroke-linecap="round"
                                                 stroke-linejoin="round">
                                                 <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                 <path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" />
                                                 <path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" />
                                             </svg>
                                             <div id="eyeFour" class="d-none">
                                                 <span>View</span>
                                             </div>
                                         </a>
                                         <a href="{{ route('hr.editstaff',$employee->id) }}" class="btn btn-ghost btn-icon btn-sm rounded-circle texttooltip" data-template="editThree">
                                             <svg
                                                 xmlns="http://www.w3.org/2000/svg"
                                                 class="icon icon-tabler icon-tabler-edit"
                                                 width="16"
                                                 height="16"
                                                 viewBox="0 0 24 24"
                                                 stroke-width="1.5"
                                                 stroke="currentColor"
                                                 fill="none"
                                                 stroke-linecap="round"
                                                 stroke-linejoin="round">
                                                 <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                 <path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1" />
                                                 <path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z" />
                                                 <path d="M16 5l3 3" />
                                             </svg>
                                             <div id="editThree" class="d-none">
                                                 <span>Edit</span>
                                             </div>
                                         </a>
                                         <a href="#!" class="btn btn-ghost btn-icon btn-sm rounded-circle texttooltip" data-template="trashFour">
                                             <svg
                                                 xmlns="http://www.w3.org/2000/svg"
                                                 class="icon icon-tabler icon-tabler-trash"
                                                 width="16"
                                                 height="16"
                                                 viewBox="0 0 24 24"
                                                 stroke-width="1.5"
                                                 stroke="currentColor"
                                                 fill="none"
                                                 stroke-linecap="round"
                                                 stroke-linejoin="round">
                                                 <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                 <path d="M4 7l16 0" />
                                                 <path d="M10 11l0 6" />
                                                 <path d="M14 11l0 6" />
                                                 <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" />
                                                 <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" />
                                             </svg>
                                             <div id="trashFour" class="d-none">
                                                 <span>Delete</span>
                                             </div>
                                         </a>
                                     </td>
                                     @endforeach


                             </tbody>
                         </table>
                     </div>

                     <div
                         class="btn-toolbar card-footer border-top border-dashed d-flex flex-md-row flex-column justify-content-md-between align-items-md-center">
                         <p class="mb-0 listjs-showing-items-label"></p>
                         <div class="d-flex flex-column flex-md-row gap-4">
                             <div class="d-flex align-items-center gap-2">
                                 <label class="form-label text-nowrap mb-0">Rows per page:</label>
                                 <select class="form-select listjs-items-per-page" data-choices="">
                                     <option value="10" selected>10</option>
                                     <option value="14">14</option>
                                 </select>
                             </div>
                             <div>
                                 <div class="pagination-buttons d-flex">
                                     <button class="btn btn-white prev">Previous</button>
                                     <ul class="pagination mb-0 ms-1"></ul>
                                     <button class="btn btn-white next">Next</button>
                                 </div>
                             </div>
                         </div>
                     </div>
                 </div>
             </div>
         </div>
     </div>
 </div>
 </div>