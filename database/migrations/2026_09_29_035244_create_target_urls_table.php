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
        Schema::create('target_urls', function (Blueprint $table) {
            $table->id();
            $table->text('url');
            $table->foreignId('domain_id')->nullable()->constrained('domains')->nullOnDelete();
            $table->string('root_domain')->index();
            $table->string('keyword')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('available')->index(); // 'available', 'in_progress', 'completed', 'skipped', 'domain_full'
            $table->foreignId('taken_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('taken_at')->nullable();
            $table->foreignId('submission_id')->nullable()->constrained('submissions')->nullOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('submissions', function (Blueprint $table) {
            $table->foreignId('target_url_id')->nullable()->after('domain_id')->constrained('target_urls')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropForeign(['target_url_id']);
            $table->dropColumn('target_url_id');
        });

        Schema::dropIfExists('target_urls');
    }
};
