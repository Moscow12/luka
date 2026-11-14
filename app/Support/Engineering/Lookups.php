<?php

namespace App\Support\Engineering;

/**
 * Fixed vocabularies of the engineering module, as value => label.
 */
class Lookups
{
    public const WORK_TYPES = [
        'ppm' => 'PPM',
        'breakdown' => 'Breakdown',
        'installation' => 'Installation',
        'inspection' => 'Inspection',
        'other' => 'Others',
    ];

    public const PRIORITIES = [
        'routine' => 'Routine',
        'emergency' => 'Emergency',
    ];

    public const EQUIPMENT_STATUSES = [
        'working' => 'Working',
        'partly_defective' => 'Partly defective',
        'out_of_service' => 'Out of service',
    ];

    public const EQUIPMENT_STATUS_BADGES = [
        'working' => 'bg-success',
        'partly_defective' => 'bg-warning text-dark',
        'out_of_service' => 'bg-danger',
    ];

    public const FAILURE_REASONS = [
        'wear_and_tear' => 'Wear & Tear',
        'rough_handling' => 'Rough Handling',
        'poor_installation' => 'Poor Installation',
        'water_penetration' => 'Water Penetration',
        'mains_unstable' => 'Mains Unstable',
        'dirty' => 'Dirty',
        'wind_and_storm' => 'Wind & Storm',
        'other' => 'Others',
    ];

    public const FINAL_STATUSES = [
        'job_complete' => 'Job complete',
        'beyond_repair' => 'Beyond repair',
        'assistance_needed' => 'Additional assistance needed',
    ];

    public const SAFETY_TESTS = [
        'electrical' => 'Electrical safety test completed',
        'functional' => 'Functional test completed',
        'visual' => 'Visual inspection complete',
    ];

    public const PPM_FREQUENCIES = [
        '3_months' => 'Every 3 months',
        '6_months' => 'Every 6 months',
        '12_months' => 'Every 12 months',
        'running_hours' => 'Running hours',
    ];

    public const PPM_FREQUENCY_MONTHS = [
        '3_months' => 3,
        '6_months' => 6,
        '12_months' => 12,
    ];

    public const PPM_PERFORMERS = [
        'user' => 'User',
        'engineering' => 'Engineering',
    ];

    public const JOB_CARD_STATUSES = [
        'draft' => 'Draft',
        'pending_certification' => 'Pending certification',
        'pending_authorisation' => 'Pending authorisation',
        'approved' => 'Approved',
        'returned' => 'Returned',
        'rejected' => 'Rejected',
        'purchasing' => 'Purchasing',
        'delivered' => 'Delivered',
        'closed' => 'Closed',
    ];

    public const JOB_CARD_STATUS_BADGES = [
        'draft' => 'bg-secondary',
        'pending_certification' => 'bg-info',
        'pending_authorisation' => 'bg-primary',
        'approved' => 'bg-success',
        'returned' => 'bg-warning text-dark',
        'rejected' => 'bg-danger',
        'purchasing' => 'bg-warning text-dark',
        'delivered' => 'bg-success',
        'closed' => 'bg-dark',
    ];

    public const SPARES_LINE_STATUSES = [
        'requested' => 'Requested',
        'issued' => 'Issued',
        'partial' => 'Partially issued',
        'rejected' => 'Rejected',
        'to_procure' => 'To procure',
    ];

    public const PROCUREMENT_STATUSES = [
        'complete' => ['label' => 'Complete', 'badge' => 'bg-success', 'colour' => '198754'],
        'on_track' => ['label' => 'On track', 'badge' => 'bg-warning text-dark', 'colour' => 'FFC107'],
        'delayed' => ['label' => 'Delayed', 'badge' => 'bg-danger', 'colour' => 'DC3545'],
    ];

    public static function label(array $map, ?string $value, string $fallback = '—'): string
    {
        return $value !== null && isset($map[$value]) ? $map[$value] : $fallback;
    }
}
