<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('article_article_category', function (Blueprint $table) {
            $table->unsignedBigInteger('article_id');
            $table->unsignedBigInteger('article_category_id');
            $table->primary(['article_id', 'article_category_id']);
            $table->foreign('article_id')->references('id')->on('articles')->cascadeOnDelete();
            $table->foreign('article_category_id')->references('id')->on('article_categories')->cascadeOnDelete();
        });

        Schema::create('article_article_tag', function (Blueprint $table) {
            $table->unsignedBigInteger('article_id');
            $table->unsignedBigInteger('article_tag_id');
            $table->primary(['article_id', 'article_tag_id']);
            $table->foreign('article_id')->references('id')->on('articles')->cascadeOnDelete();
            $table->foreign('article_tag_id')->references('id')->on('article_tags')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('article_article_tag');
        Schema::dropIfExists('article_article_category');
    }
};
