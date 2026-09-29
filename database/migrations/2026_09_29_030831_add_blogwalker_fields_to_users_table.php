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
            $table->string('role')->default('blogwalker')->after('email'); // 'admin' or 'blogwalker'
            $table->decimal('default_rate', 10, 2)->default(700.00)->after('role');
            $table->string('phone')->nullable()->after('default_rate');
            $table->boolean('is_active')->default(true)->after('phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'default_rate', 'phone', 'is_active']);
        });
    }
};
