<?php

namespace App\Services\HrDocuments;

use App\Support\EncryptedFileStore;
use finfo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

/**
 * Sensitive HR files: validated against a strict per-type allow-list (client extension
 * AND detected content), stored AES-256 encrypted on the private disk, and read back only
 * after re-checking the decrypted content type. Also reads not-yet-migrated legacy files
 * from the public disk, under the same content checks, until `hr-documents:secure` runs.
 */
class HrDocumentStore
{
    public function __construct(protected EncryptedFileStore $files) {}

    /** Livewire/validator rules for an upload of this type. */
    public static function rules(HrDocumentType $type, bool $required = false, int $maxKilobytes = 10240): array
    {
        $extensions = implode(',', $type->extensions());

        return [
            $required ? 'required' : 'nullable',
            'file',
            'max:'.$maxKilobytes,
            'extensions:'.$extensions,                       // name the client sent
            'mimes:'.$extensions,                            // extension guessed from content
            'mimetypes:'.implode(',', $type->mimeTypes()),   // content type itself
        ];
    }

    /** Human-readable list for form hints and messages, e.g. "PDF, JPG, JPEG, PNG". */
    public static function acceptedLabel(HrDocumentType $type): string
    {
        return strtoupper(implode(', ', $type->extensions()));
    }

    /** `accept` attribute for the file input. */
    public static function acceptAttribute(HrDocumentType $type): string
    {
        return implode(',', array_map(fn ($ext) => '.'.$ext, $type->extensions()));
    }

    /**
     * Encrypts and stores the upload, returning its private path. Re-checks the content
     * even though callers validate, so no route can store an unexpected type.
     */
    public function store(HrDocumentType $type, UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file->getRealPath()) ?: '';

        if (! in_array($extension, $type->extensions(), true) || ! in_array($mime, $type->mimeTypes(), true)) {
            throw ValidationException::withMessages([
                'file' => 'Only '.self::acceptedLabel($type).' files are accepted.',
            ]);
        }

        return $this->files->put($file, $type->directory());
    }

    /** Stores the new upload and removes the file it replaces. */
    public function replace(HrDocumentType $type, ?string $previous, UploadedFile $file): string
    {
        $path = $this->store($type, $file);
        $this->delete($type, $previous);

        return $path;
    }

    /** Removes a stored file (private or legacy public). Unknown paths are ignored. */
    public function delete(HrDocumentType $type, ?string $path): void
    {
        if ($this->isEncryptedPath($type, $path)) {
            $this->files->delete($path);
        } elseif ($legacy = $this->legacyAbsolutePath($type, $path)) {
            @unlink($legacy);
        }
    }

    /**
     * Decrypted (or legacy) contents plus the detected MIME type, or null when the file is
     * missing, the path is not one this type may use, or the content is not allow-listed.
     *
     * @return array{contents: string, mime: string, extension: string, legacy: bool}|null
     */
    public function read(HrDocumentType $type, ?string $path): ?array
    {
        if ($this->isEncryptedPath($type, $path)) {
            if (! Storage::disk(EncryptedFileStore::DISK)->exists($path)) {
                return null;
            }
            $contents = $this->files->get($path);
            $legacy = false;
        } elseif ($absolute = $this->legacyAbsolutePath($type, $path)) {
            $contents = (string) file_get_contents($absolute);
            $legacy = true;
        } else {
            return null;
        }

        $mime = (new finfo(FILEINFO_MIME_TYPE))->buffer($contents) ?: '';

        if (! in_array($mime, $type->mimeTypes(), true)) {
            return null;
        }

        return ['contents' => $contents, 'mime' => $mime, 'extension' => HrDocumentType::extensionForMime($mime), 'legacy' => $legacy];
    }

    public function isEncryptedPath(HrDocumentType $type, ?string $path): bool
    {
        return is_string($path)
            && preg_match('#^'.preg_quote($type->directory(), '#').'/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}\.enc$#D', $path) === 1;
    }

    /**
     * Absolute path of a legacy public-disk file, only if it sits directly in one of the
     * type's old folders, has a plain hashed-style name, and resolves inside public storage.
     */
    public function legacyAbsolutePath(HrDocumentType $type, ?string $path): ?string
    {
        if (! is_string($path) || ! preg_match('#^[A-Za-z0-9_/-]+/[A-Za-z0-9_-]+\.[A-Za-z0-9]{1,8}$#D', $path)) {
            return null;
        }

        if (! in_array(dirname($path), $type->legacyDirectories(), true)) {
            return null;
        }

        $root = realpath(Storage::disk('public')->path(''));
        $real = realpath(Storage::disk('public')->path($path));

        if ($root === false || $real === false || is_link(Storage::disk('public')->path($path))
            || ! str_starts_with($real, $root.DIRECTORY_SEPARATOR) || ! is_file($real)) {
            return null;
        }

        return $real;
    }
}
