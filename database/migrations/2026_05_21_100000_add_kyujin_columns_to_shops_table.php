<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->string('kyujin_address', 100)->nullable()->after('eigyo_space');
            $table->string('kyujin_time', 50)->nullable()->after('kyujin_address');
            $table->string('kyujin_person', 20)->nullable()->after('kyujin_time');
            $table->string('kyujin_tel', 50)->nullable()->after('kyujin_person');
            $table->string('kyujin_junre', 30)->nullable()->after('kyujin_tel');
            $table->string('work_address', 100)->nullable()->after('kyujin_junre');
            $table->string('near_station', 100)->nullable()->after('work_address');
            $table->string('oubo_shikaku', 100)->nullable()->after('near_station');
            $table->string('work_time', 100)->nullable()->after('oubo_shikaku');
            $table->string('kyujin_model', 500)->nullable()->after('work_time');
            $table->text('kyujin_speciality')->nullable()->after('kyujin_model');
            $table->string('kyujin1_file_name', 100)->nullable()->after('kyujin_speciality');
            $table->string('kyujin2_file_name', 100)->nullable()->after('kyujin1_file_name');
            $table->string('kyujin3_file_name', 100)->nullable()->after('kyujin2_file_name');
            $table->string('kyujin1_text', 100)->nullable()->after('kyujin3_file_name');
            $table->string('kyujin2_text', 100)->nullable()->after('kyujin1_text');
            $table->string('kyujin3_text', 100)->nullable()->after('kyujin2_text');
        });
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn([
                'kyujin_address', 'kyujin_time', 'kyujin_person', 'kyujin_tel',
                'kyujin_junre', 'work_address', 'near_station', 'oubo_shikaku',
                'work_time', 'kyujin_model', 'kyujin_speciality',
                'kyujin1_file_name', 'kyujin2_file_name', 'kyujin3_file_name',
                'kyujin1_text', 'kyujin2_text', 'kyujin3_text',
            ]);
        });
    }
};
