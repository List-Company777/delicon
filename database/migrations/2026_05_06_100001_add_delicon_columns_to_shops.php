<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('shops', function (Blueprint $table) {
            // kanaは既存、nameをshop_nameの代わりに使用
            // delicon固有カラムを追加
            $table->string('base', 100)->nullable();
            $table->string('catche', 200)->nullable();
            $table->text('system_text')->nullable();
            $table->text('coupon')->nullable();
            $table->string('open_time', 50)->nullable();
            $table->string('close_time', 50)->nullable();
            $table->boolean('all_time')->default(false);
            $table->string('rest_day', 100)->nullable();
            $table->unsignedInteger('price_60')->nullable();
            $table->unsignedInteger('price_90')->nullable();
            $table->unsignedInteger('price_120')->nullable();
            $table->unsignedInteger('price_high')->nullable();
            $table->text('eigyo_area')->nullable();
            $table->string('eigyo_space', 200)->nullable();
            $table->unsignedInteger('shop_type_id')->nullable();
            $table->unsignedInteger('shop_type_id2')->nullable();
            $table->string('shop_file_name', 255)->nullable();
            $table->integer('ranking_count')->default(0);
            $table->integer('old_id')->nullable()->unique();
        });
    }
    public function down(): void {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn(['base','catche','system_text','coupon',
                'open_time','close_time','all_time','rest_day',
                'price_60','price_90','price_120','price_high',
                'eigyo_area','eigyo_space','shop_type_id','shop_type_id2',
                'shop_file_name','ranking_count','old_id']);
        });
    }
};
