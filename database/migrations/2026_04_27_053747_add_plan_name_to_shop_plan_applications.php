<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('shop_plan_applications', function (Blueprint $table) {
            $table->string('plan_name')->nullable()->after('note');
        });
    }

    public function down(): void
    {
        Schema::table('shop_plan_applications', function (Blueprint $table) {
            $table->dropColumn('plan_name');
        });
    }
};
