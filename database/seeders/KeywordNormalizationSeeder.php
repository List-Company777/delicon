<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KeywordNormalizationSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $genders = ['male', 'female', 'yoasobi'];

        // エリアスラッグ → ID のマップ
        $areaMap = DB::table('areas')->pluck('id', 'slug');

        // [ エリアslug => [表記ゆれキーワード, ...] ]
        $aliases = [
            // ── 東京 ──────────────────────────────────
            'shinjuku'       => ['新宿駅', '新宿三丁目', '新宿東口', '新宿西口', '新宿南口', '西新宿', '東新宿'],
            'kabukicho'      => ['歌舞伎町一丁目', '歌舞伎町二丁目', '大久保', 'コリアンタウン'],
            'ikebukuro'      => ['池袋駅', '池袋東口', '池袋西口', '池袋北口', '東池袋'],
            'shibuya'        => ['渋谷駅', '渋谷道玄坂', '渋谷センター街', '宇田川', 'マークシティ'],
            'roppongi'       => ['六本木駅', '六本木一丁目', '六本木ヒルズ', '西麻布'],
            'ginza'          => ['銀座駅', '銀座一丁目', '銀座六丁目', '東銀座', '数寄屋橋'],
            'akasaka'        => ['赤坂駅', '赤坂見附', '赤坂サカス', '溜池山王'],
            'ebisu'          => ['恵比寿駅', '恵比寿ガーデンプレイス'],
            'nakameguro'     => ['中目黒駅', '目黒川'],
            'azabujuban'     => ['麻布十番駅', '麻布'],
            'shimbashi'      => ['新橋駅', '汐留', '烏森'],
            'shinagawa'      => ['品川駅', '品川インターシティ', '港南'],
            'gotanda'        => ['五反田駅', '西五反田'],
            'ueno'           => ['上野駅', '上野広小路', 'アメ横'],
            'asakusa'        => ['浅草駅', '浅草寺', '田原町'],
            'akihabara'      => ['秋葉原駅', 'アキバ'],
            'kitasenju'      => ['北千住駅', '千住'],
            'kinshicho'      => ['錦糸町駅', '亀戸'],
            'kichijoji'      => ['吉祥寺駅', '井の頭'],
            'nakano'         => ['中野駅', '中野ブロードウェイ'],
            'tachikawa'      => ['立川駅', '北口'],
            'machida'        => ['町田駅', '小田急町田'],
            'hachioji'       => ['八王子駅', '八王子'],
            'shimokitazawa'  => ['下北沢駅', '下北'],
            'daikanyama'     => ['代官山駅'],
            'jiyugaoka'      => ['自由が丘駅'],
            'omotesando'     => ['表参道駅', '原宿', 'キャットストリート'],
            'harajuku'       => ['原宿駅', '竹下通り'],
            'aoyama'         => ['青山一丁目', '南青山', '北青山'],
            'toranomon'      => ['虎ノ門駅', '虎ノ門ヒルズ'],
            'meguro'         => ['目黒駅', '目黒'],
            'kamata'         => ['蒲田駅', '西蒲田'],
            'kawasaki'       => ['川崎駅', '川崎'],

            // ── 神奈川 ────────────────────────────────
            'yokohama'       => ['横浜駅', '横浜西口', '横浜東口'],
            'kannai'         => ['関内駅', '伊勢佐木町', 'イセザキ'],

            // ── 大阪 ──────────────────────────────────
            'umeda'          => ['梅田駅', '大阪駅', '阪急梅田', '東梅田', '西梅田', '北新地'],
            'namba'          => ['難波駅', 'なんば', 'なんば駅', '南海なんば', '千日前', 'ミナミ'],
            'shinsaibashi'   => ['心斎橋駅', '心斎橋筋', 'アメリカ村', '堀江'],
            'kitashinchi'    => ['北新地駅', '北新地'],
            'tenma'          => ['天満駅', '天神橋'],
            'tennoji'        => ['天王寺駅', '天王寺', 'あべの'],

            // ── 愛知 ──────────────────────────────────
            'nagoya'         => ['名古屋駅', 'ナゴヤ', '名駅'],
            'nishiki'        => ['錦三', '錦三丁目', '錦二丁目'],
            'sakae'          => ['栄駅', '栄', '矢場町'],

            // ── 福岡 ──────────────────────────────────
            'hakata'         => ['博多駅', '博多'],
            'nakasu'         => ['中洲川端', '中州', 'なかす'],
            'tenjin'         => ['天神駅', '西鉄天神', '天神南'],
            'kokura'         => ['小倉駅', '北九州'],

            // ── 北海道 ────────────────────────────────
            'sapporo'        => ['札幌駅', '大通'],
            'susukino'       => ['すすきの駅', 'ススキノ', '薄野'],

            // ── 宮城 ──────────────────────────────────
            'sendai'         => ['仙台駅', '仙台'],
            'kokubuncho'     => ['国分町', '一番町'],

            // ── 京都 ──────────────────────────────────
            'kyoto'          => ['京都駅', '四条', '烏丸'],
            'gion'           => ['祇園四条', '花見小路'],
            'kiyamachi'      => ['木屋町通', '先斗町'],

            // ── 兵庫 ──────────────────────────────────
            'sannomiya'      => ['三宮駅', '三ノ宮', '元町'],

            // ── 広島 ──────────────────────────────────
            'hiroshima'      => ['広島駅', '紙屋町'],
            'nagarekawa'     => ['流川通', '薬研堀'],

            // ── 沖縄 ──────────────────────────────────
            'naha'           => ['那覇市', '国際通り', 'おもろまち'],
            'matsuyama-oki'  => ['松山公園', '桜坂'],
        ];

        $rows = [];
        foreach ($aliases as $slug => $keywords) {
            $areaId = $areaMap[$slug] ?? null;
            if (!$areaId) continue;

            foreach ($keywords as $keyword) {
                foreach ($genders as $gender) {
                    $rows[] = [
                        'keyword'      => $keyword,
                        'gender'       => $gender,
                        'area_id'      => $areaId,
                        'job_type_id'  => null,
                        'is_active'    => true,
                        'created_at'   => $now,
                        'updated_at'   => $now,
                    ];
                }
            }
        }

        // 既存レコードと重複しないよう insertOrIgnore
        foreach (array_chunk($rows, 100) as $chunk) {
            DB::table('keyword_normalizations')->insertOrIgnore($chunk);
        }

        $this->command->info('キーワード正規化マッピング: ' . count($rows) . ' 件を投入しました');
    }
}
