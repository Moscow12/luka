<?php

namespace App\Http\Controllers;

use App\Models\ChatAttachment;
use App\Models\Ticket;
use App\Services\Chat\ChatAccess;
use App\Services\Chat\ChatService;
use App\Services\Tickets\TicketAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Non-Livewire chat endpoints: decrypted attachment downloads and ticket hover cards.
 */
class ChatController extends Controller
{
    public function attachment(Request $request, ChatAttachment $attachment, ChatService $chat)
    {
        $message = $attachment->message;
        abort_unless($message && ! $message->trashed() && ChatAccess::isMember($request->user(), $message->conversation_id), 403);

        $contents = $chat->attachmentContents($attachment);
        $mime = $attachment->mime_type ?: 'application/octet-stream';
        $inline = $request->boolean('inline') || $attachment->isImage() || $mime === 'application/pdf';

        return response($contents, 200, [
            'Content-Type' => $mime,
            'Content-Length' => strlen($contents),
            'Content-Disposition' => ($inline ? 'inline' : 'attachment').'; filename="'.addcslashes($attachment->original_name, '"\\').'"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, max-age=86400',
        ]);
    }

    /** Hover-card data for HRP-YYYY-NNNNN references (PRD 4.3). */
    public function ticketCard(Request $request, string $number): JsonResponse
    {
        $ticket = Ticket::with('assignee:id,first_name,last_name')->where('ticket_number', $number)->first();
        abort_unless($ticket, 404);
        abort_unless(TicketAccess::canView($request->user(), $ticket), 403);

        return response()->json([
            'number' => $ticket->ticket_number,
            'subject' => $ticket->subject,
            'status' => $ticket->status->label(),
            'status_class' => $ticket->status->badgeClass(),
            'priority' => ucfirst($ticket->priority),
            'priority_class' => $ticket->priorityBadgeClass(),
            'assignee' => $ticket->assignee ? trim($ticket->assignee->first_name.' '.$ticket->assignee->last_name) : null,
            'due' => $ticket->due_date->format('d M Y'),
            'url' => route('tickets.view', $ticket),
        ]);
    }

    public function openTicket(string $number)
    {
        $ticket = Ticket::where('ticket_number', $number)->firstOrFail();

        return redirect()->route('tickets.view', $ticket);
    }
}
