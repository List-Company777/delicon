<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AreaSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // 都道府県（東京は既存 id=1）
        $prefectures = [
            ['id' => 2,  'name' => '神奈川県', 'slug' => 'kanagawa',  'sort_order' => 2],
            ['id' => 3,  'name' => '大阪府',   'slug' => 'osaka',     'sort_order' => 3],
            ['id' => 4,  'name' => '愛知県',   'slug' => 'aichi',     'sort_order' => 4],
            ['id' => 5,  'name' => '福岡県',   'slug' => 'fukuoka',   'sort_order' => 5],
            ['id' => 6,  'name' => '北海道',   'slug' => 'hokkaido',  'sort_order' => 6],
            ['id' => 7,  'name' => '宮城県',   'slug' => 'miyagi',    'sort_order' => 7],
            ['id' => 8,  'name' => '京都府',   'slug' => 'kyoto',     'sort_order' => 8],
            ['id' => 9,  'name' => '兵庫県',   'slug' => 'hyogo',     'sort_order' => 9],
            ['id' => 10, 'name' => '広島県',   'slug' => 'hiroshima', 'sort_order' => 10],
            ['id' => 11, 'name' => '沖縄県',   'slug' => 'okinawa',   'sort_order' => 11],
        ];

        foreach ($prefectures as $p) {
            DB::table('prefectures')->insertOrIgnore([...$p, 'created_at' => $now, 'updated_at' => $now]);
        }

        // エリア（prefecture_id, name, slug, sort_order）
        $areas = [
            // ── 東京 ──────────────────────────────────
            [1, '新宿',     'shinjuku',      1],
            [1, '歌舞伎町', 'kabukicho',     2],
            [1, '池袋',     'ikebukuro',     3],
            [1, '渋谷',     'shibuya',       4],
            [1, '六本木',   'roppongi',      5],
            [1, '銀座',     'ginza',         6],
            [1, '赤坂',     'akasaka',       7],
            [1, '恵比寿',   'ebisu',         8],
            [1, '中目黒',   'nakameguro',    9],
            [1, '麻布十番', 'azabujuban',   10],
            [1, '西麻布',   'nishiazabu',   11],
            [1, '新橋',     'shimbashi',    12],
            [1, '品川',     'shinagawa',    13],
            [1, '五反田',   'gotanda',      14],
            [1, '上野',     'ueno',         15],
            [1, '浅草',     'asakusa',      16],
            [1, '秋葉原',   'akihabara',    17],
            [1, '北千住',   'kitasenju',    18],
            [1, '錦糸町',   'kinshicho',    19],
            [1, '吉祥寺',   'kichijoji',    20],
            [1, '中野',     'nakano',       21],
            [1, '高円寺',   'koenji',       22],
            [1, '荻窪',     'ogikubo',      23],
            [1, '立川',     'tachikawa',    24],
            [1, '町田',     'machida',      25],
            [1, '八王子',   'hachioji',     26],
            [1, '調布',     'chofu',        27],
            [1, '下北沢',   'shimokitazawa',28],
            [1, '代官山',   'daikanyama',   29],
            [1, '自由が丘', 'jiyugaoka',    30],
            [1, '表参道',   'omotesando',   31],
            [1, '原宿',     'harajuku',     32],
            [1, '青山',     'aoyama',       33],
            [1, '虎ノ門',   'toranomon',    34],
            [1, '神保町',   'jimbocho',     35],
            [1, '水道橋',   'suidobashi',   36],
            [1, '神楽坂',   'kagurazaka',   37],
            [1, '浜松町',   'hamamatsucho', 38],
            [1, '目黒',     'meguro',       39],
            [1, '蒲田',     'kamata',       40],
            [1, '川崎',     'kawasaki',     41], // 実際は神奈川だが検索では東京扱いも多い

            // ── 神奈川 ────────────────────────────────
            [2, '横浜',     'yokohama',     50],
            [2, '関内',     'kannai',       51],
            [2, 'みなとみらい', 'minatomirai', 52],
            [2, '横浜駅',   'yokohama-sta', 53],
            [2, '上大岡',   'kamiooka',     54],
            [2, '藤沢',     'fujisawa',     55],
            [2, '本厚木',   'honatsugri',   56],

            // ── 大阪 ──────────────────────────────────
            [3, '梅田',     'umeda',        60],
            [3, '北新地',   'kitashinchi',  61],
            [3, '難波',     'namba',        62],
            [3, '心斎橋',   'shinsaibashi', 63],
            [3, '天満',     'tenma',        64],
            [3, '天王寺',   'tennoji',      65],
            [3, '京橋',     'kyobashi',     66],
            [3, '堺',       'sakai',        67],

            // ── 愛知 ──────────────────────────────────
            [4, '名古屋',   'nagoya',       70],
            [4, '錦',       'nishiki',      71],
            [4, '栄',       'sakae',        72],
            [4, '金山',     'kanayama',     73],

            // ── 福岡 ──────────────────────────────────
            [5, '博多',     'hakata',       80],
            [5, '中洲',     'nakasu',       81],
            [5, '天神',     'tenjin',       82],
            [5, '小倉',     'kokura',       83],

            // ── 北海道 ────────────────────────────────
            [6, '札幌',     'sapporo',      90],
            [6, 'すすきの', 'susukino',     91],

            // ── 宮城 ──────────────────────────────────
            [7, '仙台',     'sendai',       100],
            [7, '国分町',   'kokubuncho',   101],

            // ── 京都 ──────────────────────────────────
            [8, '京都',     'kyoto',        110],
            [8, '祇園',     'gion',         111],
            [8, '木屋町',   'kiyamachi',    112],

            // ── 兵庫 ──────────────────────────────────
            [9, '神戸',     'kobe',         120],
            [9, '三宮',     'sannomiya',    121],

            // ── 広島 ──────────────────────────────────
            [10, '広島',    'hiroshima',    130],
            [10, '流川',    'nagarekawa',   131],

            // ── 沖縄 ──────────────────────────────────
            [11, '那覇',    'naha',         140],
            [11, '松山',    'matsuyama-oki',141],
        ];

        foreach ($areas as [$prefId, $name, $slug, $sort]) {
            DB::table('areas')->insertOrIgnore([
                'prefecture_id' => $prefId,
                'name'          => $name,
                'slug'          => $slug,
                'sort_order'    => $sort,
                'created_at'    => $now,
                'updated_at'    => $now,
            ]);
        }
    }
}
