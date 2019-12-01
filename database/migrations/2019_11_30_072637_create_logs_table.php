<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::rename('announces', 'user_actions');
        Schema::create('logs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('city_id');
            $table->integer('guild_id')->nullable();
            $table->string('type')->default('raid');
            $table->boolean('success')->default(0);
            $table->string('error')->nullable();
            $table->string('source_type')->default('img');
            $table->string('source');
            $table->integer('user_id')->default(0);
            $table->string('channel_discord_id')->nullable();
            $table->json('result')->nullable();
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
        Schema::rename('user_actions', 'announces' );
        Schema::dropIfExists('logs');
    }
}
