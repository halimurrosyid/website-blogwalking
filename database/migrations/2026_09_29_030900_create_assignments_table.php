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
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->json('allowed_tlds')->nullable(); // e.g. [".co.id", ".web.id", ".id"]
            $table->text('target_keywords')->nullable(); // e.g. "jasa backlink, seo website"
            $table->text('target_backlink_url')->nullable(); // e.g. "https://klien-a.com"
            $table->text('custom_instructions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
