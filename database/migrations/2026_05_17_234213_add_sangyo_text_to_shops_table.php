<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->string('sangyo_text1', 30)->nullable()->after('catche');
            $table->string('sangyo_text2', 30)->nullable()->after('sangyo_text1');
            $table->string('sangyo_text3', 30)->nullable()->after('sangyo_text2');
        });
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn(['sangyo_text1', 'sangyo_text2', 'sangyo_text3']);
        });
    }
};
