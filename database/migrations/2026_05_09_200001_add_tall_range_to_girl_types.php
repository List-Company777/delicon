<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('girl_types', function (Blueprint $table) {
            $table->unsignedSmallInteger('tall_min')->nullable()->after('age_max');
            $table->unsignedSmallInteger('tall_max')->nullable()->after('tall_min');
            $table->unsignedInteger('body_type_id')->nullable()->after('tall_max');
        });

        DB::table('girl_types')->insert([
            ['name' => '長身・高身長', 'slug' => 'joshin', 'tall_min' => 170, 'tall_max' => null, 'age_min' => null, 'age_max' => null, 'body_type_id' => null],
            ['name' => '小柄・低身長', 'slug' => 'kogara', 'tall_min' => null, 'tall_max' => 150, 'age_min' => null, 'age_max' => null, 'body_type_id' => null],
            ['name' => '巨乳',         'slug' => 'kyonyuu',   'tall_min' => null, 'tall_max' => null, 'age_min' => null, 'age_max' => null, 'body_type_id' => 1],
            ['name' => '貧乳・ちっぱい','slug' => 'hinnyuu',  'tall_min' => null, 'tall_max' => null, 'age_min' => null, 'age_max' => null, 'body_type_id' => 2],
            ['name' => '爆乳',         'slug' => 'bakunyuu',  'tall_min' => null, 'tall_max' => null, 'age_min' => null, 'age_max' => null, 'body_type_id' => 16],
            ['name' => 'スレンダー',    'slug' => 'slender',   'tall_min' => null, 'tall_max' => null, 'age_min' => null, 'age_max' => null, 'body_type_id' => 5],
            ['name' => 'グラマー',      'slug' => 'glamour',   'tall_min' => null, 'tall_max' => null, 'age_min' => null, 'age_max' => null, 'body_type_id' => 8],
            ['name' => 'ちょいポチャ',  'slug' => 'choipocha', 'tall_min' => null, 'tall_max' => null, 'age_min' => null, 'age_max' => null, 'body_type_id' => 6],
            ['name' => '激ポチャ',      'slug' => 'gekipocha', 'tall_min' => null, 'tall_max' => null, 'age_min' => null, 'age_max' => null, 'body_type_id' => 7],
        ]);
    }

    public function down(): void
    {
        Schema::table('girl_types', function (Blueprint $table) {
            $table->dropColumn(['tall_min', 'tall_max', 'body_type_id']);
        });
    }
};
