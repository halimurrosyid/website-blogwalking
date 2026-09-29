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
        // 1. Periods table
        Schema::create('periods', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. "Oktober 2026"
            $table->unsignedTinyInteger('month'); // 1 - 12
            $table->unsignedSmallInteger('year'); // 2026
            $table->unsignedInteger('min_target')->default(100); // Target minimal bulanan agar lolos
            $table->unsignedInteger('max_target')->nullable(); // Maksimal kuota bulanan jika ada
            $table->unsignedInteger('max_urls_per_domain')->default(5); // Maksimal URL per domain
            $table->string('status')->default('active')->index(); // 'active', 'closed'
            $table->date('starts_at');
            $table->date('ends_at');
            $table->timestamp('closed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 2. Global app settings
        Schema::create('app_settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // 3. Update assignments table with period, custom target & qualification
        Schema::table('assignments', function (Blueprint $table) {
            $table->foreignId('period_id')->nullable()->after('user_id')->constrained('periods')->cascadeOnDelete();
            $table->unsignedInteger('min_target')->nullable()->after('target_backlink_url');
            $table->unsignedInteger('max_target')->nullable()->after('min_target');
            $table->string('status')->default('active')->after('max_target')->index(); // 'active', 'qualified', 'disqualified', 'dispensed'
            $table->boolean('is_eligible_next_period')->default(true)->after('status');
            $table->text('qualification_notes')->nullable()->after('is_eligible_next_period');
        });

        // 4. Update submissions table with period_id
        Schema::table('submissions', function (Blueprint $table) {
            $table->foreignId('period_id')->nullable()->after('domain_id')->constrained('periods')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropForeign(['period_id']);
            $table->dropColumn('period_id');
        });

        Schema::table('assignments', function (Blueprint $table) {
            $table->dropForeign(['period_id']);
            $table->dropColumn([
                'period_id',
                'min_target',
                'max_target',
                'status',
                'is_eligible_next_period',
                'qualification_notes',
            ]);
        });

        Schema::dropIfExists('app_settings');
        Schema::dropIfExists('periods');
    }
};
