<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInstagramAccountMediasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('instagram_account_medias', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('like_count')->default(0);
            $table->unsignedBigInteger('comment_count')->default(0);
            $table->unsignedBigInteger('view_count')->default(0);
            $table->timestampsTz();

            $table->unsignedBigInteger('instagram_account_id');
            $table->foreign('instagram_account_id')->references('id')->on('instagram_accounts');

            $table->unsignedBigInteger('instagram_media_id');
            $table->foreign('instagram_media_id')->references('id')->on('instagram_medias');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('instagram_account_medias');
    }
}
