<div>
    <div>
        <h5 class="mb-5">Designations</h5>
    </div>
    <div class="d-flex flex-column gap-3 mb-4">
        <div>
            <ul class="nav nav-line-bottom" id="tabEmail" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active py-2" id="mail-tab" data-bs-toggle="pill" href="#mail" role="tab" aria-controls="mail" aria-selected="true">Designations List ( {{ $designations->count() }} )</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link py-2" id="draft-tab" data-bs-toggle="pill" href="#draft" role="tab" aria-controls="draft" aria-selected="true">Add Designation</a>
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
                                <th class="listjs-sorter ps-0" data-sort="email_subject">Name</th>
                                <th class="listjs-sorter" data-sort="email_sender">Code</th>
                                <th class="listjs-sorter" data-sort="email_date">Added By</th>
                                <th class="listjs-sorter" data-sort="email_status">Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody class="list">
                            @foreach($designations as $designation)
                            <tr>
                                <td class="pe-0">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault65" />
                                        <label class="form-check-label" for="flexCheckDefault65"></label>
                                    </div>
                                </td>
                                <td class="task_title">{{ $designation->name }}</td>
                                <td class="task_type">{{ $designation->code }}</td>
                                <td class="task_assigned">{{ $designation->added_by?->full_name ?? 'N/A' }}</td>
                                <td class="task_date">{{ $designation->status }}</td>
                                <td class="task_priority"><span class="badge bg-danger-subtle text-danger-emphasis rounded-pill">High</span></td>
                                <td class="text-center">

                                </td>
                            </tr>
                            @endforeach
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
            <div class="card card-md" >
                    <!-- form for adding designation -->
                    <form  wire:submit.prevent="storeDesignation">
                        @csrf
                        <div class="col-md-6 mb-3">
                            <label for="validationCustom01" class="form-label">Designation Name</label>
                            <input type="text" class="form-control" wire:model="designation.name"  value="Chief Executive Officer" required>
                            <div class="valid-feedback">
                                Looks good!
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="validationCustom02" class="form-label">Designation Code</label>
                            <input type="text" class="form-control" wire:model="designation.code"  value="CEO" required>
                            <div class="valid-feedback">
                                Looks good!
                            </div>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-primary" type="submit">Submit form</button>
                        </div>
                    </form>
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