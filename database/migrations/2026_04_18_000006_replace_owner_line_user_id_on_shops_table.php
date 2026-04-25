<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn('owner_line_user_id');
            $table->string('line_for_business', 50)->nullable()->after('line_id');
            $table->string('line_for_female',   50)->nullable()->after('line_for_business');
            $table->string('line_for_male',     50)->nullable()->after('line_for_female');
        });
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn(['line_for_business', 'line_for_female', 'line_for_male']);
            $table->string('owner_line_user_id', 50)->nullable()->after('line_id');
        });
    }
};
