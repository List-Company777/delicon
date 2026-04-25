<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->unique()->constrained()->cascadeOnDelete(); // 1店舗1レコード
            $table->text('content')->nullable();               // 営業情報テキスト
            $table->string('set_price', 100)->nullable();      // セット料金
            $table->string('nomination_fee', 100)->nullable(); // 指名料
            $table->boolean('all_you_can_drink')->default(false); // 飲み放題
            $table->string('opening_hours', 50)->nullable();   // 営業開始時間
            $table->string('closing_hours', 50)->nullable();   // 営業終了時間
            $table->string('holiday', 100)->nullable();        // 定休日
            $table->json('image_paths')->nullable();           // 複数画像
            $table->enum('status', ['active', 'inactive'])->default('active');
            // ホットリンクオプション
            $table->boolean('is_hotlink')->default(false);
            $table->string('hotlink_url', 500)->nullable();
            $table->unsignedInteger('bid_price')->default(10);
            $table->unsignedInteger('click_count')->default(0);
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_details');
    }
};
