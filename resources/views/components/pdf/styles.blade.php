{{-- Shared dompdf styles for printouts (same look as the salary slip). --}}
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        font-family: 'DejaVu Sans', sans-serif;
        font-size: 10px;
        line-height: 1.4;
        color: #333;
    }

    /* Letterhead */
    .letterhead { margin-bottom: 12px; padding-bottom: 10px; border-bottom: 3px solid #0d6efd; }
    .letterhead-banner { width: 100%; max-height: 120px; display: block; }
    .letterhead-table { width: 100%; border-collapse: collapse; }
    .letterhead-logo-cell { width: 90px; vertical-align: middle; padding-right: 12px; }
    .letterhead-logo { max-width: 80px; max-height: 80px; }
    .letterhead-info { vertical-align: middle; }
    .org-name { font-size: 18px; font-weight: bold; color: #0d6efd; margin-bottom: 3px; }
    .org-detail { font-size: 10px; color: #495057; line-height: 1.5; }

    /* Title block */
    .doc-header { text-align: center; margin-bottom: 12px; }
    .doc-header h2 { font-size: 16px; font-weight: bold; letter-spacing: 1px; text-transform: uppercase; }
    .doc-header .subtitle { font-size: 11px; color: #6c757d; margin-top: 3px; }

    /* Filter / info line */
    .doc-meta { width: 100%; border-collapse: collapse; margin-bottom: 12px; background-color: #f8f9fa; }
    .doc-meta td { padding: 5px 10px; font-size: 9px; }
    .doc-meta .label { color: #6c757d; font-weight: bold; }

    .section { margin-bottom: 12px; }
    .section-title {
        background-color: #f8f9fa; padding: 6px 10px; font-weight: bold; font-size: 11px;
        border-left: 4px solid #0d6efd; margin-bottom: 8px;
    }

    /* Summary cards */
    .summary-cards { display: table; width: 100%; margin-bottom: 12px; border: 1px solid #dee2e6; }
    .summary-card { display: table-cell; text-align: center; padding: 8px; border-right: 1px solid #dee2e6; }
    .summary-card:last-child { border-right: none; }
    .summary-card .value { font-size: 14px; font-weight: bold; color: #0d6efd; }
    .summary-card .label { font-size: 9px; color: #6c757d; text-transform: uppercase; }

    /* Data table */
    .data-table { width: 100%; border-collapse: collapse; }
    .data-table th, .data-table td { padding: 5px 7px; border: 1px solid #dee2e6; font-size: 9px; vertical-align: top; }
    .data-table thead th { background-color: #0d6efd; color: #fff; font-weight: bold; text-align: left; }
    .data-table tbody tr:nth-child(even) td { background-color: #f8f9fa; }
    .data-table tfoot td { background-color: #cfe2ff; font-weight: bold; color: #084298; }
    .data-table thead { display: table-header-group; } /* repeat header on every page */
    .data-table tr { page-break-inside: avoid; }
    .num { text-align: right !important; white-space: nowrap; }
    .center { text-align: center !important; }

    .badge { display: inline-block; padding: 2px 6px; border-radius: 3px; font-size: 8px; font-weight: bold; }
    .badge-success { background-color: #d1e7dd; color: #0f5132; }
    .badge-danger { background-color: #f8d7da; color: #842029; }
    .badge-warning { background-color: #fff3cd; color: #664d03; }
    .badge-info { background-color: #cff4fc; color: #055160; }
    .badge-secondary { background-color: #e9ecef; color: #495057; }

    .text-muted { color: #6c757d; }
    .text-success { color: #198754; }
    .text-danger { color: #dc3545; }
    .text-primary { color: #0d6efd; }
    .small { font-size: 8px; }

    /* Signatures and notes */
    .signatures { width: 100%; margin-top: 35px; border-collapse: collapse; page-break-inside: avoid; }
    .signatures td { padding: 0 10px; vertical-align: bottom; text-align: center; }
    .signature-line { border-top: 1px solid #333; padding-top: 4px; font-size: 9px; color: #6c757d; }
    .doc-note { margin-top: 15px; font-size: 8px; color: #6c757d; }

</style>
