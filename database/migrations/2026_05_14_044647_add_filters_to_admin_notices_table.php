<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admin_notices', function (Blueprint $table) {
            $table->unsignedBigInteger('filter_pref_id')->nullable()->after('target');
            $table->tinyInteger('filter_plan')->unsigned()->nullable()->after('filter_pref_id');
        });
    }

    public function down(): void
    {
        Schema::table('admin_notices', function (Blueprint $table) {
            $table->dropColumn(['filter_pref_id', 'filter_plan']);
        });
    }
};
