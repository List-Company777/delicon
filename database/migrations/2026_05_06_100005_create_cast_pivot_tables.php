<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // チャームポイント中間（旧charmpoint/2/3カラムを置換）
        Schema::create('cast_charms', function (Blueprint $table) {
            $table->unsignedBigInteger('cast_id');
            $table->unsignedBigInteger('charm_type_id');
            $table->primary(['cast_id', 'charm_type_id']);
            $table->foreign('cast_id')->references('id')->on('casts')->onDelete('cascade');
            $table->foreign('charm_type_id')->references('id')->on('cast_charm_types')->onDelete('cascade');
        });

        // 可能プレイ中間（旧kanoupkay/2/3カラムを置換）
        Schema::create('cast_plays', function (Blueprint $table) {
            $table->unsignedBigInteger('cast_id');
            $table->unsignedBigInteger('play_type_id');
            $table->primary(['cast_id', 'play_type_id']);
            $table->foreign('cast_id')->references('id')->on('casts')->onDelete('cascade');
            $table->foreign('play_type_id')->references('id')->on('cast_play_types')->onDelete('cascade');
        });

        // 性格中間（旧seikaku/2カラムを置換）
        Schema::create('cast_personalities', function (Blueprint $table) {
            $table->unsignedBigInteger('cast_id');
            $table->unsignedBigInteger('personality_type_id');
            $table->primary(['cast_id', 'personality_type_id']);
            $table->foreign('cast_id')->references('id')->on('casts')->onDelete('cascade');
            $table->foreign('personality_type_id')->references('id')->on('cast_personality_types')->onDelete('cascade');
        });

        // アイコン/タグ中間（旧girl_icons）
        Schema::create('cast_tags', function (Blueprint $table) {
            $table->unsignedBigInteger('cast_id');
            $table->unsignedBigInteger('tag_id');
            $table->primary(['cast_id', 'tag_id']);
            $table->foreign('cast_id')->references('id')->on('casts')->onDelete('cascade');
            $table->foreign('tag_id')->references('id')->on('cast_tag_masters')->onDelete('cascade');
        });
    }
    public function down(): void {
        Schema::dropIfExists('cast_tags');
        Schema::dropIfExists('cast_personalities');
        Schema::dropIfExists('cast_plays');
        Schema::dropIfExists('cast_charms');
    }
};
