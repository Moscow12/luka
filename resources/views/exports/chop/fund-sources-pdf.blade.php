<x-pdf.layout
    title="Sources of Fund"
    subtitle="Comprehensive Hospital Operational Plan (CHOP)"
    :workstation="$workstation"
    :meta="[
        'Financial Year' => $financialYearName,
        'Search' => $search,
        'Records' => number_format($sources->count()),
    ]"
    :signatures="['Prepared by', 'Checked by', 'Approved by']"
    note="All amounts are in Tanzanian Shillings (TZS). This is a system-generated document.">

    <div class="summary-cards">
        <div class="summary-card" style="width: 40%;">
            <div class="label">Total Estimated Cost</div>
            <div class="value">{{ number_format($totalEstimatedCost, 2) }}</div>
        </div>
        <div class="summary-card" style="width: 30%;">
            <div class="label">Sources</div>
            <div class="value">{{ number_format($sources->count()) }}</div>
        </div>
        <div class="summary-card" style="width: 30%;">
            <div class="label">Active</div>
            <div class="value text-success">{{ number_format($sources->where('is_active', true)->count()) }}</div>
        </div>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th class="center" style="width: 4%;">#</th>
                <th style="width: 24%;">Source of Fund</th>
                <th style="width: 13%;">Financial Year</th>
                <th class="num" style="width: 16%;">Estimated Cost (TZS)</th>
                <th class="num" style="width: 8%;">Share</th>
                <th class="center" style="width: 9%;">Status</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sources as $source)
                <tr>
                    <td class="center">{{ $loop->iteration }}</td>
                    <td>
                        @if($source->colorcode)
                            <span style="display: inline-block; width: 8px; height: 8px; background-color: {{ $source->colorcode }}; border: 1px solid #adb5bd;"></span>
                        @endif
                        <strong>{{ $source->name }}</strong>
                    </td>
                    <td>{{ $source->financialYear?->name ?? '-' }}</td>
                    <td class="num">{{ $source->estimated_cost ? number_format($source->estimated_cost_value, 2) : '-' }}</td>
                    <td class="num">{{ $totalEstimatedCost > 0 ? number_format($source->estimated_cost_value / $totalEstimatedCost * 100, 1).'%' : '-' }}</td>
                    <td class="center">
                        <span class="badge {{ $source->is_active ? 'badge-success' : 'badge-danger' }}">{{ $source->is_active ? 'Active' : 'Inactive' }}</span>
                    </td>
                    <td>{{ $source->description ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="center text-muted" style="padding: 20px;">No sources of fund match the selected filters.</td>
                </tr>
            @endforelse
        </tbody>
        @if($sources->isNotEmpty())
            <tfoot>
                <tr>
                    <td colspan="3">TOTAL</td>
                    <td class="num">{{ number_format($totalEstimatedCost, 2) }}</td>
                    <td class="num">100%</td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        @endif
    </table>
</x-pdf.layout>
