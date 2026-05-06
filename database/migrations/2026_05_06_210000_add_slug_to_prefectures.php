<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prefectures', function (Blueprint $table) {
            $table->string('slug', 60)->nullable()->unique()->after('prefecture');
        });

        // slug を一括セット
        $slugs = [
            1  => 'tokyo-shinjuku',
            2  => 'tokyo-shibuya',
            3  => 'tokyo-ikebukuro',
            4  => 'tokyo-ueno',
            5  => 'tokyo-shinagawa',
            6  => 'tokyo-tachikawa',
            7  => 'kanagawa',
            8  => 'saitama',
            9  => 'chiba',
            10 => 'ibaraki',
            11 => 'tochigi',
            12 => 'gunma',
            13 => 'tokyo',
            14 => 'osaka-hokusetsu',
            15 => 'osaka-shin-osaka',
            16 => 'osaka-umeda',
            17 => 'osaka-namba',
            18 => 'osaka-higashi',
            19 => 'osaka-minamikawachi',
            20 => 'osaka-sakai',
            21 => 'osaka-sennan',
            22 => 'kyoto',
            23 => 'nara',
            24 => 'shiga',
            25 => 'hanshin',
            26 => 'kobe',
            27 => 'himeji',
            28 => 'wakayama',
            29 => 'nagoya',
            30 => 'owari',
            31 => 'mikawa',
            32 => 'gifu',
            33 => 'shizuoka-east',
            34 => 'shizuoka-west',
            35 => 'mie',
        ];
        foreach ($slugs as $id => $slug) {
            DB::table('prefectures')->where('id', $id)->update(['slug' => $slug]);
        }
    }

    public function down(): void
    {
        Schema::table('prefectures', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
