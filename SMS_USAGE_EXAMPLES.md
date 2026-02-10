# SMS Service Usage Examples

This document provides examples of how to use the SMS functionality in the Dasher HR Management System.

## Setup

First, configure your SMS API settings in the system:
1. Navigate to **Setup > SMS API Settings** (`/setup/setup/smsapis`)
2. Add your SMS provider details (API Key, Secret Key, URLs, etc.)
3. Set one provider as default

## Helper Functions Available

The system provides the following global helper functions:

### 1. `send_sms()` - Send SMS to Phone Number(s)
```php
/**
 * @param string|array $phoneNumber - Single or multiple phone numbers
 * @param string $message - SMS message content
 * @param string|null $senderId - Optional sender ID (uses default if not provided)
 * @param string|null $providerId - Optional specific provider UUID
 * @return array Response with success status
 */
send_sms($phoneNumber, $message, $senderId = null, $providerId = null);
```

### 2. `send_sms_to_staff()` - Send SMS to Staff Members
```php
/**
 * @param array $staffIds - Array of staff IDs
 * @param string $message - SMS message content
 * @param string|null $providerId - Optional specific provider UUID
 * @return array Results for each staff member
 */
send_sms_to_staff($staffIds, $message, $providerId = null);
```

### 3. `format_phone_number()` - Format Phone Number
```php
/**
 * @param string $phoneNumber - Phone number to format
 * @return string Formatted phone number (255...)
 */
format_phone_number($phoneNumber);
```

### 4. `get_sms_providers()` - Get All Active Providers
```php
/**
 * @return \Illuminate\Database\Eloquent\Collection
 */
get_sms_providers();
```

### 5. `get_default_sms_provider()` - Get Default Provider
```php
/**
 * @return \App\Models\SmsApiSetting|null
 */
get_default_sms_provider();
```

## Usage Examples

### Example 1: Send SMS to Single Phone Number
```php
// Send SMS using default provider
$result = send_sms('0712345678', 'Your leave request has been approved.');

if ($result['success']) {
    echo "SMS sent successfully!";
} else {
    echo "Failed: " . $result['message'];
}
```

### Example 2: Send SMS to Multiple Phone Numbers
```php
$phoneNumbers = ['0712345678', '0723456789', '0734567890'];
$message = 'Reminder: Staff meeting tomorrow at 10 AM.';

$result = send_sms($phoneNumbers, $message);

if ($result['success']) {
    echo "SMS sent to " . count($result['recipients']) . " recipients.";
}
```

### Example 3: Send SMS to Staff Members
```php
// Get staff IDs (example)
$staffIds = ['staff-uuid-1', 'staff-uuid-2', 'staff-uuid-3'];
$message = 'Your salary for this month has been processed.';

$results = send_sms_to_staff($staffIds, $message);

// Check results for each staff
foreach ($results as $result) {
    if ($result['success']) {
        echo "SMS sent to {$result['staff_name']}: {$result['phone_number']}";
    } else {
        echo "Failed for {$result['staff_name']}: {$result['message']}";
    }
}
```

### Example 4: Send SMS with Custom Sender ID
```php
$result = send_sms(
    '0712345678',
    'Your OTP code is: 123456',
    'DASHER-HR' // Custom sender ID
);
```

### Example 5: Send SMS Using Specific Provider
```php
// Get specific provider
$provider = \App\Models\SmsApiSetting::where('provider_name', 'Twilio')->first();

$result = send_sms(
    '0712345678',
    'Test message from specific provider',
    null,
    $provider->id // Use specific provider UUID
);
```

### Example 6: Using SmsService Class Directly
```php
use App\Services\SmsService;

// Initialize with default provider
$smsService = new SmsService();

// Or initialize with specific provider
$smsService = new SmsService('provider-uuid-here');

// Send SMS
$result = $smsService->send('0712345678', 'Hello from Dasher!');
```

### Example 7: Send SMS in Livewire Component
```php
namespace App\Livewire\Hr\Leave;

use Livewire\Component;

class Leaveapproval extends Component
{
    public function approveLeave($leaveId)
    {
        $leave = Leave::findOrFail($leaveId);
        $leave->status = 'approved';
        $leave->save();

        // Send SMS notification to staff
        $message = "Dear {$leave->staff->first_name}, your leave request from {$leave->start_date} to {$leave->end_date} has been approved.";

        $result = send_sms($leave->staff->mobile_number, $message);

        if ($result['success']) {
            session()->flash('success', 'Leave approved and SMS notification sent!');
        } else {
            session()->flash('warning', 'Leave approved but SMS failed: ' . $result['message']);
        }
    }
}
```

### Example 8: Send Bulk SMS to Department
```php
use App\Models\staffs;

// Get all staff from HR department
$hrStaff = staffs::where('department_id', $departmentId)->pluck('id')->toArray();

// Send bulk SMS
$message = 'Important: Department meeting scheduled for next Monday at 9 AM.';
$results = send_sms_to_staff($hrStaff, $message);

// Count successes
$successCount = collect($results)->where('success', true)->count();
echo "SMS sent successfully to {$successCount} staff members.";
```

### Example 9: Format Phone Numbers Before Sending
```php
$phoneNumbers = ['0712345678', '255723456789', '734567890'];

// Format all numbers
$formattedNumbers = array_map('format_phone_number', $phoneNumbers);

// Send SMS
$result = send_sms($formattedNumbers, 'Test message with formatted numbers');
```

### Example 10: Check SMS Logs
```php
use App\Models\SmsLog;

// Get recent SMS logs
$recentLogs = SmsLog::with(['smsApiSetting', 'user'])
    ->latest()
    ->take(10)
    ->get();

foreach ($recentLogs as $log) {
    echo "Phone: {$log->phone_number}, Status: {$log->status}, Sent: {$log->created_at}";
}

// Get failed SMS
$failedSms = SmsLog::where('status', 'failed')->get();

// Get SMS sent by specific user
$userSms = SmsLog::where('sent_by', auth()->id())->get();
```

### Example 11: Birthday SMS Notification
```php
use App\Models\staffs;
use Carbon\Carbon;

// Get staff with birthdays today
$birthdayStaff = staffs::whereMonth('date_of_birth', Carbon::today()->month)
    ->whereDay('date_of_birth', Carbon::today()->day)
    ->get();

foreach ($birthdayStaff as $staff) {
    $message = "Happy Birthday {$staff->first_name}! 🎉 Wishing you a wonderful day. - Dasher HR Team";
    send_sms($staff->mobile_number, $message);
}
```

### Example 12: Leave Balance Alert
```php
use App\Models\staffs;

// Alert staff with low leave balance
$staffWithLowLeave = staffs::where('annual_leave_balance', '<', 5)->get();

foreach ($staffWithLowLeave as $staff) {
    $message = "Leave Balance Alert: You have {$staff->annual_leave_balance} days remaining. Plan accordingly.";
    send_sms($staff->mobile_number, $message);
}
```

### Example 13: Using in Artisan Command
```php
namespace App\Console\Commands;

use App\Models\staffs;
use Illuminate\Console\Command;

class SendMonthlyReminders extends Command
{
    protected $signature = 'sms:monthly-reminders';
    protected $description = 'Send monthly reminders to staff';

    public function handle()
    {
        $allStaff = staffs::whereNotNull('mobile_number')->pluck('id')->toArray();

        $message = 'Monthly Reminder: Please update your timesheets by end of month.';

        $results = send_sms_to_staff($allStaff, $message);

        $successCount = collect($results)->where('success', true)->count();

        $this->info("SMS sent to {$successCount} staff members.");
    }
}
```

## Phone Number Format

The system automatically formats phone numbers to international format (255...):

- `0712345678` → `255712345678`
- `712345678` → `255712345678`
- `255712345678` → `255712345678` (unchanged)

## SMS Logging

All SMS messages are automatically logged to the `sms_logs` table with:
- Phone number
- Message content
- Status (sent, failed, pending, delivered)
- API response
- Sender information
- Timestamps

## Error Handling

The helper functions return arrays with success status:

```php
[
    'success' => true,  // or false
    'message' => 'SMS sent successfully',  // or error message
    'response' => [...],  // API response
    'recipients' => [...],  // List of recipients
]
```

Always check the `success` key before proceeding:

```php
$result = send_sms($phone, $message);

if (!$result['success']) {
    Log::error('SMS failed: ' . $result['error']);
    // Handle error
}
```

## Best Practices

1. **Always validate phone numbers** before sending
2. **Keep messages concise** - SMS has character limits
3. **Use meaningful sender IDs** - Help recipients identify the sender
4. **Handle failures gracefully** - Log errors and notify admins
5. **Respect rate limits** - Don't send too many SMS at once
6. **Test with your provider** - Each provider may have different requirements
7. **Monitor SMS logs** - Track delivery status and failures
8. **Use queues for bulk SMS** - Don't block requests when sending many SMS

## Queued SMS Example (Recommended for Bulk Sending)

```php
use Illuminate\Support\Facades\Queue;

// Create a job for sending SMS
Queue::push(function () use ($staffIds, $message) {
    send_sms_to_staff($staffIds, $message);
});
```

## Support

For issues or questions about SMS functionality:
- Check the SMS API Settings page for configuration
- Review SMS logs for failed messages
- Verify your provider's API documentation
- Contact your SMS provider for API-related issues
