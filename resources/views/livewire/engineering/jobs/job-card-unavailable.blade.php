<x-engineering.page :title="'Engineering job card · '.$job->number" subtitle="No job card">
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="fa-solid fa-id-card fa-2x text-muted opacity-50 mb-3"></i>
            <p class="mb-3">{{ $unavailable }}</p>
            <a href="{{ route('engineering.jobs.summary', $job) }}" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i>Back to the requisition</a>
        </div>
    </div>
</x-engineering.page>
