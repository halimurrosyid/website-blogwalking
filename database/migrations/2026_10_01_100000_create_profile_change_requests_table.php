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
        Schema::create('profile_change_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Old vs New Name
            $table->string('old_name')->nullable();
            $table->string('new_name')->nullable();

            // Old vs New Phone
            $table->string('old_phone')->nullable();
            $table->string('new_phone')->nullable();

            // Old vs New Bank Details
            $table->string('old_bank_name')->nullable();
            $table->string('new_bank_name')->nullable();
            $table->string('old_bank_account_number')->nullable();
            $table->string('new_bank_account_number')->nullable();
            $table->string('old_bank_account_name')->nullable();
            $table->string('new_bank_account_name')->nullable();

            // Optional new bank book or supporting document
            $table->string('bank_book_path')->nullable();

            // Requester reason
            $table->text('reason')->nullable();

            // Status & Approval workflow
            $table->string('status')->default('pending'); // 'pending', 'approved', 'rejected'
            $table->text('admin_notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profile_change_requests');
    }
};
