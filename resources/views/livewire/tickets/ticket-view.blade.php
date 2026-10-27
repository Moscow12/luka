@php
    use App\Enums\TicketStatus;

    $statusButtons = [
        'accepted' => ['Accept', 'btn-primary', 'fa-handshake'],
        'in_progress' => ['Start work', 'btn-warning', 'fa-play'],
        'pending_response' => ['Pending response', 'btn-outline-secondary', 'fa-hourglass-half'],
        'resolved' => ['Mark resolved', 'btn-success', 'fa-circle-check'],
        'cancelled' => ['Cancel ticket', 'btn-outline-danger', 'fa-ban'],
    ];
    $tracker = [TicketStatus::Open, TicketStatus::Assigned, TicketStatus::Accepted, TicketStatus::InProgress, TicketStatus::Resolved, TicketStatus::Closed];
    $trackerIndex = match ($ticket->status) {
        TicketStatus::PendingResponse, TicketStatus::Reopened => 3,
        TicketStatus::Cancelled => -1,
        default => array_search($ticket->status, $tracker, true),
    };
@endphp
<div>
    {{-- Header --}}
    <div class="row mb-3">
        <div class="col-12 d-md-flex justify-content-between align-items-start">
            <div>
                <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('tickets.mine') }}" class="small text-decoration-none"><i class="fa-solid fa-arrow-left me-1"></i>Back</a>
                <h4 class="mb-1 mt-1">
                    <span class="text-muted">{{ $ticket->ticket_number }}</span> · {{ $ticket->subject }}
                </h4>
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <span class="badge {{ $ticket->status->badgeClass() }}">{{ $ticket->status->label() }}</span>
                    <span class="badge {{ $ticket->priorityBadgeClass() }}">{{ ucfirst($ticket->priority) }}</span>
                    <span class="badge bg-light text-dark border"><i class="fa-solid fa-{{ $ticket->visibility === 'department' ? 'building' : 'lock' }} me-1"></i>{{ $ticket->visibility === 'department' ? 'Department visible' : 'Participants only' }}</span>
                    @if($ticket->reopen_count)
                        <span class="badge bg-danger-subtle text-danger">Reopened ×{{ $ticket->reopen_count }}</span>
                    @endif
                    @if($ticket->isOverdue())
                        <span class="badge bg-danger"><i class="fa-solid fa-triangle-exclamation me-1"></i>Overdue</span>
                    @endif
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2 mt-3 mt-md-0">
                @foreach($transitions as $t)
                    @if($t === TicketStatus::Closed)
                        <button class="btn btn-success" wire:click="openModal('close')"><i class="fa-solid fa-check-double me-1"></i>Verify &amp; close</button>
                    @elseif($t === TicketStatus::Reopened)
                        <button class="btn btn-outline-danger" wire:click="openModal('reopen')"><i class="fa-solid fa-rotate-left me-1"></i>Reopen</button>
                    @elseif(isset($statusButtons[$t->value]))
                        @php [$label, $class, $icon] = $statusButtons[$t->value]; @endphp
                        <button class="btn {{ $class }}" wire:click="quickTransition('{{ $t->value }}')" wire:loading.attr="disabled"><i class="fa-solid {{ $icon }} me-1"></i>{{ $label }}</button>
                    @endif
                @endforeach
                @if($canReassign)
                    <button class="btn btn-outline-primary" wire:click="openModal('assign')"><i class="fa-solid fa-user-pen me-1"></i>{{ $ticket->assignee ? 'Reassign' : 'Assign' }}</button>
                @endif
                @if($canManage && ! $ticket->status->isTerminal())
                    <button class="btn btn-outline-secondary" wire:click="openModal('details')" title="Edit priority / due date"><i class="fa-solid fa-pen"></i></button>
                @endif
            </div>
        </div>
    </div>

    @include('livewire.tickets.partials.flash')

    {{-- Status tracker --}}
    @if($trackerIndex !== -1)
        <div class="card mb-4">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between text-center small">
                    @foreach($tracker as $i => $step)
                        <div class="flex-fill">
                            <div class="mx-auto rounded-circle d-flex align-items-center justify-content-center mb-1 {{ $i <= $trackerIndex ? 'bg-primary text-white' : 'bg-light text-muted border' }}" style="width: 28px; height: 28px;">
                                @if($i < $trackerIndex)<i class="fa-solid fa-check"></i>@else{{ $i + 1 }}@endif
                            </div>
                            <span class="{{ $i === $trackerIndex ? 'fw-semibold' : 'text-muted' }}">
                                {{ $i === 3 && in_array($ticket->status, [TicketStatus::PendingResponse, TicketStatus::Reopened], true) ? $ticket->status->label() : $step->label() }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <div class="row g-4">
        {{-- Main column --}}
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between">
                    <h6 class="mb-0">Description</h6>
                    <small class="text-muted">by {{ $ticket->creator?->first_name }} {{ $ticket->creator?->surname }} · {{ $ticket->created_at->format('d M Y H:i') }}</small>
                </div>
                <div class="card-body">
                    <div class="ticket-rich-text">{!! $descriptionHtml !!}</div>
                    @if($ticket->attachments->isNotEmpty())
                        <hr>
                        @include('livewire.tickets.partials.attachments', ['attachments' => $ticket->attachments])
                    @endif
                </div>
            </div>

            {{-- Conversation --}}
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="fa-regular fa-comments me-2"></i>Conversation</h6>
                    <small class="text-muted">{{ $messages->count() }} message(s)</small>
                </div>
                <div class="card-body" wire:poll.10s="refreshThread" style="max-height: 560px; overflow-y: auto;"
                     x-data x-init="$el.scrollTop = $el.scrollHeight">
                    @forelse($messages as $message)
                        @php
                            $mine = $message->user_id === auth()->id();
                            $isNew = ! $mine && $readMarker && $message->created_at->gt(\Illuminate\Support\Carbon::parse($readMarker));
                        @endphp
                        <div class="d-flex mb-3 {{ $mine ? 'flex-row-reverse' : '' }}" wire:key="msg-{{ $message->id }}">
                            <div class="flex-shrink-0 {{ $mine ? 'ms-2' : 'me-2' }}">
                                @if($message->user?->profile_picture_url)
                                    <img src="{{ $message->user->profile_picture_url }}" class="rounded-circle" width="36" height="36" alt="">
                                @else
                                    <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center fw-semibold" style="width:36px;height:36px;">
                                        {{ strtoupper(substr($message->user?->first_name ?? '?', 0, 1).substr($message->user?->surname ?? '', 0, 1)) }}
                                    </div>
                                @endif
                            </div>
                            <div style="max-width: 80%;">
                                <div class="small text-muted mb-1 {{ $mine ? 'text-end' : '' }}">
                                    <strong>{{ $message->user?->first_name }} {{ $message->user?->surname }}</strong>
                                    · {{ $message->created_at->format('d M H:i') }}
                                    @if($isNew)<span class="badge bg-primary ms-1">new</span>@endif
                                </div>
                                <div class="p-2 px-3 rounded-3 {{ $message->isPrivate() ? 'bg-warning-subtle border border-warning' : ($mine ? 'bg-primary-subtle' : 'bg-body-tertiary') }}">
                                    @if($message->isPrivate())
                                        <div class="small text-warning-emphasis mb-1"><i class="fa-solid fa-lock me-1"></i>Private note — hidden from the requester</div>
                                    @endif
                                    <div class="ticket-rich-text">{!! \App\Support\HtmlSanitizer::clean($message->body) !!}</div>
                                    @if($message->attachments->isNotEmpty())
                                        @include('livewire.tickets.partials.attachments', ['attachments' => $message->attachments])
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-center text-muted py-4 mb-0">No messages yet. Start the conversation below.</p>
                    @endforelse
                </div>

                @if(! $ticket->status->isTerminal())
                    <div class="card-footer">
                        <form wire:submit="sendMessage">
                            <x-rich-editor model="newMessage" placeholder="Write a message..." height="90px" />
                            @error('newMessage') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            @error('messageAttachments.*') <div class="text-danger small mt-1">{{ $message }}</div> @enderror

                            @if(count($messageAttachments))
                                <div class="mt-2 d-flex flex-wrap gap-2">
                                    @foreach($messageAttachments as $i => $file)
                                        <span class="badge bg-light text-dark border">
                                            <i class="fa-solid fa-paperclip me-1"></i>{{ Str::limit($file->getClientOriginalName(), 30) }}
                                            <a href="#" class="text-danger ms-1" wire:click.prevent="removeMessageAttachment({{ $i }})"><i class="fa-solid fa-xmark"></i></a>
                                        </span>
                                    @endforeach
                                </div>
                            @endif

                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <div class="d-flex align-items-center gap-3">
                                    <label class="btn btn-sm btn-outline-secondary mb-0" title="Attach files">
                                        <i class="fa-solid fa-paperclip"></i>
                                        <input type="file" class="d-none" wire:model="messageAttachments" multiple>
                                    </label>
                                    <span wire:loading wire:target="messageAttachments" class="small text-muted"><span class="spinner-border spinner-border-sm"></span></span>
                                    @if($canSeePrivate)
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" id="privateNote" wire:model.live="isPrivate">
                                            <label class="form-check-label small" for="privateNote"><i class="fa-solid fa-lock me-1"></i>Private note</label>
                                        </div>
                                    @endif
                                </div>
                                <button type="submit" class="btn {{ $isPrivate ? 'btn-warning' : 'btn-primary' }}" wire:loading.attr="disabled" wire:target="sendMessage,messageAttachments">
                                    <i class="fa-solid fa-paper-plane me-1"></i>{{ $isPrivate ? 'Add note' : 'Send' }}
                                </button>
                            </div>
                        </form>
                    </div>
                @endif
            </div>
        </div>

        {{-- Side column --}}
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header"><h6 class="mb-0">Details</h6></div>
                <div class="card-body">
                    <dl class="row mb-0 small">
                        <dt class="col-5 text-muted fw-normal">Category</dt><dd class="col-7">{{ $ticket->category?->name }}</dd>
                        <dt class="col-5 text-muted fw-normal">Department</dt><dd class="col-7">{{ $ticket->department?->name }}</dd>
                        <dt class="col-5 text-muted fw-normal">Assignee</dt>
                        <dd class="col-7">{{ $ticket->assignee ? $ticket->assignee->getFullName() : '— department queue —' }}</dd>
                        <dt class="col-5 text-muted fw-normal">Due date</dt>
                        <dd class="col-7 {{ $ticket->isOverdue() ? 'text-danger fw-semibold' : '' }}">{{ $ticket->due_date->format('d M Y') }}</dd>
                        <dt class="col-5 text-muted fw-normal">Created</dt><dd class="col-7">{{ $ticket->created_at->format('d M Y H:i') }}</dd>
                        @if($ticket->resolved_at)
                            <dt class="col-5 text-muted fw-normal">Resolved</dt><dd class="col-7">{{ $ticket->resolved_at->format('d M Y H:i') }}</dd>
                        @endif
                        @if($ticket->closed_at)
                            <dt class="col-5 text-muted fw-normal">Closed</dt>
                            <dd class="col-7">{{ $ticket->closed_at->format('d M Y H:i') }}<br><small class="text-muted">by {{ $ticket->closedBy?->first_name }} {{ $ticket->closedBy?->surname }}</small></dd>
                            <dt class="col-5 text-muted fw-normal">Rating</dt>
                            <dd class="col-7 text-warning">
                                @for($i = 1; $i <= 5; $i++)<i class="fa-{{ $i <= $ticket->quality_rating ? 'solid' : 'regular' }} fa-star"></i>@endfor
                            </dd>
                            @if($ticket->closing_remarks)
                                <dt class="col-5 text-muted fw-normal">Remarks</dt><dd class="col-7">{{ $ticket->closing_remarks }}</dd>
                            @endif
                        @endif
                    </dl>
                    @if($ticket->duty)
                        <div class="alert alert-info small mt-3 mb-0">
                            <i class="fa-solid fa-clipboard-list me-1"></i>Counts towards duty
                            <strong>{{ $ticket->duty->duty_name }}</strong>
                            @if($ticket->duty->score !== null)<br>Current duty score: {{ number_format($ticket->duty->score, 0) }}%@endif
                        </div>
                    @endif
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Participants</h6>
                    @if($canAddParticipants && ! $ticket->status->isTerminal())
                        <button class="btn btn-sm btn-link p-0" wire:click="openModal('participant')"><i class="fa-solid fa-user-plus"></i></button>
                    @endif
                </div>
                <ul class="list-group list-group-flush small">
                    @foreach($ticket->participants->sortBy(fn ($p) => array_search($p->role, ['assignee', 'creator', 'collaborator', 'watcher'])) as $p)
                        @php $seen = $lastMessageAt && $p->last_read_at && $p->last_read_at->gte($lastMessageAt); @endphp
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>{{ $p->user?->first_name }} {{ $p->user?->surname }}</span>
                            <span>
                                <span class="badge bg-light text-dark border">{{ ucfirst($p->role) }}</span>
                                @if($lastMessageAt)
                                    <i class="fa-solid {{ $seen ? 'fa-check-double text-primary' : 'fa-check text-muted' }} ms-1" title="{{ $seen ? 'Read latest message' : 'Has unread messages' }}"></i>
                                @endif
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="card">
                <div class="card-header"><h6 class="mb-0">History</h6></div>
                <div class="card-body small" style="max-height: 420px; overflow-y: auto;">
                    @foreach($history as $entry)
                        <div class="d-flex mb-3">
                            <i class="fa-solid {{ $entry['icon'] }} text-muted mt-1 me-2"></i>
                            <div>
                                <div>{{ $entry['text'] }}</div>
                                <div class="text-muted">{{ $entry['by'] }} · {{ $entry['at']->format('d M Y H:i') }}</div>
                                @if($entry['remarks'])<div class="fst-italic">“{{ $entry['remarks'] }}”</div>@endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Modals --}}
    @if($modal)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    @if($modal === 'status')
                        @php $target = TicketStatus::from($targetStatus); @endphp
                        <div class="modal-header"><h5 class="modal-title">{{ $statusButtons[$target->value][0] ?? $target->label() }}</h5><button type="button" class="btn-close" wire:click="closeModal"></button></div>
                        <form wire:submit="submitStatus">
                            <div class="modal-body">
                                <label class="form-label">
                                    {{ match ($target) { TicketStatus::Cancelled => 'Reason for cancelling', TicketStatus::PendingResponse => 'What are you waiting for?', default => 'Resolution summary' } }}
                                    @if($target === TicketStatus::Cancelled)<span class="text-danger">*</span>@endif
                                </label>
                                <textarea class="form-control @error('remarks') is-invalid @enderror" rows="3" wire:model="remarks"></textarea>
                                @error('remarks') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <div class="form-text">This is posted to the conversation.</div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" wire:click="closeModal">Back</button>
                                <button type="submit" class="btn btn-primary">Confirm</button>
                            </div>
                        </form>
                    @elseif($modal === 'close' || $modal === 'reopen')
                        <div class="modal-header {{ $modal === 'close' ? 'bg-success' : 'bg-danger' }} text-white">
                            <h5 class="modal-title">{{ $modal === 'close' ? 'Verify and close' : 'Reopen ticket' }}</h5>
                            <button type="button" class="btn-close btn-close-white" wire:click="closeModal"></button>
                        </div>
                        <form wire:submit="{{ $modal === 'close' ? 'submitClose' : 'submitReopen' }}">
                            <div class="modal-body">
                                @include('livewire.tickets.partials.close-reopen-fields', ['type' => $modal])
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" wire:click="closeModal">Back</button>
                                <button type="submit" class="btn {{ $modal === 'close' ? 'btn-success' : 'btn-danger' }}">{{ $modal === 'close' ? 'Close ticket' : 'Reopen' }}</button>
                            </div>
                        </form>
                    @elseif($modal === 'assign')
                        <div class="modal-header"><h5 class="modal-title">Assign / delegate</h5><button type="button" class="btn-close" wire:click="closeModal"></button></div>
                        <form wire:submit="submitAssign">
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Department</label>
                                    <select class="form-select" wire:model.live="assignDepartment">
                                        @foreach($departments as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Officer</label>
                                    <select class="form-select" wire:model.live="assignEmployee">
                                        <option value="">Department queue</option>
                                        @foreach($assignEmployees as $e)<option value="{{ $e->id }}">{{ $e->getFullName() }}</option>@endforeach
                                    </select>
                                </div>
                                @if($assignEmployee)
                                    <div class="mb-3">
                                        <label class="form-label">Link to duty</label>
                                        <select class="form-select" wire:model="assignDuty">
                                            <option value="">Not linked</option>
                                            @foreach($assignDuties as $duty)<option value="{{ $duty->id }}">{{ $duty->duty_name }}</option>@endforeach
                                        </select>
                                    </div>
                                @endif
                                <label class="form-label">Reason</label>
                                <textarea class="form-control" rows="2" wire:model="remarks" placeholder="Recorded in the assignment history"></textarea>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" wire:click="closeModal">Back</button>
                                <button type="submit" class="btn btn-primary">Save assignment</button>
                            </div>
                        </form>
                    @elseif($modal === 'details')
                        <div class="modal-header"><h5 class="modal-title">Priority &amp; due date</h5><button type="button" class="btn-close" wire:click="closeModal"></button></div>
                        <form wire:submit="submitDetails">
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Priority</label>
                                    <select class="form-select" wire:model="editPriority">
                                        <option value="low">Low</option><option value="medium">Medium</option><option value="high">High</option><option value="critical">Critical</option>
                                    </select>
                                </div>
                                <label class="form-label">Due date</label>
                                <input type="date" class="form-control @error('editDueDate') is-invalid @enderror" wire:model="editDueDate" min="{{ now()->toDateString() }}">
                                @error('editDueDate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <div class="form-text">Changes are recorded in the audit log.</div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" wire:click="closeModal">Back</button>
                                <button type="submit" class="btn btn-primary">Save</button>
                            </div>
                        </form>
                    @elseif($modal === 'participant')
                        <div class="modal-header"><h5 class="modal-title">Add participant</h5><button type="button" class="btn-close" wire:click="closeModal"></button></div>
                        <form wire:submit="submitParticipant">
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Staff member</label>
                                    <select class="form-select @error('participantUserId') is-invalid @enderror" wire:model="participantUserId">
                                        <option value="">Select</option>
                                        @foreach($participantCandidates as $c)<option value="{{ $c->user_id }}">{{ $c->first_name }} {{ $c->last_name }}</option>@endforeach
                                    </select>
                                    @error('participantUserId') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <label class="form-label">Role</label>
                                <select class="form-select" wire:model="participantRole">
                                    <option value="collaborator">Collaborator (can see private notes)</option>
                                    <option value="watcher">Watcher</option>
                                </select>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" wire:click="closeModal">Back</button>
                                <button type="submit" class="btn btn-primary">Add</button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
