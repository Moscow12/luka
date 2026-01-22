<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CertificateOfService extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'certificates_of_service';

    protected $fillable = [
        'certificate_number',
        'employee_id',
        'contract_id',
        'contract_request_id',
        'separation_type',
        'termination_reason_id',
        'service_start_date',
        'service_end_date',
        'position_held',
        'department',
        'duties_performed',
        'additional_remarks',
        'declaration_text',
        'status',
        'prepared_by',
        'approved_by',
        'approved_at',
        'approval_comments',
        'printed_at',
        'print_count',
        'workstation_id',
    ];

    protected $casts = [
        'service_start_date' => 'date',
        'service_end_date' => 'date',
        'approved_at' => 'datetime',
        'printed_at' => 'datetime',
        'print_count' => 'integer',
    ];

    /**
     * Boot the model to generate certificate number.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($certificate) {
            if (empty($certificate->certificate_number)) {
                $certificate->certificate_number = self::generateCertificateNumber();
            }
        });
    }

    /**
     * Generate a unique certificate number.
     */
    public static function generateCertificateNumber(): string
    {
        $year = now()->format('Y');
        $prefix = 'COS';

        $lastCertificate = self::where('certificate_number', 'like', "{$prefix}-{$year}-%")
            ->orderBy('certificate_number', 'desc')
            ->first();

        if ($lastCertificate) {
            $lastNumber = (int) substr($lastCertificate->certificate_number, -5);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return sprintf('%s-%s-%05d', $prefix, $year, $newNumber);
    }

    /**
     * Get the default declaration text.
     */
    public static function getDefaultDeclarationText(): string
    {
        return 'This is to certify that the above-named person was employed by this organization during the period stated above. '.
            'This certificate is issued upon request for whatever legal purpose it may serve. '.
            'We wish them well in their future endeavors.';
    }

    /**
     * Get the employee for this certificate.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the contract for this certificate.
     */
    public function contract(): BelongsTo
    {
        return $this->belongsTo(Employeecontracts::class, 'contract_id');
    }

    /**
     * Get the contract request that triggered this certificate.
     */
    public function contractRequest(): BelongsTo
    {
        return $this->belongsTo(ContractRequest::class);
    }

    /**
     * Get the termination reason.
     */
    public function terminationReason(): BelongsTo
    {
        return $this->belongsTo(TerminationReason::class);
    }

    /**
     * Get the user who prepared this certificate.
     */
    public function preparedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    /**
     * Get the user who approved this certificate.
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the workstation for letterhead.
     */
    public function workstation(): BelongsTo
    {
        return $this->belongsTo(workstations::class, 'workstation_id');
    }

    /**
     * Get separation type label.
     */
    public function getSeparationTypeLabelAttribute(): string
    {
        return match ($this->separation_type) {
            'termination' => 'Termination of Employment',
            'end_of_contract' => 'End of Contract',
            'resignation' => 'Voluntary Resignation',
            'retirement' => 'Retirement',
            'other' => 'Other',
            default => ucfirst($this->separation_type),
        };
    }

    /**
     * Get status badge class.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'bg-secondary',
            'pending_approval' => 'bg-warning',
            'approved' => 'bg-success',
            'printed' => 'bg-info',
            'cancelled' => 'bg-danger',
            default => 'bg-secondary',
        };
    }

    /**
     * Get status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'Draft',
            'pending_approval' => 'Pending Approval',
            'approved' => 'Approved',
            'printed' => 'Printed',
            'cancelled' => 'Cancelled',
            default => ucfirst($this->status),
        };
    }

    /**
     * Calculate years of service.
     */
    public function getYearsOfServiceAttribute(): string
    {
        $start = $this->service_start_date;
        $end = $this->service_end_date;

        if (! $start || ! $end) {
            return '-';
        }

        $years = $start->diffInYears($end);
        $months = $start->diffInMonths($end) % 12;

        if ($years > 0 && $months > 0) {
            return "{$years} year(s) and {$months} month(s)";
        } elseif ($years > 0) {
            return "{$years} year(s)";
        } else {
            return "{$months} month(s)";
        }
    }

    /**
     * Scope for draft certificates.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope for pending approval.
     */
    public function scopePendingApproval($query)
    {
        return $query->where('status', 'pending_approval');
    }

    /**
     * Scope for approved certificates.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope for ready to print (approved but not yet printed).
     */
    public function scopeReadyToPrint($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Check if certificate can be edited.
     */
    public function canBeEdited(): bool
    {
        return in_array($this->status, ['draft', 'pending_approval']);
    }

    /**
     * Check if certificate can be approved.
     */
    public function canBeApproved(): bool
    {
        return $this->status === 'pending_approval';
    }

    /**
     * Check if certificate can be printed.
     */
    public function canBePrinted(): bool
    {
        return in_array($this->status, ['approved', 'printed']);
    }
}
