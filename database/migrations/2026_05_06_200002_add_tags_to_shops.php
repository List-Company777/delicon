<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->json('tags')->nullable()->after('shop_type_id2');
        });
    }
    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn('tags');
        });
    }
};
