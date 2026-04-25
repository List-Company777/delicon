<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE jobs ADD FULLTEXT INDEX jobs_title_ngram (title) WITH PARSER ngram');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE jobs DROP INDEX jobs_title_ngram');
    }
};
