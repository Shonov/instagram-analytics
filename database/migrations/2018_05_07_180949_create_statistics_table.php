<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStatisticsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('statistics', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('following_count');
            $table->integer('follower_count');
            $table->integer('media_count');
            $table->integer('usertags_count');
            $table->integer('like_count');
            $table->integer('comment_count');


            $table->unsignedInteger('instagram_account_id');
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
        Schema::dropIfExists('statistics');
    }
}
