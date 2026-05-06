<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('pref_cast_type_ids')->nullable()->after('notify_working');
            $table->json('pref_area_ids')->nullable()->after('pref_cast_type_ids');
        });
    }
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['pref_cast_type_ids', 'pref_area_ids']);
        });
    }
};
