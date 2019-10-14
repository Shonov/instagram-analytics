<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class LinkInstagramAccountsWithUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('instagram_accounts', function (Blueprint $table) {
            $table->unsignedBigInteger('id', false)->change();
            $table->dropColumn('password');
        });

        Schema::table('statistics', function (Blueprint $table) {
            $table->unsignedBigInteger('instagram_account_id')->change();
        });

        Schema::table('instagram_account_followers', function (Blueprint $table) {
            $table->unsignedBigInteger('instagram_account_id')->change();
        });

        Schema::create('users_instagram_accounts', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');

            $table->unsignedBigInteger('instagram_account_id');
            $table->foreign('instagram_account_id')->references('id')->on('instagram_accounts');

            $table->enum('type', ['self_account', 'competitor']);

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
        Schema::dropIfExists('users_instagram_accounts');

        Schema::table('instagram_accounts', function (Blueprint $table) {
            $table->bigInteger('id', false)->change();
            $table->string('password')->nullable();
        });
    }
}
