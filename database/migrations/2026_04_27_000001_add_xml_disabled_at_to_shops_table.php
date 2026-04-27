<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->timestamp('xml_disabled_at')
                  ->nullable()
                  ->after('xml_plan_activated_at')
                  ->comment('XML連携が終了した日時（生存確認タイマーの起点として使用）');
        });
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn('xml_disabled_at');
        });
    }
};
