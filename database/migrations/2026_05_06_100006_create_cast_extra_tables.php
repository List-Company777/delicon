<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // 複数画像
        Schema::create('cast_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cast_id');
            $table->string('img_path', 255);
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->boolean('is_main')->default(false);
            $table->timestamps();
            $table->foreign('cast_id')->references('id')->on('casts')->onDelete('cascade');
            $table->index(['cast_id', 'sort_order']);
        });

        // 出勤スケジュール
        Schema::create('cast_schedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cast_id');
            $table->date('work_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('note', 200)->nullable();
            $table->timestamps();
            $table->foreign('cast_id')->references('id')->on('casts')->onDelete('cascade');
            $table->index(['cast_id', 'work_date']);
            $table->index('work_date');
        });

        // 口コミ・評価
        Schema::create('cast_reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cast_id');
            $table->unsignedBigInteger('shop_id');
            $table->string('nickname', 50)->default('名無しさん');
            $table->unsignedTinyInteger('rating');
            $table->text('body');
            $table->boolean('is_approved')->default(false);
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
            $table->foreign('cast_id')->references('id')->on('casts')->onDelete('cascade');
            $table->index(['cast_id', 'is_approved']);
        });

        // お気に入り（セッションベース）
        Schema::create('favorites', function (Blueprint $table) {
            $table->id();
            $table->string('session_id', 100)->index();
            $table->unsignedBigInteger('cast_id');
            $table->timestamps();
            $table->foreign('cast_id')->references('id')->on('casts')->onDelete('cascade');
            $table->unique(['session_id', 'cast_id']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('favorites');
        Schema::dropIfExists('cast_reviews');
        Schema::dropIfExists('cast_schedules');
        Schema::dropIfExists('cast_images');
    }
};
