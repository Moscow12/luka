<div>
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