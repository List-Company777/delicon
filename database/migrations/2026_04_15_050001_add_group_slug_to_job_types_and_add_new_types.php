<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // group_slug カラム追加
        Schema::table('job_types', function (Blueprint $table) {
            $table->string('group_slug', 100)->nullable()->after('slug')
                  ->comment('検索時の親カテゴリslug（設定するとLP検索で親カテゴリにも含まれる）');
        });

        // ① フロアスタッフ追加（黒服・ボーイの統合先）
        $floorStaffId = DB::table('job_types')->insertGetId([
            'name'          => 'フロアスタッフ',
            'slug'          => 'floor-staff',
            'group_slug'    => null,
            'target_gender' => 'male',
            'sort_order'    => 1,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        // 黒服・ボーイを使っている求人をフロアスタッフに移行
        $oldIds = DB::table('job_types')->whereIn('name', ['黒服', 'ボーイ'])->pluck('id');
        DB::table('jobs')->whereIn('job_type_id', $oldIds)->update(['job_type_id' => $floorStaffId]);

        // 黒服・ボーイ削除
        DB::table('job_types')->whereIn('name', ['黒服', 'ボーイ'])->delete();

        // 既存男性職種のsort_order調整
        DB::table('job_types')->where('name', 'キッチン')->update(['sort_order'  => 2]);
        DB::table('job_types')->where('name', 'ドライバー')->update(['sort_order' => 3]);
        DB::table('job_types')->where('name', '案内スタッフ')->update(['sort_order' => 4]);
        DB::table('job_types')->where('name', 'ヘアメイク')->update(['sort_order' => 99]);

        // ② 女性職種：ホステス・カウンターレディ・フロアレディ追加
        // group_slug='cast' → キャスト検索時にも含まれる
        DB::table('job_types')->insert([
            [
                'name'          => 'ホステス',
                'slug'          => 'hostess',
                'group_slug'    => 'cast',
                'target_gender' => 'female',
                'sort_order'    => 3,
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            [
                'name'          => 'カウンターレディ',
                'slug'          => 'counter-lady',
                'group_slug'    => 'cast',
                'target_gender' => 'female',
                'sort_order'    => 4,
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
            [
                'name'          => 'フロアレディ',
                'slug'          => 'floor-lady',
                'group_slug'    => 'cast',
                'target_gender' => 'female',
                'sort_order'    => 5,
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
        ]);
    }

    public function down(): void
    {
        DB::table('job_types')->whereIn('name', ['ホステス', 'カウンターレディ', 'フロアレディ'])->delete();
        DB::table('job_types')->where('name', 'フロアスタッフ')->delete();

        Schema::table('job_types', function (Blueprint $table) {
            $table->dropColumn('group_slug');
        });
    }
};
