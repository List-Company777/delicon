<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->tinyInteger('plan')->unsigned()->notNull()->default(5)->after('rank_score')
                  ->comment('1=paid1 2=paid2 3=paid3(migrated) 4=free+link 5=free');
        });
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn('plan');
        });
    }
};
