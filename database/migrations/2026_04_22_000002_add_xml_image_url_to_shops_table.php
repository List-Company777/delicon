<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->string('xml_image_url', 500)->nullable()->after('xml_plan_activated_at')
                  ->comment('XMLインポート元の画像URL（変更時のみ再ダウンロード）');
        });
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn('xml_image_url');
        });
    }
};
