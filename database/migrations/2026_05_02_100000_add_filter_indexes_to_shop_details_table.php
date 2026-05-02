<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shop_details', function (Blueprint $table) {
            $table->index('all_you_can_drink');
            $table->index('has_karaoke');
            $table->index('has_private_room');
            $table->index('discount_first_set');
        });
    }

    public function down(): void
    {
        Schema::table('shop_details', function (Blueprint $table) {
            $table->dropIndex(['all_you_can_drink']);
            $table->dropIndex(['has_karaoke']);
            $table->dropIndex(['has_private_room']);
            $table->dropIndex(['discount_first_set']);
        });
    }
};
