<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * - Encrypts fingerprint-device passwords at rest (FpDevice casts `password` as `encrypted`;
 *   ciphertext exceeds 255 chars, so the column becomes TEXT).
 * - Creates the `manage-fp-devices` permission that now guards the device-management screen.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fp_devices', function (Blueprint $table) {
            $table->text('password')->nullable()->comment('Encrypted device password')->change();
        });

        DB::table('fp_devices')->whereNotNull('password')->where('password', '!=', '')
            ->orderBy('id')
            ->each(function ($row) {
                if ($this->isEncrypted($row->password)) {
                    return;
                }

                DB::table('fp_devices')->where('id', $row->id)
                    ->update(['password' => Crypt::encryptString($row->password)]);
            });

        if (Schema::hasTable('permissions')) {
            $categoryId = DB::table('permission_categories')->where('name', 'Staff Management')->value('id');

            if (! $categoryId) {
                $categoryId = (string) Str::uuid();
                DB::table('permission_categories')->insert([
                    'id' => $categoryId,
                    'name' => 'Staff Management',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            if (! DB::table('permissions')->where('name', 'manage-fp-devices')->where('guard_name', 'web')->exists()) {
                DB::table('permissions')->insert([
                    'id' => (string) Str::uuid(),
                    'name' => 'manage-fp-devices',
                    'guard_name' => 'web',
                    'description' => 'Register fingerprint devices, sync attendance and clear device logs',
                    'category_id' => $categoryId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        }
    }

    public function down(): void
    {
        DB::table('fp_devices')->whereNotNull('password')->orderBy('id')->each(function ($row) {
            if ($this->isEncrypted($row->password)) {
                DB::table('fp_devices')->where('id', $row->id)
                    ->update(['password' => Crypt::decryptString($row->password)]);
            }
        });

        Schema::table('fp_devices', function (Blueprint $table) {
            $table->string('password')->nullable()->comment('Password for Anviz devices')->change();
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
