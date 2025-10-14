<div>
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
                <a href="#!" class="btn btn-dark d-md-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#contact-modal" >
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
                    ADD LEAVE TYPE
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
                                    <th class="listjs-sorter" data-sort="task_title">Name</th>
                                    <th class="listjs-sorter" data-sort="task_type">Leave Type</th>
                                    <th class="listjs-sorter" data-sort="task_assigned">Days</th>
                                    <th class="listjs-sorter" data-sort="task_date">Gender</th>
                                    <th class="listjs-sorter" data-sort="task_priority">Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody class="list">
                                @foreach($leavetypes as $leave)
                                    <tr>
                                        <td class="pe-0">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault65" />
                                                <label class="form-check-label" for="flexCheckDefault65"></label>
                                            </div>
                                        </td>
                                        <td class="task_title">{{ $leave->name }}</td>
                                        <td class="task_type">{{ $leave->description }}</td>
                                        <td class="task_assigned">{{ $leave->days }}</td>
                                        <td class="task_date">{{ $leave->gender }}</td>
                                        <td class="task_priority">{{ $leave->status }}</td>
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
        </div>
    </div>

        <!-- Modal -->
          <!-- Modal -->
    <div class="modal fade" id="contact-modal" tabindex="-1" aria-labelledby="contact-modal-label" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h4 class="modal-title" id="contact-modal-label">Add Leave Type</h4>
            <button type="button" class="btn-close" id="btn-close-modal" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form wire:submit.prevent="storeLeaveType">
                @csrf
              <div class="mb-3">
                <label class="form-label" for="contact-name-field">Name</label>
                <input type="text" class="form-control" placeholder="Enter Name" wire:model="leavetype.name" required />
              </div>
              <div class="mb-3">
                <label class="form-label" for="email-field">Description</label>
                <input type="text" class="form-control" placeholder="Enter Description" wire:model="leavetype.description" required />
              </div>
              <div class="mb-3">
                <label class="form-label" for="phone-number-field">Days</label>
                <input type="text" class="form-control" placeholder="Enter Phone" wire:model="leavetype.days" required />
              </div>
              <div class="mb-3">
                <label class="form-label" for="lead-status-field">Leave Gender</label>
                <select class="form-control" wire:model="leavetype.gender"  required>
                  <option value="">Select Lead Status</option>
                  <option value="Both" selected>Both</option>
                  <option value="Male" >Male</option>
                  <option value="Female">Female</option>
                </select>
              </div>
              
              <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">+ Add Contact</button>

                <button class="btn btn-secondary ms-2" data-bs-dismiss="modal" aria-label="Close">Close</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
</div>