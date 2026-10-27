<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Stores uploads encrypted (AES-256 via the app key) on the private disk.
 * Shared by ticket and chat attachments.
 */
class EncryptedFileStore
{
    public const DISK = 'local';

    /** Accepted attachment types and size (25MB), per the ticket and chat PRDs. */
    public const UPLOAD_RULES = ['file', 'max:25600', 'mimes:pdf,jpg,jpeg,png,gif,webp,doc,docx,xls,xlsx,ppt,pptx,txt,csv'];

    /** Encrypts and stores the file under $directory, returning its path. */
    public function put(UploadedFile $file, string $directory): string
    {
        $path = trim($directory, '/').'/'.Str::uuid().'.enc';
        Storage::disk(self::DISK)->put($path, Crypt::encryptString($file->get()));

        return $path;
    }

    public function get(string $path): string
    {
        return Crypt::decryptString(Storage::disk(self::DISK)->get($path));
    }

    public function delete(string $path): void
    {
        Storage::disk(self::DISK)->delete($path);
    }
}
