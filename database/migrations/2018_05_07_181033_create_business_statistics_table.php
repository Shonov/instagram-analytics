<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBusinessStatisticsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('business_statistics', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('average_engagement_count')->nullable();
            $table->integer('followers_count')->nullable();
            $table->integer('followers_delta_from_last_week')->nullable();
            $table->integer('last_week_call')->nullable();
            $table->integer('last_week_email')->nullable();
            $table->integer('last_week_get_direction')->nullable();
            $table->integer('last_week_impressions')->nullable();
            $table->integer('last_week_profile_visits')->nullable();
            $table->integer('last_week_reach')->nullable();
            $table->integer('last_week_text')->nullable();
            $table->integer('last_week_website_visits')->nullable();
            $table->integer('posts_delta_from_last_week')->nullable();
            $table->integer('week_over_week_call')->nullable();
            $table->integer('week_over_week_email')->nullable();
            $table->integer('week_over_week_get_direction')->nullable();
            $table->integer('week_over_week_impressions')->nullable();
            $table->integer('week_over_week_profile_visits')->nullable();
            $table->integer('week_over_week_reach')->nullable();
            $table->integer('week_over_week_text')->nullable();
            $table->integer('week_over_week_website_visits')->nullable();

            $table->unsignedInteger('statistic_id')->nullable();
            $table->foreign('statistic_id')->references('id')->on('statistics');

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
        Schema::dropIfExists('business_statistics');
    }
}
