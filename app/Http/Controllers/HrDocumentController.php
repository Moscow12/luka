<?php

namespace App\Http\Controllers;

use App\Services\HrDocuments\HrDocumentAccess;
use App\Services\HrDocuments\HrDocumentStore;
use App\Services\HrDocuments\HrDocumentType;
use Illuminate\Http\Request;

/**
 * Streams a sensitive HR file after checking the viewer may see the record it belongs to.
 * The file path always comes from the database record, never from the request.
 */
class HrDocumentController extends Controller
{
    public function __invoke(Request $request, HrDocumentStore $store, string $type, string $id)
    {
        $documentType = HrDocumentType::tryFrom($type) ?? abort(404);
        $record = $documentType->model()::findOrFail($id);

        abort_unless(HrDocumentAccess::canView($request->user(), $documentType, $record), 403);

        $file = $store->read($documentType, $record->{$documentType->column()}) ?? abort(404);

        activity('hr-documents')
            ->causedBy($request->user())
            ->performedOn($record)
            ->event('downloaded')
            ->withProperties(['type' => $documentType->value])
            ->log("Viewed {$documentType->label()} document");

        $inline = in_array($file['mime'], ['application/pdf', 'image/jpeg', 'image/png'], true);
        $filename = $documentType->value.'-'.substr($record->getKey(), 0, 8).'.'.$file['extension'];

        $headers = [
            'Content-Type' => $file['mime'],
            'Content-Length' => strlen($file['contents']),
            'Content-Disposition' => ($inline ? 'inline' : 'attachment').'; filename="'.$filename.'"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
            'Referrer-Policy' => 'no-referrer',
        ];

        if (str_starts_with($file['mime'], 'image/')) {
            // Images never need script; PDFs are left alone because sandboxing breaks browser PDF viewers
            $headers['Content-Security-Policy'] = "default-src 'none'; img-src 'self'; style-src 'unsafe-inline'; sandbox";
        }

        return response($file['contents'], 200, $headers);
    }
}
