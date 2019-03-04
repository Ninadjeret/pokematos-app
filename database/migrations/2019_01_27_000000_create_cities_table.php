<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cities', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('slug');
            $table->float('lat', 10, 6)->nullable();
            $table->float('lng', 10, 6)->nullable();
            $table->timestamps();
        });

        Schema::create('guilds', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('discord_id')->unique();
            $table->string('name');
            $table->string('type');
            $table->integer('city_id');
            $table->string('access_rule')->default('everyone'); //everyone, specific_roles
            $table->json('authorized_roles')->nullable();
            $table->timestamps();

            $table->foreign('city_id')->references('id')->on('cities');
        });

        Schema::create('user_guilds', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('guild_id');
            $table->integer('user_id');
            $table->string('user_roles')->nullable();
            $table->boolean('admin')->default(false);
            $table->timestamps();

            $table->foreign('guild_id')->references('id')->on('guilds');
            $table->foreign('user_id')->references('id')->on('users');
        });

        Schema::create('pokemons', function (Blueprint $table) {
            $table->increments('id');
            $table->string('pokedex_id');
            $table->string('niantic_id');
            $table->string('name_fr')->nullable();
            $table->string('form_id')->default('00');
            $table->integer('base_att')->nullable();
            $table->integer('base_def')->nullable();
            $table->integer('base_sta')->nullable();
            $table->boolean('boss')->default(false);
            $table->integer('boss_level')->nullable();
            $table->boolean('shiny')->default(false);
            $table->integer('parent_id')->nullable();
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('pokemons');
        });

        Schema::create('zones', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('city_id');
            $table->timestamps();

            $table->foreign('city_id')->references('id')->on('cities');
        });

        Schema::create('stops', function (Blueprint $table) {
            $table->increments('id');
            $table->string('niantic_name');
            $table->string('name');
            $table->string('description')->nullable();
            $table->float('lat', 10, 5)->nullable();
            $table->float('lng', 10, 5)->nullable();
            $table->boolean('ex')->default(false);
            $table->boolean('gym')->default(false);
            $table->integer('city_id');
            $table->integer('zone_id')->nullable();
            $table->timestamps();

            $table->foreign('city_id')->references('id')->on('cities');
            $table->foreign('zone_id')->references('id')->on('zones');
        });

        Schema::create('announces', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('raid_id');
            $table->string('type');
            $table->string('source');
            $table->dateTime('date');
            $table->integer('user_id');
            $table->string('url')->nullable();
            $table->text('content')->nullable();
            $table->integer('message_id')->nullable();
            $table->integer('guild_id')->nullable();
            $table->timestamps();

            $table->foreign('raid_id')->references('id')->on('raids');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('guild_id')->references('id')->on('guilds');
        });

        Schema::create('raids', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('egg_level');
            $table->dateTime('start_time');
            $table->integer('pokemon_id')->nullable();
            $table->integer('city_id')->nullable();
            $table->integer('gym_id')->nullable();
            $table->timestamps();

            $table->foreign('pokemon_id')->references('id')->on('pokemons');
            $table->foreign('city_id')->references('id')->on('cities');
            $table->foreign('gym_id')->references('id')->on('stops');
        });

        Schema::create('connectors', function (Blueprint $table) {
            $table->increments('id');
            $table->string('filter_pokemon_type')->default('none'); //none, level, pokemon
            $table->json('filter_pokemon_level')->nullable();
            $table->json('filter_pokemon_pokemon')->nullable();
            $table->string('filter_gym_type')->default('none'); //none, zone, gym
            $table->json('filter_gym_zone')->nullable();
            $table->json('filter_gym_gym')->nullable();
            $table->string('format')->default('auto'); //auto, custom
            $table->string('custom_message_before')->nullable();
            $table->string('custom_message_after')->nullable();
            $table->boolean('delete_after_end');
            $table->string('guild_id');
            $table->string('channel_id');
            $table->timestamps();

            $table->foreign('guild_id')->references('id')->on('guilds');
        });

        Schema::create('channel_types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('guild_id');
            $table->string('description')->default('none'); //none, level, pokemon
            $table->timestamps();

            $table->foreign('guild_id')->references('id')->on('guilds');
        });

        Schema::create('channels', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('discord_id');
            $table->integer('guild_id');
            $table->integer('type_id');
            $table->timestamps();

            $table->foreign('guild_id')->references('id')->on('guilds');
            $table->foreign('type_id')->references('id')->on('channel_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cities');
    }
}
