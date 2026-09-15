<div>
    <div>
        <h5 class="mb-5">Physical Stock Count</h5>
    </div>
    <div class="d-flex flex-column gap-6">
        <div class="d-flex flex-md-row flex-column gap-2 justify-content-between">
            <div class="d-flex flex-row gap-3 align-items-center">
                <div>
                    <form>
                        <input class="form-control" type="search" wire:model.live.debounce.300ms="search" placeholder="Search by store" />
                    </form>
                </div>
            </div>
            <div>
                <button class="btn btn-primary d-flex flex-row gap-1 align-items-center" wire:click="openModal">
                    <i class="fa-solid fa-plus"></i>
                    NEW COUNT
                </button>
            </div>
        </div>
        <div>
            <div class="card card-lg overflow-hidden" id="taskTable" data-list="name">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        @if (session()->has('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif
                        @if (session()->has('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif

                        <table class="table text-nowrap mb-0 table-centered table-hover" data-check-container="">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Store</th>
                                    <th>Count Date</th>
                                    <th>Status</th>
                                    <th>Added By</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody class="list">
                                @php
                                    $number = 1;
                                @endphp
                                @forelse($counts as $count)
                                    <tr>
                                        <td>{{ $number++ }}</td>
                                        <td class="name">{{ $count->departmentStore->name ?? 'N/A' }}</td>
                                        <td>{{ $count->count_date->format('Y-m-d') }}</td>
                                        <td>
                                            @if($count->status === 'approved')
                                                <span class="badge bg-success-subtle text-success-emphasis">Approved</span>
                                            @else
                                                <span class="badge bg-warning-subtle text-warning-emphasis">Draft</span>
                                            @endif
                                        </td>
                                        <td>{{ $count->addedBy->name ?? 'N/A' }}</td>
                                        <td>
                                            <button class="btn btn-sm btn-secondary" wire:click="view('{{ $count->id }}')">View</button>
                                            @if($count->status !== 'approved')
                                                <button class="btn btn-sm btn-success" wire:click="approve('{{ $count->id }}')"
                                                    onclick="return confirm('Approve this count and post stock adjustments for any variances?')">Approve</button>
                                                <button class="btn btn-sm btn-danger" wire:click="delete('{{ $count->id }}')"
                                                    onclick="return confirm('Delete this physical count?')">Delete</button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">No physical counts recorded yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade @if($showModal) show d-block @endif" tabindex="-1"
        @if($showModal) style="background: rgba(0,0,0,0.5);" @endif>
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form wire:submit.prevent="save">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            {{ $modalMode === 'view' ? 'View Physical Count' : 'New Physical Count' }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label>Department Store</label>
                                <select class="form-select" wire:model="department_store_id" @if($modalMode === 'view') disabled @endif>
                                    <option value="">-- Select Store --</option>
                                    @foreach($departmentStores as $store)
                                        <option value="{{ $store->id }}">{{ $store->name }}</option>
                                    @endforeach
                                </select>
                                @error('department_store_id') <small class="text-danger d-block">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-6">
                                <label>Count Date</label>
                                <input type="date" class="form-control" wire:model="count_date" @if($modalMode === 'view') disabled @endif>
                                @error('count_date') <small class="text-danger d-block">{{ $message }}</small> @enderror
                            </div>
                        </div>

                        @if($modalMode === 'create')
                            <div class="mb-3">
                                <button type="button" class="btn btn-outline-primary btn-sm" wire:click="loadStockableItems">
                                    <i class="fa-solid fa-list"></i> Load Stockable Items
                                </button>
                            </div>
                        @endif

                        @if(count($lines))
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Item</th>
                                            <th>Unit</th>
                                            <th>System Qty</th>
                                            <th>Counted Qty</th>
                                            <th>Variance</th>
                                            <th>Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($lines as $index => $line)
                                            <tr>
                                                <td>{{ $line['name'] }}</td>
                                                <td>{{ $line['unit'] }}</td>
                                                <td>{{ $line['system_qty'] }}</td>
                                                <td style="min-width:110px">
                                                    @if($modalMode === 'create')
                                                        <input type="number" class="form-control form-control-sm"
                                                            wire:model="lines.{{ $index }}.counted_qty" placeholder="Qty">
                                                    @else
                                                        {{ $line['counted_qty'] ?? '-' }}
                                                    @endif
                                                </td>
                                                <td>
                                                    @if(!is_null($line['counted_qty']))
                                                        @php $variance = $line['counted_qty'] - $line['system_qty']; @endphp
                                                        <span class="{{ $variance == 0 ? 'text-muted' : ($variance > 0 ? 'text-success' : 'text-danger') }}">
                                                            {{ $variance > 0 ? '+' : '' }}{{ $variance }}
                                                        </span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td style="min-width:140px">
                                                    @if($modalMode === 'create')
                                                        <input type="text" class="form-control form-control-sm"
                                                            wire:model="lines.{{ $index }}.remarks" placeholder="Remarks">
                                                    @else
                                                        {{ $line['remarks'] ?? '-' }}
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @elseif($modalMode === 'create')
                            <p class="text-muted">Select a department store then load stockable items to begin counting.</p>
                        @endif

                        @if($modalMode === 'create')
                            <div class="mb-3">
                                <label>Remarks</label>
                                <textarea class="form-control" wire:model="remarks" rows="2" placeholder="Optional remarks"></textarea>
                            </div>
                        @endif
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showModal', false)">Close</button>
                        @if($modalMode === 'create')
                            <button type="submit" class="btn btn-primary" @if(!count($lines)) disabled @endif>Save Count</button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
