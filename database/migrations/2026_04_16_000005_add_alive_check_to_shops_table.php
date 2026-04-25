<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->string('alive_check_token', 64)->nullable()->unique()->after('bid_price');
            $table->timestamp('alive_check_sent_at')->nullable()->after('alive_check_token');
            $table->timestamp('alive_confirmed_at')->nullable()->after('alive_check_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn(['alive_check_token', 'alive_check_sent_at', 'alive_confirmed_at']);
        });
    }
};
