<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->date('plan_expires_on')->nullable()->after('plan4_since');
        });

        Schema::table('shop_plan_applications', function (Blueprint $table) {
            $table->enum('application_type', ['new', 'renewal'])->default('new')->after('plan');
            $table->date('effective_date')->nullable()->after('application_type');
            $table->date('expires_on')->nullable()->after('effective_date');
        });
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn('plan_expires_on');
        });
        Schema::table('shop_plan_applications', function (Blueprint $table) {
            $table->dropColumn(['application_type', 'effective_date', 'expires_on']);
        });
    }
};
