<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSystemAccounts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //Страна	Логин	Пароль			IP	HTTP	SOCKS
        Schema::create('proxies', function (Blueprint $table) {
            $table->increments('id');
            $table->string('login');
            $table->string('password');
            $table->ipAddress('ip');
            $table->unsignedTinyInteger('http');
            $table->unsignedTinyInteger('socks');

            $table->timestampsTz();
        });

        Schema::create('system_accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('login');
            $table->string('password');

            $table->unsignedInteger('proxy_id');
            $table->foreign('proxy_id')->references('id')->on('proxies');

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
        Schema::dropIfExists('system_accounts');
        Schema::dropIfExists('proxies');
    }
}
