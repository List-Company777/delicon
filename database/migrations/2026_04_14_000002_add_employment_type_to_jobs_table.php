<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            // Schema.org JobPosting employmentType 準拠
            // PART_TIME=アルバイト / CONTRACTOR=業務委託 / FULL_TIME=正社員
            // PER_DIEM=日払い / OTHER=体験入店・その他
            $table->enum('employment_type', [
                'PART_TIME',
                'CONTRACTOR',
                'FULL_TIME',
                'PER_DIEM',
                'OTHER',
            ])->nullable()->after('working_hours');
        });
    }

    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropColumn('employment_type');
        });
    }
};
