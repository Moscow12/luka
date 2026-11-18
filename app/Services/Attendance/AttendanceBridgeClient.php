<?php

namespace App\Services\Attendance;

use App\Models\FpDevice;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * HTTP client for the external Python attendance service, which does all
 * communication with the fingerprint devices.
 *
 * Contract (all endpoints are POST, JSON in and out, `Authorization: Bearer <token>`):
 *
 *   POST {url}/devices/sync        body: {device, days_back}
 *        -> {success: true, users: [{user_id, name}], attendance: [{user_id, timestamp: "Y-m-d H:i:s"}]}
 *   POST {url}/devices/clear-logs  body: {device}
 *        -> {success: true}
 *   POST {url}/devices/diagnose    body: {device}
 *        -> {success: true, report: "<text>"}
 *
 *   device = {type: "zkteco"|"anviz", ip_address, port|null, username|null, password|null}
 *   Failure -> {success: false, error: "<message>"} (any status code).
 *
 * The service picks the protocol (e.g. Anviz TCP 5010 vs HTTP) itself.
 */
class AttendanceBridgeClient
{
    public function sync(FpDevice $device, int $daysBack = 1): array
    {
        $data = $this->post('devices/sync', [
            'device' => $this->devicePayload($device),
            'days_back' => $daysBack,
        ]);

        return [
            'users' => is_array($data['users'] ?? null) ? $data['users'] : [],
            'attendance' => is_array($data['attendance'] ?? null) ? $data['attendance'] : [],
        ];
    }

    public function clearLogs(FpDevice $device): void
    {
        $this->post('devices/clear-logs', ['device' => $this->devicePayload($device)]);
    }

    public function diagnose(FpDevice $device): string
    {
        $data = $this->post('devices/diagnose', ['device' => $this->devicePayload($device)]);

        return is_string($data['report'] ?? null) ? $data['report'] : '';
    }

    private function devicePayload(FpDevice $device): array
    {
        return [
            'type' => $device->device_type,
            'ip_address' => $device->ip_address,
            'port' => $device->port,
            'username' => $device->username,
            'password' => $device->password,
        ];
    }

    private function post(string $endpoint, array $payload): array
    {
        try {
            $response = $this->request()->post($endpoint, $payload);
        } catch (ConnectionException $e) {
            Log::warning('Attendance bridge unreachable', ['endpoint' => $endpoint, 'error' => $e->getMessage()]);

            throw new AttendanceBridgeException('The attendance service is unreachable or timed out.');
        }

        $data = $response->json();

        if (! is_array($data)) {
            $this->logFailure($endpoint, $response);

            throw new AttendanceBridgeException('The attendance service returned an invalid response (HTTP '.$response->status().').');
        }

        if (! $response->successful() || ($data['success'] ?? false) !== true) {
            $this->logFailure($endpoint, $response);
            $error = is_string($data['error'] ?? null) ? mb_substr($data['error'], 0, 300) : 'HTTP '.$response->status();

            throw new AttendanceBridgeException($error);
        }

        return $data;
    }

    private function request(): PendingRequest
    {
        $url = config('services.attendance_bridge.url');
        $token = config('services.attendance_bridge.token');

        if (blank($url) || blank($token)) {
            throw new AttendanceBridgeException('The attendance service is not configured (ATTENDANCE_BRIDGE_URL / ATTENDANCE_BRIDGE_TOKEN).');
        }

        return Http::baseUrl(rtrim($url, '/'))
            ->withToken($token)
            ->acceptJson()
            ->asJson()
            ->timeout((int) config('services.attendance_bridge.timeout', 200))
            ->withOptions(['verify' => (bool) config('services.attendance_bridge.verify_tls', true)]);
    }

    private function logFailure(string $endpoint, Response $response): void
    {
        // Never log the request (it carries device credentials); a truncated response body is enough.
        Log::error('Attendance bridge request failed', [
            'endpoint' => $endpoint,
            'status' => $response->status(),
            'body' => mb_substr($response->body(), 0, 500),
        ]);
    }
}
