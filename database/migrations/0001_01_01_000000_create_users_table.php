<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            // UUID primary key
            $table->uuid('id')->primary();

            // Personal Information
            $table->string('salutation')->nullable();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('surname');
            $table->enum('gender', ['Male', 'Female']);
            $table->date('dob')->nullable();
            $table->string('address')->nullable();
            $table->string('district')->nullable()->index();
            $table->string('region')->nullable()->index();
            $table->string('country')->default('Tanzania');
            $table->string('postal_code')->nullable();

            // Jobtitle
            $table->foreignUuid('jobtitle_id')->nullable();

            // Contact Information
            $table->string('email')->unique();
            $table->string('phone_number')->unique();

            // Authentication
            $table->string('username')->unique();
            $table->string('password');

            // Optional Profile Picture
            $table->string('profile_picture')->nullable();

            // Provider Details
            $table->string('provider_type')->nullable();
            $table->string('reg_number')->nullable()->index();
            $table->string('qualification')->nullable();

            // Spatie Permissions & Audit
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token', 6);
            $table->timestamp('expires_at');
            $table->boolean('used')->default(false);
            $table->timestamps();
        });

        Schema::create('two_factor_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('email')->index();
            $table->string('token', 6);
            $table->timestamp('expires_at');
            $table->boolean('used')->default(false);
            $table->timestamps();

            $table->index(['email', 'token']);
        });

        Schema::create('trusted_devices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('device_fingerprint')->unique();
            $table->string('device_name')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('trusted_at');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['user_id', 'device_fingerprint']);
            $table->index(['user_id', 'is_active']);
            $table->index('expires_at');
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignUuid('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trusted_devices');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('two_factor_tokens');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
