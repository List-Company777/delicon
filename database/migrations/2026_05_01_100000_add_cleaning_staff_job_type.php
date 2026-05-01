<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('job_types')->insert([
            'slug'          => 'cleaning',
            'name'          => '清掃スタッフ',
            'role_type'     => 'staff',
            'target_gender' => 'male',
            'sort_order'    => 110,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('job_types')->where('slug', 'cleaning')->delete();
    }
};
