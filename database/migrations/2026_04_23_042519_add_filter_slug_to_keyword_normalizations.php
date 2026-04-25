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
        Schema::table('keyword_normalizations', function (Blueprint $table) {
            $table->string('filter_slug', 100)->nullable()->after('search_keyword');
        });
    }

    public function down(): void
    {
        Schema::table('keyword_normalizations', function (Blueprint $table) {
            $table->dropColumn('filter_slug');
        });
    }
};
