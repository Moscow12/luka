@php
    use App\Support\Engineering\Eng;
    use App\Support\Engineering\Lookups;

    $requisition = $job->requisition;
    $certification = $card->certification();
    $authorisation = $card->authorisation();
    $procurement = Lookups::PROCUREMENT_STATUSES[$card->procurementStatus()];
@endphp

<x-engineering.page :title="'Engineering job card · '.$card->number" :subtitle="$job->number.' · '.$job->equipmentName()">
    <x-slot:actions>
        <span class="badge fs-6 {{ Lookups::JOB_CARD_STATUS_BADGES[$card->status] }}">{{ Lookups::JOB_CARD_STATUSES[$card->status] }}@if($card->revision > 1) · rev {{ $card->revision }}@endif</span>
        <button type="button" class="btn btn-outline-secondary" onclick="window.print()"><i class="fa-solid fa-print me-1"></i>Print</button>
        <a href="{{ route('engineering.jobs.summary', $job) }}" class="btn btn-outline-secondary"><i class="fa-solid fa-file-lines me-1"></i>MRV summary</a>
    </x-slot:actions>

    {{-- Header from the MRV --}}
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <x-engineering.detail label="Requisition number">{{ $job->number }}</x-engineering.detail>
                <x-engineering.detail label="Department requesting">{{ $requisition->department?->name ?? '—' }}</x-engineering.detail>
                <x-engineering.detail label="Requisition date">{{ Eng::dt($requisition->requested_at) }}</x-engineering.detail>
                <x-engineering.detail label="Administrative responsibility">{{ $requisition->requester_role ?? '—' }}</x-engineering.detail>
                <x-engineering.detail label="Place / floor / room">{{ $requisition->locationLabel() }}</x-engineering.detail>
                <x-engineering.detail label="Equipment">{{ $job->equipmentName() }}</x-engineering.detail>
                <x-engineering.detail label="Inventory code / ID">{{ $job->equipmentCode() }}</x-engineering.detail>
                <x-engineering.detail label="Serial number">{{ $job->equipment?->serial_number ?? '—' }}</x-engineering.detail>
                <x-engineering.detail label="Type of work">{{ Lookups::label(Lookups::WORK_TYPES, $job->type_of_work) }}</x-engineering.detail>
                <x-engineering.detail label="Section required">{{ $job->section->name }}</x-engineering.detail>
                <x-engineering.detail label="Assigned engineer">{{ Eng::name($job->engineer) }}</x-engineering.detail>
                <x-engineering.detail label="Assistant engineers">{{ $job->assistants->map(fn ($a) => Eng::name($a))->implode(', ') ?: '—' }}</x-engineering.detail>
                <x-engineering.detail label="Requesting engineer">{{ Eng::name($card->requestingEngineer) }}</x-engineering.detail>
                <x-engineering.detail label="Submitted">{{ Eng::dt($card->submitted_at) }}</x-engineering.detail>
            </div>
        </div>
    </div>

    <fieldset @disabled(! $canEdit)>
        {{-- Diagnosis --}}
        <div class="card mb-4">
            <div class="card-header"><h6 class="mb-0"><i class="fa-solid fa-stethoscope me-2"></i>Diagnosis</h6></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Complaints / faults</label>
                        <textarea class="form-control" rows="3" wire:model="form.complaints_faults"></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Defects</label>
                        <textarea class="form-control" rows="3" wire:model="form.defects" placeholder="One defect per line"></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Diagnosis / action</label>
                        <textarea class="form-control" rows="3" wire:model="form.diagnosis_action"></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Job card description</label>
                        <textarea class="form-control" rows="3" wire:model="form.description"></textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Reason of failure <span class="text-danger">*</span></label>
                        <select class="form-select @error('form.failure_reason') is-invalid @enderror" wire:model.live="form.failure_reason">
                            <option value="">Select</option>
                            @foreach(Lookups::FAILURE_REASONS as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if($form['failure_reason'] === 'other')
                        <div class="col-md-8">
                            <label class="form-label">Describe the reason <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('form.failure_reason_text') is-invalid @enderror" wire:model="form.failure_reason_text">
                            @error('form.failure_reason_text') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    @endif
                    <div class="col-md-4">
                        <label class="form-label">Date part requested</label>
                        <input type="date" class="form-control" wire:model="form.date_part_requested">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Comments</label>
                        <textarea class="form-control" rows="2" wire:model="form.comments"></textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- Items and costing --}}
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fa-solid fa-receipt me-2"></i>Items ordered & costing</h6>
                @if($canEdit)
                    <button type="button" class="btn btn-sm btn-outline-primary eng-no-print" wire:click="addItem"><i class="fa-solid fa-plus me-1"></i>Add item</button>
                @endif
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>S/N</th><th>Item(s) ordered</th><th style="width: 100px;">UoM</th><th style="width: 140px;">Item code</th><th style="width: 110px;">Quantity</th><th style="width: 150px;">Price</th><th class="text-end" style="width: 150px;">Total</th><th></th></tr>
                    </thead>
                    <tbody>
                        @forelse($items as $index => $item)
                            <tr wire:key="item-{{ $index }}">
                                <td>{{ $index + 1 }}</td>
                                <td><input type="text" class="form-control form-control-sm @error('items.'.$index.'.description') is-invalid @enderror" wire:model="items.{{ $index }}.description"></td>
                                <td><input type="text" class="form-control form-control-sm" wire:model="items.{{ $index }}.uom"></td>
                                <td><input type="text" class="form-control form-control-sm" wire:model="items.{{ $index }}.item_code"></td>
                                <td><input type="number" min="0" step="any" class="form-control form-control-sm @error('items.'.$index.'.quantity') is-invalid @enderror" wire:model.live.debounce.400ms="items.{{ $index }}.quantity"></td>
                                <td><input type="number" min="0" step="any" class="form-control form-control-sm @error('items.'.$index.'.price') is-invalid @enderror" wire:model.live.debounce.400ms="items.{{ $index }}.price"></td>
                                <td class="text-end">{{ number_format((float) $item['quantity'] * (float) $item['price']) }}</td>
                                <td class="text-end">
                                    @if($canEdit)<button type="button" class="btn btn-sm btn-link text-danger eng-no-print" wire:click="removeItem({{ $index }})"><i class="fa-solid fa-trash"></i></button>@endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted py-3">No items yet.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="6" class="text-end fw-semibold">Sub total</td>
                            <td class="text-end fw-semibold">{{ number_format($totals['sub']) }}</td><td></td>
                        </tr>
                        <tr>
                            <td colspan="4" class="text-end">Labour charge × quantity</td>
                            <td><input type="number" min="0" step="any" class="form-control form-control-sm" wire:model.live.debounce.400ms="form.labour_qty" title="Quantity (hours or units)"></td>
                            <td><input type="number" min="0" step="any" class="form-control form-control-sm" wire:model.live.debounce.400ms="form.labour_charge" title="Labour charge"></td>
                            <td class="text-end">{{ number_format($totals['labour']) }}</td><td></td>
                        </tr>
                        <tr>
                            <td colspan="4" class="text-end">Fabrication × quantity</td>
                            <td><input type="number" min="0" step="any" class="form-control form-control-sm" wire:model.live.debounce.400ms="form.fabrication_qty" title="Quantity"></td>
                            <td><input type="number" min="0" step="any" class="form-control form-control-sm" wire:model.live.debounce.400ms="form.fabrication_charge" title="Fabrication charge"></td>
                            <td class="text-end">{{ number_format($totals['fabrication']) }}</td><td></td>
                        </tr>
                        <tr class="table-light">
                            <td colspan="6" class="text-end fw-bold">Estimated grand total</td>
                            <td class="text-end fw-bold text-nowrap">{{ Eng::money($totals['grand']) }}</td><td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @if($canEdit)
                <div class="card-footer d-flex flex-wrap justify-content-between align-items-center gap-2 eng-no-print">
                    <span class="small text-muted">Totals are calculated by the system. Once submitted, the card is locked unless it is returned to you.</span>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-primary" wire:click="save">Save draft</button>
                        <button type="button" class="btn btn-primary" wire:click="submit" wire:confirm="Submit this job card for certification? You will not be able to change it afterwards.">
                            <i class="fa-solid fa-paper-plane me-1"></i>Submit for certification
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </fieldset>

    {{-- Certification & authorisation --}}
    <div class="card mb-4">
        <div class="card-header"><h6 class="mb-0"><i class="fa-solid fa-stamp me-2"></i>Certification & authorisation</h6></div>
        <div class="card-body">
            <div class="row g-3">
                @foreach(['Certified' => $certification, 'Authorised' => $authorisation] as $label => $approval)
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100 {{ $approval ? 'border-success' : '' }}">
                            <div class="text-muted small">{{ $label }} by</div>
                            <div class="fw-semibold">{{ $approval?->actor?->full_name ?? '—' }}</div>
                            <div class="small text-muted">{{ $approval ? Eng::dt($approval->created_at) : 'Pending' }}</div>
                            @if($approval?->comment)<div class="mt-1">{{ $approval->comment }}</div>@endif
                        </div>
                    </div>
                @endforeach
            </div>

            @php $pushedBack = $card->approvals->whereIn('decision', ['returned', 'rejected']); @endphp
            @if($pushedBack->isNotEmpty())
                <ul class="list-unstyled small mt-3 mb-0">
                    @foreach($pushedBack as $approval)
                        <li class="text-danger"><i class="fa-solid fa-reply me-1"></i>{{ ucfirst($approval->decision) }} by {{ $approval->actor?->full_name }} on {{ Eng::dt($approval->created_at) }} (rev {{ $approval->revision }}): {{ $approval->comment }}</li>
                    @endforeach
                </ul>
            @endif

            @if($canDecide)
                <div class="border-top mt-3 pt-3 eng-no-print">
                    <label class="form-label">Comment <span class="text-muted small">(required to return or reject)</span></label>
                    <textarea class="form-control mb-2" rows="2" wire:model="decisionComment"></textarea>
                    <div class="d-flex flex-wrap gap-2 justify-content-end">
                        <button type="button" class="btn btn-outline-warning" wire:click="decide('returned')">Return to engineer</button>
                        <button type="button" class="btn btn-outline-danger" wire:click="decide('rejected')" wire:confirm="Reject this job card? This cannot be undone.">Reject</button>
                        <button type="button" class="btn btn-success" wire:click="decide('approved')">
                            <i class="fa-solid fa-check me-1"></i>{{ $card->status === 'pending_certification' ? 'Certify' : 'Authorise' }}
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Purchasing information --}}
    @if($card->isApproved())
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fa-solid fa-truck-ramp-box me-2"></i>Purchasing information</h6>
                <span class="badge {{ $procurement['badge'] }}">{{ $procurement['label'] }}</span>
            </div>
            <div class="card-body">
                <fieldset @disabled(! $canPurchase)>
                    <div class="row g-3">
                        @if($canPurchase)
                            <div class="col-md-4">
                                <label class="form-label">Link an existing LPO</label>
                                <select class="form-select" wire:model.live="purchasing.local_purchase_order_id">
                                    <option value="">None</option>
                                    @foreach($lpos as $lpo)
                                        <option value="{{ $lpo->id }}">{{ $lpo->lpo_number }} · {{ $lpo->generated_at?->format('d M Y') }}</option>
                                    @endforeach
                                </select>
                                <div class="form-text">Fills the order number and date from purchasing.</div>
                            </div>
                        @endif
                        <div class="col-md-4">
                            <label class="form-label">Order number</label>
                            <input type="text" class="form-control" wire:model="purchasing.order_number">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Supplier name</label>
                            <input type="text" class="form-control" wire:model="purchasing.supplier_name">
                        </div>
                        @foreach([
                            'proforma_requested_at' => 'Proforma requested',
                            'proforma_received_at' => 'Proforma received',
                            'tender_opened_at' => 'Tender / LPO opened',
                            'tender_approved_at' => 'Tender / LPO approved',
                            'order_date' => 'Order date',
                            'spare_procured_at' => 'Spare procured',
                            'delivery_expected_at' => 'Expected arrival',
                            'delivery_actual_at' => 'Actual arrival',
                        ] as $field => $label)
                            <div class="col-6 col-md-3">
                                <label class="form-label">{{ $label }}</label>
                                <input type="date" class="form-control @error('purchasing.'.$field) is-invalid @enderror" wire:model="purchasing.{{ $field }}">
                            </div>
                        @endforeach
                        <div class="col-md-6">
                            <label class="form-label">Reason for delay, approval → order</label>
                            <input type="text" class="form-control" wire:model="purchasing.delay_reason_order" placeholder="Required beyond {{ $limits['approval_to_order_days'] }} days">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Reason for delay, order → delivery</label>
                            <input type="text" class="form-control" wire:model="purchasing.delay_reason_delivery" placeholder="Required beyond {{ $limits['order_to_delivery_days'] }} days">
                        </div>
                    </div>
                </fieldset>

                <div class="row g-3 mt-1">
                    <x-engineering.detail label="Date of approval">{{ Eng::dt($card->approved_at) }}</x-engineering.detail>
                    <x-engineering.detail label="Days, approval → order">{{ $card->daysApprovalToOrder() ?? '—' }}</x-engineering.detail>
                    <x-engineering.detail label="Days, order → delivery">{{ $card->daysOrderToDelivery() ?? '—' }}</x-engineering.detail>
                    <x-engineering.detail label="Delivery period (days)">{{ $card->deliveryPeriod() ?? '—' }}</x-engineering.detail>
                </div>

                @if($canPurchase)
                    <div class="text-end mt-3 eng-no-print">
                        <button type="button" class="btn btn-primary" wire:click="savePurchasing"><i class="fa-solid fa-floppy-disk me-1"></i>Save purchasing information</button>
                    </div>
                @endif
            </div>
        </div>
    @endif
</x-engineering.page>
