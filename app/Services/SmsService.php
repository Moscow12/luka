<?php

namespace App\Services;

use App\Models\SmsApiSetting;
use Exception;
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
            Log::error('SMS sending failed: '.$e->getMessage(), [
                'phone_numbers' => $phoneNumbers ?? [],
                'message' => $message,
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
     * Send request to SMS API
     */
    protected function sendToApi(array $payload): array
    {
        $ch = curl_init($this->apiSetting->sending_url);

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Basic '.base64_encode($this->apiSetting->api_key.':'.$this->apiSetting->secret_key),
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception('cURL Error: '.$error);
        }

        curl_close($ch);

        $responseData = json_decode($response, true);

        if ($httpCode !== 200 && $httpCode !== 201) {
            throw new Exception('API returned status code '.$httpCode.': '.$response);
        }

        return $responseData ?? ['raw_response' => $response];
    }

    /**
     * Log SMS to database
     */
    protected function logSms(array $phoneNumbers, string $message, array $response): void
    {
        try {
            foreach ($phoneNumbers as $phoneNumber) {
                \App\Models\SmsLog::create([
                    'sms_api_setting_id' => $this->apiSetting->id,
                    'phone_number' => $phoneNumber,
                    'message' => $message,
                    'status' => $response['success'] ?? true ? 'sent' : 'failed',
                    'response' => json_encode($response),
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
            $response = Http::withHeaders([
                'Authorization' => 'Basic '.base64_encode($this->apiSetting->api_key.':'.$this->apiSetting->secret_key),
            ])->get($this->apiSetting->delivery_report_url, [
                'message_id' => $messageId,
            ]);

            return $response->json();
        } catch (Exception $e) {
            Log::error('Failed to get delivery report: '.$e->getMessage());

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
