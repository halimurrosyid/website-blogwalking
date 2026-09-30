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
        Schema::table('domains', function (Blueprint $table) {
            $table->unsignedTinyInteger('da')->nullable()->after('ip_subnet')->comment('Domain Authority (Moz)');
            $table->unsignedTinyInteger('pa')->nullable()->after('da')->comment('Page Authority (Moz)');
            $table->unsignedTinyInteger('dr')->nullable()->after('pa')->comment('Domain Rating (Ahrefs)');
            $table->string('pr', 10)->nullable()->after('dr')->comment('PageRank / Score');
            $table->timestamp('seo_updated_at')->nullable()->after('pr')->comment('Last SEO metrics fetch time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('domains', function (Blueprint $table) {
            $table->dropColumn(['da', 'pa', 'dr', 'pr', 'seo_updated_at']);
        });
    }
};
