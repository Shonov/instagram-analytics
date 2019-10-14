<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TimeAndCanViewForStatistic extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('statistics', function (Blueprint $table) {
            $table->boolean('can_view')->default(false);
            $table->integer('time_to_grab_subs')->nullable()->default(0);
        });

        DB::table('statistics')->update([
            'can_view' => true,
            'time_to_grab_subs' => 0,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('statistics', function (Blueprint $table) {
            $table->dropColumn('can_view');
            $table->dropColumn('time_to_grab_subs');
        });
    }
}
