<?php

/*
|--------------------------------------------------------------------------
| Engineering / Maintenance module
|--------------------------------------------------------------------------
| Defaults for the engineering module. Values under "kpi.weights" can be
| overridden at runtime from Engineering > Setup (stored in eng_settings).
*/

return [
    // Timestamps are stored in UTC and shown in hospital local time
    'timezone' => env('ENGINEERING_TIMEZONE', 'Africa/Dar_es_Salaam'),

    'currency' => env('ENGINEERING_CURRENCY', 'TZS'),

    'max_items_per_requisition' => 5,

    // A requester sees their own requisitions ("own") or their whole department's ("department")
    'requester_scope' => env('ENGINEERING_REQUESTER_SCOPE', 'own'),

    // Default "days to be done" offered on the assignment panel
    'lead_time_days' => ['emergency' => 1, 'routine' => 2],

    // A job open this long is highlighted and escalated to the HOD
    'stale_job_days' => 7,

    'ppm_due_soon_days' => 7,

    // Equipment this close to its predicted replacement date is listed as due for replacement
    'replacement_warning_months' => 12,

    'procurement' => [
        'approval_to_order_days' => 7,
        'order_to_delivery_days' => 14,
    ],

    'oxygen' => [
        'min_purity' => 90,
        'cylinder_types' => ['E (680 L)', 'F (1,360 L)', 'J (6,800 L)', 'Portable'],
        // Section code that receives oxygen cylinder orders
        'section' => 'MECHANICAL',
    ],

    // Sections whose jobs need all three safety tests before closing as complete
    'safety_test_sections' => ['BIOMEDICAL', 'ELECTRICAL'],

    // Sections shown as their own column on the weekly summary; the rest roll up into "Other"
    'summary_sections' => ['BIOMEDICAL', 'ELECTRICAL', 'ESTATE', 'MECHANICAL'],

    'kpi' => [
        // Must total 100. "oxygen" is shared equally between the plants a person is responsible for.
        'weights' => [
            'jobs' => 20,
            'ppm' => 15,
            'tat' => 15,
            'uptime' => 15,
            'satisfaction' => 15,
            'five_s' => 5,
            'oxygen' => 15,
        ],

        'targets' => [
            'jobs' => 85,
            'ppm' => 75,
            'tat' => 2,
            'uptime' => 85,
            'satisfaction' => 90,
            'five_s' => 90,
            'oxygen' => 90,
            'overall' => 85,
        ],

        // [hours from, hours to, score at band start, score at band end] - interpolated inside a band
        'tat_bands' => [
            [0, 24, 100, 90],
            [25, 48, 89, 70],
            [49, 72, 69, 50],
            [73, 96, 49, 30],
            [97, 120, 29, 10],
        ],

        // Overall individual score: red up to "red", green from "green", yellow in between
        'overall_bands' => ['red' => 0.600, 'green' => 0.901],
    ],

    // Weekly summary colour bands: green from "green", red below "yellow" (reversed for TAT)
    'weekly_thresholds' => [
        'jobs_pct' => ['yellow' => 70, 'green' => 90],
        'ppm_pct' => ['yellow' => 65, 'green' => 75],
        'satisfaction_pct' => ['yellow' => 60, 'green' => 90],
        'tat_days' => ['green' => 2.0, 'yellow' => 2.5],
    ],

    // Variance bands: a percentage must move more than this to count as improved / dropped
    'variance_thresholds' => ['pct' => 1, 'tat_days' => 0.5],
];
