<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shop_plan_applications', function (Blueprint $table) {
            $table->tinyInteger('plan')->unsigned()->nullable()->after('partner_id');
            $table->integer('amount')->unsigned()->default(0)->change();
            $table->integer('bid_price_requested')->unsigned()->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('shop_plan_applications', function (Blueprint $table) {
            $table->dropColumn('plan');
        });
    }
};
