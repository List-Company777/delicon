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
        Schema::table('shop_details', function (Blueprint $table) {
            $table->boolean('discount_first_set')->default(false)->after('has_private_room');
            $table->string('discount_custom', 200)->nullable()->after('discount_first_set');
        });
    }

    public function down(): void
    {
        Schema::table('shop_details', function (Blueprint $table) {
            $table->dropColumn(['discount_first_set', 'discount_custom']);
        });
    }
};
