<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUserFollowersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('instagram_profiles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('full_name');
            $table->longText('profile_pic_url');
            $table->integer('following_count')->default(0);
            $table->integer('follower_count')->default(0);
            $table->integer('media_count')->default(0);

            $table->timestampsTz();
        });

        Schema::create('instagram_account_followers', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('instagram_profile_id');
            $table->foreign('instagram_profile_id')->references('id')->on('instagram_profiles');


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
        Schema::dropIfExists('instagram_account_followers');
        Schema::dropIfExists('instagram_profiles');
    }
}
