<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            // 同一入札スコア内でのランダムローテーション用。30分ごとにシャッフルされる
            $table->unsignedSmallInteger('display_sort')->default(0)->after('bid_price');
            $table->index(['bid_price', 'display_sort'], 'shops_bid_display_idx');
        });
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropIndex('shops_bid_display_idx');
            $table->dropColumn('display_sort');
        });
    }
};
