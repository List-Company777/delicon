<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('genres', function (Blueprint $table) {
            $table->enum('default_gender', ['female', 'male', 'both'])
                  ->default('female')
                  ->after('slug')
                  ->comment('求人のsearch_groupデフォルト値');
        });

        // 各業種のデフォルト性別を設定
        $map = [
            'female' => ['キャバクラ', 'ガールズバー', 'スナック', 'ラウンジ', 'コンカフェ'],
            'male'   => ['ホストクラブ', 'ボーイズバー'],
            'both'   => ['クラブ', 'バー', 'ニューハーフ', 'パブ', '無料案内所'],
        ];

        foreach ($map as $gender => $names) {
            DB::table('genres')->whereIn('name', $names)
                ->update(['default_gender' => $gender]);
        }
    }

    public function down(): void
    {
        Schema::table('genres', function (Blueprint $table) {
            $table->dropColumn('default_gender');
        });
    }
};
