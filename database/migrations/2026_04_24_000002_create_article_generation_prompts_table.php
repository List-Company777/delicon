<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('article_generation_prompts', function (Blueprint $table) {
            $table->id();
            $table->enum('gender', ['female', 'male', 'business', 'shop'])->unique();
            $table->text('instruction');
            $table->timestamps();
        });

        DB::table('article_generation_prompts')->insert([
            ['gender' => 'female',   'instruction' => '女性ナイトワーク（キャスト・ガールズバー・ラウンジ等）を検討している、または現在働いている20代女性向け。未経験の不安解消・稼ぎ方・職場選びの実用情報を提供する。専門用語はやさしく解説し、実際に働く女性の目線で書く。', 'created_at' => now(), 'updated_at' => now()],
            ['gender' => 'male',     'instruction' => '男性ナイトワーク（ホスト・黒服・ボーイ等）を検討している、または現在働いている20代男性向け。仕事内容・給与・キャリアパスを具体的に解説する。未経験者が抱く疑問に答える実践的な内容を心がける。', 'created_at' => now(), 'updated_at' => now()],
            ['gender' => 'business', 'instruction' => '夜遊び（キャバクラ・ガールズバー・ラウンジ等）を楽しみたいお客様向け。料金・マナー・エリア情報を初心者にもわかりやすく紹介する。初めて足を運ぶ読者が安心して利用できるよう丁寧に解説する。', 'created_at' => now(), 'updated_at' => now()],
            ['gender' => 'shop',     'instruction' => '夜遊び業態（キャバクラ・ラウンジ・ガールズバー等）の店舗オーナー・店長・経営幹部向け。採用・集客・スタッフ管理・法令対応・売上改善などの経営実務に役立つ情報を提供する。現場感のある具体的なアドバイスを心がける。', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('article_generation_prompts');
    }
};
