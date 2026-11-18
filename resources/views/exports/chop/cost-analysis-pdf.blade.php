@use('App\Reports\Chop\CostAnalysisReport')

<x-pdf.layout
    :title="$sheetTitle"
    subtitle="Comprehensive Hospital Operational Plan (CHOP)"
    :workstation="$workstation"
    :meta="$filters"
    :signatures="['Prepared by', 'Checked by', 'Approved by']"
    note="Estimates = unit cost × number of units. Total Amount per intervention is shaded in its source of fund's colour. Amounts in TZS.">

    <style>
        .ca th, .ca td { font-size: 7.5px; padding: 3px 4px; }
        .ca thead th { background-color: #e9ecef; color: #212529; text-align: center; vertical-align: middle; }
        .ca tbody tr:nth-child(even) td { background-color: transparent; }
        .ca .objective { font-weight: bold; }
        .ca .subtotal td { font-weight: bold; }
        .ca .grand td { background-color: #cfe2ff; font-weight: bold; color: #084298; }
    </style>

    <table class="data-table ca">
        <thead>
            <tr>
                <th colspan="2" rowspan="2" style="width: 13%;">OBJECTIVE</th>
                <th rowspan="2" style="width: 13%;">TARGETS</th>
                <th rowspan="2" style="width: 3%;">S/N</th>
                <th rowspan="2" style="width: 18%;">Planned interventions for priority areas</th>
                <th rowspan="2" style="width: 6%;">GFC Code</th>
                <th style="width: 11%;">Required inputs</th>
                <th rowspan="2" style="width: 5%;">Unit of description</th>
                <th rowspan="2" style="width: 7%;">Unit cost of input</th>
                <th style="width: 5%;">Annual Budget Estimates</th>
                <th rowspan="2" style="width: 7%;">Estimates</th>
                <th rowspan="2" style="width: 7%;">Total Amount</th>
                <th rowspan="2" style="width: 5%;">Source of funds</th>
            </tr>
            <tr>
                <th>GFC Code description</th>
                <th>No of Units</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sheet['objectives'] as $objective)
                @foreach($objective['targets'] as $target)
                    @foreach($target['interventions'] as $intervention)
                        @php
                            $firstOfObjective = $loop->parent->first && $loop->first;
                            $firstOfTarget = $loop->first;
                        @endphp
                        <tr>
                            <td class="center objective" style="width: 2%;">{{ $firstOfObjective ? $objective['number'] : '' }}</td>
                            <td class="objective">{{ $firstOfObjective ? $objective['name'] : '' }}</td>
                            <td class="objective">{{ $firstOfTarget ? $target['text'] : '' }}</td>
                            <td class="center">{{ $firstOfTarget ? $target['number'] : '' }}</td>
                            <td>{{ $intervention['number'] }}. {{ $intervention['name'] }}</td>
                            @for($c = 0; $c < 8; $c++)<td></td>@endfor
                        </tr>
                        @foreach($intervention['inputs'] as $input)
                            <tr>
                                @for($c = 0; $c < 5; $c++)<td></td>@endfor
                                <td>{{ $input['gfc_code'] }}</td>
                                <td>{{ $input['name'] }}</td>
                                <td>{{ $input['unit'] }}</td>
                                <td class="num">{{ number_format($input['unit_cost'], 2) }}</td>
                                <td class="num">{{ rtrim(rtrim(number_format($input['quantity'], 2), '0'), '.') }}</td>
                                <td class="num">{{ number_format($input['estimate'], 2) }}</td>
                                <td></td><td></td>
                            </tr>
                        @endforeach
                        @php $text = '#'.CostAnalysisReport::textOn($intervention['colour']); @endphp
                        <tr class="subtotal">
                            @for($c = 0; $c < 11; $c++)<td style="background-color: #{{ $intervention['colour'] }};"></td>@endfor
                            <td class="num" style="background-color: #{{ $intervention['colour'] }}; color: {{ $text }};">{{ number_format($intervention['total'], 2) }}</td>
                            <td class="center" style="background-color: #{{ $intervention['colour'] }}; color: {{ $text }};">{{ $intervention['source'] }}</td>
                        </tr>
                    @endforeach
                @endforeach
            @empty
                <tr>
                    <td colspan="13" class="center text-muted" style="padding: 20px;">No activities match the selected filters.</td>
                </tr>
            @endforelse
            @if($sheet['objectives'])
                <tr class="grand">
                    <td colspan="11">GRAND TOTAL</td>
                    <td class="num">{{ number_format($sheet['grand_total'], 2) }}</td>
                    <td></td>
                </tr>
            @endif
        </tbody>
    </table>

    @if($sheet['by_source'])
        <div class="section" style="margin-top: 14px; width: 50%;">
            <div class="section-title">Summary by Source of Fund</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Source of Fund</th>
                        <th class="num">Total Amount (TZS)</th>
                        <th class="num">Share</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sheet['by_source'] as $source => $data)
                        <tr>
                            <td>
                                <span style="display: inline-block; width: 8px; height: 8px; background-color: #{{ CostAnalysisReport::hexColour($data['colour']) }};"></span>
                                {{ $source }}
                            </td>
                            <td class="num">{{ number_format($data['total'], 2) }}</td>
                            <td class="num">{{ $sheet['grand_total'] > 0 ? number_format($data['total'] / $sheet['grand_total'] * 100, 1).'%' : '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td>TOTAL</td>
                        <td class="num">{{ number_format($sheet['grand_total'], 2) }}</td>
                        <td class="num">100%</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif
</x-pdf.layout>
