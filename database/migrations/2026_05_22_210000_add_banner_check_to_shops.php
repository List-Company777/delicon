<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            if (!Schema::hasColumn('shops', 'banner_ok')) {
                $table->tinyInteger('banner_ok')->nullable()->after('is_banner_plan');
            }
            if (!Schema::hasColumn('shops', 'banner_checked_at')) {
                $table->timestamp('banner_checked_at')->nullable()->after('banner_ok');
            }
        });
    }
    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn(['banner_ok', 'banner_checked_at']);
        });
    }
};
