<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        Schema::table('shops', function (Blueprint $table) {
            $table->date('paid_since')->nullable()->after('plan');
        });
        // 既存の有料店舗は created_at を起点にセット
        DB::statement("UPDATE shops SET paid_since = DATE(created_at) WHERE plan >= 3");
    }
    public function down(): void {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn('paid_since');
        });
    }
};
