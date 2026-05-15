<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->date('plan1_since')->nullable()->after('paid_since'); // VIP継続開始日
            $table->date('plan2_since')->nullable()->after('plan1_since'); // ミドル継続開始日
            $table->date('plan3_since')->nullable()->after('plan2_since'); // ベーシック継続開始日
            $table->date('plan4_since')->nullable()->after('plan3_since'); // 無料上位継続開始日
        });
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn(['plan1_since', 'plan2_since', 'plan3_since', 'plan4_since']);
        });
    }
};
