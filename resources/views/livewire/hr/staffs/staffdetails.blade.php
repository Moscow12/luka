<div>

    <x-pages.breadcrumn title="Staff Details"
        :breadcrumbs="[
        ['label' => 'Home', 'url' => route('dashboard')], 
        ['label' => 'Staff List', 'url' => route('hr.stafflist')],
        ['label' => 'Staff Details']  
        ]">
        <a href="{{ route('hr.stafflist') }}" class="btn btn-sm btn-primary">Back to List</a>
    </x-pages.breadcrumn>
    <!-- row -->
    <div class="card card-lg overflow-hidden">
        <div class="pt-16 rounded-top position-relative"
            style="background: url({{ asset('../../assets/images/background/profile-cover.jpg') }}) no-repeat; background-size: cover">
            <div class="position-absolute top-0 end-0 m-4">
                <a href="{{ route('hr.editstaff',$employee->id) }}" class="icon-shape icon-md bg-white rounded-circle">
                    <i class="ti ti-pencil"></i>
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="d-flex flex-column flex-lg-row gap-4">
                <div>
                    <img src="{{ asset('../../assets/images/avatar/avatar-1.jpg') }}" alt="" class="rounded-circle avatar avatar-xl" />
                </div>
                <div class="d-flex flex-column flex-lg-row justify-content-between w-100 gap-2">
                    <div class="d-lg-flex flex-lg-column">
                        <h3 class="mb-0"> {{ $employee->getFullName() }}</h3>
                        <div class="d-lg-flex align-items-center gap-2">
                            <span> {{ $employee->getAgeAttribute() }}</span>
                            <span class="text-secondary"> {{ $employee->gender }} </span>
                            <span class="text-secondary"> {{ $employee->email }}</span>
                        </div>

                    </div>
                    <div class="d-flex align-items-center gap-10">
                        <div class="d-flex flex-column">
                            <span class="fw-semibold fs-5">12,500</span>
                            <span class="text-secondary">Followers</span>
                        </div>
                        <div class="d-flex flex-column">
                            <span class="fw-semibold fs-5">350</span>
                            <span class="text-secondary">Following</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row mb-8">
        <div class="col-12">
            <ul class="nav nav-lb-tab border-bottom">
                <li class="nav-item">
                    <a class='nav-link' href='profile-overview.html'>
                        <div class="d-flex align-items-center gap-2 lh-1">
                            <span><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                                    class="icon icon-tabler icons-tabler-outline icon-tabler-user-circle">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" />
                                    <path d="M12 10m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" />
                                    <path d="M6.168 18.849a4 4 0 0 1 3.832 -2.849h4a4 4 0 0 1 3.834 2.855" />
                                </svg>
                            </span>
                            <span>Profile</span>
                        </div>
                    </a>
                </li>
                <li class="nav-item">
                    <a class='nav-link active' href='profile-project.html'>
                        <div class="d-flex align-items-center gap-2 lh-1">
                            <span><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                                    class="icon icon-tabler icons-tabler-outline icon-tabler-briefcase">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M3 7m0 2a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v9a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2z" />
                                    <path d="M8 7v-2a2 2 0 0 1 2 -2h4a2 2 0 0 1 2 2v2" />
                                    <path d="M12 12l0 .01" />
                                    <path d="M3 13a20 20 0 0 0 18 0" />
                                </svg>
                            </span>
                            <span>Project</span>
                        </div>
                    </a>
                </li>
                <li class="nav-item">
                    <a class='nav-link' href='profile-team.html'>
                        <div class="d-flex align-items-center gap-2 lh-1">
                            <span><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                                    class="icon icon-tabler icons-tabler-outline icon-tabler-users-group">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M10 13a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" />
                                    <path d="M8 21v-1a2 2 0 0 1 2 -2h4a2 2 0 0 1 2 2v1" />
                                    <path d="M15 5a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" />
                                    <path d="M17 10h2a2 2 0 0 1 2 2v1" />
                                    <path d="M5 5a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" />
                                    <path d="M3 13v-1a2 2 0 0 1 2 -2h2" />
                                </svg>
                            </span>
                            <span>Teams</span>
                        </div>
                    </a>
                </li>
                <li class="nav-item">
                    <a class='nav-link' href='profile-followers.html'>
                        <div class="d-flex align-items-center gap-2 lh-1">
                            <span><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                                    class="icon icon-tabler icons-tabler-outline icon-tabler-users">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" />
                                    <path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" />
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                                    <path d="M21 21v-2a4 4 0 0 0 -3 -3.85" />
                                </svg>
                            </span>
                            <span>Followers</span>
                        </div>
                    </a>
                </li>
                <li class="nav-item">
                    <a class='nav-link' href='profile-activity.html'>
                        <div class="d-flex align-items-center gap-2 lh-1">
                            <span><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                                    class="icon icon-tabler icons-tabler-outline icon-tabler-activity">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M3 12h4l3 8l4 -16l3 8h4" />
                                </svg>
                            </span>
                            <span>Activity</span>
                        </div>
                    </a>
                </li>
            </ul>
        </div>
    </div>
   
    
</div>