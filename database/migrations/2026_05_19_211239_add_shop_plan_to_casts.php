<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('casts', 'sort_rank')) {
            Schema::table('casts', function (Blueprint $table) {
                $table->unsignedInteger('sort_rank')->nullable()->after('cast_score');
                $table->index(['status', 'sort_rank'], 'casts_status_sort_rank_index');
            });
        }
    }

    public function down(): void
    {
        Schema::table('casts', function (Blueprint $table) {
            $table->dropIndex('casts_status_sort_rank_index');
            $table->dropColumn('sort_rank');
        });
    }
};
