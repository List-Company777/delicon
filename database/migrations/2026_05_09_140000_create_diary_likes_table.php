<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('diary_likes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('diary_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamp('created_at')->useCurrent();
            $table->foreign('diary_id')->references('id')->on('cast_diaries')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['diary_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diary_likes');
    }
};
