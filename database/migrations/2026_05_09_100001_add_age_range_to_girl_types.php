<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('girl_types', function (Blueprint $table) {
            $table->unsignedTinyInteger('age_min')->nullable()->after('slug');
            $table->unsignedTinyInteger('age_max')->nullable()->after('age_min');
        });

        DB::table('girl_types')->insert([
            ['name' => '還暦・六十路', 'slug' => 'kanreki',   'age_min' => 60, 'age_max' => 69],
            ['name' => '五十路',       'slug' => 'isoji',     'age_min' => 50, 'age_max' => 59],
            ['name' => '七十路以上',   'slug' => 'nanatoji',  'age_min' => 70, 'age_max' => null],
            ['name' => '超熟女',       'slug' => 'chojukujo', 'age_min' => 50, 'age_max' => null],
        ]);
    }

    public function down(): void
    {
        DB::table('girl_types')->whereIn('slug', ['kanreki', 'isoji', 'nanatoji', 'chojukujo'])->delete();
        Schema::table('girl_types', function (Blueprint $table) {
            $table->dropColumn(['age_min', 'age_max']);
        });
    }
};
