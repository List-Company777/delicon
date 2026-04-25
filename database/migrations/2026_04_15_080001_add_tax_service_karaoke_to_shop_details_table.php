<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shop_details', function (Blueprint $table) {
            $table->boolean('tax_included')->nullable()->after('all_you_can_drink')
                  ->comment('税表示：true=税込, false=税別, null=未設定');
            $table->string('service_charge', 50)->nullable()->after('tax_included')
                  ->comment('サービス料（例：10%、1,000円）');
            $table->boolean('has_karaoke')->default(false)->after('service_charge')
                  ->comment('カラオケ設備の有無');
        });
    }

    public function down(): void
    {
        Schema::table('shop_details', function (Blueprint $table) {
            $table->dropColumn(['tax_included', 'service_charge', 'has_karaoke']);
        });
    }
};
