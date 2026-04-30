<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 30日範囲クエリ（TopController, DashboardController）で使用
        Schema::table('search_page_views', function (Blueprint $table) {
            $table->index('date', 'spv_date_idx');
        });

        // PartnerPortal の WHERE area_id IN (...) AND status = 'active' で使用
        Schema::table('shops', function (Blueprint $table) {
            $table->index(['area_id', 'status'], 'shops_area_status_idx');
        });

        // keyword_normalizations の genre_id / prefecture_id — whereHas フィルタ用
        Schema::table('keyword_normalizations', function (Blueprint $table) {
            $table->index('genre_id',      'kn_genre_idx');
            $table->index('prefecture_id', 'kn_prefecture_idx');
        });
    }

    public function down(): void
    {
        Schema::table('search_page_views', fn(Blueprint $t) => $t->dropIndex('spv_date_idx'));
        Schema::table('shops', fn(Blueprint $t) => $t->dropIndex('shops_area_status_idx'));
        Schema::table('keyword_normalizations', function (Blueprint $t) {
            $t->dropIndex('kn_genre_idx');
            $t->dropIndex('kn_prefecture_idx');
        });
    }
};
