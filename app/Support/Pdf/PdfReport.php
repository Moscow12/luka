<?php

namespace App\Support\Pdf;

use App\Models\Employee;
use App\Models\User;
use App\Models\workstations;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Renders a Blade view built on <x-pdf.layout> into a real PDF (dompdf), so reports
 * are generated documents rather than screenshots of the page.
 *
 *   PdfReport::make('exports.chop.fund-sources-pdf', $data)
 *       ->landscape()
 *       ->download('sources-of-fund.pdf');   // or ->stream(...) to open in the browser for printing
 *
 * The view receives `$workstation` (for the letterhead) and `$generatedBy` on top of $data.
 */
class PdfReport
{
    protected string $paper = 'a4';

    protected string $orientation = 'portrait';

    protected ?workstations $workstation = null;

    protected ?string $footer = null;

    public function __construct(protected string $view, protected array $data = []) {}

    public static function make(string $view, array $data = []): self
    {
        return new self($view, $data);
    }

    public function landscape(): self
    {
        $this->orientation = 'landscape';

        return $this;
    }

    public function portrait(): self
    {
        $this->orientation = 'portrait';

        return $this;
    }

    public function paper(string $paper): self
    {
        $this->paper = $paper;

        return $this;
    }

    /** Letterhead workstation; defaults to the signed-in user's workstation. */
    public function workstation(?workstations $workstation): self
    {
        $this->workstation = $workstation;

        return $this;
    }

    /** Text at the bottom-left of every page, before "Generated … by …" (usually the report title). */
    public function footer(?string $text): self
    {
        $this->footer = $text;

        return $this;
    }

    /** The raw PDF bytes. */
    public function output(): string
    {
        return $this->build()->output();
    }

    /** Download as a file (works from controllers and Livewire actions). */
    public function download(string $filename): StreamedResponse
    {
        $pdf = $this->build();

        return response()->streamDownload(fn () => print ($pdf->output()), self::safeFilename($filename), [
            'Content-Type' => 'application/pdf',
        ]);
    }

    /** Show inline in the browser's PDF viewer, ready to print. Controllers only. */
    public function stream(string $filename): Response
    {
        return response($this->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.self::safeFilename($filename).'"',
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    protected function build()
    {
        $user = auth()->user();
        $generatedBy = $user ? trim($user->first_name.' '.$user->surname) : null;

        $pdf = Pdf::loadView($this->view, array_merge([
            'workstation' => $this->workstation ?? self::defaultWorkstation($user),
            'generatedBy' => $generatedBy,
        ], $this->data))->setPaper($this->paper, $this->orientation);

        $pdf->render();
        $this->stampFooter($pdf->getDomPDF(), $generatedBy);

        return $pdf;
    }

    /**
     * "Title · Generated … by …" and "Page X of Y" on every page. Done on the canvas after
     * rendering because dompdf clips fixed elements in the page margin and has no CSS page total.
     */
    protected function stampFooter($dompdf, ?string $generatedBy): void
    {
        $canvas = $dompdf->getCanvas();
        $metrics = $dompdf->getFontMetrics();
        $font = $metrics->getFont('DejaVu Sans', 'normal');
        $size = 7;
        $colour = [0.42, 0.46, 0.49];
        $side = 34; // ≈12mm, the layout's side margin
        $y = $canvas->get_height() - 30;

        $left = collect([
            $this->footer,
            'Generated '.now()->format('d M Y H:i').($generatedBy ? ' by '.$generatedBy : ''),
        ])->filter()->implode('  ·  ');

        $canvas->page_text($side, $y, $left, $font, $size, $colour);

        $pageLabel = 'Page {PAGE_NUM} of {PAGE_COUNT}';
        $width = $metrics->getTextWidth('Page 000 of 000', $font, $size);
        $canvas->page_text($canvas->get_width() - $side - $width, $y, $pageLabel, $font, $size, $colour);
    }

    public static function defaultWorkstation(?User $user = null): ?workstations
    {
        $workstationId = $user ? Employee::where('user_id', $user->id)->value('workstation_id') : null;

        return ($workstationId ? workstations::find($workstationId) : null)
            ?? workstations::where('status', 'active')->first()
            ?? workstations::first();
    }

    /** A storage/app/public file as a data URI, so dompdf embeds it without fetching URLs. */
    public static function embedImage(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        $fullPath = storage_path('app/public/'.ltrim($path, '/'));

        if (! is_file($fullPath)) {
            return null;
        }

        $mime = match (strtolower(pathinfo($fullPath, PATHINFO_EXTENSION))) {
            'png' => 'image/png',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'webp' => 'image/webp',
            default => 'image/jpeg',
        };

        return 'data:'.$mime.';base64,'.base64_encode(file_get_contents($fullPath));
    }

    public static function safeFilename(string $filename): string
    {
        $filename = preg_replace('/[^A-Za-z0-9._-]+/', '-', $filename);

        return str_ends_with(strtolower($filename), '.pdf') ? $filename : $filename.'.pdf';
    }
}
