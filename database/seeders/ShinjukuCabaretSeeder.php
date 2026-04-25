<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ShinjukuCabaretSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // ─── 都道府県 ────────────────────────────────────────
        DB::table('prefectures')->updateOrInsert(
            ['slug' => 'tokyo'],
            ['name' => '東京都', 'sort_order' => 1, 'updated_at' => $now, 'created_at' => $now]
        );
        $prefectureId = DB::table('prefectures')->where('slug', 'tokyo')->value('id');

        // ─── エリア ──────────────────────────────────────────
        DB::table('areas')->updateOrInsert(
            ['slug' => 'shinjuku'],
            ['prefecture_id' => $prefectureId, 'name' => '新宿', 'sort_order' => 1, 'updated_at' => $now, 'created_at' => $now]
        );
        $areaId = DB::table('areas')->where('slug', 'shinjuku')->value('id');

        // ─── 駅 ──────────────────────────────────────────────
        DB::table('stations')->updateOrInsert(
            ['slug' => 'shinjuku-st'],
            ['prefecture_id' => $prefectureId, 'area_id' => $areaId, 'name' => '新宿駅', 'line' => 'JR・各線', 'updated_at' => $now, 'created_at' => $now]
        );
        $stationId = DB::table('stations')->where('slug', 'shinjuku-st')->value('id');

        // ─── ジャンル ─────────────────────────────────────────
        DB::table('genres')->updateOrInsert(
            ['slug' => 'cabaret'],
            ['name' => 'キャバクラ', 'sort_order' => 1, 'updated_at' => $now, 'created_at' => $now]
        );
        $genreId = DB::table('genres')->where('slug', 'cabaret')->value('id');

        // ─── 職種（女性向け） ──────────────────────────────────
        $femaleJobTypes = [];
        foreach ([
            ['name' => 'キャスト', 'slug' => 'cast',   'sort' => 1],
            ['name' => '体入',     'slug' => 'taiken',  'sort' => 2],
            ['name' => 'ヘアメイク', 'slug' => 'hair-makeup', 'sort' => 3],
        ] as $jt) {
            DB::table('job_types')->updateOrInsert(
                ['slug' => $jt['slug']],
                ['name' => $jt['name'], 'target_gender' => 'female', 'sort_order' => $jt['sort'], 'updated_at' => $now, 'created_at' => $now]
            );
            $femaleJobTypes[] = DB::table('job_types')->where('slug', $jt['slug'])->value('id');
        }

        // ─── 職種（男性向け） ──────────────────────────────────
        $maleJobTypes = [];
        foreach ([
            ['name' => '黒服',       'slug' => 'kurofuku',  'sort' => 1],
            ['name' => 'ボーイ',     'slug' => 'boy',     'sort' => 2],
            ['name' => 'キッチン',   'slug' => 'kitchen', 'sort' => 3],
            ['name' => 'ドライバー', 'slug' => 'driver',  'sort' => 4],
        ] as $jt) {
            DB::table('job_types')->updateOrInsert(
                ['slug' => $jt['slug']],
                ['name' => $jt['name'], 'target_gender' => 'male', 'sort_order' => $jt['sort'], 'updated_at' => $now, 'created_at' => $now]
            );
            $maleJobTypes[] = DB::table('job_types')->where('slug', $jt['slug'])->value('id');
        }

        // ─── 店舗名一覧（20店舗） ─────────────────────────────
        $shopNames = [
            ['name' => 'クラブ・ブランシュ',   'kana' => 'クラブブランシュ'],
            ['name' => 'ラ・ボエーム',         'kana' => 'ラボエーム'],
            ['name' => 'クラブ・エレガンス',   'kana' => 'クラブエレガンス'],
            ['name' => 'Club REVUE',            'kana' => 'クラブレビュー'],
            ['name' => 'ナイトクラブ・ルージュ','kana' => 'ナイトクラブルージュ'],
            ['name' => 'Club CIEL',             'kana' => 'クラブシエル'],
            ['name' => 'クラブ・アンバー',      'kana' => 'クラブアンバー'],
            ['name' => 'Club LUMIÈRE',          'kana' => 'クラブリュミエール'],
            ['name' => 'クラブ・ノワール',      'kana' => 'クラブノワール'],
            ['name' => 'Club LIAISON',          'kana' => 'クラブリエゾン'],
            ['name' => 'クラブ・ヴィーナス',    'kana' => 'クラブヴィーナス'],
            ['name' => 'Club ÉTOILE',           'kana' => 'クラブエトワール'],
            ['name' => 'クラブ・セレーナ',      'kana' => 'クラブセレーナ'],
            ['name' => 'Club MIRAGE',           'kana' => 'クラブミラージュ'],
            ['name' => 'クラブ・ペタル',        'kana' => 'クラブペタル'],
            ['name' => 'Club SAPHIR',           'kana' => 'クラブサフィール'],
            ['name' => 'クラブ・ドリーム',      'kana' => 'クラブドリーム'],
            ['name' => 'Club AURORA',           'kana' => 'クラブオーロラ'],
            ['name' => 'クラブ・シャルム',      'kana' => 'クラブシャルム'],
            ['name' => 'Club SOLEIL',           'kana' => 'クラブソレイユ'],
        ];

        $addresses = [
            '東京都新宿区歌舞伎町1-1-X',
            '東京都新宿区歌舞伎町2-3-X',
            '東京都新宿区西新宿1-2-X',
            '東京都新宿区新宿3-4-X',
            '東京都新宿区歌舞伎町1-5-X',
        ];

        $workingHours = [
            '20:00〜翌5:00',
            '21:00〜翌6:00',
            '19:00〜翌4:00',
            '20:00〜翌4:00',
            '22:00〜翌5:00',
        ];

        $femaleDescriptions = [
            'キャスト' => [
                '未経験・体入大歓迎！新宿歌舞伎町の人気キャバクラでキャストとして活躍しませんか？高日給保証あり、送迎あり。',
                '人気店で一緒に働きましょう！お客様に楽しい時間を提供するお仕事です。シフト自由、週1日〜OK。',
                '高収入を目指したい方へ！指名バック・同伴バックで収入アップを目指せます。未経験でも安心サポート。',
                'アットホームな雰囲気のお店です。困ったことがあれば何でも相談できる環境を整えています。',
                '昼職との掛け持ちOK！夜だけしっかり稼ぎたい方にぴったりです。日払い・週払い対応。',
            ],
            '体入' => [
                '体入だけでもOK！雰囲気を確認してから決めてください。体入料金しっかり支払います。',
                'まずは一度体験してみてください！店内を見学しながらお仕事の流れを丁寧に説明します。',
                '体入当日から日払い可能！まずはお気軽に体験入店してみてください。',
            ],
            'ヘアメイク' => [
                'ヘアメイクスタッフ募集中！キャストのヘアセット・メイクをお任せします。夜職未経験でも大丈夫。',
                'ヘアメイクアーティスト大募集！センスと技術を活かして働きませんか？未経験歓迎・研修あり。',
            ],
        ];

        $maleDescriptions = [
            '黒服' => [
                '新宿の人気キャバクラで黒服スタッフ募集！キャストやお客様の管理をお任せします。未経験可。',
                '将来は店長を目指したい方歓迎！マネジメント経験が積めます。キャバクラ未経験でもOK。',
                '幅広い年齢層が活躍中！落ち着いた雰囲気のお店で長期安定して働けます。昇給あり。',
                'ホール・接客補助など、まずは基本業務から。キャリアアップを目指せる環境です。',
                'チームワーク重視のお店です。スタッフ同士の仲が良く働きやすい職場です。',
            ],
            'ボーイ' => [
                'ドリンク・フード提供、灰皿交換などのホール業務。未経験歓迎・丁寧に教えます。',
                'ホールスタッフ（ボーイ）大募集！体力に自信のある方歓迎。深夜手当あり。',
                '夜だけ働きたい方、学生・フリーター歓迎！シフト相談OK。',
            ],
            'キッチン' => [
                'キッチンスタッフ募集！フードの仕込みや提供をお任せします。調理経験があれば尚可。',
                'お客様に喜ばれるフードを作るお仕事です。簡単な調理スキルがあればOK。',
            ],
            'ドライバー' => [
                'キャスト・お客様の送迎ドライバー募集。普通自動車免許必須。安全運転できる方歓迎。',
                '深夜帯の送迎ドライバー大募集！シフト制・日払いOK。普免をお持ちの方。',
            ],
        ];

        // ─── 店舗 & 求人を生成 ────────────────────────────────
        $jobCount = 0;

        foreach ($shopNames as $idx => $shopInfo) {
            $shopId = DB::table('shops')->insertGetId([
                'name'          => $shopInfo['name'],
                'kana'          => $shopInfo['kana'],
                'genre_id'      => $genreId,
                'prefecture_id' => $prefectureId,
                'area_id'       => $areaId,
                'station_id'    => $stationId,
                'address'       => $addresses[$idx % count($addresses)],
                'tel'           => '03-' . rand(3000, 9999) . '-' . rand(1000, 9999),
                'status'        => 'active',
                'xml_source'    => 'manual',
                'created_at'    => $now,
                'updated_at'    => $now,
            ]);

            // shop_details も追加
            DB::table('shop_details')->insert([
                'shop_id'           => $shopId,
                'set_price'         => rand(5, 15) * 1000 . '円',
                'nomination_fee'    => rand(1, 5) * 1000 . '円',
                'all_you_can_drink' => rand(0, 1),
                'opening_hours'     => '20:00',
                'closing_hours'     => '翌5:00',
                'holiday'           => '年中無休',
                'status'            => 'active',
                'created_at'        => $now,
                'updated_at'        => $now,
            ]);

            // 女性向け求人（3件）
            $femaleTypeNames = ['キャスト', 'キャスト', '体入'];
            if ($idx % 5 === 0) {
                $femaleTypeNames = ['キャスト', '体入', 'ヘアメイク'];
            }

            foreach ($femaleTypeNames as $typeName) {
                $jtId = match($typeName) {
                    'キャスト'   => $femaleJobTypes[0],
                    '体入'       => $femaleJobTypes[1],
                    'ヘアメイク' => $femaleJobTypes[2],
                };
                $descList = $femaleDescriptions[$typeName];
                $desc     = $descList[array_rand($descList)];
                $minWage  = rand(30, 50) * 100;
                $maxWage  = $minWage + rand(10, 30) * 100;

                DB::table('jobs')->insert([
                    'shop_id'        => $shopId,
                    'job_type_id'    => $jtId,
                    'area_id'        => $areaId,
                    'prefecture_id'  => $prefectureId,
                    'station_id'     => $stationId,
                    'title'          => $shopInfo['name'] . "【{$typeName}募集】新宿歌舞伎町",
                    'description'    => $desc,
                    'hourly_wage_min'=> $minWage,
                    'hourly_wage_max'=> $maxWage,
                    'working_hours'  => $workingHours[$idx % count($workingHours)],
                    'search_group'   => 'female',
                    'status'         => 'active',
                    'bid_price'      => rand(10, 50),
                    'published_at'   => $now->copy()->subDays(rand(1, 30)),
                    'created_at'     => $now,
                    'updated_at'     => $now,
                ]);
                $jobCount++;
            }

            // 男性向け求人（2件）
            $maleTypeNames = ['黒服', 'ボーイ'];
            if ($idx % 4 === 0) {
                $maleTypeNames = ['黒服', 'キッチン'];
            } elseif ($idx % 7 === 0) {
                $maleTypeNames = ['黒服', 'ドライバー'];
            }

            foreach ($maleTypeNames as $typeName) {
                $jtId = match($typeName) {
                    '黒服'       => $maleJobTypes[0],
                    'ボーイ'     => $maleJobTypes[1],
                    'キッチン'   => $maleJobTypes[2],
                    'ドライバー' => $maleJobTypes[3],
                };
                $descList = $maleDescriptions[$typeName];
                $desc     = $descList[array_rand($descList)];
                $minWage  = rand(12, 18) * 100;
                $maxWage  = $minWage + rand(5, 10) * 100;

                DB::table('jobs')->insert([
                    'shop_id'        => $shopId,
                    'job_type_id'    => $jtId,
                    'area_id'        => $areaId,
                    'prefecture_id'  => $prefectureId,
                    'station_id'     => $stationId,
                    'title'          => $shopInfo['name'] . "【{$typeName}募集】新宿歌舞伎町",
                    'description'    => $desc,
                    'hourly_wage_min'=> $minWage,
                    'hourly_wage_max'=> $maxWage,
                    'working_hours'  => $workingHours[$idx % count($workingHours)],
                    'search_group'   => 'male',
                    'status'         => 'active',
                    'bid_price'      => rand(10, 30),
                    'published_at'   => $now->copy()->subDays(rand(1, 30)),
                    'created_at'     => $now,
                    'updated_at'     => $now,
                ]);
                $jobCount++;
            }
        }

        $this->command->info("投入完了: 店舗 " . count($shopNames) . "件 / 求人 {$jobCount}件");
    }
}
