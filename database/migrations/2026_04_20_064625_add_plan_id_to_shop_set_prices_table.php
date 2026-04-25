<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Column plan_id may already exist (from a prior partial migration attempt).
        // Only add it if it doesn't exist yet.
        if (!Schema::hasColumn('shop_set_prices', 'plan_id')) {
            Schema::table('shop_set_prices', function (Blueprint $table) {
                $table->foreignId('plan_id')->nullable()->after('shop_id')
                      ->constrained('shop_price_plans')->nullOnDelete();
            });
        } else {
            // Column exists but FK may not — add just the FK
            Schema::table('shop_set_prices', function (Blueprint $table) {
                $table->foreign('plan_id')->references('id')->on('shop_price_plans')->nullOnDelete();
            });
        }

        // 既存データ移行: shop_id ごとにデフォルトプランを作成して plan_id を設定
        $shopIds = DB::table('shop_set_prices')->whereNull('plan_id')->distinct()->pluck('shop_id');
        foreach ($shopIds as $shopId) {
            $planId = DB::table('shop_price_plans')->insertGetId([
                'shop_id'    => $shopId,
                'name'       => null,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('shop_set_prices')
                ->where('shop_id', $shopId)
                ->whereNull('plan_id')
                ->update(['plan_id' => $planId]);
        }
    }

    public function down(): void
    {
        Schema::table('shop_set_prices', function (Blueprint $table) {
            $table->dropForeign(['plan_id']);
            $table->dropColumn('plan_id');
        });
    }
};
