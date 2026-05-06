<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // キャストタイプ（旧girl_types）
        Schema::create('cast_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->unsignedInteger('sort_order')->default(0);
        });

        // 体型（旧girl_bodies）
        Schema::create('cast_body_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
        });

        // チャームポイント（旧girl_charms）
        Schema::create('cast_charm_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->unsignedInteger('sort_order')->default(0);
        });

        // 可能プレイ（旧girl_plays）
        Schema::create('cast_play_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->unsignedInteger('sort_order')->default(0);
        });

        // 性格（旧girl_minds）
        Schema::create('cast_personality_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->unsignedInteger('sort_order')->default(0);
        });

        // アイコン/タグ（旧icons）
        Schema::create('cast_tag_masters', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->unsignedInteger('sort_order')->default(0);
        });
    }
    public function down(): void {
        Schema::dropIfExists('cast_tag_masters');
        Schema::dropIfExists('cast_personality_types');
        Schema::dropIfExists('cast_play_types');
        Schema::dropIfExists('cast_charm_types');
        Schema::dropIfExists('cast_body_types');
        Schema::dropIfExists('cast_types');
    }
};
