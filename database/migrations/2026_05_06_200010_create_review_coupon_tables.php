<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // visitorロールを追加
        DB::statement("ALTER TABLE users MODIFY role ENUM('admin','company','agency','visitor') NOT NULL DEFAULT 'company'");

        Schema::create('shop_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cast_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('rating'); // 1-5
            $table->string('title', 100)->nullable();
            $table->text('body');
            $table->enum('status', ['pending','approved','rejected'])->default('pending');
            $table->timestamps();
            $table->index(['shop_id', 'status']);
        });

        Schema::create('discount_coupons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('code', 20)->unique();
            $table->unsignedInteger('discount_amount'); // 円
            $table->unsignedInteger('min_order_amount')->nullable();
            $table->text('conditions')->nullable();
            $table->text('message')->nullable();
            $table->date('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'shop_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discount_coupons');
        Schema::dropIfExists('shop_reviews');
        DB::statement("ALTER TABLE users MODIFY role ENUM('admin','company','agency') NOT NULL DEFAULT 'company'");
    }
};
