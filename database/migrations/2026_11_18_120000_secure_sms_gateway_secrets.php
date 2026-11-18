<?php

use App\Support\SecretRedactor;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * - Encrypts SMS gateway api_key / secret_key at rest (SmsApiSetting casts both as `encrypted`).
 * - Masks credentials (e.g. "Password: ...") in SMS messages already stored in sms_logs.
 *
 * Masking is one-way by design, so down() only decrypts the gateway keys.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('sms_api_settings')->orderBy('id')->each(function ($row) {
            $updates = [];

            foreach (['api_key', 'secret_key'] as $column) {
                if (filled($row->{$column}) && ! $this->isEncrypted($row->{$column})) {
                    $updates[$column] = Crypt::encryptString($row->{$column});
                }
            }

            if ($updates !== []) {
                DB::table('sms_api_settings')->where('id', $row->id)->update($updates);
            }
        });

        if (Schema::hasTable('sms_logs')) {
            DB::table('sms_logs')->orderBy('id')->each(function ($row) {
                $message = SecretRedactor::text($row->message);
                $response = $row->response === null ? null : SecretRedactor::text($row->response);

                if ($message !== $row->message || $response !== $row->response) {
                    DB::table('sms_logs')->where('id', $row->id)->update(['message' => $message, 'response' => $response]);
                }
            });
        }
    }

    public function down(): void
    {
        DB::table('sms_api_settings')->orderBy('id')->each(function ($row) {
            $updates = [];

            foreach (['api_key', 'secret_key'] as $column) {
                if (filled($row->{$column}) && $this->isEncrypted($row->{$column})) {
                    $updates[$column] = Crypt::decryptString($row->{$column});
                }
            }

            if ($updates !== []) {
                DB::table('sms_api_settings')->where('id', $row->id)->update($updates);
            }
        });
    }

    private function isEncrypted(string $value): bool
    {
        try {
            Crypt::decryptString($value);

            return true;
        } catch (\Throwable) {
            return false;
        }
    }
};
