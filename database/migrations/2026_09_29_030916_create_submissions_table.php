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
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('domain_id')->constrained()->cascadeOnDelete();
            $table->text('target_url');
            $table->string('screenshot_path');
            $table->string('comment_type')->default('approved_live'); // 'approved_live' or 'pending_moderation'
            $table->string('review_status')->default('pending')->index(); // 'pending', 'approved', 'rejected'
            $table->text('rejection_reason')->nullable();
            $table->decimal('rate_amount', 10, 2)->default(700.00);
            $table->boolean('is_paid')->default(false)->index();
            $table->unsignedBigInteger('payout_id')->nullable()->index();
            $table->timestamp('paid_at')->nullable();
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
        Schema::dropIfExists('submissions');
    }
};
