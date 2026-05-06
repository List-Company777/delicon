<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('casts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shop_id');
            $table->string('name', 100);
            $table->unsignedTinyInteger('age')->nullable();
            $table->unsignedSmallInteger('tall')->nullable();
            $table->unsignedTinyInteger('bust')->nullable();
            $table->char('cup', 2)->nullable();
            $table->unsignedTinyInteger('west')->nullable();
            $table->unsignedTinyInteger('hip')->nullable();
            $table->string('img_file_name', 255)->nullable();
            $table->unsignedInteger('type_id')->nullable();
            $table->unsignedInteger('body_id')->nullable();
            $table->text('comment')->nullable();
            $table->text('message')->nullable();
            $table->string('blood', 5)->nullable();
            $table->string('country', 70)->nullable();
            $table->string('hatsutaiken', 70)->nullable();
            $table->string('seikantai', 70)->nullable();
            $table->string('tokuiwaza', 70)->nullable();
            $table->string('sukinatype', 70)->nullable();
            $table->string('shumi', 70)->nullable();
            $table->string('zenshoku', 70)->nullable();
            $table->tinyInteger('tabacco')->default(0);
            $table->string('seiza', 70)->nullable();
            $table->string('likeeat', 70)->nullable();
            $table->tinyInteger('osake')->default(0);
            $table->string('yuumeijin', 70)->nullable();
            $table->string('shiofuki', 30)->nullable();
            $table->string('zitaku', 30)->nullable();
            $table->string('twitter_account', 100)->nullable();
            $table->string('official_url', 255)->nullable();
            $table->unsignedInteger('price_on')->nullable();
            $table->boolean('is_recommended')->default(false);
            $table->unsignedInteger('sort_order')->default(9999);
            $table->integer('ranking_count')->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->integer('old_id')->nullable()->unique();
            $table->timestamps();

            $table->foreign('shop_id')->references('id')->on('shops')->onDelete('cascade');
            $table->index(['shop_id', 'status']);
            $table->index(['status', 'sort_order']);
        });
    }
    public function down(): void { Schema::dropIfExists('casts'); }
};
