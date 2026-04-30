<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('search_page_views', function (Blueprint $table) {
            $table->string('source', 20)->default('direct')->after('job_slug');
        });

        // 既存レコードのPKを拡張（source列を含む複合PK）
        Schema::table('search_page_views', function (Blueprint $table) {
            $table->dropPrimary(['gender', 'area_slug', 'job_slug', 'date']);
            $table->primary(['gender', 'area_slug', 'job_slug', 'source', 'date']);
        });
    }

    public function down(): void
    {
        Schema::table('search_page_views', function (Blueprint $table) {
            $table->dropPrimary(['gender', 'area_slug', 'job_slug', 'source', 'date']);
            $table->primary(['gender', 'area_slug', 'job_slug', 'date']);
            $table->dropColumn('source');
        });
    }
};
