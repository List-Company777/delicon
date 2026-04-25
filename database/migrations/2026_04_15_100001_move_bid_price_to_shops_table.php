<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // shops に bid_price を追加（デフォルト10）
        Schema::table('shops', function (Blueprint $table) {
            $table->unsignedInteger('bid_price')->default(10)->after('status')
                  ->comment('検索結果掲載順の入札単価（円）');
        });

        // 既存データ移行：jobs と shop_details の bid_price の最大値を shop に引き継ぐ
        DB::statement("
            UPDATE shops s
            SET bid_price = GREATEST(
                COALESCE((SELECT MAX(j.bid_price) FROM jobs j WHERE j.shop_id = s.id), 10),
                COALESCE((SELECT sd.bid_price FROM shop_details sd WHERE sd.shop_id = s.id), 10)
            )
        ");

        // jobs と shop_details から bid_price を削除
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropColumn('bid_price');
        });

        Schema::table('shop_details', function (Blueprint $table) {
            $table->dropColumn('bid_price');
        });
    }

    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->unsignedInteger('bid_price')->default(10)->after('status');
        });

        Schema::table('shop_details', function (Blueprint $table) {
            $table->unsignedInteger('bid_price')->default(10)->after('status');
        });

        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn('bid_price');
        });
    }
};
