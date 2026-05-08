<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedTinyInteger('pref_age_min')->nullable()->after('preferred_times');
            $table->unsignedTinyInteger('pref_age_max')->nullable()->after('pref_age_min');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['pref_age_min', 'pref_age_max']);
        });
    }
};
