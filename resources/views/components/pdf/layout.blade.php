@props([
    'title',
    'subtitle' => null,
    'workstation' => null,
    'meta' => [],            // ['Financial Year' => '2026/2027', ...] shown under the title; empty values skipped
    'signatures' => [],      // ['Prepared by', 'Approved by'] signature lines at the end
    'note' => null,          // small print under the content
    'margin' => '15mm 12mm 18mm 12mm',
])

{{--
    Reusable dompdf printout: letterhead, title, filter line, your content, signature lines
    and (added by PdfReport) a "Page X of Y" footer on every page. Render with App\Support\Pdf\PdfReport.

    <x-pdf.layout title="Sources of Fund" :workstation="$workstation" :meta="[...]" :signatures="['Prepared by']">
        <table class="data-table">...</table>
    </x-pdf.layout>

    Available classes: section, section-title, summary-cards/summary-card, data-table, num, center,
    badge badge-*, text-*, small. Add report-specific CSS with a <style> tag inside the slot.
--}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $title }}</title>
    <style>@page { margin: {{ $margin }}; }</style>
    <x-pdf.styles />
</head>
<body>
    <x-pdf.letterhead :workstation="$workstation" />

    <div class="doc-header">
        <h2>{{ $title }}</h2>
        @if($subtitle)
            <div class="subtitle">{{ $subtitle }}</div>
        @endif
    </div>

    @php $meta = array_filter($meta, fn ($value) => filled($value)); @endphp
    @if($meta)
        <table class="doc-meta">
            <tr>
                @foreach($meta as $label => $value)
                    <td><span class="label">{{ $label }}:</span> {{ $value }}</td>
                @endforeach
            </tr>
        </table>
    @endif

    {{ $slot }}

    @if($signatures)
        <table class="signatures">
            <tr>
                @foreach($signatures as $signature)
                    <td style="width: {{ round(100 / count($signatures), 2) }}%;">
                        <div style="height: 30px;"></div>
                        <div class="signature-line">{{ $signature }}<br>Name, signature &amp; date</div>
                    </td>
                @endforeach
            </tr>
        </table>
    @endif

    @if($note)
        <div class="doc-note">{{ $note }}</div>
    @endif
</body>
</html>
