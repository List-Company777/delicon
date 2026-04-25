<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            // 店舗メイン画像（営業面への露出用）
            $table->string('main_image', 255)->nullable()->after('logo_image');
            // 最寄り駅情報（路線・駅名・徒歩分）
            $table->string('nearest_line', 100)->nullable()->after('address');          // 路線
            $table->string('nearest_station_name', 100)->nullable()->after('nearest_line'); // 駅名
            $table->unsignedTinyInteger('nearest_station_walk')->nullable()->after('nearest_station_name'); // 徒歩分
        });
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn(['main_image', 'nearest_line', 'nearest_station_name', 'nearest_station_walk']);
        });
    }
};
