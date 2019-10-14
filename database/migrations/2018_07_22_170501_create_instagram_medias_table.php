<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInstagramMediasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('instagram_medias', function (Blueprint $table) {
            $table->unsignedBigInteger('id', false);
            $table->primary('id');
            $table->tinyInteger('media_type');
            $table->unsignedBigInteger('like_count');
            $table->unsignedBigInteger('comment_count');
            $table->unsignedBigInteger('view_count');
            $table->unsignedTinyInteger('filter_type');
            $table->longText('pic_url');

            $table->unsignedBigInteger('instagram_account_id');
            $table->foreign('instagram_account_id')->references('id')->on('instagram_accounts');

            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('instagram_medias');
    }
}
