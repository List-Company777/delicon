<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // 新着情報（旧events）
        Schema::create('shop_news', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shop_id');
            $table->text('body');
            $table->integer('old_id')->nullable()->unique();
            $table->timestamps();
            $table->foreign('shop_id')->references('id')->on('shops')->onDelete('cascade');
            $table->index(['shop_id', 'created_at']);
        });

        // ホテル情報
        Schema::create('hotels', function (Blueprint $table) {
            $table->id();
            $table->string('name', 250);
            $table->unsignedBigInteger('area_id')->nullable();
            $table->unsignedInteger('type_id')->nullable();
            $table->string('address', 250)->nullable();
            $table->string('tel', 50)->nullable();
            $table->string('url', 250)->nullable();
            $table->string('memo', 300)->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('hotels');
        Schema::dropIfExists('shop_news');
    }
};
