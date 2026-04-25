<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->enum('wage_type', ['hourly', 'daily', 'monthly'])
                  ->default('hourly')
                  ->after('hourly_wage_max')
                  ->comment('給与形態：時給/日給/月給');
        });
    }

    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropColumn('wage_type');
        });
    }
};
