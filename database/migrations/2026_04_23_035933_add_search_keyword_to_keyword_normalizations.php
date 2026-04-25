<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('keyword_normalizations', function (Blueprint $table) {
            $table->string('search_keyword', 200)->nullable()->after('genre_id');
        });
    }

    public function down(): void
    {
        Schema::table('keyword_normalizations', function (Blueprint $table) {
            $table->dropColumn('search_keyword');
        });
    }
};
