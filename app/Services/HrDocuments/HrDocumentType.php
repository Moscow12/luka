<?php

namespace App\Services\HrDocuments;

use App\Models\contract_documents;
use App\Models\ContractRequest;
use App\Models\Employeecontracts;
use App\Models\Employeedisplineissue;
use App\Models\Employeeleaves;
use App\Models\Employeeotherattachments;
use App\Models\Employeepromotions;
use App\Models\Employeequalifications;

/**
 * Every kind of sensitive HR file, the record column that points at it, what may be
 * uploaded for it, and where the old public-disk copies used to live.
 */
enum HrDocumentType: string
{
    case EmployeeContract = 'contract';
    case Promotion = 'promotion';
    case Qualification = 'qualification';
    case Disciplinary = 'disciplinary';
    case OtherDocument = 'other-document';
    case Leave = 'leave';
    case ContractRequest = 'contract-request';
    case ContractRequestLetter = 'contract-request-letter';
    case InstitutionalContract = 'institutional-contract';

    /** Extension => content MIME types accepted for it. */
    protected const FORMATS = [
        'pdf' => ['application/pdf'],
        'jpg' => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png' => ['image/png'],
    ];

    /** @return class-string<\Illuminate\Database\Eloquent\Model> */
    public function model(): string
    {
        return match ($this) {
            self::EmployeeContract => Employeecontracts::class,
            self::Promotion => Employeepromotions::class,
            self::Qualification => Employeequalifications::class,
            self::Disciplinary => Employeedisplineissue::class,
            self::OtherDocument => Employeeotherattachments::class,
            self::Leave => Employeeleaves::class,
            self::ContractRequest, self::ContractRequestLetter => ContractRequest::class,
            self::InstitutionalContract => contract_documents::class,
        };
    }

    public function column(): string
    {
        return match ($this) {
            self::Leave => 'document',
            self::ContractRequest => 'employee_attachment',
            self::ContractRequestLetter => 'hr_confirmation_letter',
            self::InstitutionalContract => 'file_path',
            default => 'attachment',
        };
    }

    /** Folder on the private disk holding this type's encrypted files. */
    public function directory(): string
    {
        return 'hr-documents/'.$this->value;
    }

    /** Folder(s) on the public disk this type was written to before files were secured. */
    public function legacyDirectories(): array
    {
        return match ($this) {
            self::EmployeeContract => ['contracts'],
            self::Leave => ['leave-documents'],
            self::ContractRequest => ['contract-requests'],
            self::ContractRequestLetter => ['contract-requests/hr'],
            self::InstitutionalContract => ['contracts/documents'],
            default => ['attachments'],
        };
    }

    /** @return list<string> */
    public function extensions(): array
    {
        // Every HR document type: PDF or image only. Word files (.doc/.docx) are not accepted.
        return ['pdf', 'jpg', 'jpeg', 'png'];
    }

    /** @return list<string> */
    public function mimeTypes(): array
    {
        return array_values(array_unique(array_merge(...array_map(
            fn (string $extension) => self::FORMATS[$extension],
            $this->extensions(),
        ))));
    }

    /** File extension to offer on download for a detected MIME type. */
    public static function extensionForMime(string $mime): ?string
    {
        foreach (self::FORMATS as $extension => $mimes) {
            if (in_array($mime, $mimes, true)) {
                return $extension === 'jpeg' ? 'jpg' : $extension;
            }
        }

        return null;
    }

    public function label(): string
    {
        return str_replace('-', ' ', $this->value);
    }
}
