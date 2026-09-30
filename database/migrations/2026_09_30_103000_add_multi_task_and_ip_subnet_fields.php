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
        // 1. Add IP address and Subnet detection to domains table
        Schema::table('domains', function (Blueprint $table) {
            $table->string('ip_address', 45)->nullable()->after('tld');
            $table->string('ip_subnet', 45)->nullable()->index()->after('ip_address');
        });

        // 2. Add multi-task types, client target backlink, and custom reward amount to target_urls table
        Schema::table('target_urls', function (Blueprint $table) {
            $table->string('task_type')->default('comment')->index()->after('url');
            $table->text('client_url')->nullable()->after('task_type');
            $table->decimal('reward_amount', 10, 2)->nullable()->after('keyword');
        });

        // 3. Add multi-task details to submissions table
        Schema::table('submissions', function (Blueprint $table) {
            $table->string('task_type')->default('comment')->index()->after('target_url');
            $table->text('client_url')->nullable()->after('task_type');
            $table->string('keyword')->nullable()->after('client_url');
            $table->text('published_url')->nullable()->after('keyword');
            $table->string('platform')->nullable()->after('published_url');
            $table->unsignedInteger('domain_rating')->nullable()->after('platform');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropColumn([
                'task_type',
                'client_url',
                'keyword',
                'published_url',
                'platform',
                'domain_rating',
            ]);
        });

        Schema::table('target_urls', function (Blueprint $table) {
            $table->dropColumn([
                'task_type',
                'client_url',
                'reward_amount',
            ]);
        });

        Schema::table('domains', function (Blueprint $table) {
            $table->dropIndex(['ip_subnet']);
            $table->dropColumn([
                'ip_address',
                'ip_subnet',
            ]);
        });
    }
};
