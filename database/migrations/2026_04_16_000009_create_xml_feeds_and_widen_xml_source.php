<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ① xml_feeds テーブル（外部連携先の登録簿）
        Schema::create('xml_feeds', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->comment('サイト名（管理画面表示用）');
            $table->string('slug', 50)->unique()->comment('コード名。shops/jobs の xml_source に格納される');
            $table->string('url', 500)->comment('フィードURL');
            $table->enum('feed_type', ['staff_jobs', 'cast_jobs', 'business_info'])
                  ->comment('staff_jobs=スタッフ求人, cast_jobs=キャスト求人, business_info=営業情報');
            $table->boolean('is_own_site')->default(false)
                  ->comment('自社サイト: true=クレーム登録・追加案内あり, false=読み取り専用');
            $table->json('allowed_categories')->nullable()
                  ->comment('取り込むカテゴリ名の配列（null=全件）');
            $table->json('category_genre_map')->nullable()
                  ->comment('カテゴリ名→genre_id のマッピング');
            $table->string('bid_price_xml_field', 50)->nullable()
                  ->comment('XML中の入札単価フィールド名（is_own_site=true のみ）');
            $table->string('monthly_budget_xml_field', 50)->nullable()
                  ->comment('XML中の月次予算フィールド名（is_own_site=true のみ）');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamp('last_imported_at')->nullable();
            $table->timestamps();
        });

        // ② shops.xml_source / jobs.xml_source を enum → varchar(50) に変更
        DB::statement("ALTER TABLE shops MODIFY COLUMN xml_source VARCHAR(50) NOT NULL DEFAULT 'manual'");
        DB::statement("ALTER TABLE jobs   MODIFY COLUMN xml_source VARCHAR(50) NOT NULL DEFAULT 'manual'");

        // ③ www.up-stage.info の初期レコードを挿入
        DB::table('xml_feeds')->insert([
            'name'                    => 'www.up-stage.info',
            'slug'                    => 'upstage',
            'url'                     => '', // 設定は .env UPSTAGE_XML_FEED_URL で管理
            'feed_type'               => 'staff_jobs',
            'is_own_site'             => true,
            'allowed_categories'      => json_encode([
                'キャバクラ', 'ガールズバー', 'ホスト', 'ホストクラブ',
                'ボーイズバー', 'スナック', 'ラウンジ', 'コンカフェ',
                'クラブ', 'バー', 'パブ', 'ラウンジバー',
            ]),
            'category_genre_map'      => json_encode([
                'キャバクラ'   => 1,
                'ホスト'       => 2,
                'ホストクラブ' => 2,
                'ボーイズバー' => 3,
                'ガールズバー' => 4,
                'スナック'     => 5,
                'ラウンジ'     => 6,
                'コンカフェ'   => 7,
                'クラブ'       => 8,
                'バー'         => 9,
                'パブ'         => 11,
                'ラウンジバー' => 6,
            ]),
            'bid_price_xml_field'         => 'nightwork_bid_price',
            'monthly_budget_xml_field'    => 'nightwork_monthly_budget',
            'status'                      => 'active',
            'created_at'                  => now(),
            'updated_at'                  => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('xml_feeds');

        DB::statement("ALTER TABLE shops MODIFY COLUMN xml_source ENUM('upstage','cabareuclub','manual') NOT NULL DEFAULT 'manual'");
        DB::statement("ALTER TABLE jobs   MODIFY COLUMN xml_source ENUM('upstage','cabareuclub','manual') NOT NULL DEFAULT 'manual'");
    }
};
