<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Step1: ENUMに yoasobi を追加（business と共存）
        DB::statement("ALTER TABLE articles MODIFY COLUMN gender ENUM('female','male','business','yoasobi','shop') NOT NULL DEFAULT 'shop'");
        DB::statement("ALTER TABLE article_topics MODIFY COLUMN gender ENUM('female','male','business','yoasobi','shop') NOT NULL DEFAULT 'shop'");
        DB::statement("ALTER TABLE article_generation_prompts MODIFY COLUMN gender ENUM('female','male','business','yoasobi','shop')");
        DB::statement("ALTER TABLE search_keywords MODIFY COLUMN gender ENUM('male','female','business','yoasobi')");
        DB::statement("ALTER TABLE keyword_normalizations MODIFY COLUMN gender ENUM('male','female','business','yoasobi')");

        // Step2: データを更新
        DB::table('articles')->where('gender', 'business')->update(['gender' => 'yoasobi']);
        DB::table('article_topics')->where('gender', 'business')->update(['gender' => 'yoasobi']);
        DB::table('article_generation_prompts')->where('gender', 'business')->update(['gender' => 'yoasobi']);
        DB::table('search_keywords')->where('gender', 'business')->update(['gender' => 'yoasobi']);
        DB::table('keyword_normalizations')->where('gender', 'business')->update(['gender' => 'yoasobi']);
        DB::table('search_page_views')->where('gender', 'business')->update(['gender' => 'yoasobi']);

        // Step3: ENUMから business を削除
        DB::statement("ALTER TABLE articles MODIFY COLUMN gender ENUM('female','male','yoasobi','shop') NOT NULL DEFAULT 'shop'");
        DB::statement("ALTER TABLE article_topics MODIFY COLUMN gender ENUM('female','male','yoasobi','shop') NOT NULL DEFAULT 'shop'");
        DB::statement("ALTER TABLE article_generation_prompts MODIFY COLUMN gender ENUM('female','male','yoasobi','shop')");
        DB::statement("ALTER TABLE search_keywords MODIFY COLUMN gender ENUM('male','female','yoasobi')");
        DB::statement("ALTER TABLE keyword_normalizations MODIFY COLUMN gender ENUM('male','female','yoasobi')");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE articles MODIFY COLUMN gender ENUM('female','male','yoasobi','business','shop') NOT NULL DEFAULT 'shop'");
        DB::statement("ALTER TABLE article_topics MODIFY COLUMN gender ENUM('female','male','yoasobi','business','shop') NOT NULL DEFAULT 'shop'");
        DB::statement("ALTER TABLE article_generation_prompts MODIFY COLUMN gender ENUM('female','male','yoasobi','business','shop')");
        DB::statement("ALTER TABLE search_keywords MODIFY COLUMN gender ENUM('male','female','yoasobi','business')");
        DB::statement("ALTER TABLE keyword_normalizations MODIFY COLUMN gender ENUM('male','female','yoasobi','business')");

        DB::table('articles')->where('gender', 'yoasobi')->update(['gender' => 'business']);
        DB::table('article_topics')->where('gender', 'yoasobi')->update(['gender' => 'business']);
        DB::table('article_generation_prompts')->where('gender', 'yoasobi')->update(['gender' => 'business']);
        DB::table('search_keywords')->where('gender', 'yoasobi')->update(['gender' => 'business']);
        DB::table('keyword_normalizations')->where('gender', 'yoasobi')->update(['gender' => 'business']);
        DB::table('search_page_views')->where('gender', 'yoasobi')->update(['gender' => 'business']);

        DB::statement("ALTER TABLE articles MODIFY COLUMN gender ENUM('female','male','business','shop') NOT NULL DEFAULT 'shop'");
        DB::statement("ALTER TABLE article_topics MODIFY COLUMN gender ENUM('female','male','business','shop') NOT NULL DEFAULT 'shop'");
        DB::statement("ALTER TABLE article_generation_prompts MODIFY COLUMN gender ENUM('female','male','business','shop')");
        DB::statement("ALTER TABLE search_keywords MODIFY COLUMN gender ENUM('male','female','business')");
        DB::statement("ALTER TABLE keyword_normalizations MODIFY COLUMN gender ENUM('male','female','business')");
    }
};
