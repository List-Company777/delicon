<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE jobs MODIFY COLUMN wage_type ENUM('hourly','daily','monthly','commission') NOT NULL DEFAULT 'hourly'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE jobs MODIFY COLUMN wage_type ENUM('hourly','daily','monthly') NOT NULL DEFAULT 'hourly'");
    }
};
