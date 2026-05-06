<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('cast_diaries', function (Blueprint $t) {
            $t->id();
            $t->foreignId('cast_id')->constrained()->cascadeOnDelete();
            $t->string('title', 100)->nullable();
            $t->text('body')->nullable();
            $t->enum('status', ['draft', 'published'])->default('published');
            $t->timestamps();
            $t->index(['cast_id', 'status', 'created_at']);
        });
        Schema::create('cast_diary_images', function (Blueprint $t) {
            $t->id();
            $t->foreignId('diary_id')->constrained('cast_diaries')->cascadeOnDelete();
            $t->string('img_path', 255);
            $t->tinyInteger('sort_order')->unsigned()->default(0);
            $t->timestamp('created_at')->nullable();
        });
    }
    public function down(): void {
        Schema::dropIfExists('cast_diary_images');
        Schema::dropIfExists('cast_diaries');
    }
};
