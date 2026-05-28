<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('cast_diary_tokens', function (Blueprint $table) {
            $table->boolean('is_email_token')->default(false)->after('token');
            $table->timestamp('expires_at')->nullable()->change();
        });
    }
    public function down(): void {
        Schema::table('cast_diary_tokens', function (Blueprint $table) {
            $table->dropColumn('is_email_token');
            $table->timestamp('expires_at')->nullable(false)->change();
        });
    }
};
