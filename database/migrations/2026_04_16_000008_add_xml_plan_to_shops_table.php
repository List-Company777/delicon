<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            // www.up-stage.info が XML 経由で管理する掲載プラン情報
            $table->unsignedInteger('xml_bid_price')
                  ->default(0)
                  ->after('xml_enabled')
                  ->comment('XML連携で設定された入札単価（0=フリープラン）');

            $table->unsignedInteger('xml_monthly_budget')
                  ->default(0)
                  ->after('xml_bid_price')
                  ->comment('XML連携で設定された月次予算チャージ額（円）');

            $table->timestamp('xml_plan_activated_at')
                  ->nullable()
                  ->after('xml_monthly_budget')
                  ->comment('XMLプラン有効化（0→有料）になった日時');
        });
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn(['xml_bid_price', 'xml_monthly_budget', 'xml_plan_activated_at']);
        });
    }
};
