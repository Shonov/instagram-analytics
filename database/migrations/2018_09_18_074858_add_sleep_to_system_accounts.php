<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSleepToSystemAccounts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('system_accounts', function (Blueprint $table) {
            $table->dateTimeTz('start_work_at')->nullable(true);
            $table->boolean('is_work')->default(false);
        });

        DB::table('system_accounts')->update([
            'start_work_at' => \Carbon\Carbon::yesterday(),
            'is_work' => true,
        ]);

        Schema::table('system_accounts', function (Blueprint $table) {
            $table->dateTimeTz('start_work_at')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('system_accounts', function (Blueprint $table) {
            $table->dropColumn('start_work_at');
            $table->dropColumn('is_work');
        });
    }
}
