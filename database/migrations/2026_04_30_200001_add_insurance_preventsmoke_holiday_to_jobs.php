<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->text('insurance')->nullable()->after('job_benefits');
            $table->text('preventsmoke')->nullable()->after('insurance');
            $table->text('holiday')->nullable()->after('preventsmoke');
        });
    }

    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropColumn(['insurance', 'preventsmoke', 'holiday']);
        });
    }
};
