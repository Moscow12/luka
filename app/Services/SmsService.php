<?php

namespace App\Services;

use App\Models\SmsApiSetting;
use App\Support\OutboundUrlGuard;
use App\Support\SecretRedactor;
use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected ?SmsApiSetting $apiSetting = null;

    /**
     * Initialize the SMS service with default or specified API setting
     */
    public function __construct(?string $providerId = null)
    {
        if ($providerId) {
            $this->apiSetting = SmsApiSetting::where('id', $providerId)
                ->where('is_active', true)
                ->first();
        } else {
            $this->apiSetting = SmsApiSetting::where('is_default', true)
                ->where('is_active', true)
                ->first();
        }

        if (! $this->apiSetting) {
            throw new Exception('No active SMS API configuration found. Please configure SMS API settings.');
        }
    }

    /**
     * Send SMS to a single or multiple recipients
     *
     * @param  string|array  $phoneNumber  Phone number(s) to send SMS to
     * @param  string  $message  Message content to send
     * @param  string|null  $senderId  Optional sender ID (uses configured sender_id if not provided)
     * @return array Response with success status and details
     */
    public function send(string|array $phoneNumber, string $message, ?string $senderId = null): array
    {
        try {
            // Normalize phone numbers to array
            $phoneNumbers = is_array($phoneNumber) ? $phoneNumber : [$phoneNumber];

            // Format phone numbers
            $formattedNumbers = array_map(fn ($number) => $this->formatPhoneNumber($number), $phoneNumbers);

            // Prepare recipients for API
            $recipients = $this->prepareRecipients($formattedNumbers);

            // Get sender ID
            $finalSenderId = $senderId ?? $this->apiSetting->sender_id ?? 'INFO';

            // Prepare API payload
            $payload = [
                'source_addr' => $finalSenderId,
                'encoding' => 0,
                'schedule_time' => '',
                'message' => $message,
                'recipients' => $recipients,
            ];

            // Send SMS via API
            $response = $this->sendToApi($payload);

            // Log the SMS
            $this->logSms($formattedNumbers, $message, $response);

            return [
                'success' => true,
                'message' => 'SMS sent successfully',
                'response' => $response,
                'recipients' => $formattedNumbers,
            ];
        } catch (Exception $e) {
            // Message bodies can carry credentials (e.g. new-account SMS), so only the length is logged
            Log::error('SMS sending failed: '.SecretRedactor::text($e->getMessage(), $this->secrets()), [
                'phone_numbers' => $phoneNumbers ?? [],
                'message_length' => mb_strlen($message),
                'provider' => $this->apiSetting->provider_name ?? 'Unknown',
            ]);

            return [
                'success' => false,
                'message' => 'Failed to send SMS: '.$e->getMessage(),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send SMS to multiple staff members
     *
     * @param  array  $staffIds  Array of staff IDs
     * @param  string  $message  Message content
     * @return array Results for each staff member
     */
    public function sendToStaff(array $staffIds, string $message): array
    {
        $results = [];

        foreach ($staffIds as $staffId) {
            $staff = \App\Models\staffs::find($staffId);

            if (! $staff || ! $staff->mobile_number) {
                $results[] = [
                    'staff_id' => $staffId,
                    'success' => false,
                    'message' => 'Staff not found or no phone number',
                ];

                continue;
            }

            $result = $this->send($staff->mobile_number, $message);
            $results[] = array_merge($result, [
                'staff_id' => $staffId,
                'staff_name' => $staff->first_name.' '.$staff->last_name,
                'phone_number' => $staff->mobile_number,
            ]);
        }

        return $results;
    }

    /**
     * Format phone number to international format (255...)
     */
    protected function formatPhoneNumber(string $phoneNumber): string
    {
        // Remove any spaces, dashes, or special characters
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);

        // Check if it already starts with country code (255)
        if (strlen($phoneNumber) > 10 && substr($phoneNumber, 0, 3) === '255') {
            return $phoneNumber;
        }

        // Replace leading 0 with 255 (Tanzania country code)
        if (substr($phoneNumber, 0, 1) === '0') {
            return '255'.substr($phoneNumber, 1);
        }

        // If it's just 9 digits, add 255
        if (strlen($phoneNumber) === 9) {
            return '255'.$phoneNumber;
        }

        return $phoneNumber;
    }

    /**
     * Prepare recipients array for API
     */
    protected function prepareRecipients(array $phoneNumbers): array
    {
        $recipients = [];
        foreach ($phoneNumbers as $index => $number) {
            $recipients[] = [
                'recipient_id' => (string) ($index + 1),
                'dest_addr' => $number,
            ];
        }

        return $recipients;
    }

    /**
     * Send request to SMS API.
     *
     * The URL is re-checked against the SSRF guard on every send (stored settings may
     * predate the allow-list, and DNS can change), the connection is pinned to the
     * checked address, TLS certificates are verified and redirects are not followed.
     */
    protected function sendToApi(array $payload): array
    {
        $url = (string) $this->apiSetting->sending_url;
        $checked = app(OutboundUrlGuard::class)->check($url, config('sms.allowed_hosts', []));

        try {
            $response = $this->http($checked)->post($url, $payload);
        } catch (ConnectionException $e) {
            Log::warning('SMS provider connection failed', ['host' => $checked['host'], 'error' => SecretRedactor::text($e->getMessage(), $this->secrets())]);

            throw new Exception('Could not connect securely to the SMS provider.');
        }

        if (! $response->successful()) {
            $this->logProviderError($response, $checked['host']);

            // The provider's body is not echoed back to users: it may contain request details
            throw new Exception('SMS provider returned HTTP '.$response->status().'.');
        }

        return $response->json() ?? ['raw_response' => mb_substr($response->body(), 0, 1000)];
    }

    protected function http(array $checked): PendingRequest
    {
        return Http::withBasicAuth((string) $this->apiSetting->api_key, (string) $this->apiSetting->secret_key)
            ->acceptJson()
            ->asJson()
            ->timeout((int) config('sms.timeout', 30))
            ->connectTimeout((int) config('sms.connect_timeout', 10))
            ->withoutRedirecting()
            ->withOptions([
                'verify' => true,
                'curl' => OutboundUrlGuard::pinOption($checked),
            ]);
    }

    protected function logProviderError(Response $response, string $host): void
    {
        Log::error('SMS provider error', [
            'host' => $host,
            'status' => $response->status(),
            'body' => SecretRedactor::text(mb_substr($response->body(), 0, 500), $this->secrets()),
        ]);
    }

    /** @return array<int, string|null> */
    protected function secrets(): array
    {
        return [$this->apiSetting?->api_key, $this->apiSetting?->secret_key];
    }

    /**
     * Log SMS to database (message text is stored with credentials masked)
     */
    protected function logSms(array $phoneNumbers, string $message, array $response): void
    {
        try {
            $storedMessage = SecretRedactor::text($message, $this->secrets());
            $storedResponse = json_encode(SecretRedactor::data($response, $this->secrets()));

            foreach ($phoneNumbers as $phoneNumber) {
                \App\Models\SmsLog::create([
                    'sms_api_setting_id' => $this->apiSetting->id,
                    'phone_number' => $phoneNumber,
                    'message' => $storedMessage,
                    'status' => $response['success'] ?? true ? 'sent' : 'failed',
                    'response' => $storedResponse,
                    'sent_by' => auth()->id(),
                ]);
            }
        } catch (Exception $e) {
            Log::error('Failed to log SMS: '.$e->getMessage());
        }
    }

    /**
     * Get delivery report if supported by provider
     */
    public function getDeliveryReport(string $messageId): ?array
    {
        if (! $this->apiSetting->delivery_report_url) {
            return null;
        }

        try {
            $url = (string) $this->apiSetting->delivery_report_url;
            $checked = app(OutboundUrlGuard::class)->check($url, config('sms.allowed_hosts', []));
            $response = $this->http($checked)->get($url, ['message_id' => $messageId]);

            if (! $response->successful()) {
                $this->logProviderError($response, $checked['host']);

                return null;
            }

            return $response->json();
        } catch (Exception $e) {
            Log::error('Failed to get delivery report: '.SecretRedactor::text($e->getMessage(), $this->secrets()));

            return null;
        }
    }

    /**
     * Get available SMS API providers
     */
    public static function getAvailableProviders(): \Illuminate\Database\Eloquent\Collection
    {
        return SmsApiSetting::where('is_active', true)->get();
    }

    /**
     * Get default provider
     */
    public static function getDefaultProvider(): ?SmsApiSetting
    {
        return SmsApiSetting::where('is_default', true)
            ->where('is_active', true)
            ->first();
    }
}
