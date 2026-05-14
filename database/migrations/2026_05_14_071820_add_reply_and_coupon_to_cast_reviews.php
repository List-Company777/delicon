<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('cast_reviews', function (Blueprint $table) {
            $table->text('shop_reply')->nullable()->after('deletion_requested_at');
            $table->timestamp('shop_replied_at')->nullable()->after('shop_reply');
            $table->boolean('coupon_sent')->default(false)->after('shop_replied_at');
        });

        Schema::table('discount_coupons', function (Blueprint $table) {
            $table->unsignedBigInteger('review_id')->nullable()->after('user_id');
            $table->foreign('review_id')->references('id')->on('cast_reviews')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('discount_coupons', function (Blueprint $table) {
            $table->dropForeign(['review_id']);
            $table->dropColumn('review_id');
        });
        Schema::table('cast_reviews', function (Blueprint $table) {
            $table->dropColumn(['shop_reply', 'shop_replied_at', 'coupon_sent']);
        });
    }
};
