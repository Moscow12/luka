<?php

namespace App\Services\Engineering;

use App\Models\Employee;
use App\Models\Engineering\MaintenanceJob;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Outbound SMS / email for engineering events. Delivery problems are logged and never
 * interrupt the action that triggered them.
 */
class EngineeringNotifier
{
    public function employee(?Employee $employee, string $message): void
    {
        if ($employee) {
            $this->send($employee->phone, $employee->email, $message);
        }
    }

    public function user(?User $user, string $message): void
    {
        if ($user) {
            $this->send($user->phone_number, $user->email, $message);
        }
    }

    /** The person who raised the requisition, on the phone number they gave for it. */
    public function requester(MaintenanceJob $job, string $message): void
    {
        $requisition = $job->requisition;

        $this->send($requisition->requester_phone ?: $requisition->requester?->phone_number, $requisition->requester?->email, $message);
    }

    /** Everyone holding a permission, e.g. the helpdesk or the HOD. */
    public function holders(string $permission, string $message): void
    {
        try {
            User::permission($permission)->get()->each(fn (User $user) => $this->user($user, $message));
        } catch (Throwable $e) {
            Log::warning('Engineering notification skipped: '.$e->getMessage(), ['permission' => $permission]);
        }
    }

    /** Heads of the job's section plus the HOD. */
    public function supervisors(MaintenanceJob $job, string $message): void
    {
        $job->section->staff()->wherePivot('is_head', true)->get()
            ->each(fn (Employee $head) => $this->employee($head, $message));

        $this->holders('engineering-manage', $message);
    }

    /** Queued until after the response, so a slow gateway never holds up the screen. */
    protected function send(?string $phone, ?string $email, string $message): void
    {
        defer(fn () => $this->deliver($phone, $email, $message));
    }

    protected function deliver(?string $phone, ?string $email, string $message): void
    {
        if (filled($phone)) {
            try {
                (new SmsService)->send($phone, $message);
            } catch (Throwable $e) {
                Log::warning('Engineering SMS not sent: '.$e->getMessage());
            }
        }

        if (filled($email)) {
            try {
                Mail::raw($message, fn ($mail) => $mail->to($email)->subject('Engineering: '.config('app.name')));
            } catch (Throwable $e) {
                Log::warning('Engineering email not sent: '.$e->getMessage());
            }
        }
    }
}
