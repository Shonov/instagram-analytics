<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTopUnfollowers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('top_unfollowers', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('statistic_id');
            $table->foreign('statistic_id')->references('id')->on('statistics');
            $table->integer('follower_count');

            $table->unsignedBigInteger('instagram_profile_id');
            $table->foreign('instagram_profile_id')->references('id')->on('instagram_profiles');

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
        Schema::dropIfExists('top_unfollowers');
    }
}
