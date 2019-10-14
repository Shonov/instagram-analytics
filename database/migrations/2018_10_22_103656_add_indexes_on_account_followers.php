<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexesOnAccountFollowers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \DB::statement('CREATE INDEX instagram_account_id_instagram_account_followers_idx ON instagram_account_followers (instagram_account_id);');
        \DB::statement('CREATE INDEX instagram_profile_id_instagram_account_followers_idx ON instagram_account_followers (instagram_profile_id);');
        \DB::statement('CREATE INDEX created_at_instagram_account_followers_idx ON instagram_account_followers (created_at);');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \DB::statement('DROP INDEX instagram_account_id_instagram_account_followers_idx;');
        \DB::statement('DROP INDEX instagram_profile_id_instagram_account_followers_idx;');
        \DB::statement('DROP INDEX created_at_instagram_account_followers_idx;');
    }
}
