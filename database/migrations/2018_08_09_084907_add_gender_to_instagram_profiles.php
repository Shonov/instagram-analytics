<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGenderToInstagramProfiles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('instagram_profiles', function (Blueprint $table) {
            $table->tinyInteger('gender')->nullable();
        });

        Schema::table('instagram_medias', function (Blueprint $table) {
            $table->timestampTz('posted_at')->nullable();
        });

        Schema::table('statistics', function (Blueprint $table) {
            $table->bigInteger('views_count')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('instagram_profiles', function (Blueprint $table) {
            $table->dropColumn('gender');
        });

        Schema::table('instagram_medias', function (Blueprint $table) {
            $table->dropColumn('posted_at');
        });

        Schema::table('statistics', function (Blueprint $table) {
            $table->dropColumn('views_count');
        });
    }
}
