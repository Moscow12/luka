<?php

namespace App\Http\Controllers;

use App\Models\TicketAttachment;
use App\Services\Tickets\TicketAccess;
use App\Services\Tickets\TicketService;
use Illuminate\Http\Request;

/**
 * Streams decrypted ticket attachments after checking the viewer may see them.
 */
class TicketAttachmentController extends Controller
{
    public function __invoke(Request $request, TicketAttachment $attachment, TicketService $service)
    {
        $user = $request->user();
        $ticket = $attachment->ticket;

        abort_unless($ticket && TicketAccess::canView($user, $ticket), 403);

        if ($attachment->message?->isPrivate()) {
            abort_unless(TicketAccess::canViewPrivateNotes($user, $ticket), 403);
        }

        $contents = $service->attachmentContents($attachment);
        $mime = $attachment->mime_type ?: 'application/octet-stream';
        $inline = str_starts_with($mime, 'image/') || $mime === 'application/pdf';

        return response($contents, 200, [
            'Content-Type' => $mime,
            'Content-Length' => strlen($contents),
            'Content-Disposition' => ($inline ? 'inline' : 'attachment').'; filename="'.addcslashes($attachment->original_name, '"\\').'"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }
}
