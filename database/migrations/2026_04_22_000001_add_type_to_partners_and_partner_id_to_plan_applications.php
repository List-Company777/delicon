<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->enum('type', ['referral', 'management'])->default('referral')->after('id');
        });

        Schema::table('shop_plan_applications', function (Blueprint $table) {
            $table->foreignId('partner_id')->nullable()->after('shop_id')
                  ->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('shop_plan_applications', function (Blueprint $table) {
            $table->dropForeign(['partner_id']);
            $table->dropColumn('partner_id');
        });

        Schema::table('partners', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
