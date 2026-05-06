<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('prefectures', function (Blueprint $table) {
            $table->string('parent_slug', 30)->nullable()->after('slug');
        });

        $map = [
            1=>'tokyo',2=>'tokyo',3=>'tokyo',4=>'tokyo',5=>'tokyo',6=>'tokyo',
            7=>'kanagawa',8=>'saitama',9=>'chiba',10=>'ibaraki',
            11=>'tochigi',12=>'gunma',13=>'tokyo',
            14=>'osaka',15=>'osaka',16=>'osaka',17=>'osaka',18=>'osaka',
            19=>'osaka',20=>'osaka',21=>'osaka',
            22=>'kyoto',23=>'nara',24=>'shiga',
            25=>'hyogo',26=>'hyogo',27=>'hyogo',28=>'wakayama',
            29=>'aichi',30=>'aichi',31=>'aichi',
            32=>'gifu',33=>'shizuoka',34=>'shizuoka',35=>'mie',
        ];
        foreach ($map as $id => $slug) {
            DB::table('prefectures')->where('id', $id)->update(['parent_slug' => $slug]);
        }
    }

    public function down(): void
    {
        Schema::table('prefectures', function (Blueprint $table) {
            $table->dropColumn('parent_slug');
        });
    }
};
