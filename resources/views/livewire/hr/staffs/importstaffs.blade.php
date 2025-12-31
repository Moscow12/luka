<div>
    <div class="container-fluid">
        {{-- Flash Messages --}}
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

        <div class="row">
            {{-- Requirements Section --}}
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Import Requirements</h5>
                    </div>
                    <div class="card-body">
                        <h6 class="text-primary fw-semibold mb-3">Required Fields</h6>
                        <ul class="list-unstyled mb-4">
                            @foreach ($requiredFields as $field => $description)
                                <li class="mb-2">
                                    <i class="ti ti-circle-check text-success me-2"></i>
                                    <strong>{{ $field }}</strong>
                                    <br>
                                    <small class="text-muted ms-4">{{ $description }}</small>
                                </li>
                            @endforeach
                        </ul>

                        <h6 class="text-secondary fw-semibold mb-3">Optional Fields</h6>
                        <ul class="list-unstyled mb-4">
                            @foreach ($optionalFields as $field => $description)
                                <li class="mb-2">
                                    <i class="ti ti-circle text-secondary me-2"></i>
                                    <strong>{{ $field }}</strong>
                                    <br>
                                    <small class="text-muted ms-4">{{ $description }}</small>
                                </li>
                            @endforeach
                        </ul>

                        <div class="alert alert-info mb-0">
                            <h6 class="alert-heading"><i class="ti ti-info-circle me-2"></i>Tips</h6>
                            <ul class="mb-0 ps-3">
                                <li>Use the template file for correct column names</li>
                                <li>Lookup values (department, region, etc.) must match existing records</li>
                                <li>Dates should be in YYYY-MM-DD format</li>
                                <li>Gender: Male, Female, or Other</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Upload Section --}}
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Import Staff from Excel/CSV</h5>
                        <div class="btn-group">
                            <button type="button" wire:click="downloadTemplate" class="btn btn-outline-primary btn-sm">
                                <i class="ti ti-download me-1"></i> Download Template
                            </button>
                            <button type="button" wire:click="downloadAvailableData" class="btn btn-outline-secondary btn-sm">
                                <i class="ti ti-list me-1"></i> Available Lookup Data
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        @if (!$showPreview)
                            {{-- File Upload Form --}}
                            <div class="upload-area p-5 border-2 border-dashed rounded-3 text-center"
                                 x-data="{ isDragging: false }"
                                 x-on:dragover.prevent="isDragging = true"
                                 x-on:dragleave.prevent="isDragging = false"
                                 x-on:drop.prevent="isDragging = false; $refs.fileInput.files = $event.dataTransfer.files; $refs.fileInput.dispatchEvent(new Event('change'))"
                                 :class="{ 'border-primary bg-light': isDragging }">
                                <div class="mb-3">
                                    <i class="ti ti-cloud-upload display-4 text-primary"></i>
                                </div>
                                <h5>Drag and drop your file here</h5>
                                <p class="text-muted mb-3">or click to browse</p>
                                <input type="file"
                                       wire:model="file"
                                       x-ref="fileInput"
                                       class="form-control d-none"
                                       id="fileInput"
                                       accept=".xlsx,.xls,.csv">
                                <label for="fileInput" class="btn btn-primary">
                                    <i class="ti ti-upload me-1"></i> Select File
                                </label>
                                <p class="text-muted mt-3 mb-0">
                                    <small>Supported formats: Excel (.xlsx, .xls) or CSV (.csv) - Max size: 10MB</small>
                                </p>
                            </div>

                            {{-- Loading indicator --}}
                            <div wire:loading wire:target="file" class="text-center mt-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Processing...</span>
                                </div>
                                <p class="mt-2 text-muted">Processing file...</p>
                            </div>

                            @error('file')
                                <div class="alert alert-danger mt-3">
                                    {{ $message }}
                                </div>
                            @enderror
                        @else
                            {{-- Preview Section --}}
                            <div class="preview-section">
                                {{-- Summary Stats --}}
                                <div class="row mb-4">
                                    <div class="col-md-4">
                                        <div class="card bg-primary text-white">
                                            <div class="card-body text-center">
                                                <h3 class="mb-0">{{ $totalRows }}</h3>
                                                <small>Total Rows</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card bg-success text-white">
                                            <div class="card-body text-center">
                                                <h3 class="mb-0">{{ $validRows }}</h3>
                                                <small>Valid Rows</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card bg-danger text-white">
                                            <div class="card-body text-center">
                                                <h3 class="mb-0">{{ $invalidRows }}</h3>
                                                <small>Invalid Rows</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Preview Table --}}
                                <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                                    <table class="table table-bordered table-hover table-sm">
                                        <thead class="table-light sticky-top">
                                            <tr>
                                                <th style="width: 50px;">#</th>
                                                <th>Status</th>
                                                <th>Employee No</th>
                                                <th>Name</th>
                                                <th>Gender</th>
                                                <th>DOB</th>
                                                <th>Department</th>
                                                <th>Job Title</th>
                                                <th>Email</th>
                                                <th>Errors</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($previewData as $row)
                                                <tr class="{{ empty($row['errors']) ? 'table-success' : 'table-danger' }}">
                                                    <td>{{ $row['row_number'] }}</td>
                                                    <td>
                                                        @if (empty($row['errors']))
                                                            <span class="badge bg-success">
                                                                <i class="ti ti-check"></i> Valid
                                                            </span>
                                                        @else
                                                            <span class="badge bg-danger">
                                                                <i class="ti ti-x"></i> Invalid
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $row['data']['employee_no'] ?? '-' }}</td>
                                                    <td>
                                                        {{ ($row['data']['first_name'] ?? '') . ' ' . ($row['data']['middle_name'] ?? '') . ' ' . ($row['data']['last_name'] ?? '') }}
                                                    </td>
                                                    <td>{{ $row['data']['gender'] ?? '-' }}</td>
                                                    <td>{{ $row['resolved']['dob'] ?? ($row['data']['dob'] ?? '-') }}</td>
                                                    <td>{{ $row['data']['department'] ?? '-' }}</td>
                                                    <td>{{ $row['data']['job_title'] ?? '-' }}</td>
                                                    <td>{{ $row['data']['email'] ?? '-' }}</td>
                                                    <td>
                                                        @if (!empty($row['errors']))
                                                            <ul class="list-unstyled mb-0 small text-danger">
                                                                @foreach ($row['errors'] as $error)
                                                                    <li><i class="ti ti-alert-circle me-1"></i>{{ $error }}</li>
                                                                @endforeach
                                                            </ul>
                                                        @else
                                                            <span class="text-success"><i class="ti ti-check"></i></span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                {{-- Action Buttons --}}
                                <div class="d-flex justify-content-between mt-4">
                                    <button type="button" wire:click="cancelImport" class="btn btn-outline-secondary">
                                        <i class="ti ti-arrow-left me-1"></i> Cancel & Upload New File
                                    </button>

                                    @if ($validRows > 0)
                                        <button type="button"
                                                wire:click="import"
                                                wire:loading.attr="disabled"
                                                class="btn btn-success">
                                            <span wire:loading.remove wire:target="import">
                                                <i class="ti ti-upload me-1"></i> Import {{ $validRows }} Valid Record(s)
                                            </span>
                                            <span wire:loading wire:target="import">
                                                <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                                                Importing...
                                            </span>
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-secondary" disabled>
                                            <i class="ti ti-upload me-1"></i> No Valid Records to Import
                                        </button>
                                    @endif
                                </div>

                                @if ($invalidRows > 0)
                                    <div class="alert alert-warning mt-3">
                                        <i class="ti ti-alert-triangle me-2"></i>
                                        <strong>Note:</strong> {{ $invalidRows }} row(s) with errors will be skipped during import.
                                        Fix the errors in your file and re-upload for a complete import.
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
