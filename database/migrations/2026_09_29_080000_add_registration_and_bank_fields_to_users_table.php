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
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->unique()->after('name');
            $table->string('bank_name')->nullable()->after('phone');
            $table->string('bank_account_number')->nullable()->after('bank_name');
            $table->string('bank_account_name')->nullable()->after('bank_account_number');
            $table->string('id_card_path')->nullable()->after('bank_account_name');
            $table->string('bank_book_path')->nullable()->after('id_card_path');
            $table->string('approval_status')->default('approved')->after('is_active'); // 'pending', 'approved', 'rejected'
            $table->text('rejection_reason')->nullable()->after('approval_status');
            $table->timestamp('approved_at')->nullable()->after('rejection_reason');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete()->after('approved_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn([
                'username',
                'bank_name',
                'bank_account_number',
                'bank_account_name',
                'id_card_path',
                'bank_book_path',
                'approval_status',
                'rejection_reason',
                'approved_at',
                'approved_by',
            ]);
        });
    }
};
