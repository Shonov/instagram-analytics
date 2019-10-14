<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveMetricsFromMedias extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('instagram_medias', function (Blueprint $table) {
            $table->dropColumn([
                'like_count',
                'comment_count',
                'view_count',
                'instagram_account_id',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('instagram_medias', function (Blueprint $table) {
            $table->unsignedBigInteger('like_count')->default(0);
            $table->unsignedBigInteger('comment_count')->default(0);
            $table->unsignedBigInteger('view_count')->default(0);
            $table->unsignedBigInteger('instagram_account_id')->nullable();
            $table->foreign('instagram_account_id')->references('id')->on('instagram_accounts');
        });

    }
}
