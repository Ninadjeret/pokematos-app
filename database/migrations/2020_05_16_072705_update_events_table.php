<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('discord_link');
            $table->string('channel_discord_type')->default('none')->after('image');
            $table->boolean('delete_after_end')->default(1)->after('channel_discord_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('channel_discord_type');
            $table->dropColumn('delete_after_end');
            $table->boolean('discord_link')->default(0);
        });
    }
}
