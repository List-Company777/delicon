<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // お気に入りキャスト
        Schema::create('cast_favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cast_id')->constrained()->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['user_id', 'cast_id']);
        });

        // 閲覧履歴
        Schema::create('cast_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('session_id', 64)->nullable();
            $table->foreignId('cast_id')->constrained()->cascadeOnDelete();
            $table->timestamp('viewed_at')->useCurrent();
            $table->index(['user_id', 'viewed_at']);
            $table->index(['session_id', 'viewed_at']);
        });

        // ユーザー通知設定
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('notify_new_cast')->default(false)->after('last_login_at');
            $table->boolean('notify_working')->default(false)->after('notify_new_cast');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['notify_new_cast', 'notify_working']);
        });
        Schema::dropIfExists('cast_views');
        Schema::dropIfExists('cast_favorites');
    }
};
