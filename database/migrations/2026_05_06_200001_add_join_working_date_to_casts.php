<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('casts', function (Blueprint $table) {
            $table->date('join_date')->nullable()->after('is_recommended');
            $table->date('working_date')->nullable()->after('join_date');
        });
    }
    public function down(): void
    {
        Schema::table('casts', function (Blueprint $table) {
            $table->dropColumn(['join_date', 'working_date']);
        });
    }
};
