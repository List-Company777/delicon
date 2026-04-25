<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_types', function (Blueprint $table) {
            $table->enum('role_type', ['cast', 'staff', 'both'])->default('cast')->after('group_slug');
        });

        DB::table('job_types')->whereIn('slug', [
            'cast', 'taiken', 'hostess', 'counter-lady', 'floor-lady', 'mens-cast',
        ])->update(['role_type' => 'cast']);

        DB::table('job_types')->whereIn('slug', [
            'floor-staff', 'kurofuku', 'boy', 'kanbu',
            'kitchen', 'driver', 'annai', 'escort',
            'hair-makeup', 'gaihan', 'casher', 'bartender',
        ])->update(['role_type' => 'staff']);

        DB::table('job_types')->whereIn('slug', [
            'hibarai', 'mikeiken',
        ])->update(['role_type' => 'both']);
    }

    public function down(): void
    {
        Schema::table('job_types', function (Blueprint $table) {
            $table->dropColumn('role_type');
        });
    }
};
