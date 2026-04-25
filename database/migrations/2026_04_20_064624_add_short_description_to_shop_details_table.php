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
            $table->string('short_description', 30)->nullable()->after('content');
        });
    }

    public function down(): void
    {
        Schema::table('shop_details', function (Blueprint $table) {
            $table->dropColumn('short_description');
        });
    }
};
