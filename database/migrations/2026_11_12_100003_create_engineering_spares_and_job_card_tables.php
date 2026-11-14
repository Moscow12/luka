<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eng_spares_requisitions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('number', 20)->unique();
            $table->foreignUuid('job_id')->constrained('eng_jobs')->cascadeOnDelete();
            $table->foreignUuid('requested_by')->constrained('users');
            $table->dateTime('requested_at');
            $table->enum('status', ['requested', 'partially_issued', 'issued', 'to_procure', 'closed'])->default('requested');
            $table->timestamps();

            $table->index('status');
        });

        Schema::create('eng_spares_lines', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('spares_requisition_id')->constrained('eng_spares_requisitions')->cascadeOnDelete();
            $table->unsignedSmallInteger('seq');
            // Null product => non-stock item, entered free-text and flagged for procurement
            $table->foreignUuid('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('spare_name', 200);
            $table->string('uom', 40)->nullable();
            $table->string('item_code', 100)->nullable();
            $table->decimal('qty_requested', 10, 2);
            $table->decimal('qty_issued', 10, 2)->default(0);
            $table->date('date_consumed')->nullable();
            $table->enum('status', ['requested', 'issued', 'partial', 'rejected', 'to_procure'])->default('requested');
            $table->string('reject_reason', 300)->nullable();
            $table->foreignUuid('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('issued_at')->nullable();
            $table->timestamps();
        });

        Schema::create('eng_job_cards', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('number', 20)->unique();
            $table->foreignUuid('job_id')->unique()->constrained('eng_jobs')->cascadeOnDelete();
            $table->text('complaints_faults')->nullable();
            $table->text('defects')->nullable();
            $table->text('diagnosis_action')->nullable();
            $table->enum('failure_reason', [
                'wear_and_tear', 'rough_handling', 'poor_installation', 'water_penetration',
                'mains_unstable', 'dirty', 'wind_and_storm', 'other',
            ])->nullable();
            $table->string('failure_reason_text', 300)->nullable();
            $table->date('date_part_requested')->nullable();
            $table->foreignUuid('requesting_engineer_id')->constrained('employees');
            $table->text('description')->nullable();
            $table->text('comments')->nullable();
            $table->decimal('labour_charge', 15, 2)->default(0);
            $table->decimal('labour_qty', 10, 2)->default(0);
            $table->decimal('fabrication_charge', 15, 2)->default(0);
            $table->decimal('fabrication_qty', 10, 2)->default(0);
            // Stored for reporting; always recomputed by the system, never typed
            $table->decimal('sub_total', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->enum('status', [
                'draft', 'pending_certification', 'pending_authorisation', 'approved',
                'returned', 'rejected', 'purchasing', 'delivered', 'closed',
            ])->default('draft');
            $table->unsignedSmallInteger('revision')->default(1);
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();

            // Purchasing information, owned by the purchasing office
            $table->foreignUuid('local_purchase_order_id')->nullable()->constrained('local_purchase_orders')->nullOnDelete();
            $table->string('order_number', 60)->nullable();
            $table->date('order_date')->nullable();
            $table->string('supplier_name', 150)->nullable();
            $table->date('proforma_requested_at')->nullable();
            $table->date('proforma_received_at')->nullable();
            $table->date('spare_procured_at')->nullable();
            $table->date('tender_opened_at')->nullable();
            $table->date('tender_approved_at')->nullable();
            $table->date('delivery_expected_at')->nullable();
            $table->date('delivery_actual_at')->nullable();
            $table->string('delay_reason_order', 300)->nullable();
            $table->string('delay_reason_delivery', 300)->nullable();
            $table->timestamps();

            $table->index('status');
        });

        Schema::create('eng_job_card_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('job_card_id')->constrained('eng_job_cards')->cascadeOnDelete();
            $table->unsignedSmallInteger('seq');
            $table->string('description', 200);
            $table->string('uom', 40)->nullable();
            $table->string('item_code', 100)->nullable();
            $table->decimal('quantity', 10, 2);
            $table->decimal('price', 15, 2)->default(0);
            $table->timestamps();
        });

        // Append-only certification / authorisation trail
        Schema::create('eng_job_card_approvals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('job_card_id')->constrained('eng_job_cards')->cascadeOnDelete();
            $table->unsignedSmallInteger('revision');
            $table->enum('stage', ['certify', 'authorise']);
            $table->enum('decision', ['approved', 'returned', 'rejected']);
            $table->foreignUuid('actor_id')->constrained('users');
            $table->text('comment')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eng_job_card_approvals');
        Schema::dropIfExists('eng_job_card_items');
        Schema::dropIfExists('eng_job_cards');
        Schema::dropIfExists('eng_spares_lines');
        Schema::dropIfExists('eng_spares_requisitions');
    }
};
