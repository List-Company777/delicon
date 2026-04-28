<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shop_details', function (Blueprint $table) {
            $table->index(['status', 'has_karaoke']);
            $table->index(['status', 'has_private_room']);
        });
    }

    public function down(): void
    {
        Schema::table('shop_details', function (Blueprint $table) {
            $table->dropIndex(['status', 'has_karaoke']);
            $table->dropIndex(['status', 'has_private_room']);
        });
    }
};
