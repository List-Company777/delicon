<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('shop_external_urls', function (Blueprint $table) {
            if (!Schema::hasColumn('shop_external_urls', 'url_status')) {
                $table->smallInteger('url_status')->nullable()->after('url');
            }
            if (!Schema::hasColumn('shop_external_urls', 'url_checked_at')) {
                $table->timestamp('url_checked_at')->nullable()->after('url_status');
            }
        });
    }
    public function down(): void
    {
        Schema::table('shop_external_urls', function (Blueprint $table) {
            $table->dropColumn(['url_status', 'url_checked_at']);
        });
    }
};
