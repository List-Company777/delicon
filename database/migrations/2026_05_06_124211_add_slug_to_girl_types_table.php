<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('girl_types', function (Blueprint $table) {
            $table->string('slug', 60)->unique()->nullable()->after('name');
        });

        DB::table('girl_types')->upsert([
            ['name' => 'ロリ系',         'slug' => 'rori'],
            ['name' => 'キレイ系',        'slug' => 'kirei'],
            ['name' => 'カワイイ系',       'slug' => 'kawaii'],
            ['name' => 'エロカワ系',       'slug' => 'erokawa'],
            ['name' => 'セクシー系',       'slug' => 'sexy'],
            ['name' => 'ギャル系',         'slug' => 'gal'],
            ['name' => 'モデル系',         'slug' => 'model'],
            ['name' => 'お姉さん系',       'slug' => 'oneesan'],
            ['name' => '熟女系',           'slug' => 'jukujo'],
            ['name' => '人妻系',           'slug' => 'hitozuma'],
            ['name' => 'ドM',              'slug' => 'doemu'],
            ['name' => '女王様・ドＳ',     'slug' => 'joosama'],
            ['name' => '癒し系',           'slug' => 'iyashi'],
            ['name' => '痴女系',           'slug' => 'chijo'],
            ['name' => 'ガイジン系',       'slug' => 'gaijin'],
            ['name' => '清楚系',           'slug' => 'seiso'],
            ['name' => 'ニューハーフ・性転換', 'slug' => 'newhalfu'],
            ['name' => 'AV女優',           'slug' => 'av'],
            ['name' => '素人系',           'slug' => 'shirouto'],
            ['name' => 'OL系',             'slug' => 'ol'],
            ['name' => 'キャバ系',         'slug' => 'kyaba'],
        ], ['name'], ['slug']);
    }

    public function down(): void
    {
        Schema::table('girl_types', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
