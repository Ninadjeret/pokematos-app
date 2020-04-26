<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEventInvitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('event_invits', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('event_id');
            $table->string('guild_id');
            $table->string('status')->default('pending');
            $table->dateTime('status_time');
            $table->string('channel_discord_id')->nullable();
            $table->timestamps();
        });
        Schema::table('events', function (Blueprint $table) {
            $table->boolean('multi_guilds')->nullable()->after('type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('event_invits');
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('multi_guilds');
        });
    }
}
