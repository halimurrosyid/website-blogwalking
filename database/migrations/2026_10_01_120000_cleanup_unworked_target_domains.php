<?php

use App\Models\Domain;
use App\Models\TargetUrl;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Unlink target URLs from domains that have never been worked on (0 submissions and url_count = 0)
        TargetUrl::whereHas('domain', function ($query) {
            $query->where('url_count', 0)->doesntHave('submissions');
        })->update(['domain_id' => null]);

        // Delete unworked dummy domains
        Domain::where('url_count', 0)->doesntHave('submissions')->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse needed as unworked dummy records do not need restoration
    }
};
