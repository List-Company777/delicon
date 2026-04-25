<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('search_page_views', function (Blueprint $table) {
            $table->string('gender', 10);
            $table->string('area_slug', 100);
            $table->string('job_slug', 100);
            $table->date('date');
            $table->unsignedInteger('count')->default(0);
            $table->primary(['gender', 'area_slug', 'job_slug', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('search_page_views');
    }
};
