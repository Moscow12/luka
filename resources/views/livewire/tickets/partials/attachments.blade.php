<div class="d-flex flex-wrap gap-2 mt-2">
    @foreach($attachments as $attachment)
        <a href="{{ route('tickets.attachment', $attachment) }}" class="badge bg-light text-dark border text-decoration-none py-2" target="_blank">
            <i class="fa-solid {{ str_starts_with((string) $attachment->mime_type, 'image/') ? 'fa-image' : (str_contains((string) $attachment->mime_type, 'pdf') ? 'fa-file-pdf' : 'fa-file') }} me-1"></i>
            {{ Str::limit($attachment->original_name, 40) }}
            <span class="text-muted fw-normal">({{ $attachment->humanSize() }})</span>
        </a>
    @endforeach
</div>
