<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('leave_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('key')->unique(); // Setting key
            $table->text('value')->nullable(); // Setting value (JSON for complex settings)
            $table->string('type')->default('string'); // string, boolean, json, integer
            $table->string('category')->default('general'); // general, acting_assignment, approval, notification
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Insert default settings
        DB::table('leave_settings')->insert([
            [
                'id' => \Illuminate\Support\Str::uuid(),
                'key' => 'acting_assignment_enabled',
                'value' => '1',
                'type' => 'boolean',
                'category' => 'acting_assignment',
                'description' => 'Enable acting assignments for leave requests',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => \Illuminate\Support\Str::uuid(),
                'key' => 'acting_assignment_mandatory',
                'value' => '0',
                'type' => 'boolean',
                'category' => 'acting_assignment',
                'description' => 'Make acting assignment mandatory for leave requests exceeding certain days',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => \Illuminate\Support\Str::uuid(),
                'key' => 'acting_assignment_min_days',
                'value' => '5',
                'type' => 'integer',
                'category' => 'acting_assignment',
                'description' => 'Minimum leave days to require acting assignment',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => \Illuminate\Support\Str::uuid(),
                'key' => 'acting_assignment_auto_approve',
                'value' => '0',
                'type' => 'boolean',
                'category' => 'acting_assignment',
                'description' => 'Auto-approve acting assignments when leave is approved',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => \Illuminate\Support\Str::uuid(),
                'key' => 'acting_assignment_notify_employee',
                'value' => '1',
                'type' => 'boolean',
                'category' => 'acting_assignment',
                'description' => 'Notify acting employee when assignment is created/approved',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => \Illuminate\Support\Str::uuid(),
                'key' => 'acting_assignment_designations',
                'value' => '[]',
                'type' => 'json',
                'category' => 'acting_assignment',
                'description' => 'Designations that require acting assignments (empty = all)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_settings');
    }
};
