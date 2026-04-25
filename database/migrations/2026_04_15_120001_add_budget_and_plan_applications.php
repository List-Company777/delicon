<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // shops に残高カラムを追加
        Schema::table('shops', function (Blueprint $table) {
            $table->unsignedInteger('budget_balance')->default(0)->after('bid_price')
                  ->comment('クリック課金残高（円）。0になったらbid_priceを10にリセット');
        });

        // 有料プラン申し込みテーブル
        Schema::create('shop_plan_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('amount');                // 申し込み金額（円）
            $table->unsignedInteger('bid_price_requested');   // 希望入札単価
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('note')->nullable();                 // admin メモ
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['shop_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_plan_applications');
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn('budget_balance');
        });
    }
};
