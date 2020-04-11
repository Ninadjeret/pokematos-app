<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('city_id');
            $table->integer('guild_id');
            $table->dateTime('start_time');
            $table->dateTime('end_time')->nullable();
            $table->string('type');
            $table->integer('relation_id')->nullable();
            $table->boolean('discord_link')->default(0);
            $table->string('channel_discord_id')->nullable();
            $table->timestamps();
        });

        Schema::create('event_trains', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('event_id');
            $table->string('message_discord_id')->nullable();
            $table->timestamps();
        });

        Schema::create('event_train_steps', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('train_id');
            $table->string('type')->default('stop');
            $table->integer('stop_id')->nullable();
            $table->dateTime('start_time')->nullable();
            $table->float('duration')->nullable();
            $table->text('description')->nullable();
            $table->boolean('checked')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('events');
    }
}
