<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('casts', function (Blueprint $table) {
            $table->smallInteger('cast_score')->default(0)->after('sort_order')->index();
        });
    }
    public function down(): void {
        Schema::table('casts', function (Blueprint $table) {
            $table->dropColumn('cast_score');
        });
    }
};
