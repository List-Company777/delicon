<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('article_videos', function (Blueprint $table) {
            $table->renameColumn('did_job_id', 'video_job_id');
        });
    }

    public function down(): void
    {
        Schema::table('article_videos', function (Blueprint $table) {
            $table->renameColumn('video_job_id', 'did_job_id');
        });
    }
};
