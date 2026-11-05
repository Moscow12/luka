<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeePlanImplementation;
use App\Services\Performance\EvaluationWorkflow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Streams an implementation entry's evidence file to its owner and to those who may verify it.
 */
class PerformanceEvidenceController extends Controller
{
    public function __invoke(Request $request, EmployeePlanImplementation $implementation)
    {
        $user = $request->user();
        $isOwner = $implementation->employee_id === Employee::where('user_id', $user->id)->value('id');

        abort_unless($isOwner || EvaluationWorkflow::canVerify($user, $implementation), 403);

        $disk = Storage::disk('local');

        abort_unless($implementation->evidence_path && $disk->exists($implementation->evidence_path), 404);

        $contents = $disk->get($implementation->evidence_path);
        $mime = $disk->mimeType($implementation->evidence_path) ?: 'application/octet-stream';
        $inline = str_starts_with($mime, 'image/') || $mime === 'application/pdf';
        $name = str_replace(["\r", "\n"], '', $implementation->evidence_name ?: 'evidence');

        return response($contents, 200, [
            'Content-Type' => $mime,
            'Content-Length' => strlen($contents),
            'Content-Disposition' => ($inline ? 'inline' : 'attachment').'; filename="'.addcslashes($name, '"\\').'"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }
}
