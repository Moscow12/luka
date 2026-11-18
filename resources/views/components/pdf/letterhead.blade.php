@props(['workstation' => null])

{{--
    Workstation letterhead for dompdf documents: the uploaded banner if there is one,
    otherwise logo + workstation details, otherwise the application name.
    Styles live in <x-pdf.styles>.
--}}
@php
    $letterHeadImg = $workstation ? \App\Support\Pdf\PdfReport::embedImage($workstation->letter_head) : null;
    $logoImg = $workstation ? \App\Support\Pdf\PdfReport::embedImage($workstation->logo) : null;
@endphp

<div class="letterhead">
    @if($letterHeadImg)
        <img src="{{ $letterHeadImg }}" alt="Letter Head" class="letterhead-banner">
    @elseif($workstation)
        <table class="letterhead-table">
            <tr>
                @if($logoImg)
                    <td class="letterhead-logo-cell">
                        <img src="{{ $logoImg }}" alt="Logo" class="letterhead-logo">
                    </td>
                @endif
                <td class="letterhead-info">
                    <div class="org-name">{{ $workstation->workstation_name }}</div>
                    <div class="org-detail">
                        @if($workstation->physical_address){{ $workstation->physical_address }}@endif
                        @if($workstation->postal_code), P.O. Box {{ $workstation->postal_code }}@endif
                        @if($workstation->location), {{ $workstation->location }}@endif
                        <br>
                        @if($workstation->phone_number)Tel: {{ $workstation->phone_number }}@endif
                        @if($workstation->email_address) &nbsp;|&nbsp; Email: {{ $workstation->email_address }}@endif
                        @if($workstation->tin_number)<br>TIN: {{ $workstation->tin_number }}@endif
                    </div>
                </td>
            </tr>
        </table>
    @else
        <div style="text-align: center;">
            <div class="org-name">{{ config('app.name', 'STAFF MANAGEMENT SYSTEM') }}</div>
            <div class="org-detail">HRP SYSTEM</div>
        </div>
    @endif
</div>
