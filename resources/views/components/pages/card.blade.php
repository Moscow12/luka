 <!-- card -->
 <div class="card card-lg mb-1">
    <div class="card-header border-bottom-1">
        <div class="mb-1 d-flex justify-content-between align-items-center">
             <!-- hneading -->
             <div>
                 <h5 class="mb-1">{{ $title }}</h5>
             </div>
             <div>
                 <div class="dropdown dropstart">
                     <a class="btn btn-icon btn-ghost btn-sm rounded-circle" href="#!" role="button"
                         data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                         <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-dots-vertical"
                             width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                             fill="none" stroke-linecap="round" stroke-linejoin="round">
                             <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                             <path d="M12 12m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"></path>
                             <path d="M12 19m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"></path>
                             <path d="M12 5m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"></path>
                         </svg>
                     </a>
                     <div class="dropdown-menu">
                         <a class="dropdown-item d-flex align-items-center" href="#!">Action</a>
                         <a class="dropdown-item d-flex align-items-center" href="#!">Another action</a>
                         <a class="dropdown-item d-flex align-items-center" href="#!">Something else here</a>
                     </div>
                 </div>
             </div>
         </div>
    </div>
     <!-- card body -->
     <div class="card-body" data-simplebar style="height: 500px;">         
         <div >
            {{ $slot }}
         </div>
     </div>
     <!-- card footer -->
     <div class="card-footer border-top border-dashed px-6 py-5">
        <span class="me-3">
            <span>
                <i class="ti ti-list"></i>
            </span>
            <span class="ms-1">10 Task</span>
        </span>
        <span>
            <span>
                <i class="ti ti-user"></i>
            </span>
            <span class="ms-1">2k Comments</span>
        </span>
    </div>
 </div>
 <!-- card -->