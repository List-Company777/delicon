<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE articles MODIFY COLUMN gender ENUM('female','male','business','shop') NOT NULL DEFAULT 'shop'");
        DB::table('articles')->where('gender', 'all')->update(['gender' => 'shop']);

        DB::statement("ALTER TABLE article_topics MODIFY COLUMN gender ENUM('female','male','business','shop') NOT NULL DEFAULT 'shop'");
        DB::table('article_topics')->where('gender', 'all')->update(['gender' => 'shop']);
    }

    public function down(): void
    {
        DB::table('articles')->where('gender', 'shop')->update(['gender' => 'all']);
        DB::statement("ALTER TABLE articles MODIFY COLUMN gender ENUM('female','male','business','all') NOT NULL DEFAULT 'all'");

        DB::table('article_topics')->where('gender', 'shop')->update(['gender' => 'all']);
        DB::statement("ALTER TABLE article_topics MODIFY COLUMN gender ENUM('female','male','business','all') NOT NULL DEFAULT 'all'");
    }
};
