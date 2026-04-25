<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('keyword_normalizations', function (Blueprint $table) {
            $table->foreignId('prefecture_id')->nullable()->constrained()->nullOnDelete()->after('area_id');
            $table->foreignId('genre_id')->nullable()->constrained()->nullOnDelete()->after('job_type_id');
        });
    }

    public function down(): void
    {
        Schema::table('keyword_normalizations', function (Blueprint $table) {
            $table->dropForeign(['prefecture_id']);
            $table->dropForeign(['genre_id']);
            $table->dropColumn(['prefecture_id', 'genre_id']);
        });
    }
};
