<div class="custom-container">
    
    <x-pages.breadcrumn title="Staff Details"
        :breadcrumbs="[
        ['label' => 'Home', 'url' => route('dashboard')], 
        ['label' => 'Staff List', 'url' => route('hr.stafflist')],
        ['label' => 'Staff Details']  
        ]">
        <a href="{{ route('hr.stafflist') }}" class="btn btn-sm btn-primary">Back to List</a>
    </x-pages.breadcrumn>
    <!-- row -->
   
    <div class="row gy-5">
        <aside class="col-xxl-3 col-xl-4 col-12">
            <div class="card card-lg mb-6">
                <div class="card-body d-flex flex-column gap-5">
                    <h5 class="mb-0">Deal Owner</h5>
                    <div class="d-flex flex-row justify-content-between align-items-center gap-5">
                        <div class="d-flex flex-row gap-2 align-items-center">
                            <img src="../../assets/images/avatar/avatar-1.jpg" alt="avatar" class="avatar avatar-md rounded-circle" />
                            <div>Johnny Emond</div>
                        </div>
                        <div class="flex-fill">
                            <select class="form-select border-0" data-choices>
                                <option selected>Owner</option>
                                <option value="John Smith">John Smith</option>
                                <option value="Jane Doe">Jane Doe</option>
                                <option value="Alex Johnson">Alex Johnson</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card card-lg mb-6">
                <div class="card-body d-flex flex-column gap-4">
                    <h5 class="mb-0">Deal Info</h5>
                    <table class="table table-borderless mb-0 text-nowrap table-sm">
                        <tbody>
                            <tr>
                                <td class="px-0 text-secondary d-flex align-items-center gap-1 lh-1">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="16"
                                        height="16"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.5"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        class="icon icon-tabler icons-tabler-outline icon-tabler-currency-dollar">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M16.7 8a3 3 0 0 0 -2.7 -2h-4a3 3 0 0 0 0 6h4a3 3 0 0 1 0 6h-4a3 3 0 0 1 -2.7 -2" />
                                        <path d="M12 3v3m0 12v3" />
                                    </svg>
                                    <span>Value:</span>
                                </td>

                                <td class="px-0 fw-semi-bold text-end text-gray-900">$100,000.00</td>
                            </tr>
                            <tr>
                                <td class="px-0 text-secondary d-flex align-items-center gap-1 lh-1">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="16"
                                        height="16"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.5"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        class="icon icon-tabler icons-tabler-outline icon-tabler-hash">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M5 9l14 0" />
                                        <path d="M5 15l14 0" />
                                        <path d="M11 4l-4 16" />
                                        <path d="M17 4l-4 16" />
                                    </svg>
                                    <span>Deal ID:</span>
                                </td>

                                <td class="px-0 text-end">#001</td>
                            </tr>
                            <tr>
                                <td class="px-0 text-secondary d-flex align-items-center gap-1 lh-1">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="16"
                                        height="16"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.5"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        class="icon icon-tabler icons-tabler-outline icon-tabler-headphones">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M4 13m0 2a2 2 0 0 1 2 -2h1a2 2 0 0 1 2 2v3a2 2 0 0 1 -2 2h-1a2 2 0 0 1 -2 -2z" />
                                        <path d="M15 13m0 2a2 2 0 0 1 2 -2h1a2 2 0 0 1 2 2v3a2 2 0 0 1 -2 2h-1a2 2 0 0 1 -2 -2z" />
                                        <path d="M4 15v-3a8 8 0 0 1 16 0v3" />
                                    </svg>
                                    <span>Deal Type:</span>
                                </td>

                                <td class="px-0 text-end">#Renewal1</td>
                            </tr>
                            <tr>
                                <td class="px-0 text-secondary d-flex align-items-center gap-1 lh-1">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="16"
                                        height="16"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.5"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        class="icon icon-tabler icons-tabler-outline icon-tabler-calendar text-secondary">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z" />
                                        <path d="M16 3v4" />
                                        <path d="M8 3v4" />
                                        <path d="M4 11h16" />
                                        <path d="M11 15h1" />
                                        <path d="M12 15v3" />
                                    </svg>
                                    <span>Created Date:</span>
                                </td>

                                <td class="px-0 text-end">Oct 03, 2025</td>
                            </tr>
                            <tr>
                                <td class="px-0 text-secondary d-flex align-items-center gap-1 lh-1">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="16"
                                        height="16"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.5"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        class="icon icon-tabler icons-tabler-outline icon-tabler-calendar text-secondary">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z" />
                                        <path d="M16 3v4" />
                                        <path d="M8 3v4" />
                                        <path d="M4 11h16" />
                                        <path d="M11 15h1" />
                                        <path d="M12 15v3" />
                                    </svg>
                                    <span>Last Updated:</span>
                                </td>

                                <td class="px-0 text-end">Nav 21, 2025</td>
                            </tr>
                            <tr>
                                <td class="px-0 text-secondary d-flex align-items-center gap-1 lh-1">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="16"
                                        height="16"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.5"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        class="icon icon-tabler icons-tabler-outline icon-tabler-flag">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M5 5a5 5 0 0 1 7 0a5 5 0 0 0 7 0v9a5 5 0 0 1 -7 0a5 5 0 0 0 -7 0v-9z" />
                                        <path d="M5 21v-7" />
                                    </svg>
                                    <span>Close Date:</span>
                                </td>

                                <td class="px-0 text-end">Dec 30, 2025</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card card-lg mb-6">
                <div class="card-body d-flex flex-column gap-4">
                    <h5 class="mb-0">Deal Start</h5>
                    <table class="table table-borderless mb-0 text-nowrap table-sm">
                        <tbody>
                            <tr>
                                <td class="px-0 d-flex align-items-center gap-2 text-secondary lh-1">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="16"
                                        height="16"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.5"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        class="icon icon-tabler icons-tabler-outline icon-tabler-chart-histogram text-success">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M3 3v18h18" />
                                        <path d="M20 18v3" />
                                        <path d="M16 16v5" />
                                        <path d="M12 13v8" />
                                        <path d="M8 16v5" />
                                        <path d="M3 11c6 0 5 -5 9 -5s3 5 9 5" />
                                    </svg>
                                    <span>Probability (%)</span>
                                </td>

                                <td class="px-0 fw-semi-bold text-end text-gray-900">15.5</td>
                            </tr>
                            <tr>
                                <td class="px-0 d-flex align-items-center gap-2 text-secondary lh-1">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="16"
                                        height="16"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.5"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        class="icon icon-tabler icons-tabler-outline icon-tabler-trending-up text-warning">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M3 17l6 -6l4 4l8 -8" />
                                        <path d="M14 7l7 0l0 7" />
                                    </svg>
                                    <span>Revenue</span>
                                </td>

                                <td class="px-0 fw-semi-bold text-end text-gray-900">$25,000.00</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card card-lg">
                <div class="card-body d-flex flex-column gap-4">
                    <div class="d-flex flex-row justify-content-between">
                        <h5 class="mb-0">Contact Person</h5>
                        <a href="#!">View Details</a>
                    </div>
                    <div class="d-flex flex-column gap-4">
                        <div class="d-flex flex-row gap-4 align-items-center">
                            <img src="../../assets/images/avatar/avatar-8.jpg" alt="avatar" class="avatar avatar-md rounded-circle" />
                            <div>
                                <div>Sandip Chauhan</div>
                                <small>
                                    Client -
                                    <a href="#!">Code Persistent</a>
                                </small>
                            </div>
                        </div>

                        <table class="table table-borderless mb-0 text-nowrap table-sm">
                            <tbody>
                                <tr>
                                    <td class="px-0 d-flex align-items-center gap-2 text-secondary gap-2">
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            width="16"
                                            height="16"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.5"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            class="icon icon-tabler icons-tabler-outline icon-tabler-phone">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path d="M5 4h4l2 5l-2.5 1.5a11 11 0 0 0 5 5l1.5 -2.5l5 2v4a2 2 0 0 1 -2 2a16 16 0 0 1 -15 -15a2 2 0 0 1 2 -2" />
                                        </svg>
                                        <span>Phone</span>
                                    </td>

                                    <td class="px-0 text-end">+01 123 456 789</td>
                                </tr>
                                <tr>
                                    <td class="px-0 d-flex align-items-center gap-2 text-secondary">
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            width="16"
                                            height="16"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.5"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            class="icon icon-tabler icons-tabler-outline icon-tabler-mail">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path d="M3 7a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v10a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-10z" />
                                            <path d="M3 7l9 6l9 -6" />
                                        </svg>
                                        <span>Email</span>
                                    </td>

                                    <td class="px-0 text-end">
                                        <a href="#!" class="text-inherit">sandipchaua@email.com</a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </aside>
        <div class="col-xxl-9 col-xl-8 col-12">
            <ul class="nav nav-line-bottom mb-6 text-nowrap" id="tab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active py-2" id="activity-tab" data-bs-toggle="pill" href="#activity" role="tab" aria-controls="activity" aria-selected="true">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="16"
                            height="16"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.5"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            class="icon icon-tabler icons-tabler-outline icon-tabler-activity">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M3 12h4l3 8l4 -16l3 8h4" />
                        </svg>
                        Activity
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link py-2" id="notes-tab" data-bs-toggle="pill" href="#notes" role="tab" aria-controls="notes" aria-selected="true">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="16"
                            height="16"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.5"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            class="icon icon-tabler icons-tabler-outline icon-tabler-file-text">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M14 3v4a1 1 0 0 0 1 1h4" />
                            <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" />
                            <path d="M9 9l1 0" />
                            <path d="M9 13l6 0" />
                            <path d="M9 17l6 0" />
                        </svg>
                        Notes
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-2" id="email-tab" data-bs-toggle="pill" href="#email" role="tab" aria-controls="email" aria-selected="true">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="16"
                            height="16"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.5"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            class="icon icon-tabler icons-tabler-outline icon-tabler-mail">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M3 7a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v10a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-10z" />
                            <path d="M3 7l9 6l9 -6" />
                        </svg>
                        Emails
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-2" id="call-tab" data-bs-toggle="pill" href="#call" role="tab" aria-controls="call" aria-selected="true">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="16"
                            height="16"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.5"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            class="icon icon-tabler icons-tabler-outline icon-tabler-phone">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M5 4h4l2 5l-2.5 1.5a11 11 0 0 0 5 5l1.5 -2.5l5 2v4a2 2 0 0 1 -2 2a16 16 0 0 1 -15 -15a2 2 0 0 1 2 -2" />
                        </svg>
                        Call
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-2" id="task-tab" data-bs-toggle="pill" href="#task" role="tab" aria-controls="task" aria-selected="true">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="16"
                            height="16"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.5"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            class="icon icon-tabler icons-tabler-outline icon-tabler-list">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M9 6l11 0" />
                            <path d="M9 12l11 0" />
                            <path d="M9 18l11 0" />
                            <path d="M5 6l0 .01" />
                            <path d="M5 12l0 .01" />
                            <path d="M5 18l0 .01" />
                        </svg>
                        Task
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-2" id="meetings-tab" data-bs-toggle="pill" href="#meetings" role="tab" aria-controls="meetings" aria-selected="true">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="16"
                            height="16"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.5"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            class="icon icon-tabler icons-tabler-outline icon-tabler-video">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M15 10l4.553 -2.276a1 1 0 0 1 1.447 .894v6.764a1 1 0 0 1 -1.447 .894l-4.553 -2.276v-4z" />
                            <path d="M3 6m0 2a2 2 0 0 1 2 -2h8a2 2 0 0 1 2 2v8a2 2 0 0 1 -2 2h-8a2 2 0 0 1 -2 -2z" />
                        </svg>
                        Meetings
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-2" id="attachments-tab" data-bs-toggle="pill" href="#attachments" role="tab" aria-controls="attachments" aria-selected="true">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="16"
                            height="16"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.5"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            class="icon icon-tabler icons-tabler-outline icon-tabler-paperclip">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M15 7l-6.5 6.5a1.5 1.5 0 0 0 3 3l6.5 -6.5a3 3 0 0 0 -6 -6l-6.5 6.5a4.5 4.5 0 0 0 9 9l6.5 -6.5" />
                        </svg>
                        Attachments
                    </a>
                </li>
            </ul>

            <div class="tab-content" id="tabContent">
                <div class="tab-pane fade show active" id="activity" role="tabpanel" aria-labelledby="activity-tab">
                    <div class="d-md-flex flex-row justify-content-between mb-6">
                        <div class="mb-2 mb-md-0">
                            <form>
                                <input class="form-control" type="search" value="" placeholder="Search" />
                            </form>
                        </div>
                        <div>
                            <select class="form-select" data-choices>
                                <option selected>Filter Activity</option>
                                <option value="Logged Call">Logged Call</option>
                                <option value="Deal Activity">Deal Activity</option>
                                <option value="Task">Task</option>
                                <option value="Email">Email</option>
                                <option value="Meetings">Meetings</option>
                            </select>
                        </div>
                    </div>
                    <div class="d-flex flex-row flex-column gap-3 mb-10">
                        <div>
                            <h5 class="mb-0">Upcoming</h5>
                        </div>
                        <div class="card card-lg">
                            <div class="card-body d-flex flex-column">
                                <div class="d-flex flex-column">
                                    <div class="d-flex flex-lg-row flex-column align-items-lg-center gap-3 justify-content-between">
                                        <div>
                                            <a
                                                data-bs-toggle="collapse"
                                                class="d-flex flex-row gap-2 text-inherit align-items-center"
                                                href="#collapseExample"
                                                role="button"
                                                aria-expanded="false"
                                                aria-controls="collapseExample">
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    width="16"
                                                    height="16"
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    stroke-width="1.5"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    class="icon icon-tabler icons-tabler-outline icon-tabler-chevron-right chevron-down">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                    <path d="M9 6l6 6l-6 6" />
                                                </svg>

                                                <div class="d-flex flex-md-row flex-column align-items-md-center gap-md-2">
                                                    <div>Logged call - Connected</div>
                                                    <span>by</span>
                                                    <span>Jitu Chauhan</span>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="d-flex flex-row gap-2 text-secondary">
                                            <span>Feb 22, 2025</span>
                                            <span>at 6:17 PM EST</span>
                                        </div>
                                    </div>
                                    <div class="collapse" id="collapseExample">
                                        <div class="d-flex flex-column gap-6 ms-xl-6 mt-6">
                                            <div class="border rounded py-3 px-6 d-flex flex-lg-row flex-column gap-3 justify-content-between">
                                                <div class="d-flex flex-column gap-1 text-secondary">
                                                    <span>Due Date</span>
                                                    <div class="d-flex flex-row gap-3 text-secondary">
                                                        <div class="d-flex flex-row gap-2 align-items-center">
                                                            <svg
                                                                xmlns="http://www.w3.org/2000/svg"
                                                                width="16"
                                                                height="16"
                                                                viewBox="0 0 24 24"
                                                                fill="none"
                                                                stroke="currentColor"
                                                                stroke-width="1.5"
                                                                stroke-linecap="round"
                                                                stroke-linejoin="round"
                                                                class="icon icon-tabler icons-tabler-outline icon-tabler-calendar text-secondary">
                                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                                <path d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z" />
                                                                <path d="M16 3v4" />
                                                                <path d="M8 3v4" />
                                                                <path d="M4 11h16" />
                                                                <path d="M11 15h1" />
                                                                <path d="M12 15v3" />
                                                            </svg>
                                                            <span>Feb 22, 2025</span>
                                                        </div>
                                                        <div class="d-flex flex-row gap-2 align-items-center">
                                                            <svg
                                                                xmlns="http://www.w3.org/2000/svg"
                                                                width="16"
                                                                height="16"
                                                                viewBox="0 0 24 24"
                                                                fill="none"
                                                                stroke="currentColor"
                                                                stroke-width="1.5"
                                                                stroke-linecap="round"
                                                                stroke-linejoin="round"
                                                                class="icon icon-tabler icons-tabler-outline icon-tabler-clock">
                                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                                <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" />
                                                                <path d="M12 7v5l3 3" />
                                                            </svg>
                                                            <span>at 6:17 PM EST</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="d-flex flex-column gap-1 text-secondary">
                                                    <span>Type</span>
                                                    <span>Meeting</span>
                                                </div>
                                                <div class="d-flex flex-column gap-1 text-secondary">
                                                    <span>Contacted</span>
                                                    <span>0 Contacts</span>
                                                </div>
                                            </div>
                                            <div class="d-flex flex-row gap-3">
                                                <img src="../../assets/images/avatar/avatar-1.jpg" alt="avatar" class="avatar avatar-xs rounded-circle" />
                                                <div class="d-flex flex-row align-items-center gap-2">
                                                    <h6 class="mb-0">Jitu Chauhan</h6>
                                                    <span>logged a call</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex flex-row flex-column gap-3">
                        <div>
                            <h5 class="mb-0">January 2025</h5>
                        </div>
                        <div>
                            <ul class="timeline list-unstyled d-flex flex-column">
                                <li class="timeline-event">
                                    <div class="timeline-event-icon">
                                        <div class="icon-shape icon-md text-info-emphasis bg-info-subtle rounded-circle">
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                width="16"
                                                height="16"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="1.5"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                class="icon icon-tabler icons-tabler-outline icon-tabler-phone">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                <path d="M5 4h4l2 5l-2.5 1.5a11 11 0 0 0 5 5l1.5 -2.5l5 2v4a2 2 0 0 1 -2 2a16 16 0 0 1 -15 -15a2 2 0 0 1 2 -2" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="card timeline-event-card card-lg">
                                        <div class="card-body">
                                            <div class="accordion d-flex flex-column" id="accordionExample1">
                                                <div class="d-flex flex-lg-row flex-column align-items-lg-center gap-3 justify-content-between">
                                                    <div>
                                                        <a
                                                            href="#"
                                                            class="d-flex flex-row gap-2 collapsed text-inherit align-items-center"
                                                            data-bs-toggle="collapse"
                                                            data-bs-target="#collapseOne"
                                                            aria-expanded="false"
                                                            aria-controls="collapseOne">
                                                            <svg
                                                                xmlns="http://www.w3.org/2000/svg"
                                                                width="16"
                                                                height="16"
                                                                viewBox="0 0 24 24"
                                                                fill="none"
                                                                stroke="currentColor"
                                                                stroke-width="1.5"
                                                                stroke-linecap="round"
                                                                stroke-linejoin="round"
                                                                class="icon icon-tabler icons-tabler-outline icon-tabler-chevron-right chevron-down">
                                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                                <path d="M9 6l6 6l-6 6" />
                                                            </svg>

                                                            <div class="d-flex flex-md-row flex-column align-items-md-center gap-md-2">
                                                                <div>Logged call - Connected</div>
                                                                <span>by</span>
                                                                <span>Jitu Chauhan</span>
                                                            </div>
                                                        </a>
                                                    </div>
                                                    <div class="d-flex flex-row gap-2 text-secondary">
                                                        <span>Feb 22, 2025</span>
                                                        <span>at 6:17 PM EST</span>
                                                    </div>
                                                </div>
                                                <div id="collapseOne" class="accordion-collapse collapse" data-bs-parent="#accordionExample1">
                                                    <div class="d-flex flex-column gap-6 ms-xl-6 mt-6">
                                                        <div class="border rounded py-3 px-6 d-flex flex-md-row flex-column gap-3 justify-content-between">
                                                            <div class="d-flex flex-column gap-1 text-secondary">
                                                                <span>Due Date</span>
                                                                <div class="d-flex flex-lg-row flex-column gap-1 gap-lg-3 text-secondary">
                                                                    <div class="d-flex flex-row gap-2 align-items-center">
                                                                        <svg
                                                                            xmlns="http://www.w3.org/2000/svg"
                                                                            width="16"
                                                                            height="16"
                                                                            viewBox="0 0 24 24"
                                                                            fill="none"
                                                                            stroke="currentColor"
                                                                            stroke-width="1.5"
                                                                            stroke-linecap="round"
                                                                            stroke-linejoin="round"
                                                                            class="icon icon-tabler icons-tabler-outline icon-tabler-calendar text-secondary">
                                                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                                            <path d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z" />
                                                                            <path d="M16 3v4" />
                                                                            <path d="M8 3v4" />
                                                                            <path d="M4 11h16" />
                                                                            <path d="M11 15h1" />
                                                                            <path d="M12 15v3" />
                                                                        </svg>
                                                                        <span>Feb 22, 2025</span>
                                                                    </div>
                                                                    <div class="d-flex flex-row gap-2 align-items-center">
                                                                        <svg
                                                                            xmlns="http://www.w3.org/2000/svg"
                                                                            width="16"
                                                                            height="16"
                                                                            viewBox="0 0 24 24"
                                                                            fill="none"
                                                                            stroke="currentColor"
                                                                            stroke-width="1.5"
                                                                            stroke-linecap="round"
                                                                            stroke-linejoin="round"
                                                                            class="icon icon-tabler icons-tabler-outline icon-tabler-clock">
                                                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                                            <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" />
                                                                            <path d="M12 7v5l3 3" />
                                                                        </svg>
                                                                        <span>at 6:17 PM EST</span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="d-flex flex-column gap-1 text-secondary">
                                                                <span>Type</span>
                                                                <span>Meeting</span>
                                                            </div>
                                                            <div class="d-flex flex-column gap-1 text-secondary">
                                                                <span>Contacted</span>
                                                                <span>0 Contacts</span>
                                                            </div>
                                                        </div>
                                                        <div class="d-flex flex-row gap-3">
                                                            <img src="../../assets/images/avatar/avatar-1.jpg" alt="avatar" class="avatar avatar-xs rounded-circle" />
                                                            <div class="d-flex flex-row align-items-center gap-2">
                                                                <h6 class="mb-0">Jitu Chauhan</h6>
                                                                <span>logged a call</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>

                                <li class="timeline-event">
                                    <div class="timeline-event-icon">
                                        <div class="icon-shape icon-md text-danger-emphasis bg-danger-subtle rounded-circle">
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                width="16"
                                                height="16"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="1.5"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                class="icon icon-tabler icons-tabler-outline icon-tabler-tag">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                <path d="M7.5 7.5m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                                                <path
                                                    d="M3 6v5.172a2 2 0 0 0 .586 1.414l7.71 7.71a2.41 2.41 0 0 0 3.408 0l5.592 -5.592a2.41 2.41 0 0 0 0 -3.408l-7.71 -7.71a2 2 0 0 0 -1.414 -.586h-5.172a3 3 0 0 0 -3 3z" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="card timeline-event-card card-lg">
                                        <div class="card-body">
                                            <div class="accordion d-flex flex-column" id="accordionExample2">
                                                <div class="d-flex flex-lg-row flex-column align-items-xxl-center gap-3 justify-content-between">
                                                    <div>
                                                        <a
                                                            href="#"
                                                            class="d-flex flex-row collapsed gap-2 text-inherit align-items-center"
                                                            data-bs-toggle="collapse"
                                                            data-bs-target="#collapseTwo"
                                                            aria-expanded="false"
                                                            aria-controls="collapseTwo">
                                                            <svg
                                                                xmlns="http://www.w3.org/2000/svg"
                                                                width="16"
                                                                height="16"
                                                                viewBox="0 0 24 24"
                                                                fill="none"
                                                                stroke="currentColor"
                                                                stroke-width="1.5"
                                                                stroke-linecap="round"
                                                                stroke-linejoin="round"
                                                                class="icon icon-tabler icons-tabler-outline icon-tabler-chevron-right chevron-down">
                                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                                <path d="M9 6l6 6l-6 6" />
                                                            </svg>

                                                            <div class="d-flex flex-xxl-row flex-column align-items-xxl-center gap-xl-2">
                                                                <div>Deal Activity</div>
                                                                <div>
                                                                    <span>Jitu chauhan</span>
                                                                    <span>moved deal to Qualification</span>
                                                                </div>
                                                            </div>
                                                        </a>
                                                    </div>
                                                    <div class="d-flex flex-row gap-2 text-secondary">
                                                        <span>Feb 22, 2025</span>
                                                        <span>at 6:17 PM EST</span>
                                                    </div>
                                                </div>
                                                <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#accordionExample2">
                                                    <div class="d-flex flex-lg-row flex-column gap-lg-4 gap-2 ms-xl-6 mt-6">
                                                        <div>
                                                            <div>
                                                                <span class="text-primary">Jitu chauhan</span>
                                                                <span>moved deal to</span>
                                                                <span>Qualification</span>
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <a href="#!">
                                                                View Details
                                                                <svg
                                                                    xmlns="http://www.w3.org/2000/svg"
                                                                    width="16"
                                                                    height="16"
                                                                    viewBox="0 0 24 24"
                                                                    fill="none"
                                                                    stroke="currentColor"
                                                                    stroke-width="1.5"
                                                                    stroke-linecap="round"
                                                                    stroke-linejoin="round"
                                                                    class="icon icon-tabler icons-tabler-outline icon-tabler-external-link">
                                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                                    <path d="M12 6h-6a2 2 0 0 0 -2 2v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-6" />
                                                                    <path d="M11 13l9 -9" />
                                                                    <path d="M15 4h5v5" />
                                                                </svg>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                <li class="timeline-event">
                                    <div class="timeline-event-icon">
                                        <div class="icon-shape icon-md text-warning-emphasis bg-warning-subtle rounded-circle">
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                width="16"
                                                height="16"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="1.5"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                class="icon icon-tabler icons-tabler-outline icon-tabler-list">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                <path d="M9 6l11 0" />
                                                <path d="M9 12l11 0" />
                                                <path d="M9 18l11 0" />
                                                <path d="M5 6l0 .01" />
                                                <path d="M5 12l0 .01" />
                                                <path d="M5 18l0 .01" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="card timeline-event-card card-lg">
                                        <div class="card-body">
                                            <div class="accordion d-flex flex-column" id="accordionExample3">
                                                <div class="d-flex flex-lg-row flex-column align-items-lg-center gap-2 justify-content-between">
                                                    <div>
                                                        <a
                                                            href="#"
                                                            class="d-flex flex-row collapsed gap-2 text-inherit align-items-center"
                                                            data-bs-toggle="collapse"
                                                            data-bs-target="#collapseThree"
                                                            aria-expanded="false"
                                                            aria-controls="collapseThree">
                                                            <svg
                                                                xmlns="http://www.w3.org/2000/svg"
                                                                width="16"
                                                                height="16"
                                                                viewBox="0 0 24 24"
                                                                fill="none"
                                                                stroke="currentColor"
                                                                stroke-width="1.5"
                                                                stroke-linecap="round"
                                                                stroke-linejoin="round"
                                                                class="icon icon-tabler icons-tabler-outline icon-tabler-chevron-right chevron-down">
                                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                                <path d="M9 6l6 6l-6 6" />
                                                            </svg>

                                                            <div class="d-flex flex-md-row flex-column align-items-md-center gap-md-2">
                                                                <div>Task</div>
                                                                <span>assigned to</span>
                                                                <span>Jitu Chauhan</span>
                                                            </div>
                                                        </a>
                                                    </div>
                                                    <div class="d-flex flex-row gap-2 text-secondary">
                                                        <span>Feb 22, 2025</span>
                                                        <span>at 6:17 PM EST</span>
                                                    </div>
                                                </div>
                                                <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#accordionExample3">
                                                    <div class="d-flex flex-column gap-5 ms-xl-6 mt-6">
                                                        <ul class="list-unstyled mb-0 d-flex flex-column gap-3">
                                                            <li class="d-flex flex-row gap-2">
                                                                <span><svg
                                                                        xmlns="http://www.w3.org/2000/svg"
                                                                        width="16"
                                                                        height="16"
                                                                        viewBox="0 0 24 24"
                                                                        fill="none"
                                                                        stroke="currentColor"
                                                                        stroke-width="1.5"
                                                                        stroke-linecap="round"
                                                                        stroke-linejoin="round"
                                                                        class="icon icon-tabler icons-tabler-outline icon-tabler-circle-check text-primary">
                                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                                        <path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" />
                                                                        <path d="M9 12l2 2l4 -4" />
                                                                    </svg>
                                                                </span>
                                                                <span>Follow up with brian product demo</span>
                                                            </li>
                                                            <li class="d-flex flex-row gap-2">
                                                                <span><svg
                                                                        xmlns="http://www.w3.org/2000/svg"
                                                                        width="16"
                                                                        height="16"
                                                                        viewBox="0 0 24 24"
                                                                        fill="none"
                                                                        stroke="currentColor"
                                                                        stroke-width="1.5"
                                                                        stroke-linecap="round"
                                                                        stroke-linejoin="round"
                                                                        class="icon icon-tabler icons-tabler-outline icon-tabler-circle-check text-primary">
                                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                                        <path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" />
                                                                        <path d="M9 12l2 2l4 -4" />
                                                                    </svg>
                                                                </span>
                                                                <span>Share the demo video</span>
                                                            </li>
                                                        </ul>

                                                        <div class="border rounded py-3 px-6 d-flex flex-lg-row flex-column gap-3 justify-content-between">
                                                            <div class="d-flex flex-column gap-2">
                                                                <span>Assigned to</span>
                                                                <div class="d-flex flex-row align-items-center gap-2">
                                                                    <img src="../../assets/images/avatar/avatar-1.jpg" alt="avatar" class="avatar avatar-xs rounded-circle" />

                                                                    <div class="text-primary">Jitu Chauhan</div>
                                                                </div>
                                                            </div>
                                                            <div class="d-flex flex-column gap-1 text-secondary">
                                                                <span>Due Date</span>
                                                                <div class="d-flex flex-md-row flex-column gap-lg-3 gap-2 text-secondary">
                                                                    <div class="d-flex flex-row gap-2 align-items-center">
                                                                        <svg
                                                                            xmlns="http://www.w3.org/2000/svg"
                                                                            width="16"
                                                                            height="16"
                                                                            viewBox="0 0 24 24"
                                                                            fill="none"
                                                                            stroke="currentColor"
                                                                            stroke-width="1.5"
                                                                            stroke-linecap="round"
                                                                            stroke-linejoin="round"
                                                                            class="icon icon-tabler icons-tabler-outline icon-tabler-calendar-week text-secondary">
                                                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                                            <path d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z" />
                                                                            <path d="M16 3v4" />
                                                                            <path d="M8 3v4" />
                                                                            <path d="M4 11h16" />
                                                                            <path d="M7 14h.013" />
                                                                            <path d="M10.01 14h.005" />
                                                                            <path d="M13.01 14h.005" />
                                                                            <path d="M16.015 14h.005" />
                                                                            <path d="M13.015 17h.005" />
                                                                            <path d="M7.01 17h.005" />
                                                                            <path d="M10.01 17h.005" />
                                                                        </svg>
                                                                        <span>Feb 22, 2025</span>
                                                                    </div>
                                                                    <div class="d-flex flex-row gap-2 align-items-center">
                                                                        <svg
                                                                            xmlns="http://www.w3.org/2000/svg"
                                                                            width="16"
                                                                            height="16"
                                                                            viewBox="0 0 24 24"
                                                                            fill="none"
                                                                            stroke="currentColor"
                                                                            stroke-width="1.5"
                                                                            stroke-linecap="round"
                                                                            stroke-linejoin="round"
                                                                            class="icon icon-tabler icons-tabler-outline icon-tabler-clock">
                                                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                                            <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" />
                                                                            <path d="M12 7v5l3 3" />
                                                                        </svg>
                                                                        <span>at 6:17 PM EST</span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <p class="mb-0">
                                                                Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an
                                                                unknown printer took a galley of type and scrambled it to make a type specimen book. 
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                <li class="timeline-event">
                                    <div class="timeline-event-icon">
                                        <div class="icon-shape icon-md text-success-emphasis bg-success-subtle rounded-circle">
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                width="16"
                                                height="16"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="1.5"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                class="icon icon-tabler icons-tabler-outline icon-tabler-mail">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                <path d="M3 7a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v10a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-10z" />
                                                <path d="M3 7l9 6l9 -6" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="card timeline-event-card card-lg">
                                        <div class="card-body">
                                            <div class="accordion d-flex flex-column" id="accordionExample4">
                                                <div class="d-flex flex-lg-row flex-column align-items-lg-center gap-3 justify-content-between">
                                                    <div>
                                                        <a
                                                            href="#"
                                                            class="d-flex flex-row collapsed gap-2 text-inherit align-items-center"
                                                            data-bs-toggle="collapse"
                                                            data-bs-target="#collapseFour"
                                                            aria-expanded="false"
                                                            aria-controls="collapseFour">
                                                            <svg
                                                                xmlns="http://www.w3.org/2000/svg"
                                                                width="16"
                                                                height="16"
                                                                viewBox="0 0 24 24"
                                                                fill="none"
                                                                stroke="currentColor"
                                                                stroke-width="1.5"
                                                                stroke-linecap="round"
                                                                stroke-linejoin="round"
                                                                class="icon icon-tabler icons-tabler-outline icon-tabler-chevron-right chevron-down">
                                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                                <path d="M9 6l6 6l-6 6" />
                                                            </svg>

                                                            <div class="d-flex flex-md-row flex-column align-items-md-center gap-md-2">
                                                                <div>Email</div>
                                                                <span>assigned to</span>
                                                                <span>Jitu Chauhan</span>
                                                            </div>
                                                        </a>
                                                    </div>
                                                    <div class="d-flex flex-row gap-2 text-secondary">
                                                        <span>Feb 22, 2025</span>
                                                        <span>at 6:17 PM EST</span>
                                                    </div>
                                                </div>
                                                <div id="collapseFour" class="accordion-collapse collapse" data-bs-parent="#accordionExample4">
                                                    <div class="d-flex flex-column gap-5 ms-xl-6 mt-6">
                                                        <ul class="list-unstyled mb-0 d-flex flex-column gap-3">
                                                            <li class="d-flex flex-row gap-2">
                                                                <span><svg
                                                                        xmlns="http://www.w3.org/2000/svg"
                                                                        width="16"
                                                                        height="16"
                                                                        viewBox="0 0 24 24"
                                                                        fill="none"
                                                                        stroke="currentColor"
                                                                        stroke-width="1.5"
                                                                        stroke-linecap="round"
                                                                        stroke-linejoin="round"
                                                                        class="icon icon-tabler icons-tabler-outline icon-tabler-circle-check text-primary">
                                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                                        <path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" />
                                                                        <path d="M9 12l2 2l4 -4" />
                                                                    </svg>
                                                                </span>
                                                                <span>Follow up with brian product demo</span>
                                                            </li>
                                                            <li class="d-flex flex-row gap-2">
                                                                <span><svg
                                                                        xmlns="http://www.w3.org/2000/svg"
                                                                        width="16"
                                                                        height="16"
                                                                        viewBox="0 0 24 24"
                                                                        fill="none"
                                                                        stroke="currentColor"
                                                                        stroke-width="1.5"
                                                                        stroke-linecap="round"
                                                                        stroke-linejoin="round"
                                                                        class="icon icon-tabler icons-tabler-outline icon-tabler-circle-check text-primary">
                                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                                        <path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" />
                                                                        <path d="M9 12l2 2l4 -4" />
                                                                    </svg>
                                                                </span>
                                                                <span>Share the demo video</span>
                                                            </li>
                                                        </ul>

                                                        <div class="border rounded py-3 px-6 d-flex flex-lg-row flex-column gap-3 justify-content-between">
                                                            <div class="d-flex flex-column gap-2">
                                                                <span>Assigned to</span>
                                                                <div class="d-flex flex-row align-items-center gap-2">
                                                                    <img src="../../assets/images/avatar/avatar-1.jpg" alt="avatar" class="avatar avatar-xs rounded-circle" />

                                                                    <div class="text-primary">Jitu Chauhan</div>
                                                                </div>
                                                            </div>
                                                            <div class="d-flex flex-column gap-1 text-secondary">
                                                                <span>Due Date</span>
                                                                <div class="d-flex flex-md-row flex-column gap-lg-3 gap-2 text-secondary">
                                                                    <div class="d-flex flex-row gap-2 align-items-center">
                                                                        <svg
                                                                            xmlns="http://www.w3.org/2000/svg"
                                                                            width="16"
                                                                            height="16"
                                                                            viewBox="0 0 24 24"
                                                                            fill="none"
                                                                            stroke="currentColor"
                                                                            stroke-width="1.5"
                                                                            stroke-linecap="round"
                                                                            stroke-linejoin="round"
                                                                            class="icon icon-tabler icons-tabler-outline icon-tabler-calendar-week text-secondary">
                                                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                                            <path d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z" />
                                                                            <path d="M16 3v4" />
                                                                            <path d="M8 3v4" />
                                                                            <path d="M4 11h16" />
                                                                            <path d="M7 14h.013" />
                                                                            <path d="M10.01 14h.005" />
                                                                            <path d="M13.01 14h.005" />
                                                                            <path d="M16.015 14h.005" />
                                                                            <path d="M13.015 17h.005" />
                                                                            <path d="M7.01 17h.005" />
                                                                            <path d="M10.01 17h.005" />
                                                                        </svg>
                                                                        <span>Feb 22, 2025</span>
                                                                    </div>
                                                                    <div class="d-flex flex-row gap-2 align-items-center">
                                                                        <svg
                                                                            xmlns="http://www.w3.org/2000/svg"
                                                                            width="16"
                                                                            height="16"
                                                                            viewBox="0 0 24 24"
                                                                            fill="none"
                                                                            stroke="currentColor"
                                                                            stroke-width="1.5"
                                                                            stroke-linecap="round"
                                                                            stroke-linejoin="round"
                                                                            class="icon icon-tabler icons-tabler-outline icon-tabler-clock">
                                                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                                            <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" />
                                                                            <path d="M12 7v5l3 3" />
                                                                        </svg>
                                                                        <span>at 6:17 PM EST</span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <p class="mb-0">
                                                                Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an
                                                                unknown printer took a galley of type and scrambled it to make a type specimen book. 
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>

                                <li class="timeline-event">
                                    <div class="timeline-event-icon">
                                        <div class="icon-shape icon-md text-info-emphasis bg-info-subtle rounded-circle">
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                width="16"
                                                height="16"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="1.5"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                class="icon icon-tabler icons-tabler-outline icon-tabler-video">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                <path d="M15 10l4.553 -2.276a1 1 0 0 1 1.447 .894v6.764a1 1 0 0 1 -1.447 .894l-4.553 -2.276v-4z" />
                                                <path d="M3 6m0 2a2 2 0 0 1 2 -2h8a2 2 0 0 1 2 2v8a2 2 0 0 1 -2 2h-8a2 2 0 0 1 -2 -2z" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="card timeline-event-card card-lg">
                                        <div class="card-body">
                                            <div class="accordion d-flex flex-column" id="accordionExample5">
                                                <div class="d-flex flex-lg-row flex-column align-items-lg-center gap-3 justify-content-between">
                                                    <div>
                                                        <a
                                                            href="#"
                                                            class="d-flex flex-row collapsed gap-2 text-inherit align-items-center"
                                                            data-bs-toggle="collapse"
                                                            data-bs-target="#collapseFive"
                                                            aria-expanded="false"
                                                            aria-controls="collapseFive">
                                                            <svg
                                                                xmlns="http://www.w3.org/2000/svg"
                                                                width="16"
                                                                height="16"
                                                                viewBox="0 0 24 24"
                                                                fill="none"
                                                                stroke="currentColor"
                                                                stroke-width="1.5"
                                                                stroke-linecap="round"
                                                                stroke-linejoin="round"
                                                                class="icon icon-tabler icons-tabler-outline icon-tabler-chevron-right chevron-down">
                                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                                <path d="M9 6l6 6l-6 6" />
                                                            </svg>

                                                            <div class="d-flex flex-md-row flex-column align-items-md-center gap-md-2">
                                                                <div>Mettings</div>
                                                                <span>assigned to</span>
                                                                <span>Jitu Chauhan</span>
                                                            </div>
                                                        </a>
                                                    </div>
                                                    <div class="d-flex flex-row gap-2 text-secondary">
                                                        <span>Feb 22, 2025</span>
                                                        <span>at 6:17 PM EST</span>
                                                    </div>
                                                </div>
                                                <div id="collapseFive" class="accordion-collapse collapse" data-bs-parent="#accordionExample5">
                                                    <div class="d-flex flex-column gap-3 ms-xl-6 mt-6">
                                                        <div>
                                                            Onboarding with
                                                            <a href="#!">Sandeep Chauhan</a>
                                                        </div>

                                                        <div class="border rounded py-3 px-6">
                                                            <div class="row gy-4">
                                                                <div class="col-xxl-5 col-md-6">
                                                                    <div class="d-flex flex-column gap-1 text-secondary">
                                                                        <div>
                                                                            <span>Start Time</span>
                                                                        </div>
                                                                        <div class="d-flex flex-xl-row flex-column gap-xl-4 gap-2 text-secondary">
                                                                            <div class="d-flex align-items-center gap-1 lh-1">
                                                                                <svg
                                                                                    xmlns="http://www.w3.org/2000/svg"
                                                                                    width="16"
                                                                                    height="16"
                                                                                    viewBox="0 0 24 24"
                                                                                    fill="none"
                                                                                    stroke="currentColor"
                                                                                    stroke-width="1.5"
                                                                                    stroke-linecap="round"
                                                                                    stroke-linejoin="round"
                                                                                    class="icon icon-tabler icons-tabler-outline icon-tabler-calendar-week text-secondary">
                                                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                                                    <path d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z" />
                                                                                    <path d="M16 3v4" />
                                                                                    <path d="M8 3v4" />
                                                                                    <path d="M4 11h16" />
                                                                                    <path d="M7 14h.013" />
                                                                                    <path d="M10.01 14h.005" />
                                                                                    <path d="M13.01 14h.005" />
                                                                                    <path d="M16.015 14h.005" />
                                                                                    <path d="M13.015 17h.005" />
                                                                                    <path d="M7.01 17h.005" />
                                                                                    <path d="M10.01 17h.005" />
                                                                                </svg>
                                                                                <span>Feb 22, 2025</span>
                                                                            </div>
                                                                            <div>
                                                                                <svg
                                                                                    xmlns="http://www.w3.org/2000/svg"
                                                                                    width="16"
                                                                                    height="16"
                                                                                    viewBox="0 0 24 24"
                                                                                    fill="none"
                                                                                    stroke="currentColor"
                                                                                    stroke-width="1.5"
                                                                                    stroke-linecap="round"
                                                                                    stroke-linejoin="round"
                                                                                    class="icon icon-tabler icons-tabler-outline icon-tabler-clock">
                                                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                                                    <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" />
                                                                                    <path d="M12 7v5l3 3" />
                                                                                </svg>
                                                                                <span>at 6:17 PM EST</span>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-xxl-3 col-md-6">
                                                                    <div class="d-flex flex-column gap-1 text-secondary">
                                                                        <span>Duration</span>
                                                                        <span>1 Hour 30 minutes</span>
                                                                    </div>
                                                                </div>
                                                                <div class="col-xxl-2 col-md-6">
                                                                    <div class="d-flex flex-column gap-1 text-secondary">
                                                                        <span>Attendees</span>
                                                                        <span>2 Attendees</span>
                                                                    </div>
                                                                </div>
                                                                <div class="col-xxl-2 col-md-6">
                                                                    <div class="d-flex flex-column gap-1 text-secondary">
                                                                        <span>Via</span>
                                                                        <span>Zoom Meeting</span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                <li class="timeline-event">
                                    <div class="timeline-event-icon">
                                        <div class="icon-shape icon-md text-secondary-emphasis bg-secondary-subtle rounded-circle">
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                width="16"
                                                height="16"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="1.5"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                class="icon icon-tabler icons-tabler-outline icon-tabler-paperclip">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                <path d="M15 7l-6.5 6.5a1.5 1.5 0 0 0 3 3l6.5 -6.5a3 3 0 0 0 -6 -6l-6.5 6.5a4.5 4.5 0 0 0 9 9l6.5 -6.5" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="card timeline-event-card card-lg">
                                        <div class="card-body">
                                            <div class="accordion d-flex flex-column" id="accordionExample6">
                                                <div class="d-flex flex-lg-row flex-column align-items-lg-center gap-3 justify-content-between">
                                                    <div>
                                                        <a
                                                            href="#"
                                                            class="d-flex flex-row collapsed gap-2 text-inherit align-items-center"
                                                            data-bs-toggle="collapse"
                                                            data-bs-target="#collapseSix"
                                                            aria-expanded="false"
                                                            aria-controls="collapseSix">
                                                            <svg
                                                                xmlns="http://www.w3.org/2000/svg"
                                                                width="16"
                                                                height="16"
                                                                viewBox="0 0 24 24"
                                                                fill="none"
                                                                stroke="currentColor"
                                                                stroke-width="1.5"
                                                                stroke-linecap="round"
                                                                stroke-linejoin="round"
                                                                class="icon icon-tabler icons-tabler-outline icon-tabler-chevron-right chevron-down">
                                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                                <path d="M9 6l6 6l-6 6" />
                                                            </svg>

                                                            <div class="d-flex flex-md-row flex-column align-items-md-center gap-md-2">
                                                                <div>Attachments</div>
                                                                <span>assigned to</span>
                                                                <span>Jitu Chauhan</span>
                                                            </div>
                                                        </a>
                                                    </div>
                                                    <div class="d-flex flex-row gap-2 text-secondary">
                                                        <span>Feb 22, 2025</span>
                                                        <span>at 6:17 PM EST</span>
                                                    </div>
                                                </div>
                                                <div id="collapseSix" class="accordion-collapse collapse" data-bs-parent="#accordionExample6">
                                                    <div class="border rounded p-4 d-flex flex-lg-row flex-column gap-3 justify-content-between align-items-lg-center ms-xl-6 mt-6">
                                                        <div class="d-flex flex-column gap-2">
                                                            <div class="d-flex flex-row gap-2 align-items-center">
                                                                <svg
                                                                    xmlns="http://www.w3.org/2000/svg"
                                                                    width="16"
                                                                    height="16"
                                                                    viewBox="0 0 24 24"
                                                                    fill="none"
                                                                    stroke="currentColor"
                                                                    stroke-width="1.5"
                                                                    stroke-linecap="round"
                                                                    stroke-linejoin="round"
                                                                    class="icon icon-tabler icons-tabler-outline icon-tabler-file">
                                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                                    <path d="M14 3v4a1 1 0 0 0 1 1h4" />
                                                                    <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" />
                                                                </svg>
                                                                <div>Proposal.pdf</div>
                                                            </div>
                                                            <div class="fs-6">
                                                                <span>1.03mb</span>
                                                                <div class="vr mx-1"></div>
                                                                <span>March 19, 2025 11:53 AM</span>
                                                                <div class="vr mx-1"></div>
                                                                <span>
                                                                    Uploaded by:
                                                                    <span class="text-primary">Anita Parmar</span>
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div class="d-flex flex-row">
                                                            <a href="#!" class="btn btn-icon btn-ghost btn-sm rounded-circle">
                                                                <svg
                                                                    xmlns="http://www.w3.org/2000/svg"
                                                                    width="16"
                                                                    height="16"
                                                                    viewBox="0 0 24 24"
                                                                    fill="none"
                                                                    stroke="currentColor"
                                                                    stroke-width="1.5"
                                                                    stroke-linecap="round"
                                                                    stroke-linejoin="round"
                                                                    class="icon icon-tabler icons-tabler-outline icon-tabler-download">
                                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                                    <path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" />
                                                                    <path d="M7 11l5 5l5 -5" />
                                                                    <path d="M12 4l0 12" />
                                                                </svg>
                                                            </a>
                                                            <a href="#!" class="btn btn-icon btn-ghost btn-sm rounded-circle">
                                                                <svg
                                                                    xmlns="http://www.w3.org/2000/svg"
                                                                    width="16"
                                                                    height="16"
                                                                    viewBox="0 0 24 24"
                                                                    fill="none"
                                                                    stroke="currentColor"
                                                                    stroke-width="1.5"
                                                                    stroke-linecap="round"
                                                                    stroke-linejoin="round"
                                                                    class="icon icon-tabler icons-tabler-outline icon-tabler-trash">
                                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                                    <path d="M4 7l16 0" />
                                                                    <path d="M10 11l0 6" />
                                                                    <path d="M14 11l0 6" />
                                                                    <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" />
                                                                    <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" />
                                                                </svg>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>

                                <li class="timeline-event">
                                    <div class="timeline-event-icon">
                                        <div class="icon-shape icon-md text-primary-emphasis bg-primary-subtle rounded-circle">
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                width="16"
                                                height="16"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="1.5"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                class="icon icon-tabler icons-tabler-outline icon-tabler-file-text">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                <path d="M14 3v4a1 1 0 0 0 1 1h4" />
                                                <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" />
                                                <path d="M9 9l1 0" />
                                                <path d="M9 13l6 0" />
                                                <path d="M9 17l6 0" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="card timeline-event-card card-lg">
                                        <div class="card-body">
                                            <div class="accordion d-flex flex-column" id="accordionExample7">
                                                <div class="d-flex flex-lg-row flex-column align-items-lg-center gap-3 justify-content-between">
                                                    <div>
                                                        <a
                                                            href="#"
                                                            class="d-flex flex-row collapsed gap-2 text-inherit align-items-center"
                                                            data-bs-toggle="collapse"
                                                            data-bs-target="#collapseSeven"
                                                            aria-expanded="false"
                                                            aria-controls="collapseSeven">
                                                            <svg
                                                                xmlns="http://www.w3.org/2000/svg"
                                                                width="16"
                                                                height="16"
                                                                viewBox="0 0 24 24"
                                                                fill="none"
                                                                stroke="currentColor"
                                                                stroke-width="1.5"
                                                                stroke-linecap="round"
                                                                stroke-linejoin="round"
                                                                class="icon icon-tabler icons-tabler-outline icon-tabler-chevron-right chevron-down">
                                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                                <path d="M9 6l6 6l-6 6" />
                                                            </svg>

                                                            <div class="d-flex flex-md-row flex-column align-items-md-center gap-md-2">
                                                                <h6 class="mb-0">Notes <span>Assigned to</span></h6>
                                                                <span>Jitu Chauhan</span>
                                                            </div>
                                                        </a>
                                                    </div>
                                                    <div class="d-flex flex-row gap-2 text-secondary">
                                                        <span>Feb 22, 2025</span>
                                                        <span>at 6:17 PM EST</span>
                                                    </div>
                                                </div>
                                                <div id="collapseSeven" class="accordion-collapse collapse" data-bs-parent="#accordionExample7">
                                                    <div class="d-flex flex-column gap-4 ms-xl-5 mt-6">
                                                        <div>Interaction Type: Phone Call</div>

                                                        <div class="d-flex flex-column gap-2">
                                                            <div>Summary:</div>
                                                            <ul class="list-unstyled mb-0">
                                                                <li class="d-flex gap-2 d-flex align-items-center">
                                                                    <svg
                                                                        xmlns="http://www.w3.org/2000/svg"
                                                                        width="12"
                                                                        height="12"
                                                                        viewBox="0 0 24 24"
                                                                        fill="currentColor"
                                                                        class="icon icon-tabler icons-tabler-filled icon-tabler-point">
                                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                                        <path d="M12 7a5 5 0 1 1 -4.995 5.217l-.005 -.217l.005 -.217a5 5 0 0 1 4.995 -4.783z" />
                                                                    </svg>
                                                                    Discussed upcoming product launch event.
                                                                </li>
                                                                <li class="d-flex gap-2 d-flex align-items-center">
                                                                    <svg
                                                                        xmlns="http://www.w3.org/2000/svg"
                                                                        width="12"
                                                                        height="12"
                                                                        viewBox="0 0 24 24"
                                                                        fill="currentColor"
                                                                        class="icon icon-tabler icons-tabler-filled icon-tabler-point">
                                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                                        <path d="M12 7a5 5 0 1 1 -4.995 5.217l-.005 -.217l.005 -.217a5 5 0 0 1 4.995 -4.783z" />
                                                                    </svg>
                                                                    John expressed interest in attending and requested more information about the agenda.
                                                                </li>
                                                                <li class="d-flex gap-2 d-flex align-items-center">
                                                                    <svg
                                                                        xmlns="http://www.w3.org/2000/svg"
                                                                        width="12"
                                                                        height="12"
                                                                        viewBox="0 0 24 24"
                                                                        fill="currentColor"
                                                                        class="icon icon-tabler icons-tabler-filled icon-tabler-point">
                                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                                        <path d="M12 7a5 5 0 1 1 -4.995 5.217l-.005 -.217l.005 -.217a5 5 0 0 1 4.995 -4.783z" />
                                                                    </svg>
                                                                    Promised to send John an email with event details and registration link by end of day.
                                                                </li>

                                                                <li class="d-flex gap-2 d-flex align-items-center">
                                                                    <svg
                                                                        xmlns="http://www.w3.org/2000/svg"
                                                                        width="12"
                                                                        height="12"
                                                                        viewBox="0 0 24 24"
                                                                        fill="currentColor"
                                                                        class="icon icon-tabler icons-tabler-filled icon-tabler-point">
                                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                                        <path d="M12 7a5 5 0 1 1 -4.995 5.217l-.005 -.217l.005 -.217a5 5 0 0 1 4.995 -4.783z" />
                                                                    </svg>
                                                                    Agreed to follow up with John next week to confirm attendance.
                                                                </li>
                                                            </ul>
                                                        </div>
                                                        <div class="d-flex flex-column gap-2">
                                                            <div>Follow-Up Actions:</div>
                                                            <ul class="list-unstyled mb-0">
                                                                <li class="d-flex gap-2 d-flex align-items-center">
                                                                    <svg
                                                                        xmlns="http://www.w3.org/2000/svg"
                                                                        width="12"
                                                                        height="12"
                                                                        viewBox="0 0 24 24"
                                                                        fill="currentColor"
                                                                        class="icon icon-tabler icons-tabler-filled icon-tabler-point">
                                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                                        <path d="M12 7a5 5 0 1 1 -4.995 5.217l-.005 -.217l.005 -.217a5 5 0 0 1 4.995 -4.783z" />
                                                                    </svg>
                                                                    Send email to John with event details and registration link.
                                                                </li>
                                                                <li class="d-flex gap-2 d-flex align-items-center">
                                                                    <svg
                                                                        xmlns="http://www.w3.org/2000/svg"
                                                                        width="12"
                                                                        height="12"
                                                                        viewBox="0 0 24 24"
                                                                        fill="currentColor"
                                                                        class="icon icon-tabler icons-tabler-filled icon-tabler-point">
                                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                                        <path d="M12 7a5 5 0 1 1 -4.995 5.217l-.005 -.217l.005 -.217a5 5 0 0 1 4.995 -4.783z" />
                                                                    </svg>
                                                                    Schedule follow-up call with John for next week.
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                <li class="timeline-event">
                                    <div class="timeline-event-icon">
                                        <div class="icon-shape icon-md text-secondary-emphasis bg-secondary-subtle rounded-circle">
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                width="16"
                                                height="16"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="1.5"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                class="icon icon-tabler icons-tabler-outline icon-tabler-send">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                <path d="M10 14l11 -11" />
                                                <path d="M21 3l-6.5 18a.55 .55 0 0 1 -1 0l-3.5 -7l-7 -3.5a.55 .55 0 0 1 0 -1l18 -6.5" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="card timeline-event-card card-lg">
                                        <div class="card-body">
                                            <div class="d-lg-flex flex-row justify-content-between align-items-center">
                                                <div class="d-flex flex-row gap-2 text-secondary">
                                                    <span>
                                                        This deal was created by
                                                        <a href="#!">Jitu Chauhan</a>
                                                    </span>
                                                </div>

                                                <div class="d-flex flex-row gap-2 text-secondary">
                                                    <span>Jan 3, 2025</span>
                                                    <span>at 12:15 PM EST</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="tab-pane" id="notes" role="tabpanel" aria-labelledby="notes-tab">
                    <div>
                        <h5 class="mb-5">Notes</h5>
                    </div>
                    <div class="card mb-4 card-lg">
                        <div class="card-body">
                            <div class="accordion d-flex flex-column" id="accordionExample8">
                                <div class="d-flex flex-lg-row flex-column align-items-lg-center gap-3 justify-content-between">
                                    <div>
                                        <a
                                            href="#"
                                            class="d-flex flex-row collapsed gap-2 text-inherit align-items-center"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#collapseEight"
                                            aria-expanded="false"
                                            aria-controls="collapseEight">
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                width="16"
                                                height="16"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="1.5"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                class="icon icon-tabler icons-tabler-outline icon-tabler-chevron-right chevron-down">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                <path d="M9 6l6 6l-6 6"></path>
                                            </svg>

                                            <div class="d-flex flex-md-row flex-column align-items-md-center gap-md-2">
                                                <h6 class="mb-0">Notes <span>by</span></h6>
                                                <span>Jitu Chauhan</span>
                                            </div>
                                        </a>
                                    </div>
                                    <div class="d-flex flex-row gap-2 text-secondary">
                                        <span>Feb 22, 2025</span>
                                        <span>at 6:17 PM EST</span>
                                    </div>
                                </div>
                                <div id="collapseEight" class="accordion-collapse collapse" data-bs-parent="#accordionExample8">
                                    <div class="d-flex flex-column gap-4 ms-xl-5 mt-6">
                                        <div>Interaction Type: Phone Call</div>

                                        <div class="d-flex flex-column gap-2">
                                            <div>Summary:</div>
                                            <ul class="list-unstyled mb-0">
                                                <li class="d-flex gap-2 d-flex align-items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="currentColor" class="icon icon-tabler icons-tabler-filled icon-tabler-point">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                        <path d="M12 7a5 5 0 1 1 -4.995 5.217l-.005 -.217l.005 -.217a5 5 0 0 1 4.995 -4.783z" />
                                                    </svg>
                                                    Discussed upcoming product launch event.
                                                </li>
                                                <li class="d-flex gap-2 d-flex align-items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="currentColor" class="icon icon-tabler icons-tabler-filled icon-tabler-point">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                        <path d="M12 7a5 5 0 1 1 -4.995 5.217l-.005 -.217l.005 -.217a5 5 0 0 1 4.995 -4.783z" />
                                                    </svg>
                                                    John expressed interest in attending and requested more information about the agenda.
                                                </li>
                                                <li class="d-flex gap-2 d-flex align-items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="currentColor" class="icon icon-tabler icons-tabler-filled icon-tabler-point">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                        <path d="M12 7a5 5 0 1 1 -4.995 5.217l-.005 -.217l.005 -.217a5 5 0 0 1 4.995 -4.783z" />
                                                    </svg>
                                                    Promised to send John an email with event details and registration link by end of day.
                                                </li>

                                                <li class="d-flex gap-2 d-flex align-items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="currentColor" class="icon icon-tabler icons-tabler-filled icon-tabler-point">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                        <path d="M12 7a5 5 0 1 1 -4.995 5.217l-.005 -.217l.005 -.217a5 5 0 0 1 4.995 -4.783z" />
                                                    </svg>
                                                    Agreed to follow up with John next week to confirm attendance.
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="d-flex flex-column gap-2">
                                            <div>Follow-Up Actions:</div>
                                            <ul class="list-unstyled mb-0">
                                                <li class="d-flex gap-2 d-flex align-items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="currentColor" class="icon icon-tabler icons-tabler-filled icon-tabler-point">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                        <path d="M12 7a5 5 0 1 1 -4.995 5.217l-.005 -.217l.005 -.217a5 5 0 0 1 4.995 -4.783z" />
                                                    </svg>
                                                    Send email to John with event details and registration link.
                                                </li>
                                                <li class="d-flex gap-2 d-flex align-items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="currentColor" class="icon icon-tabler icons-tabler-filled icon-tabler-point">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                        <path d="M12 7a5 5 0 1 1 -4.995 5.217l-.005 -.217l.005 -.217a5 5 0 0 1 4.995 -4.783z" />
                                                    </svg>
                                                    Schedule follow-up call with John for next week.
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card mb-4 card-lg">
                        <div class="card-body">
                            <div class="accordion d-flex flex-column" id="accordionExample9">
                                <div class="d-flex flex-lg-row flex-column align-items-lg-center gap-3 justify-content-between">
                                    <div>
                                        <a
                                            href="#"
                                            class="d-flex flex-row collapsed gap-2 text-inherit align-items-center"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#collapseNine"
                                            aria-expanded="false"
                                            aria-controls="collapseNine">
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                width="16"
                                                height="16"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="1.5"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                class="icon icon-tabler icons-tabler-outline icon-tabler-chevron-right chevron-down">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                <path d="M9 6l6 6l-6 6" />
                                            </svg>

                                            <div class="d-flex flex-md-row flex-column align-items-md-center gap-md-2">
                                                <div class="d-flex flex-md-row align-items-md-center gap-2">
                                                    <h6 class="mb-0">Notes <span>by</span></h6>
                                                    <span>Jitu Chauhan</span>
                                                </div>
                                                <span class="text-gray-800 fw-semi-bold">Follow-up Call with Client XYZ</span>
                                            </div>
                                        </a>
                                    </div>
                                    <div class="d-flex flex-row gap-2 text-secondary">
                                        <span>Feb 22, 2025</span>
                                        <span>at 6:17 PM EST</span>
                                    </div>
                                </div>
                                <div id="collapseNine" class="accordion-collapse collapse" data-bs-parent="#accordionExample9">
                                    <div class="d-flex flex-column gap-4 ms-xl-5 mt-6">
                                        <div>Interaction Type: Phone Call</div>

                                        <div class="d-flex flex-column gap-2">
                                            <div>Summary:</div>
                                            <ul class="list-unstyled mb-0">
                                                <li class="d-flex gap-2 d-flex align-items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="currentColor" class="icon icon-tabler icons-tabler-filled icon-tabler-point">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                        <path d="M12 7a5 5 0 1 1 -4.995 5.217l-.005 -.217l.005 -.217a5 5 0 0 1 4.995 -4.783z" />
                                                    </svg>
                                                    Discussed upcoming product launch event.
                                                </li>
                                                <li class="d-flex gap-2 d-flex align-items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="currentColor" class="icon icon-tabler icons-tabler-filled icon-tabler-point">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                        <path d="M12 7a5 5 0 1 1 -4.995 5.217l-.005 -.217l.005 -.217a5 5 0 0 1 4.995 -4.783z" />
                                                    </svg>
                                                    John expressed interest in attending and requested more information about the agenda.
                                                </li>
                                                <li class="d-flex gap-2 d-flex align-items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="currentColor" class="icon icon-tabler icons-tabler-filled icon-tabler-point">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                        <path d="M12 7a5 5 0 1 1 -4.995 5.217l-.005 -.217l.005 -.217a5 5 0 0 1 4.995 -4.783z" />
                                                    </svg>
                                                    Promised to send John an email with event details and registration link by end of day.
                                                </li>

                                                <li class="d-flex gap-2 d-flex align-items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="currentColor" class="icon icon-tabler icons-tabler-filled icon-tabler-point">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                        <path d="M12 7a5 5 0 1 1 -4.995 5.217l-.005 -.217l.005 -.217a5 5 0 0 1 4.995 -4.783z" />
                                                    </svg>
                                                    Agreed to follow up with John next week to confirm attendance.
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="d-flex flex-column gap-2">
                                            <div>Follow-Up Actions:</div>
                                            <ul class="list-unstyled mb-0">
                                                <li class="d-flex gap-2 d-flex align-items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="currentColor" class="icon icon-tabler icons-tabler-filled icon-tabler-point">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                        <path d="M12 7a5 5 0 1 1 -4.995 5.217l-.005 -.217l.005 -.217a5 5 0 0 1 4.995 -4.783z" />
                                                    </svg>
                                                    Send email to John with event details and registration link.
                                                </li>
                                                <li class="d-flex gap-2 d-flex align-items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="currentColor" class="icon icon-tabler icons-tabler-filled icon-tabler-point">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                        <path d="M12 7a5 5 0 1 1 -4.995 5.217l-.005 -.217l.005 -.217a5 5 0 0 1 4.995 -4.783z" />
                                                    </svg>
                                                    Schedule follow-up call with John for next week.
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card mb-4 card-lg">
                        <div class="card-body">
                            <div class="accordion d-flex flex-column" id="accordionExample10">
                                <div class="d-flex flex-lg-row flex-column align-items-xxl-center gap-3 justify-content-between">
                                    <div>
                                        <a
                                            href="#"
                                            class="d-flex flex-row collapsed gap-2 text-inherit align-items-center"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#collapseTen"
                                            aria-expanded="false"
                                            aria-controls="collapseTen">
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                width="16"
                                                height="16"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="1.5"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                class="icon icon-tabler icons-tabler-outline icon-tabler-chevron-right chevron-down">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                <path d="M9 6l6 6l-6 6" />
                                            </svg>

                                            <div class="d-flex flex-xxl-row flex-column align-items-xxl-center gap-xl-2">
                                                <div class="d-flex flex-row align-items-md-center gap-2">
                                                    <h6 class="mb-0">Notes <span>by</span></h6>
                                                    <span>Jitu Chauhan</span>
                                                </div>
                                                <span class="text-gray-800 fw-semi-bold">Follow-up Email Sent to Prospect ABC</span>
                                            </div>
                                        </a>
                                    </div>
                                    <div class="d-flex flex-row gap-2 text-secondary">
                                        <span>Feb 22, 2025</span>
                                        <span>at 6:17 PM EST</span>
                                    </div>
                                </div>
                                <div id="collapseTen" class="accordion-collapse collapse" data-bs-parent="#accordionExample10">
                                    <div class="d-flex flex-column gap-4 ms-xl-5 mt-6">
                                        <div>Interaction Type: Phone Call</div>

                                        <div class="d-flex flex-column gap-2">
                                            <div>Summary:</div>
                                            <ul class="list-unstyled mb-0">
                                                <li class="d-flex gap-2 d-flex align-items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="currentColor" class="icon icon-tabler icons-tabler-filled icon-tabler-point">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                        <path d="M12 7a5 5 0 1 1 -4.995 5.217l-.005 -.217l.005 -.217a5 5 0 0 1 4.995 -4.783z" />
                                                    </svg>
                                                    Discussed upcoming product launch event.
                                                </li>
                                                <li class="d-flex gap-2 d-flex align-items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="currentColor" class="icon icon-tabler icons-tabler-filled icon-tabler-point">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                        <path d="M12 7a5 5 0 1 1 -4.995 5.217l-.005 -.217l.005 -.217a5 5 0 0 1 4.995 -4.783z" />
                                                    </svg>
                                                    John expressed interest in attending and requested more information about the agenda.
                                                </li>
                                                <li class="d-flex gap-2 d-flex align-items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="currentColor" class="icon icon-tabler icons-tabler-filled icon-tabler-point">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                        <path d="M12 7a5 5 0 1 1 -4.995 5.217l-.005 -.217l.005 -.217a5 5 0 0 1 4.995 -4.783z" />
                                                    </svg>
                                                    Promised to send John an email with event details and registration link by end of day.
                                                </li>

                                                <li class="d-flex gap-2 d-flex align-items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="currentColor" class="icon icon-tabler icons-tabler-filled icon-tabler-point">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                        <path d="M12 7a5 5 0 1 1 -4.995 5.217l-.005 -.217l.005 -.217a5 5 0 0 1 4.995 -4.783z" />
                                                    </svg>
                                                    Agreed to follow up with John next week to confirm attendance.
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="d-flex flex-column gap-2">
                                            <div>Follow-Up Actions:</div>
                                            <ul class="list-unstyled mb-0">
                                                <li class="d-flex gap-2 d-flex align-items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="currentColor" class="icon icon-tabler icons-tabler-filled icon-tabler-point">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                        <path d="M12 7a5 5 0 1 1 -4.995 5.217l-.005 -.217l.005 -.217a5 5 0 0 1 4.995 -4.783z" />
                                                    </svg>
                                                    Send email to John with event details and registration link.
                                                </li>
                                                <li class="d-flex gap-2 d-flex align-items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="currentColor" class="icon icon-tabler icons-tabler-filled icon-tabler-point">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                        <path d="M12 7a5 5 0 1 1 -4.995 5.217l-.005 -.217l.005 -.217a5 5 0 0 1 4.995 -4.783z" />
                                                    </svg>
                                                    Schedule follow-up call with John for next week.
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4 card-lg">
                        <div class="card-body">
                            <div class="accordion d-flex flex-column" id="accordionExample11">
                                <div class="d-flex flex-lg-row flex-column align-items-lg-center gap-3 justify-content-between">
                                    <div>
                                        <a
                                            href="#"
                                            class="d-flex flex-row collapsed gap-2 text-inherit align-items-center"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#collapseEleven"
                                            aria-expanded="false"
                                            aria-controls="collapseEleven">
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                width="16"
                                                height="16"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="1.5"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                class="icon icon-tabler icons-tabler-outline icon-tabler-chevron-right chevron-down">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                <path d="M9 6l6 6l-6 6" />
                                            </svg>

                                            <div class="d-flex flex-md-row flex-column align-items-md-center gap-md-2">
                                                <div class="d-flex flex-md-row align-items-md-center gap-2">
                                                    <h6 class="mb-0">Notes <span>by</span></h6>
                                                    <span>Anita Parmar</span>
                                                </div>
                                                <span class="text-gray-800 fw-semi-bold">Meeting with Client XYZ</span>
                                            </div>
                                        </a>
                                    </div>
                                    <div class="d-flex flex-row gap-2 text-secondary">
                                        <span>Feb 22, 2025</span>
                                        <span>at 6:17 PM EST</span>
                                    </div>
                                </div>
                                <div id="collapseEleven" class="accordion-collapse collapse" data-bs-parent="#accordionExample11">
                                    <div class="d-flex flex-column gap-4 ms-xl-5 mt-6">
                                        <div>Interaction Type: Phone Call</div>

                                        <div class="d-flex flex-column gap-2">
                                            <div>Summary:</div>
                                            <ul class="list-unstyled mb-0">
                                                <li class="d-flex gap-2 d-flex align-items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="currentColor" class="icon icon-tabler icons-tabler-filled icon-tabler-point">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                        <path d="M12 7a5 5 0 1 1 -4.995 5.217l-.005 -.217l.005 -.217a5 5 0 0 1 4.995 -4.783z" />
                                                    </svg>
                                                    Discussed upcoming product launch event.
                                                </li>
                                                <li class="d-flex gap-2 d-flex align-items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="currentColor" class="icon icon-tabler icons-tabler-filled icon-tabler-point">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                        <path d="M12 7a5 5 0 1 1 -4.995 5.217l-.005 -.217l.005 -.217a5 5 0 0 1 4.995 -4.783z" />
                                                    </svg>
                                                    John expressed interest in attending and requested more information about the agenda.
                                                </li>
                                                <li class="d-flex gap-2 d-flex align-items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="currentColor" class="icon icon-tabler icons-tabler-filled icon-tabler-point">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                        <path d="M12 7a5 5 0 1 1 -4.995 5.217l-.005 -.217l.005 -.217a5 5 0 0 1 4.995 -4.783z" />
                                                    </svg>
                                                    Promised to send John an email with event details and registration link by end of day.
                                                </li>

                                                <li class="d-flex gap-2 d-flex align-items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="currentColor" class="icon icon-tabler icons-tabler-filled icon-tabler-point">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                        <path d="M12 7a5 5 0 1 1 -4.995 5.217l-.005 -.217l.005 -.217a5 5 0 0 1 4.995 -4.783z" />
                                                    </svg>
                                                    Agreed to follow up with John next week to confirm attendance.
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="d-flex flex-column gap-2">
                                            <div>Follow-Up Actions:</div>
                                            <ul class="list-unstyled mb-0">
                                                <li class="d-flex gap-2 d-flex align-items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="currentColor" class="icon icon-tabler icons-tabler-filled icon-tabler-point">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                        <path d="M12 7a5 5 0 1 1 -4.995 5.217l-.005 -.217l.005 -.217a5 5 0 0 1 4.995 -4.783z" />
                                                    </svg>
                                                    Send email to John with event details and registration link.
                                                </li>
                                                <li class="d-flex gap-2 d-flex align-items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="currentColor" class="icon icon-tabler icons-tabler-filled icon-tabler-point">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                        <path d="M12 7a5 5 0 1 1 -4.995 5.217l-.005 -.217l.005 -.217a5 5 0 0 1 4.995 -4.783z" />
                                                    </svg>
                                                    Schedule follow-up call with John for next week.
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card mb-4 card-lg">
                        <div class="card-body">
                            <div class="accordion d-flex flex-column" id="accordionExample12">
                                <div class="d-flex flex-lg-row flex-column align-items-lg-center gap-3 justify-content-between">
                                    <div>
                                        <a
                                            href="#"
                                            class="d-flex flex-row collapsed gap-2 text-inherit align-items-center"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#collapseTwelve"
                                            aria-expanded="false"
                                            aria-controls="collapseTwelve">
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                width="16"
                                                height="16"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="1.5"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                class="icon icon-tabler icons-tabler-outline icon-tabler-chevron-right chevron-down">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                <path d="M9 6l6 6l-6 6" />
                                            </svg>

                                            <div class="d-flex flex-md-row flex-column align-items-md-center gap-md-2">
                                                <div class="d-flex flex-md-row align-items-md-center gap-2">
                                                    <h6 class="mb-0">Notes <span>by</span></h6>
                                                    <span>Sandip Chauhan</span>
                                                </div>
                                                <span class="text-gray-800 fw-semi-bold">Lead Follow-up Call</span>
                                            </div>
                                        </a>
                                    </div>
                                    <div class="d-flex flex-row gap-2 text-secondary">
                                        <span>Feb 22, 2025</span>
                                        <span>at 6:17 PM EST</span>
                                    </div>
                                </div>
                                <div id="collapseTwelve" class="accordion-collapse collapse" data-bs-parent="#accordionExample12">
                                    <div class="d-flex flex-column gap-4 ms-xl-5 mt-6">
                                        <div>Interaction Type: Phone Call</div>

                                        <div class="d-flex flex-column gap-2">
                                            <div>Summary:</div>
                                            <ul class="list-unstyled mb-0">
                                                <li class="d-flex gap-2 d-flex align-items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="currentColor" class="icon icon-tabler icons-tabler-filled icon-tabler-point">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                        <path d="M12 7a5 5 0 1 1 -4.995 5.217l-.005 -.217l.005 -.217a5 5 0 0 1 4.995 -4.783z" />
                                                    </svg>
                                                    Discussed upcoming product launch event.
                                                </li>
                                                <li class="d-flex gap-2 d-flex align-items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="currentColor" class="icon icon-tabler icons-tabler-filled icon-tabler-point">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                        <path d="M12 7a5 5 0 1 1 -4.995 5.217l-.005 -.217l.005 -.217a5 5 0 0 1 4.995 -4.783z" />
                                                    </svg>
                                                    John expressed interest in attending and requested more information about the agenda.
                                                </li>
                                                <li class="d-flex gap-2 d-flex align-items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="currentColor" class="icon icon-tabler icons-tabler-filled icon-tabler-point">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                        <path d="M12 7a5 5 0 1 1 -4.995 5.217l-.005 -.217l.005 -.217a5 5 0 0 1 4.995 -4.783z" />
                                                    </svg>
                                                    Promised to send John an email with event details and registration link by end of day.
                                                </li>

                                                <li class="d-flex gap-2 d-flex align-items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="currentColor" class="icon icon-tabler icons-tabler-filled icon-tabler-point">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                        <path d="M12 7a5 5 0 1 1 -4.995 5.217l-.005 -.217l.005 -.217a5 5 0 0 1 4.995 -4.783z" />
                                                    </svg>
                                                    Agreed to follow up with John next week to confirm attendance.
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="d-flex flex-column gap-2">
                                            <div>Follow-Up Actions:</div>
                                            <ul class="list-unstyled mb-0">
                                                <li class="d-flex gap-2 d-flex align-items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="currentColor" class="icon icon-tabler icons-tabler-filled icon-tabler-point">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                        <path d="M12 7a5 5 0 1 1 -4.995 5.217l-.005 -.217l.005 -.217a5 5 0 0 1 4.995 -4.783z" />
                                                    </svg>
                                                    Send email to John with event details and registration link.
                                                </li>
                                                <li class="d-flex gap-2 d-flex align-items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="currentColor" class="icon icon-tabler icons-tabler-filled icon-tabler-point">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                        <path d="M12 7a5 5 0 1 1 -4.995 5.217l-.005 -.217l.005 -.217a5 5 0 0 1 4.995 -4.783z" />
                                                    </svg>
                                                    Schedule follow-up call with John for next week.
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card mb-4 card-lg">
                        <div class="card-body">
                            <div class="accordion d-flex flex-column" id="accordionExample13">
                                <div class="d-flex flex-lg-row flex-column align-items-lg-center gap-3 justify-content-between">
                                    <div>
                                        <a
                                            href="#"
                                            class="d-flex flex-row collapsed gap-2 text-inherit align-items-center"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#collapseThirteen"
                                            aria-expanded="false"
                                            aria-controls="collapseThirteen">
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                width="16"
                                                height="16"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="1.5"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                class="icon icon-tabler icons-tabler-outline icon-tabler-chevron-right chevron-down">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                <path d="M9 6l6 6l-6 6" />
                                            </svg>

                                            <div class="d-flex flex-md-row flex-column align-items-md-center gap-md-2">
                                                <div class="d-flex flex-md-row align-items-md-center gap-2">
                                                    <h6 class="mb-0">Notes <span>by</span></h6>
                                                    <span>Manasvi Suthar</span>
                                                </div>
                                                <span class="text-gray-800 fw-semi-bold">Support Ticket Resolution</span>
                                            </div>
                                        </a>
                                    </div>
                                    <div class="d-flex flex-row gap-2 text-secondary">
                                        <span>Feb 22, 2025</span>
                                        <span>at 6:17 PM EST</span>
                                    </div>
                                </div>
                                <div id="collapseThirteen" class="accordion-collapse collapse" data-bs-parent="#accordionExample13">
                                    <div class="d-flex flex-column gap-4 ms-xl-5 mt-6">
                                        <div>Interaction Type: Phone Call</div>

                                        <div class="d-flex flex-column gap-2">
                                            <div>Summary:</div>
                                            <ul class="list-unstyled mb-0">
                                                <li class="d-flex gap-2 d-flex align-items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="currentColor" class="icon icon-tabler icons-tabler-filled icon-tabler-point">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                        <path d="M12 7a5 5 0 1 1 -4.995 5.217l-.005 -.217l.005 -.217a5 5 0 0 1 4.995 -4.783z" />
                                                    </svg>
                                                    Discussed upcoming product launch event.
                                                </li>
                                                <li class="d-flex gap-2 d-flex align-items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="currentColor" class="icon icon-tabler icons-tabler-filled icon-tabler-point">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                        <path d="M12 7a5 5 0 1 1 -4.995 5.217l-.005 -.217l.005 -.217a5 5 0 0 1 4.995 -4.783z" />
                                                    </svg>
                                                    John expressed interest in attending and requested more information about the agenda.
                                                </li>
                                                <li class="d-flex gap-2 d-flex align-items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="currentColor" class="icon icon-tabler icons-tabler-filled icon-tabler-point">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                        <path d="M12 7a5 5 0 1 1 -4.995 5.217l-.005 -.217l.005 -.217a5 5 0 0 1 4.995 -4.783z" />
                                                    </svg>
                                                    Promised to send John an email with event details and registration link by end of day.
                                                </li>

                                                <li class="d-flex gap-2 d-flex align-items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="currentColor" class="icon icon-tabler icons-tabler-filled icon-tabler-point">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                        <path d="M12 7a5 5 0 1 1 -4.995 5.217l-.005 -.217l.005 -.217a5 5 0 0 1 4.995 -4.783z" />
                                                    </svg>
                                                    Agreed to follow up with John next week to confirm attendance.
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="d-flex flex-column gap-2">
                                            <div>Follow-Up Actions:</div>
                                            <ul class="list-unstyled mb-0">
                                                <li class="d-flex gap-2 d-flex align-items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="currentColor" class="icon icon-tabler icons-tabler-filled icon-tabler-point">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                        <path d="M12 7a5 5 0 1 1 -4.995 5.217l-.005 -.217l.005 -.217a5 5 0 0 1 4.995 -4.783z" />
                                                    </svg>
                                                    Send email to John with event details and registration link.
                                                </li>
                                                <li class="d-flex gap-2 d-flex align-items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="currentColor" class="icon icon-tabler icons-tabler-filled icon-tabler-point">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                        <path d="M12 7a5 5 0 1 1 -4.995 5.217l-.005 -.217l.005 -.217a5 5 0 0 1 4.995 -4.783z" />
                                                    </svg>
                                                    Schedule follow-up call with John for next week.
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane" id="email" role="tabpanel" aria-labelledby="email-tab">
                    <div>
                        <h5 class="mb-5">Emails</h5>
                    </div>
                    <div class="d-flex flex-column gap-3 mb-4">
                        <div>
                            <ul class="nav nav-line-bottom" id="tabEmail" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active py-2" id="mail-tab" data-bs-toggle="pill" href="#mail" role="tab" aria-controls="mail" aria-selected="true">Mails (5)</a>
                                </li>

                                <li class="nav-item">
                                    <a class="nav-link py-2" id="draft-tab" data-bs-toggle="pill" href="#draft" role="tab" aria-controls="draft" aria-selected="true">Drafts (3)</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link py-2" id="schedule-tab" data-bs-toggle="pill" href="#schedule" role="tab" aria-controls="schedule" aria-selected="true">Scheduled (12)</a>
                                </li>
                            </ul>
                        </div>
                        <div>
                            <form>
                                <input class="form-control" type="search" value="" placeholder="Search" />
                            </form>
                        </div>
                    </div>

                    <div class="tab-content" id="tabEmailContent">
                        <div class="tab-pane fade show active" id="mail" role="tabpanel" aria-labelledby="mail-tab">
                            <div class="card card-lg overflow-hidden" id="mailList" data-list="email_subject,email_sender,email_date,email_status">
                                <div class="table-responsive">
                                    <table class="table text-nowrap mb-0 table-centered table-hover" data-check-container>
                                        <thead>
                                            <tr>
                                                <th class="pe-0">
                                                    <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault1" data-check-all />
                                                    <label class="form-check-label" for="flexCheckDefault1"></label>
                                                </th>
                                                <th class="listjs-sorter ps-0" data-sort="email_subject">Subject</th>
                                                <th class="listjs-sorter" data-sort="email_sender">Sender</th>
                                                <th class="listjs-sorter" data-sort="email_date">Date & Time</th>
                                                <th class="listjs-sorter" data-sort="email_status">Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="list">
                                            <tr>
                                                <td class="pe-0">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault2" />
                                                        <label class="form-check-label" for="flexCheckDefault2"></label>
                                                    </div>
                                                </td>
                                                <td class="ps-0 email_subject">
                                                    <div class="d-flex flex-column ms-2">
                                                        <div>Follow-up on Proposal</div>
                                                        <span class="text-secondary">jane@example.com</span>
                                                    </div>
                                                </td>
                                                <td class="email_sender">Jitu Chauhan</td>
                                                <td class="email_date">March 26, 2025 | 6:00 PM</td>
                                                <td class="email_status">
                                                    <span class="badge bg-success-subtle text-success-emphasis rounded-pill">Read</span>
                                                </td>
                                                <td>
                                                    <a href="#!">View</a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="pe-0">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault3" />
                                                        <label class="form-check-label" for="flexCheckDefault3"></label>
                                                    </div>
                                                </td>
                                                <td class="ps-0 email_subject">
                                                    <div class="d-flex flex-column ms-2">
                                                        <div>Proposal for Partnership</div>
                                                        <span class="text-secondary">jane@example.com</span>
                                                    </div>
                                                </td>

                                                <td class="email_sender">Anita parmar</td>
                                                <td class="email_date">March 23, 2025 | 3:00 PM</td>
                                                <td class="email_status">
                                                    <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill">Unread</span>
                                                </td>
                                                <td>
                                                    <a href="#!">View</a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="pe-0">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault4" />
                                                        <label class="form-check-label" for="flexCheckDefault4"></label>
                                                    </div>
                                                </td>
                                                <td class="ps-0 email_subject">
                                                    <div class="d-flex flex-column ms-2">
                                                        <div>Pricing Inquiry</div>
                                                        <span class="text-secondary">jane@example.com</span>
                                                    </div>
                                                </td>
                                                <td class="email_name">Sandip Chauhan</td>
                                                <td class="email_date">March 20, 2025 | 2:00 PM</td>
                                                <td class="email_status">
                                                    <span class="badge bg-success-subtle text-success-emphasis rounded-pill">Read</span>
                                                </td>
                                                <td>
                                                    <a href="#!">View</a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="pe-0">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault5" />
                                                        <label class="form-check-label" for="flexCheckDefault5"></label>
                                                    </div>
                                                </td>
                                                <td class="ps-0 email_subject">
                                                    <div class="d-flex flex-column ms-2">
                                                        <div>Feedback on Demo</div>
                                                        <span class="text-secondary">jane@example.com</span>
                                                    </div>
                                                </td>
                                                <td class="email_sender">Manasvi Suthar</td>
                                                <td class="email_date">March 19, 2025 | 01:20 PM</td>
                                                <td class="email_status">
                                                    <span class="badge bg-success-subtle text-success-emphasis rounded-pill">Read</span>
                                                </td>
                                                <td>
                                                    <a href="#!">View</a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="pe-0">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault6" />
                                                        <label class="form-check-label" for="flexCheckDefault6"></label>
                                                    </div>
                                                </td>
                                                <td class="ps-0 email_subject">
                                                    <div class="d-flex flex-column ms-2">
                                                        <div>Meeting Confirmation</div>
                                                        <span>jane@example.com</span>
                                                    </div>
                                                </td>
                                                <td class="email_sender">Jitu Chauhan</td>
                                                <td class="email_date">March 19, 2025 | 11:35 AM</td>
                                                <td class="email_status">
                                                    <span class="badge bg-success-subtle text-success-emphasis rounded-pill">Read</span>
                                                </td>
                                                <td>
                                                    <a href="#!">View</a>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="btn-toolbar card-footer border-top border-dashed d-flex flex-md-row flex-column justify-content-md-between align-items-md-center">
                                    <p class="mb-0 listjs-showing-items-label"></p>
                                    <div class="d-flex gap-4">
                                        <div class="d-flex align-items-center gap-2">
                                            <label class="form-label text-nowrap mb-0">Rows per page:</label>
                                            <select class="form-select listjs-items-per-page" data-choices="">
                                                <option value="5" selected>5</option>
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

                        <div class="tab-pane" id="draft" role="tabpanel" aria-labelledby="draft-tab">
                            <div class="card card-lg overflow-hidden" id="darftList" data-list="email_subject,email_sender,email_date,email_status">
                                <div class="table-responsive">
                                    <table class="table text-nowrap mb-0 table-centered table-hover" data-check-container>
                                        <thead>
                                            <tr>
                                                <th class="pe-0">
                                                    <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault12" data-check-all />
                                                    <label class="form-check-label" for="flexCheckDefault12"></label>
                                                </th>
                                                <th class="listjs-sorter ps-0" data-sort="email_subject">Subject</th>
                                                <th class="listjs-sorter" data-sort="email_sender">Sender</th>
                                                <th class="listjs-sorter" data-sort="email_date">Date & Time</th>
                                                <th class="listjs-sorter" data-sort="email_status">Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="list">
                                            <tr>
                                                <td class="pe-0">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault21" />
                                                        <label class="form-check-label" for="flexCheckDefault21"></label>
                                                    </div>
                                                </td>
                                                <td class="ps-0 email_subject">
                                                    <div class="d-flex flex-column ms-2">
                                                        <div>Follow-up on Proposal</div>
                                                        <span class="text-secondary">jane@example.com</span>
                                                    </div>
                                                </td>
                                                <td class="email_sender">Jitu Chauhan</td>
                                                <td class="email_date">March 26, 2025 | 6:00 PM</td>
                                                <td class="email_status">
                                                    <span class="badge bg-success-subtle text-success-emphasis rounded-pill">Read</span>
                                                </td>
                                                <td>
                                                    <a href="#!">View</a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="pe-0">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault34" />
                                                        <label class="form-check-label" for="flexCheckDefault34"></label>
                                                    </div>
                                                </td>
                                                <td class="ps-0 email_subject">
                                                    <div class="d-flex flex-column ms-2">
                                                        <div>Proposal for Partnership</div>
                                                        <span class="text-secondary">jane@example.com</span>
                                                    </div>
                                                </td>

                                                <td class="email_sender">Anita parmar</td>
                                                <td class="email_date">March 23, 2025 | 3:00 PM</td>
                                                <td class="email_status">
                                                    <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill">Unread</span>
                                                </td>
                                                <td>
                                                    <a href="#!">View</a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="pe-0">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault45" />
                                                        <label class="form-check-label" for="flexCheckDefault45"></label>
                                                    </div>
                                                </td>
                                                <td class="ps-0 email_subject">
                                                    <div class="d-flex flex-column ms-2">
                                                        <div>Pricing Inquiry</div>
                                                        <span class="text-secondary">jane@example.com</span>
                                                    </div>
                                                </td>
                                                <td class="email_name">Sandip Chauhan</td>
                                                <td class="email_date">March 20, 2025 | 2:00 PM</td>
                                                <td class="email_status">
                                                    <span class="badge bg-success-subtle text-success-emphasis rounded-pill">Read</span>
                                                </td>
                                                <td>
                                                    <a href="#!">View</a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="pe-0">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault56" />
                                                        <label class="form-check-label" for="flexCheckDefault56"></label>
                                                    </div>
                                                </td>
                                                <td class="ps-0 email_subject">
                                                    <div class="d-flex flex-column ms-2">
                                                        <div>Feedback on Demo</div>
                                                        <span class="text-secondary">jane@example.com</span>
                                                    </div>
                                                </td>
                                                <td class="email_sender">Manasvi Suthar</td>
                                                <td class="email_date">March 19, 2025 | 01:20 PM</td>
                                                <td class="email_status">
                                                    <span class="badge bg-success-subtle text-success-emphasis rounded-pill">Read</span>
                                                </td>
                                                <td>
                                                    <a href="#!">View</a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="pe-0">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault67" />
                                                        <label class="form-check-label" for="flexCheckDefault67"></label>
                                                    </div>
                                                </td>
                                                <td class="ps-0 email_subject">
                                                    <div class="d-flex flex-column ms-2">
                                                        <div>Meeting Confirmation</div>
                                                        <span>jane@example.com</span>
                                                    </div>
                                                </td>
                                                <td class="email_sender">Jitu Chauhan</td>
                                                <td class="email_date">March 19, 2025 | 11:35 AM</td>
                                                <td class="email_status">
                                                    <span class="badge bg-success-subtle text-success-emphasis rounded-pill">Read</span>
                                                </td>
                                                <td>
                                                    <a href="#!">View</a>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="btn-toolbar card-footer border-top border-dashed d-flex flex-md-row flex-column justify-content-md-between align-items-md-center">
                                    <p class="mb-0 listjs-showing-items-label"></p>
                                    <div class="d-flex gap-4">
                                        <div class="d-flex align-items-center gap-2">
                                            <label class="form-label text-nowrap mb-0">Rows per page:</label>
                                            <select class="form-select listjs-items-per-page" data-choices="">
                                                <option value="5" selected>5</option>
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
                        <div class="tab-pane" id="schedule" role="tabpanel" aria-labelledby="schedule-tab">
                            <div class="card card-lg overflow-hidden" id="scheduleList" data-list="email_subject,email_sender,email_date,email_status">
                                <div class="table-responsive">
                                    <table class="table text-nowrap mb-0 table-centered table-hover" data-check-container>
                                        <thead>
                                            <tr>
                                                <th class="pe-0">
                                                    <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault11" data-check-all />
                                                    <label class="form-check-label" for="flexCheckDefault11"></label>
                                                </th>
                                                <th class="listjs-sorter ps-0" data-sort="email_subject">Subject</th>
                                                <th class="listjs-sorter" data-sort="email_sender">Sender</th>
                                                <th class="listjs-sorter" data-sort="email_date">Date & Time</th>
                                                <th class="listjs-sorter" data-sort="email_status">Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="list">
                                            <tr>
                                                <td class="pe-0">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault46" />
                                                        <label class="form-check-label" for="flexCheckDefault46"></label>
                                                    </div>
                                                </td>
                                                <td class="ps-0 email_subject">
                                                    <div class="d-flex flex-column ms-2">
                                                        <div>Follow-up on Proposal</div>
                                                        <span class="text-secondary">jane@example.com</span>
                                                    </div>
                                                </td>
                                                <td class="email_sender">Jitu Chauhan</td>
                                                <td class="email_date">March 26, 2025 | 6:00 PM</td>
                                                <td class="email_status">
                                                    <span class="badge bg-success-subtle text-success-emphasis rounded-pill">Read</span>
                                                </td>
                                                <td>
                                                    <a href="#!">View</a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="pe-0">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault30" />
                                                        <label class="form-check-label" for="flexCheckDefault30"></label>
                                                    </div>
                                                </td>
                                                <td class="ps-0 email_subject">
                                                    <div class="d-flex flex-column ms-2">
                                                        <div>Proposal for Partnership</div>
                                                        <span class="text-secondary">jane@example.com</span>
                                                    </div>
                                                </td>

                                                <td class="email_sender">Anita parmar</td>
                                                <td class="email_date">March 23, 2025 | 3:00 PM</td>
                                                <td class="email_status">
                                                    <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill">Unread</span>
                                                </td>
                                                <td>
                                                    <a href="#!">View</a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="pe-0">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault40" />
                                                        <label class="form-check-label" for="flexCheckDefault40"></label>
                                                    </div>
                                                </td>
                                                <td class="ps-0 email_subject">
                                                    <div class="d-flex flex-column ms-2">
                                                        <div>Pricing Inquiry</div>
                                                        <span class="text-secondary">jane@example.com</span>
                                                    </div>
                                                </td>
                                                <td class="email_name">Sandip Chauhan</td>
                                                <td class="email_date">March 20, 2025 | 2:00 PM</td>
                                                <td class="email_status">
                                                    <span class="badge bg-success-subtle text-success-emphasis rounded-pill">Read</span>
                                                </td>
                                                <td>
                                                    <a href="#!">View</a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="pe-0">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault50" />
                                                        <label class="form-check-label" for="flexCheckDefault50"></label>
                                                    </div>
                                                </td>
                                                <td class="ps-0 email_subject">
                                                    <div class="d-flex flex-column ms-2">
                                                        <div>Feedback on Demo</div>
                                                        <span class="text-secondary">jane@example.com</span>
                                                    </div>
                                                </td>
                                                <td class="email_sender">Manasvi Suthar</td>
                                                <td class="email_date">March 19, 2025 | 01:20 PM</td>
                                                <td class="email_status">
                                                    <span class="badge bg-success-subtle text-success-emphasis rounded-pill">Read</span>
                                                </td>
                                                <td>
                                                    <a href="#!">View</a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="pe-0">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault60" />
                                                        <label class="form-check-label" for="flexCheckDefault60"></label>
                                                    </div>
                                                </td>
                                                <td class="ps-0 email_subject">
                                                    <div class="d-flex flex-column ms-2">
                                                        <div>Meeting Confirmation</div>
                                                        <span>jane@example.com</span>
                                                    </div>
                                                </td>
                                                <td class="email_sender">Jitu Chauhan</td>
                                                <td class="email_date">March 19, 2025 | 11:35 AM</td>
                                                <td class="email_status">
                                                    <span class="badge bg-success-subtle text-success-emphasis rounded-pill">Read</span>
                                                </td>
                                                <td>
                                                    <a href="#!">View</a>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="btn-toolbar card-footer border-top border-dashed d-flex flex-md-row flex-column justify-content-md-between align-items-md-center">
                                    <p class="mb-0 listjs-showing-items-label"></p>
                                    <div class="d-flex gap-4">
                                        <div class="d-flex align-items-center gap-2">
                                            <label class="form-label text-nowrap mb-0">Rows per page:</label>
                                            <select class="form-select listjs-items-per-page" data-choices="">
                                                <option value="5" selected>5</option>
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
                <div class="tab-pane fade show" id="call" role="tabpanel" aria-labelledby="call-tab">
                    <div>
                        <h5 class="mb-5">Calls</h5>
                    </div>
                    <div class="d-flex flex-column gap-4">
                        <div class="row justify-content-between gy-2">
                            <div class="col-lg-3">
                                <select class="form-select" data-choices>
                                    <option selected>All Call</option>
                                    <option value="Outbound">Outbound</option>
                                    <option value="Inbound">Inbound</option>
                                    <option value="Sales">Sales</option>
                                    <option value="Follow up">Follow up</option>
                                    <option value="Feedback">Feedback</option>
                                </select>
                            </div>
                            <div class="col-auto">
                                <a href="#!" class="btn btn-primary d-flex flex-row gap-1 align-items-center">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="16"
                                        height="16"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.5"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        class="icon icon-tabler icons-tabler-outline icon-tabler-plus">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M12 5l0 14" />
                                        <path d="M5 12l14 0" />
                                    </svg>
                                    Add new Call
                                </a>
                            </div>
                        </div>

                        <div class="card card-lg overflow-hidden" id="calls" data-list="calls_name,calls_type,calls_date,calls_created">
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table text-nowrap mb-0 table-centered table-hover" data-check-container="">
                                        <thead>
                                            <tr>
                                                <th class="pe-0">
                                                    <input class="form-check-input" type="checkbox" value="" id="checkAll" data-check-all="" />
                                                    <label class="form-check-label" for="checkAll"></label>
                                                </th>
                                                <th class="ps-0 listjs-sorter" data-sort="calls_name">Caller Name</th>
                                                <th class="pe-0 listjs-sorter" data-sort="calls_type">Call Type</th>
                                                <th class="listjs-sorter" data-sort="calls_date">Date & Time</th>
                                                <th class="listjs-sorter" data-sort="calls_created">Created by</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="list">
                                            <tr>
                                                <td class="pe-0">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault20" />
                                                        <label class="form-check-label" for="flexCheckDefault20"></label>
                                                    </div>
                                                </td>
                                                <td class="ps-0">
                                                    <div class="d-flex flex-row gap-2 align-items-center ms-2">
                                                        <img src="../../assets/images/avatar/avatar-1.jpg" alt="avatar" class="avatar avatar-sm rounded-circle" />

                                                        <a href="#!" class="text-inherit">John Smith</a>
                                                    </div>
                                                </td>
                                                <td>Outbound</td>
                                                <td>March 26, 2025 | 6:00 PM</td>
                                                <td>Jitu Chauhan</td>
                                                <td>
                                                    <a href="#!">View</a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="pe-0">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault58" />
                                                        <label class="form-check-label" for="flexCheckDefault58"></label>
                                                    </div>
                                                </td>
                                                <td class="ps-0">
                                                    <div class="d-flex flex-row gap-2 align-items-center ms-2">
                                                        <img src="../../assets/images/avatar/avatar-2.jpg" alt="avatar" class="avatar avatar-sm rounded-circle" />

                                                        <a href="#!" class="text-inherit">Jane Doe</a>
                                                    </div>
                                                </td>
                                                <td>Inbound</td>
                                                <td>March 26, 2025 | 6:00 PM</td>
                                                <td>Jitu Chauhan</td>
                                                <td>
                                                    <a href="#!">View</a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="pe-0">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault22" />
                                                        <label class="form-check-label" for="flexCheckDefault22"></label>
                                                    </div>
                                                </td>
                                                <td class="ps-0">
                                                    <div class="d-flex flex-row gap-2 align-items-center ms-2">
                                                        <img src="../../assets/images/avatar/avatar-3.jpg" alt="avatar" class="avatar avatar-sm rounded-circle" />
                                                        <a href="#!" class="text-inherit">Alex Johnson</a>
                                                    </div>
                                                </td>
                                                <td>Sales</td>
                                                <td>March 26, 2025 | 6:00 PM</td>
                                                <td>Anita Parmar</td>
                                                <td>
                                                    <a href="#!">View</a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="pe-0">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault47" />
                                                        <label class="form-check-label" for="flexCheckDefault47"></label>
                                                    </div>
                                                </td>
                                                <td class="ps-0">
                                                    <div class="d-flex flex-row gap-2 align-items-center ms-2">
                                                        <img src="../../assets/images/avatar/avatar-4.jpg" alt="avatar" class="avatar avatar-sm rounded-circle" />
                                                        <a href="#!" class="text-inherit">Emily Brown</a>
                                                    </div>
                                                </td>
                                                <td>Follow-up</td>
                                                <td>March 26, 2025 | 6:00 PM</td>
                                                <td>Sandip Chauhan</td>
                                                <td>
                                                    <a href="#!">View</a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="pe-0">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault24" />
                                                        <label class="form-check-label" for="flexCheckDefault24"></label>
                                                    </div>
                                                </td>
                                                <td class="ps-0">
                                                    <div class="d-flex flex-row gap-2 align-items-center ms-2">
                                                        <img src="../../assets/images/avatar/avatar-5.jpg" alt="avatar" class="avatar avatar-sm rounded-circle" />
                                                        <a href="#!" class="text-inherit">Mark Thompson</a>
                                                    </div>
                                                </td>
                                                <td>Feedback</td>
                                                <td>March 26, 2025 | 6:00 PM</td>
                                                <td>
                                                    <span>Manasvi Suthar</span>
                                                </td>
                                                <td>
                                                    <a href="#!">View</a>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="btn-toolbar card-footer border-top border-dashed d-flex flex-md-row flex-column justify-content-md-between align-items-md-center">
                                <p class="mb-0 listjs-showing-items-label"></p>
                                <div class="d-flex gap-4">
                                    <div class="d-flex align-items-center gap-2">
                                        <label class="form-label text-nowrap mb-0">Rows per page:</label>
                                        <select class="form-select listjs-items-per-page" data-choices="">
                                            <option value="5" selected>5</option>
                                            <option value="7">7</option>
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
                <div class="tab-pane fade show" id="task" role="tabpanel" aria-labelledby="task-tab">
                    <div>
                        <h5 class="mb-5">Tasks</h5>
                    </div>
                    <div class="d-flex flex-column gap-6">
                        <div class="d-flex flex-md-row flex-column gap-2 justify-content-between">
                            <div class="d-flex flex-row gap-3 align-items-center">
                                <div>
                                    <form>
                                        <input class="form-control" type="search" value="" placeholder="Search" />
                                    </form>
                                </div>
                                <a href="#!" class="text-inherit">
                                    <span>
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            width="14"
                                            height="14"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.5"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            class="icon icon-tabler icons-tabler-outline icon-tabler-adjustments">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path d="M4 10a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" />
                                            <path d="M6 4v4" />
                                            <path d="M6 12v8" />
                                            <path d="M10 16a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" />
                                            <path d="M12 4v10" />
                                            <path d="M12 18v2" />
                                            <path d="M16 7a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" />
                                            <path d="M18 4v1" />
                                            <path d="M18 9v11" />
                                        </svg>
                                    </span>
                                    <span>Filter</span>
                                </a>
                            </div>
                            <div>
                                <a href="#!" class="btn btn-primary d-flex flex-row gap-1 align-items-center">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="16"
                                        height="16"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.5"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        class="icon icon-tabler icons-tabler-outline icon-tabler-plus">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M12 5l0 14" />
                                        <path d="M5 12l14 0" />
                                    </svg>
                                    Create Task
                                </a>
                            </div>
                        </div>
                        <div>
                            <div class="card card-lg overflow-hidden" id="taskTable" data-list="task_title,task_type,task_assigned,task_date,task_priority">
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table text-nowrap mb-0 table-centered table-hover" data-check-container="">
                                            <thead>
                                                <tr>
                                                    <th>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault25" data-check-all="" />
                                                            <label class="form-check-label" for="flexCheckDefault25"></label>
                                                        </div>
                                                    </th>
                                                    <th class="listjs-sorter" data-sort="task_title">Task Title</th>
                                                    <th class="listjs-sorter" data-sort="task_type">Task type</th>
                                                    <th class="listjs-sorter" data-sort="task_assigned">Assigned To</th>
                                                    <th class="listjs-sorter" data-sort="task_date">Due Date</th>
                                                    <th class="listjs-sorter" data-sort="task_priority">Priority</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody class="list">
                                                <tr>
                                                    <td class="pe-0">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault65" />
                                                            <label class="form-check-label" for="flexCheckDefault65"></label>
                                                        </div>
                                                    </td>

                                                    <td class="task_title"><a href="#!">Follow-up Call</a></td>
                                                    <td class="task_type">Call</td>
                                                    <td class="task_assigned">
                                                        <a href="#!">Jitu Chauhan</a>
                                                    </td>
                                                    <td class="task_date">Mar 16,2025 10:00 AM</td>
                                                    <td class="task_priority"><span class="badge bg-danger-subtle text-danger-emphasis rounded-pill">High</span></td>
                                                    <td class="text-center">
                                                        <div class="dropdown">
                                                            <a href="#!" class="btn btn-icon btn-ghost btn-sm rounded-circle" data-bs-toggle="dropdown" aria-expanded="false">
                                                                <svg
                                                                    xmlns="http://www.w3.org/2000/svg"
                                                                    class="icon icon-tabler icon-tabler-dots-vertical"
                                                                    width="20"
                                                                    height="20"
                                                                    viewBox="0 0 24 24"
                                                                    stroke-width="1.5"
                                                                    stroke="currentColor"
                                                                    fill="none"
                                                                    stroke-linecap="round"
                                                                    stroke-linejoin="round">
                                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                                    <path d="M12 12m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"></path>
                                                                    <path d="M12 19m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"></path>
                                                                    <path d="M12 5m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"></path>
                                                                </svg>
                                                            </a>
                                                            <ul class="dropdown-menu">
                                                                <li><a class="dropdown-item" href="#">Action</a></li>
                                                                <li><a class="dropdown-item" href="#">Another action</a></li>
                                                                <li><a class="dropdown-item" href="#">Something else here</a></li>
                                                            </ul>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="pe-0">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault23" />
                                                            <label class="form-check-label" for="flexCheckDefault23"></label>
                                                        </div>
                                                    </td>

                                                    <td class="task_title"><a href="#!">Send Proposal</a></td>
                                                    <td class="task_email">Email</td>
                                                    <td>
                                                        <a href="#!">Anita parmar</a>
                                                    </td>
                                                    <td class="task_date">Mar 16,2025 10:00 AM</td>
                                                    <td class="task_priority"><span class="badge bg-warning-subtle text-warning-emphasis rounded-pill">Medium</span></td>
                                                    <td class="text-center">
                                                        <div class="dropdown">
                                                            <a href="#!" class="btn btn-icon btn-ghost btn-sm rounded-circle" data-bs-toggle="dropdown" aria-expanded="false">
                                                                <svg
                                                                    xmlns="http://www.w3.org/2000/svg"
                                                                    class="icon icon-tabler icon-tabler-dots-vertical"
                                                                    width="20"
                                                                    height="20"
                                                                    viewBox="0 0 24 24"
                                                                    stroke-width="1.5"
                                                                    stroke="currentColor"
                                                                    fill="none"
                                                                    stroke-linecap="round"
                                                                    stroke-linejoin="round">
                                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                                    <path d="M12 12m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"></path>
                                                                    <path d="M12 19m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"></path>
                                                                    <path d="M12 5m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"></path>
                                                                </svg>
                                                            </a>
                                                            <ul class="dropdown-menu">
                                                                <li><a class="dropdown-item" href="#">Action</a></li>
                                                                <li><a class="dropdown-item" href="#">Another action</a></li>
                                                                <li><a class="dropdown-item" href="#">Something else here</a></li>
                                                            </ul>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="pe-0">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault26" />
                                                            <label class="form-check-label" for="flexCheckDefault26"></label>
                                                        </div>
                                                    </td>

                                                    <td class="task_title"><a href="#!">Schedule Demo</a></td>
                                                    <td class="task_type">Call</td>
                                                    <td class="task_assigned">
                                                        <a href="#!">Sandip Chauhan</a>
                                                    </td>
                                                    <td class="task_date">Mar 16,2025 10:00 AM</td>
                                                    <td class="task_priority"><span class="badge bg-info-subtle text-info-emphasis rounded-pill">Low</span></td>
                                                    <td class="text-center">
                                                        <div class="dropdown">
                                                            <a href="#!" class="btn btn-icon btn-ghost btn-sm rounded-circle" data-bs-toggle="dropdown" aria-expanded="false">
                                                                <svg
                                                                    xmlns="http://www.w3.org/2000/svg"
                                                                    class="icon icon-tabler icon-tabler-dots-vertical"
                                                                    width="20"
                                                                    height="20"
                                                                    viewBox="0 0 24 24"
                                                                    stroke-width="1.5"
                                                                    stroke="currentColor"
                                                                    fill="none"
                                                                    stroke-linecap="round"
                                                                    stroke-linejoin="round">
                                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                                    <path d="M12 12m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"></path>
                                                                    <path d="M12 19m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"></path>
                                                                    <path d="M12 5m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"></path>
                                                                </svg>
                                                            </a>
                                                            <ul class="dropdown-menu">
                                                                <li><a class="dropdown-item" href="#">Action</a></li>
                                                                <li><a class="dropdown-item" href="#">Another action</a></li>
                                                                <li><a class="dropdown-item" href="#">Something else here</a></li>
                                                            </ul>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="pe-0">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault19" />
                                                            <label class="form-check-label" for="flexCheckDefault19"></label>
                                                        </div>
                                                    </td>

                                                    <td class="task_title"><a href="#!">Follow-up Email</a></td>
                                                    <td class="task_type">Email</td>
                                                    <td class="task_assigned">
                                                        <a href="#!">Sandip Chauhan</a>
                                                    </td>
                                                    <td class="task_date">Mar 16,2025 10:00 AM</td>
                                                    <td class="task_priority"><span class="badge bg-info-subtle text-info-emphasis rounded-pill">Low</span></td>
                                                    <td class="text-center">
                                                        <div class="dropdown">
                                                            <a href="#!" class="btn btn-icon btn-ghost btn-sm rounded-circle" data-bs-toggle="dropdown" aria-expanded="false">
                                                                <svg
                                                                    xmlns="http://www.w3.org/2000/svg"
                                                                    class="icon icon-tabler icon-tabler-dots-vertical"
                                                                    width="20"
                                                                    height="20"
                                                                    viewBox="0 0 24 24"
                                                                    stroke-width="1.5"
                                                                    stroke="currentColor"
                                                                    fill="none"
                                                                    stroke-linecap="round"
                                                                    stroke-linejoin="round">
                                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                                    <path d="M12 12m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"></path>
                                                                    <path d="M12 19m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"></path>
                                                                    <path d="M12 5m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"></path>
                                                                </svg>
                                                            </a>
                                                            <ul class="dropdown-menu">
                                                                <li><a class="dropdown-item" href="#">Action</a></li>
                                                                <li><a class="dropdown-item" href="#">Another action</a></li>
                                                                <li><a class="dropdown-item" href="#">Something else here</a></li>
                                                            </ul>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="pe-0">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault18" />
                                                            <label class="form-check-label" for="flexCheckDefault18"></label>
                                                        </div>
                                                    </td>

                                                    <td class="task_title"><a href="#!">Review Contract</a></td>
                                                    <td class="task_type">To Do</td>
                                                    <td class="task_assigned">
                                                        <a href="#!">Sandip Chauhan</a>
                                                    </td>
                                                    <td class="task_date">Mar 16,2025 10:00 AM</td>
                                                    <td class="task_priority"><span class="badge bg-info-subtle text-info-emphasis rounded-pill">Low</span></td>
                                                    <td class="text-center">
                                                        <div class="dropdown">
                                                            <a href="#!" class="btn btn-icon btn-ghost btn-sm rounded-circle" data-bs-toggle="dropdown" aria-expanded="false">
                                                                <svg
                                                                    xmlns="http://www.w3.org/2000/svg"
                                                                    class="icon icon-tabler icon-tabler-dots-vertical"
                                                                    width="20"
                                                                    height="20"
                                                                    viewBox="0 0 24 24"
                                                                    stroke-width="1.5"
                                                                    stroke="currentColor"
                                                                    fill="none"
                                                                    stroke-linecap="round"
                                                                    stroke-linejoin="round">
                                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                                    <path d="M12 12m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"></path>
                                                                    <path d="M12 19m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"></path>
                                                                    <path d="M12 5m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"></path>
                                                                </svg>
                                                            </a>
                                                            <ul class="dropdown-menu">
                                                                <li><a class="dropdown-item" href="#">Action</a></li>
                                                                <li><a class="dropdown-item" href="#">Another action</a></li>
                                                                <li><a class="dropdown-item" href="#">Something else here</a></li>
                                                            </ul>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="btn-toolbar card-footer border-top border-dashed d-flex flex-md-row flex-column justify-content-md-between align-items-md-center">
                                        <p class="mb-0 listjs-showing-items-label"></p>
                                        <div class="d-flex gap-4">
                                            <div class="d-flex align-items-center gap-2">
                                                <label class="form-label text-nowrap mb-0">Rows per page:</label>
                                                <select class="form-select listjs-items-per-page" data-choices="">
                                                    <option value="5" selected>5</option>
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

                <div class="tab-pane fade show" id="meetings" role="tabpanel" aria-labelledby="meetings-tab">
                    <div>
                        <h5 class="mb-5">Meetings</h5>
                    </div>
                    <div class="d-flex flex-column gap-6">
                        <div class="d-flex flex-md-row flex-column gap-2 justify-content-between">
                            <div>
                                <form>
                                    <input class="form-control" type="search" value="" placeholder="Search" />
                                </form>
                            </div>
                            <div>
                                <a href="#!" class="btn btn-primary d-flex flex-row gap-1 align-items-center">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="16"
                                        height="16"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.5"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        class="icon icon-tabler icons-tabler-outline icon-tabler-plus">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M12 5l0 14" />
                                        <path d="M5 12l14 0" />
                                    </svg>
                                    Add Meeting
                                </a>
                            </div>
                        </div>
                        <div>
                            <div class="row g-5">
                                <div class="col-md-6">
                                    <div class="card card-lg">
                                        <div class="card-body d-flex flex-column gap-6">
                                            <div class="d-flex flex-column gap-2">
                                                <div>
                                                    <h5 class="mb-0">Monthly Pipeline Analysis</h5>
                                                </div>
                                                <div class="d-flex flex-column flex-xxl-row gap-1 gap-xxl-4 align-items-center text-secondary">
                                                    <div class="d-flex flex-row gap-2 align-items-center">
                                                        <svg
                                                            xmlns="http://www.w3.org/2000/svg"
                                                            width="16"
                                                            height="16"
                                                            viewBox="0 0 24 24"
                                                            fill="none"
                                                            stroke="currentColor"
                                                            stroke-width="1.5"
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            class="icon icon-tabler icons-tabler-outline icon-tabler-calendar text-secondary">
                                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                            <path d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z"></path>
                                                            <path d="M16 3v4"></path>
                                                            <path d="M8 3v4"></path>
                                                            <path d="M4 11h16"></path>
                                                            <path d="M11 15h1"></path>
                                                            <path d="M12 15v3"></path>
                                                        </svg>
                                                        <span>March 15, 2025</span>
                                                    </div>
                                                    <div class="d-flex flex-row gap-2 align-items-center">
                                                        <svg
                                                            xmlns="http://www.w3.org/2000/svg"
                                                            width="16"
                                                            height="16"
                                                            viewBox="0 0 24 24"
                                                            fill="none"
                                                            stroke="currentColor"
                                                            stroke-width="1.5"
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            class="icon icon-tabler icons-tabler-outline icon-tabler-clock">
                                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                            <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" />
                                                            <path d="M12 7v5l3 3" />
                                                        </svg>
                                                        <span>10:00 AM - 11:30 AM</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="d-flex flex-row justify-content-between">
                                                <div class="avatar-group">
                                                    <span class="avatar avatar-sm">
                                                        <img alt="avatar" src="../../assets/images/avatar/avatar-1.jpg" class="rounded-circle" />
                                                    </span>
                                                    <span class="avatar avatar-sm">
                                                        <img alt="avatar" src="../../assets/images/avatar/avatar-14.jpg" class="rounded-circle" />
                                                    </span>
                                                    <span class="avatar avatar-sm">
                                                        <img alt="avatar" src="../../assets/images/avatar/avatar-15.jpg" class="rounded-circle" />
                                                    </span>
                                                    <span class="avatar avatar-sm">
                                                        <img alt="avatar" src="../../assets/images/avatar/avatar-13.jpg" class="rounded-circle" />
                                                    </span>
                                                    <span class="avatar avatar-sm avatar-primary">
                                                        <span class="avatar-initials rounded-circle">2+</span>
                                                    </span>
                                                </div>
                                                <div><span class="badge bg-info-subtle text-info-emphasis">Scheduled</span></div>
                                            </div>
                                            <div class="d-flex flex-row justify-content-between">
                                                <div class="d-flex flex-row gap-2 align-items-center">
                                                    <span><img src="../../assets/images/svg/zoom.svg" alt="zoom" /></span>
                                                    <span>Virtual (Zoom Meeting)</span>
                                                </div>
                                                <div>
                                                    <a href="#!" class="btn btn-subtle-primary btn-sm">Join now</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card card-lg">
                                        <div class="card-body d-flex flex-column gap-6">
                                            <div class="d-flex flex-column gap-2">
                                                <div>
                                                    <h5 class="mb-0">Strategy Session for Lead Generation</h5>
                                                </div>
                                                <div class="d-flex flex-column flex-xxl-row gap-1 gap-xxl-4 align-items-center text-secondary">
                                                    <div class="d-flex flex-row gap-2 align-items-center">
                                                        <svg
                                                            xmlns="http://www.w3.org/2000/svg"
                                                            width="16"
                                                            height="16"
                                                            viewBox="0 0 24 24"
                                                            fill="none"
                                                            stroke="currentColor"
                                                            stroke-width="1.5"
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            class="icon icon-tabler icons-tabler-outline icon-tabler-calendar text-secondary">
                                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                            <path d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z"></path>
                                                            <path d="M16 3v4"></path>
                                                            <path d="M8 3v4"></path>
                                                            <path d="M4 11h16"></path>
                                                            <path d="M11 15h1"></path>
                                                            <path d="M12 15v3"></path>
                                                        </svg>
                                                        <span>March 15, 2025</span>
                                                    </div>
                                                    <div class="d-flex flex-row gap-2 align-items-center">
                                                        <svg
                                                            xmlns="http://www.w3.org/2000/svg"
                                                            width="16"
                                                            height="16"
                                                            viewBox="0 0 24 24"
                                                            fill="none"
                                                            stroke="currentColor"
                                                            stroke-width="1.5"
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            class="icon icon-tabler icons-tabler-outline icon-tabler-clock">
                                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                            <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" />
                                                            <path d="M12 7v5l3 3" />
                                                        </svg>
                                                        <span>10:00 AM - 11:30 AM</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="d-flex flex-row justify-content-between">
                                                <div class="avatar-group">
                                                    <span class="avatar avatar-sm">
                                                        <img alt="avatar" src="../../assets/images/avatar/avatar-2.jpg" class="rounded-circle" />
                                                    </span>
                                                    <span class="avatar avatar-sm">
                                                        <img alt="avatar" src="../../assets/images/avatar/avatar-3.jpg" class="rounded-circle" />
                                                    </span>
                                                    <span class="avatar avatar-sm">
                                                        <img alt="avatar" src="../../assets/images/avatar/avatar-16.jpg" class="rounded-circle" />
                                                    </span>
                                                    <span class="avatar avatar-sm">
                                                        <img alt="avatar" src="../../assets/images/avatar/avatar-15.jpg" class="rounded-circle" />
                                                    </span>
                                                    <span class="avatar avatar-sm avatar-primary">
                                                        <span class="avatar-initials rounded-circle">2+</span>
                                                    </span>
                                                </div>
                                                <div><span class="badge bg-info-subtle text-info-emphasis">Scheduled</span></div>
                                            </div>
                                            <div class="d-flex flex-row justify-content-between">
                                                <div class="d-flex flex-row gap-2 align-items-center">
                                                    <span><img src="../../assets/images/svg/google-meet.svg" alt="google" /></span>
                                                    <span>Virtual (Google Meet)</span>
                                                </div>
                                                <div>
                                                    <a href="#!" class="btn btn-subtle-primary btn-sm">Join now</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card card-lg">
                                        <div class="card-body d-flex flex-column gap-6">
                                            <div class="d-flex flex-column gap-2">
                                                <div>
                                                    <h5 class="mb-0">Marketing Campaign Planning</h5>
                                                </div>
                                                <div class="d-flex flex-column flex-xxl-row gap-1 gap-xxl-4 align-items-center text-secondary">
                                                    <div class="d-flex flex-row gap-2 align-items-center">
                                                        <svg
                                                            xmlns="http://www.w3.org/2000/svg"
                                                            width="16"
                                                            height="16"
                                                            viewBox="0 0 24 24"
                                                            fill="none"
                                                            stroke="currentColor"
                                                            stroke-width="1.5"
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            class="icon icon-tabler icons-tabler-outline icon-tabler-calendar text-secondary">
                                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                            <path d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z"></path>
                                                            <path d="M16 3v4"></path>
                                                            <path d="M8 3v4"></path>
                                                            <path d="M4 11h16"></path>
                                                            <path d="M11 15h1"></path>
                                                            <path d="M12 15v3"></path>
                                                        </svg>
                                                        <span>March 15, 2025</span>
                                                    </div>
                                                    <div class="d-flex flex-row gap-2 align-items-center">
                                                        <svg
                                                            xmlns="http://www.w3.org/2000/svg"
                                                            width="16"
                                                            height="16"
                                                            viewBox="0 0 24 24"
                                                            fill="none"
                                                            stroke="currentColor"
                                                            stroke-width="1.5"
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            class="icon icon-tabler icons-tabler-outline icon-tabler-clock">
                                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                            <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" />
                                                            <path d="M12 7v5l3 3" />
                                                        </svg>
                                                        <span>10:00 AM - 11:30 AM</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="d-flex flex-row justify-content-between">
                                                <div class="avatar-group">
                                                    <span class="avatar avatar-sm">
                                                        <img alt="avatar" src="../../assets/images/avatar/avatar-2.jpg" class="rounded-circle" />
                                                    </span>
                                                    <span class="avatar avatar-sm">
                                                        <img alt="avatar" src="../../assets/images/avatar/avatar-3.jpg" class="rounded-circle" />
                                                    </span>
                                                    <span class="avatar avatar-sm">
                                                        <img alt="avatar" src="../../assets/images/avatar/avatar-16.jpg" class="rounded-circle" />
                                                    </span>
                                                    <span class="avatar avatar-sm">
                                                        <img alt="avatar" src="../../assets/images/avatar/avatar-15.jpg" class="rounded-circle" />
                                                    </span>
                                                    <span class="avatar avatar-sm avatar-primary">
                                                        <span class="avatar-initials rounded-circle">2+</span>
                                                    </span>
                                                </div>
                                                <div><span class="badge bg-info-subtle text-info-emphasis">Scheduled</span></div>
                                            </div>
                                            <div class="d-flex flex-row justify-content-between">
                                                <div class="d-flex flex-row gap-2 align-items-center">
                                                    <span><img src="../../assets/images/svg/google-meet.svg" alt="google" /></span>
                                                    <span>Virtual (Google Meet)</span>
                                                </div>
                                                <div>
                                                    <a href="#!" class="btn btn-subtle-primary btn-sm">Join now</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card card-lg">
                                        <div class="card-body d-flex flex-column gap-6">
                                            <div class="d-flex flex-column gap-2">
                                                <div>
                                                    <h5 class="mb-0">Customer Success Review</h5>
                                                </div>
                                                <div class="d-flex flex-column flex-xxl-row gap-1 gap-xxl-4 align-items-center text-secondary">
                                                    <div class="d-flex flex-row gap-2 align-items-center">
                                                        <svg
                                                            xmlns="http://www.w3.org/2000/svg"
                                                            width="16"
                                                            height="16"
                                                            viewBox="0 0 24 24"
                                                            fill="none"
                                                            stroke="currentColor"
                                                            stroke-width="1.5"
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            class="icon icon-tabler icons-tabler-outline icon-tabler-calendar text-secondary">
                                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                            <path d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z"></path>
                                                            <path d="M16 3v4"></path>
                                                            <path d="M8 3v4"></path>
                                                            <path d="M4 11h16"></path>
                                                            <path d="M11 15h1"></path>
                                                            <path d="M12 15v3"></path>
                                                        </svg>
                                                        <span>March 15, 2025</span>
                                                    </div>
                                                    <div class="d-flex flex-row gap-2 align-items-center">
                                                        <svg
                                                            xmlns="http://www.w3.org/2000/svg"
                                                            width="16"
                                                            height="16"
                                                            viewBox="0 0 24 24"
                                                            fill="none"
                                                            stroke="currentColor"
                                                            stroke-width="1.5"
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            class="icon icon-tabler icons-tabler-outline icon-tabler-clock">
                                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                            <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" />
                                                            <path d="M12 7v5l3 3" />
                                                        </svg>
                                                        <span>10:00 AM - 11:30 AM</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="d-flex flex-row justify-content-between">
                                                <div class="avatar-group">
                                                    <span class="avatar avatar-sm">
                                                        <img alt="avatar" src="../../assets/images/avatar/avatar-2.jpg" class="rounded-circle" />
                                                    </span>
                                                    <span class="avatar avatar-sm">
                                                        <img alt="avatar" src="../../assets/images/avatar/avatar-3.jpg" class="rounded-circle" />
                                                    </span>
                                                    <span class="avatar avatar-sm">
                                                        <img alt="avatar" src="../../assets/images/avatar/avatar-16.jpg" class="rounded-circle" />
                                                    </span>
                                                    <span class="avatar avatar-sm">
                                                        <img alt="avatar" src="../../assets/images/avatar/avatar-15.jpg" class="rounded-circle" />
                                                    </span>
                                                    <span class="avatar avatar-sm avatar-primary">
                                                        <span class="avatar-initials rounded-circle">2+</span>
                                                    </span>
                                                </div>
                                                <div><span class="badge bg-info-subtle text-info-emphasis">Scheduled</span></div>
                                            </div>
                                            <div class="d-flex flex-row justify-content-between">
                                                <div class="d-flex flex-row gap-2 align-items-center">
                                                    <span><img src="../../assets/images/svg/zoom.svg" alt="zoom" /></span>
                                                    <span>Virtual (Zoom Meeting)</span>
                                                </div>
                                                <div>
                                                    <a href="#!" class="btn btn-subtle-primary btn-sm">Join now</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card card-lg">
                                        <div class="card-body d-flex flex-column gap-6">
                                            <div class="d-flex flex-column gap-2">
                                                <div>
                                                    <h5 class="mb-0">Quarterly Business Review</h5>
                                                </div>
                                                <div class="d-flex flex-column flex-xxl-row gap-1 gap-xxl-4 align-items-center text-secondary">
                                                    <div class="d-flex flex-row gap-2 align-items-center">
                                                        <svg
                                                            xmlns="http://www.w3.org/2000/svg"
                                                            width="16"
                                                            height="16"
                                                            viewBox="0 0 24 24"
                                                            fill="none"
                                                            stroke="currentColor"
                                                            stroke-width="1.5"
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            class="icon icon-tabler icons-tabler-outline icon-tabler-calendar text-secondary">
                                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                            <path d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z"></path>
                                                            <path d="M16 3v4"></path>
                                                            <path d="M8 3v4"></path>
                                                            <path d="M4 11h16"></path>
                                                            <path d="M11 15h1"></path>
                                                            <path d="M12 15v3"></path>
                                                        </svg>
                                                        <span>March 15, 2025</span>
                                                    </div>
                                                    <div class="d-flex flex-row gap-2 align-items-center">
                                                        <svg
                                                            xmlns="http://www.w3.org/2000/svg"
                                                            width="16"
                                                            height="16"
                                                            viewBox="0 0 24 24"
                                                            fill="none"
                                                            stroke="currentColor"
                                                            stroke-width="1.5"
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            class="icon icon-tabler icons-tabler-outline icon-tabler-clock">
                                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                            <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" />
                                                            <path d="M12 7v5l3 3" />
                                                        </svg>
                                                        <span>10:00 AM - 11:30 AM</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="d-flex flex-row justify-content-between">
                                                <div class="avatar-group">
                                                    <span class="avatar avatar-sm">
                                                        <img alt="avatar" src="../../assets/images/avatar/avatar-4.jpg" class="rounded-circle" />
                                                    </span>
                                                    <span class="avatar avatar-sm">
                                                        <img alt="avatar" src="../../assets/images/avatar/avatar-5.jpg" class="rounded-circle" />
                                                    </span>
                                                    <span class="avatar avatar-sm">
                                                        <img alt="avatar" src="../../assets/images/avatar/avatar-6.jpg" class="rounded-circle" />
                                                    </span>
                                                    <span class="avatar avatar-sm">
                                                        <img alt="avatar" src="../../assets/images/avatar/avatar-7.jpg" class="rounded-circle" />
                                                    </span>
                                                    <span class="avatar avatar-sm avatar-primary">
                                                        <span class="avatar-initials rounded-circle">2+</span>
                                                    </span>
                                                </div>
                                                <div><span class="badge bg-success-subtle text-success-emphasis">Meeting Completed</span></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card card-lg">
                                        <div class="card-body d-flex flex-column gap-6">
                                            <div class="d-flex flex-column gap-2">
                                                <div>
                                                    <h5 class="mb-0">Product Demo Request</h5>
                                                </div>
                                                <div class="d-flex flex-column flex-xxl-row gap-1 gap-xxl-4 align-items-center text-secondary">
                                                    <div class="d-flex flex-row gap-2 align-items-center">
                                                        <svg
                                                            xmlns="http://www.w3.org/2000/svg"
                                                            width="16"
                                                            height="16"
                                                            viewBox="0 0 24 24"
                                                            fill="none"
                                                            stroke="currentColor"
                                                            stroke-width="1.5"
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            class="icon icon-tabler icons-tabler-outline icon-tabler-calendar text-secondary">
                                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                            <path d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z"></path>
                                                            <path d="M16 3v4"></path>
                                                            <path d="M8 3v4"></path>
                                                            <path d="M4 11h16"></path>
                                                            <path d="M11 15h1"></path>
                                                            <path d="M12 15v3"></path>
                                                        </svg>
                                                        <span>March 15, 2025</span>
                                                    </div>
                                                    <div class="d-flex flex-row gap-2 align-items-center">
                                                        <svg
                                                            xmlns="http://www.w3.org/2000/svg"
                                                            width="16"
                                                            height="16"
                                                            viewBox="0 0 24 24"
                                                            fill="none"
                                                            stroke="currentColor"
                                                            stroke-width="1.5"
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            class="icon icon-tabler icons-tabler-outline icon-tabler-clock">
                                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                            <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" />
                                                            <path d="M12 7v5l3 3" />
                                                        </svg>
                                                        <span>10:00 AM - 11:30 AM</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="d-flex flex-row justify-content-between">
                                                <div class="avatar-group">
                                                    <span class="avatar avatar-sm">
                                                        <img alt="avatar" src="../../assets/images/avatar/avatar-4.jpg" class="rounded-circle" />
                                                    </span>
                                                    <span class="avatar avatar-sm">
                                                        <img alt="avatar" src="../../assets/images/avatar/avatar-5.jpg" class="rounded-circle" />
                                                    </span>

                                                    <span class="avatar avatar-sm avatar-primary">
                                                        <span class="avatar-initials rounded-circle">2+</span>
                                                    </span>
                                                </div>
                                                <div><span class="badge bg-danger-subtle text-danger-emphasis">Canceled</span></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade show" id="attachments" role="tabpanel" aria-labelledby="attachments-tab">
                    <div>
                        <h5 class="mb-5">Attachments</h5>
                    </div>
                    <div>
                        <div class="d-flex flex-md-row flex-column gap-2 justify-content-between mb-6">
                            <div>
                                <form>
                                    <input class="form-control" type="search" value="" placeholder="Search" />
                                </form>
                            </div>
                            <div>
                                <a href="#!" class="btn btn-primary d-flex flex-row gap-1 align-items-center">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="16"
                                        height="16"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.5"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        class="icon icon-tabler icons-tabler-outline icon-tabler-plus">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M12 5l0 14" />
                                        <path d="M5 12l14 0" />
                                    </svg>
                                    Upload new
                                </a>
                            </div>
                        </div>
                        <div>
                            <div class="card card-lg">
                                <div class="card-body">
                                    <div class="d-flex flex-lg-row flex-column gap-3 justify-content-between align-items-lg-center border-bottom border-dashed pb-5">
                                        <div class="d-flex flex-column gap-lg-2 gap-1">
                                            <div class="d-flex flex-row gap-2 align-items-center">
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    width="20"
                                                    height="20"
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    stroke-width="1.5"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    class="icon icon-tabler icons-tabler-outline icon-tabler-file-type-pdf text-gray-600">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                    <path d="M14 3v4a1 1 0 0 0 1 1h4" />
                                                    <path d="M5 12v-7a2 2 0 0 1 2 -2h7l5 5v4" />
                                                    <path d="M5 18h1.5a1.5 1.5 0 0 0 0 -3h-1.5v6" />
                                                    <path d="M17 18h2" />
                                                    <path d="M20 15h-3v6" />
                                                    <path d="M11 15v6h1a2 2 0 0 0 2 -2v-2a2 2 0 0 0 -2 -2h-1z" />
                                                </svg>
                                                <h5 class="mb-0">Proposal.pdf</h5>
                                            </div>
                                            <div>
                                                <span>1.03mb</span>
                                                <div class="vr mx-1"></div>
                                                <span>March 19, 2025 11:53 AM</span>
                                                <div class="vr mx-1"></div>
                                                <span>
                                                    Uploaded by:
                                                    <a href="#!">Jitu Chauhan</a>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="d-flex flex-row">
                                            <a href="#!" class="btn btn-icon btn-ghost btn-sm rounded-circle"><svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    width="18"
                                                    height="18"
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    stroke-width="1.5"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    class="icon icon-tabler icons-tabler-outline icon-tabler-download">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                    <path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" />
                                                    <path d="M7 11l5 5l5 -5" />
                                                    <path d="M12 4l0 12" />
                                                </svg></a>
                                            <a href="#!" class="btn btn-icon btn-ghost btn-sm rounded-circle"><svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    width="18"
                                                    height="18"
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    stroke-width="1.5"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    class="icon icon-tabler icons-tabler-outline icon-tabler-trash">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                    <path d="M4 7l16 0" />
                                                    <path d="M10 11l0 6" />
                                                    <path d="M14 11l0 6" />
                                                    <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" />
                                                    <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" />
                                                </svg>
                                            </a>
                                        </div>
                                    </div>

                                    <div class="d-flex flex-lg-row flex-column gap-3 justify-content-between align-items-lg-center border-bottom border-dashed py-5">
                                        <div class="d-flex flex-column gap-lg-2 gap-1">
                                            <div class="d-flex flex-row gap-2 align-items-center">
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    width="20"
                                                    height="20"
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    stroke-width="1.5"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    class="icon icon-tabler icons-tabler-outline icon-tabler-file-word text-gray-600">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                    <path d="M14 3v4a1 1 0 0 0 1 1h4" />
                                                    <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2" />
                                                    <path d="M9 12l1.333 5l1.667 -4l1.667 4l1.333 -5" />
                                                </svg>
                                                <h5 class="mb-0">MeetingNotes.docx</h5>
                                            </div>
                                            <div>
                                                <span>836kb</span>
                                                <div class="vr mx-1"></div>
                                                <span>March 19, 2025 11:53 AM</span>
                                                <div class="vr mx-1"></div>
                                                <span>
                                                    Uploaded by:
                                                    <a href="#!">Sandip Chauhan</a>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="d-flex flex-row">
                                            <a href="#!" class="btn btn-icon btn-ghost btn-sm rounded-circle"><svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    width="18"
                                                    height="18"
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    stroke-width="1.5"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    class="icon icon-tabler icons-tabler-outline icon-tabler-download">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                    <path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" />
                                                    <path d="M7 11l5 5l5 -5" />
                                                    <path d="M12 4l0 12" />
                                                </svg>
                                            </a>
                                            <a href="#!" class="btn btn-icon btn-ghost btn-sm rounded-circle"><svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    width="18"
                                                    height="18"
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    stroke-width="1.5"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    class="icon icon-tabler icons-tabler-outline icon-tabler-trash">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                    <path d="M4 7l16 0" />
                                                    <path d="M10 11l0 6" />
                                                    <path d="M14 11l0 6" />
                                                    <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" />
                                                    <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" />
                                                </svg></a>
                                        </div>
                                    </div>

                                    <div class="d-flex flex-lg-row flex-column gap-3 justify-content-between align-items-lg-center border-bottom border-dashed py-5">
                                        <div class="d-flex flex-column gap-lg-2 gap-1">
                                            <div class="d-flex flex-row gap-2 align-items-center">
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    width="20"
                                                    height="20"
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    stroke-width="1.5"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    class="icon icon-tabler icons-tabler-outline icon-tabler-file-type-ppt text-gray-600">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                    <path d="M14 3v4a1 1 0 0 0 1 1h4" />
                                                    <path d="M14 3v4a1 1 0 0 0 1 1h4" />
                                                    <path d="M5 18h1.5a1.5 1.5 0 0 0 0 -3h-1.5v6" />
                                                    <path d="M11 18h1.5a1.5 1.5 0 0 0 0 -3h-1.5v6" />
                                                    <path d="M16.5 15h3" />
                                                    <path d="M18 15v6" />
                                                    <path d="M5 12v-7a2 2 0 0 1 2 -2h7l5 5v4" />
                                                </svg>
                                                <h5 class="mb-0">Presentation.pptx</h5>
                                            </div>
                                            <div>
                                                <span>2.21mb</span>
                                                <div class="vr mx-1"></div>
                                                <span>March 19, 2025 11:53 AM</span>
                                                <div class="vr mx-1"></div>
                                                <span>
                                                    Uploaded by:
                                                    <a href="#!">Anita parmar</a>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="d-flex flex-row">
                                            <a href="#!" class="btn btn-icon btn-ghost btn-sm rounded-circle"><svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    width="18"
                                                    height="18"
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    stroke-width="1.5"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    class="icon icon-tabler icons-tabler-outline icon-tabler-download">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                    <path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" />
                                                    <path d="M7 11l5 5l5 -5" />
                                                    <path d="M12 4l0 12" />
                                                </svg>
                                            </a>
                                            <a href="#!" class="btn btn-icon btn-ghost btn-sm rounded-circle"><svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    width="18"
                                                    height="18"
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    stroke-width="1.5"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    class="icon icon-tabler icons-tabler-outline icon-tabler-trash">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                    <path d="M4 7l16 0" />
                                                    <path d="M10 11l0 6" />
                                                    <path d="M14 11l0 6" />
                                                    <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" />
                                                    <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" />
                                                </svg>
                                            </a>
                                        </div>
                                    </div>

                                    <div class="d-flex flex-column gap-5 pt-5">
                                        <div class="d-flex flex-lg-row flex-column gap-3 justify-content-between align-items-lg-center">
                                            <div class="d-flex flex-column gap-lg-2 gap-1">
                                                <div class="d-flex flex-row gap-2 align-items-center">
                                                    <svg
                                                        xmlns="http://www.w3.org/2000/svg"
                                                        width="20"
                                                        height="20"
                                                        viewBox="0 0 24 24"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        stroke-width="1.5"
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        class="icon icon-tabler icons-tabler-outline icon-tabler-file-type-ppt text-gray-600">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                        <path d="M14 3v4a1 1 0 0 0 1 1h4" />
                                                        <path d="M14 3v4a1 1 0 0 0 1 1h4" />
                                                        <path d="M5 18h1.5a1.5 1.5 0 0 0 0 -3h-1.5v6" />
                                                        <path d="M11 18h1.5a1.5 1.5 0 0 0 0 -3h-1.5v6" />
                                                        <path d="M16.5 15h3" />
                                                        <path d="M18 15v6" />
                                                        <path d="M5 12v-7a2 2 0 0 1 2 -2h7l5 5v4" />
                                                    </svg>
                                                    <h5 class="mb-0">Dasher-Preview.jpg</h5>
                                                </div>
                                                <div>
                                                    <span>2.3mb</span>
                                                    <div class="vr mx-1"></div>
                                                    <span>March 19, 2025 11:53 AM</span>
                                                    <div class="vr mx-1"></div>
                                                    <span>
                                                        Uploaded by:
                                                        <a href="#!">Jitu Chauhan</a>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="d-flex flex-row">
                                                <a href="#!" class="btn btn-icon btn-ghost btn-sm rounded-circle"><svg
                                                        xmlns="http://www.w3.org/2000/svg"
                                                        width="18"
                                                        height="18"
                                                        viewBox="0 0 24 24"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        stroke-width="1.5"
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        class="icon icon-tabler icons-tabler-outline icon-tabler-download">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                        <path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" />
                                                        <path d="M7 11l5 5l5 -5" />
                                                        <path d="M12 4l0 12" />
                                                    </svg>
                                                </a>
                                                <a href="#!" class="btn btn-icon btn-ghost btn-sm rounded-circle"><svg
                                                        xmlns="http://www.w3.org/2000/svg"
                                                        width="18"
                                                        height="18"
                                                        viewBox="0 0 24 24"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        stroke-width="1.5"
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        class="icon icon-tabler icons-tabler-outline icon-tabler-trash">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                        <path d="M4 7l16 0" />
                                                        <path d="M10 11l0 6" />
                                                        <path d="M14 11l0 6" />
                                                        <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" />
                                                        <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" />
                                                    </svg>
                                                </a>
                                            </div>
                                        </div>
                                        <div>
                                            <img src="../../assets/images/png/dasher-ui-bootstrap-5.jpg" alt="bootstrap 5 admin dashboard template" class="img-fluid rounded" />
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
</div>