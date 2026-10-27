{{-- Ticket scorecard for one employee (see TicketMetricsService::scorecardFor) --}}
<div class="border rounded p-3 mb-3">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <span class="fw-semibold"><i class="fa-solid fa-ticket me-1"></i>Ticket scorecard</span>
        <div style="min-width: 160px;">@include('livewire.tickets.partials.score-bar', ['score' => $card['score']])</div>
    </div>
    <div class="row text-center g-2 small">
        <div class="col-4 col-md-2"><div class="text-muted">Assigned</div><div class="fw-semibold">{{ $card['assigned'] }}</div></div>
        <div class="col-4 col-md-2"><div class="text-muted">Active</div><div class="fw-semibold">{{ $card['active'] }}</div></div>
        <div class="col-4 col-md-2"><div class="text-muted">Overdue</div><div class="fw-semibold {{ $card['overdue'] ? 'text-danger' : '' }}">{{ $card['overdue'] }}</div></div>
        <div class="col-4 col-md-2"><div class="text-muted">On time</div><div class="fw-semibold">{{ $card['on_time_rate'] === null ? '—' : $card['on_time_rate'].'%' }}</div></div>
        <div class="col-4 col-md-2"><div class="text-muted">Avg rating</div><div class="fw-semibold">{{ $card['avg_rating'] === null ? '—' : $card['avg_rating'].' / 5' }}</div></div>
        <div class="col-4 col-md-2"><div class="text-muted">Reopen rate</div><div class="fw-semibold">{{ $card['reopen_rate'] === null ? '—' : $card['reopen_rate'].'%' }}</div></div>
    </div>
    <div class="form-text mt-2 mb-0">Score = 40% on-time closure + 30% quality rating + 30% first-time-right.</div>
</div>
