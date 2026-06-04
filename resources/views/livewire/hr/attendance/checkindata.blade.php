<div>
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between mb-3">
            <div class="my-auto mb-2">
                <h4 class="mb-1 fw-semibold">Upload Check-in Attendance</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('fp.attendance') }}">Attendance</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Upload Check-in</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="row">
            {{-- Instructions --}}
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">File Requirements</h5>
                    </div>
                    <div class="card-body">
                        <h6 class="text-primary fw-semibold mb-3">Expected Columns</h6>
                        <ul class="list-unstyled mb-4">
                            <li class="mb-2">
                                <i class="ti ti-circle-check text-success me-2"></i>
                                <strong>Name</strong><br>
                                <small class="text-muted ms-4">The device user's name (informational only)</small>
                            </li>
                            <li class="mb-2">
                                <i class="ti ti-circle-check text-success me-2"></i>
                                <strong>No.</strong><br>
                                <small class="text-muted ms-4">The device user number (matched to fingerprint users)</small>
                            </li>
                            <li class="mb-2">
                                <i class="ti ti-circle-check text-success me-2"></i>
                                <strong>Date/Time</strong><br>
                                <small class="text-muted ms-4">The punch timestamp, e.g. 1/1/2026 8:10:32 AM</small>
                            </li>
                        </ul>

                        <div class="alert alert-info mb-0">
                            <h6 class="alert-heading"><i class="ti ti-info-circle me-2"></i>Notes</h6>
                            <ul class="mb-0 ps-3">
                                <li>Supported formats: .csv or .xlsx (max 10MB)</li>
                                <li>Export the device report as <strong>CSV</strong> before uploading
                                    (raw device .xls files cannot be read)</li>
                                <li>Duplicate punches (same user &amp; timestamp) are skipped</li>
                                <li>Each row is stored as a single punch</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Upload / Preview --}}
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Import Attendance Logs</h5>
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
                                       id="checkinFileInput"
                                       accept=".csv,.xlsx">
                                <label for="checkinFileInput" class="btn btn-primary">
                                    <i class="ti ti-upload me-1"></i> Select File
                                </label>
                                <p class="text-muted mt-3 mb-0">
                                    <small>Supported formats: CSV (.csv) or Excel (.xlsx) - Max size: 10MB</small>
                                </p>
                            </div>

                            @error('file')
                                <div class="alert alert-danger mt-3">{{ $message }}</div>
                            @enderror

                            @if ($file)
                                <div class="d-flex align-items-center justify-content-between mt-3">
                                    <span class="text-success">
                                        <i class="ti ti-file-check me-1"></i>
                                        File ready: {{ $file->getClientOriginalName() }}
                                    </span>
                                    <button type="button" wire:click="parseFile" class="btn btn-primary"
                                            wire:loading.attr="disabled" wire:target="parseFile">
                                        <span wire:loading.remove wire:target="parseFile">
                                            <i class="ti ti-eye me-1"></i> Parse &amp; Preview
                                        </span>
                                        <span wire:loading wire:target="parseFile">
                                            <span class="spinner-border spinner-border-sm me-1"></span> Parsing...
                                        </span>
                                    </button>
                                </div>
                            @endif

                            {{-- Loading indicator while the file uploads --}}
                            <div wire:loading wire:target="file" class="text-center mt-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Uploading...</span>
                                </div>
                                <p class="mt-2 text-muted">Uploading file...</p>
                            </div>
                        @else
                            {{-- Preview Section --}}
                            <div class="preview-section">
                                <div class="row g-3 mb-4">
                                    <div class="col">
                                        <div class="border rounded p-3 text-center">
                                            <div class="h4 mb-0">{{ number_format($totalRows) }}</div>
                                            <small class="text-muted">Total Rows</small>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="border rounded p-3 text-center">
                                            <div class="h4 mb-0 text-success">{{ number_format($validRows) }}</div>
                                            <small class="text-muted">Valid</small>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="border rounded p-3 text-center">
                                            <div class="h4 mb-0 text-danger">{{ number_format($invalidRows) }}</div>
                                            <small class="text-muted">Invalid</small>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="border rounded p-3 text-center">
                                            <div class="h4 mb-0 text-warning">{{ number_format($unknownUsers) }}</div>
                                            <small class="text-muted">Unknown Users</small>
                                        </div>
                                    </div>
                                </div>

                                @if ($unknownUsers > 0)
                                    <div class="alert alert-warning">
                                        <i class="ti ti-alert-triangle me-1"></i>
                                        {{ number_format($unknownUsers) }} record(s) reference a device user number
                                        not yet registered as a fingerprint user. They will still be imported.
                                    </div>
                                @endif

                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th>No.</th>
                                                <th>Name</th>
                                                <th>Date</th>
                                                <th>Time</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($sample as $row)
                                                <tr>
                                                    <td>{{ $row['fpuser_id'] }}</td>
                                                    <td>{{ $row['name'] }}</td>
                                                    <td>{{ $row['clockdate'] }}</td>
                                                    <td>{{ $row['clocktime'] }}</td>
                                                    <td>
                                                        @if ($row['error'])
                                                            <span class="badge bg-danger">{{ $row['error'] }}</span>
                                                        @elseif (!$row['known'])
                                                            <span class="badge bg-warning text-dark">Unknown user</span>
                                                        @else
                                                            <span class="badge bg-success">OK</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                @if ($totalRows > count($sample))
                                    <p class="text-muted small">
                                        Showing first {{ count($sample) }} of {{ number_format($totalRows) }} rows.
                                    </p>
                                @endif

                                <div class="d-flex justify-content-end gap-2 mt-3">
                                    <button type="button" wire:click="cancel" class="btn btn-outline-secondary"
                                            wire:loading.attr="disabled" wire:target="import">
                                        Cancel
                                    </button>
                                    <button type="button" wire:click="import" class="btn btn-success"
                                            wire:loading.attr="disabled" wire:target="import"
                                            @disabled($validRows === 0)>
                                        <span wire:loading.remove wire:target="import">
                                            <i class="ti ti-database-import me-1"></i> Import {{ number_format($validRows) }} Record(s)
                                        </span>
                                        <span wire:loading wire:target="import">
                                            <span class="spinner-border spinner-border-sm me-1"></span> Importing...
                                        </span>
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
