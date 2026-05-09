<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('girl_types', 'cast_type_id')) {
            Schema::table('girl_types', function (Blueprint $table) {
                $table->unsignedInteger('cast_type_id')->nullable()->after('body_type_id');
            });
        }
        if (DB::table('girl_types')->where('slug', 'joshin')->exists()) {
            DB::table('girl_types')->where('slug', 'joshin')->update(['slug' => 'tyoshin']);
        }
    }

    public function down(): void
    {
        DB::table('girl_types')->where('slug', 'tyoshin')->update(['slug' => 'joshin']);
        Schema::table('girl_types', function (Blueprint $table) {
            $table->dropColumn('cast_type_id');
        });
    }
};
