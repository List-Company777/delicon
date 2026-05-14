<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->boolean('is_banner_plan')->default(false)->after('plan');
        });

        // 旧サイト移行のplan=3店舗（pay=trueで移行した41件）はバナー由来
        DB::table('shops')
            ->whereNotNull('old_id')
            ->where('plan', 3)
            ->update(['is_banner_plan' => true]);
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn('is_banner_plan');
        });
    }
};
