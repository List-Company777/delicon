<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('article_videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['pending', 'processing', 'done', 'failed'])->default('pending');
            $table->text('script')->nullable();
            $table->string('audio_path')->nullable();
            $table->string('did_job_id')->nullable();
            $table->string('video_path')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('article_videos');
    }
};
