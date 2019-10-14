<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexesOnAccountMedias extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \DB::statement('CREATE INDEX instagram_account_id_instagram_account_medias_idx ON instagram_account_medias (instagram_account_id);');
        \DB::statement('CREATE INDEX instagram_media_id_instagram_account_medias_idx ON instagram_account_medias (instagram_media_id);');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \DB::statement('DROP INDEX instagram_account_id_instagram_account_medias_idx;');
        \DB::statement('DROP INDEX instagram_media_id_instagram_account_medias_idx;');
    }
}
