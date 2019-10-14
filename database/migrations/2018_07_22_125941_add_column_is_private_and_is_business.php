<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnIsPrivateAndIsBusiness extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('instagram_profiles', function (Blueprint $table) {
            $table->boolean('is_private')->nullable()->default(null);
            $table->boolean('is_business')->nullable()->default(null);
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
            $table->dropColumn(['is_private', 'is_business']);
        });
    }
}
