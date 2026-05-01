<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_types', function (Blueprint $table) {
            $table->string('short_name')->nullable()->after('name');
        });

        $shorts = [
            'counter-lady' => 'カウンター',
            'web-designer' => 'WEB',
            'floor-staff'  => 'フロア',
            'gaihan'       => '外販',
            'mens-cast'    => 'メンキャス',
            'annai'        => '案内',
            'bartender'    => 'バーテン',
            'karaoke'      => 'カラオケ',
            'cleaning'     => '清掃',
        ];

        foreach ($shorts as $slug => $short) {
            DB::table('job_types')->where('slug', $slug)->update(['short_name' => $short]);
        }
    }

    public function down(): void
    {
        Schema::table('job_types', function (Blueprint $table) {
            $table->dropColumn('short_name');
        });
    }
};
