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
            $table->string('website_url', 500)->nullable()->after('content');
        });
    }

    public function down(): void
    {
        Schema::table('shop_details', function (Blueprint $table) {
            $table->dropColumn('website_url');
        });
    }
};
