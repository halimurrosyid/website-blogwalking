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
        Schema::create('domains', function (Blueprint $table) {
            $table->id();
            $table->string('root_domain')->unique(); // e.g. "tribunnews.com", "uny.ac.id"
            $table->string('tld')->index(); // e.g. ".ac.id", ".co.id", ".com"
            $table->unsignedInteger('url_count')->default(0);
            $table->unsignedInteger('max_limit')->default(5);
            $table->boolean('is_locked')->default(false)->index();
            $table->timestamp('last_reset_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('domains');
    }
};
