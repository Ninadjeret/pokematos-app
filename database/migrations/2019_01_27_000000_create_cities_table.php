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
            $table->float('lat', 10, 6)->default(48.045741);
            $table->float('lng', 10, 6)->default(-1.6082411);
            $table->timestamps();
        });

        Schema::create('guilds', function (Blueprint $table) {
            $table->increments('id');
            $table->string('discord_id')->unique()->nullable();
            $table->string('name')->nullable();
            $table->string('type')->default('discord');
            $table->integer('city_id')->nullable();
            $table->string('token')->nullable();
            $table->boolean('active')->default(0);
            $table->timestamps();

            $table->foreign('city_id')->references('id')->on('cities');
        });

        Schema::create('user_guilds', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('guild_id');
            $table->integer('user_id');
            $table->string('user_roles')->nullable();
            $table->integer('permissions')->default(0);
            $table->timestamps();

            $table->foreign('guild_id')->references('id')->on('guilds');
            $table->foreign('user_id')->references('id')->on('users');
        });

        Schema::create('pokemons', function (Blueprint $table) {
            $table->increments('id');
            $table->string('pokedex_id');
            $table->string('niantic_id');
            $table->string('name_fr')->nullable();
            $table->string('name_ocr')->nullable();
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
            $table->integer('raid_id')->nullable();
            $table->integer('quest_instance_id')->nullable();
            $table->string('type');
            $table->string('source');
            $table->dateTime('date');
            $table->integer('user_id');
            $table->string('url')->nullable();
            $table->text('content')->nullable();
            $table->string('message_discord_id')->nullable();
            $table->string('channel_discord_id')->nullable();
            $table->integer('guild_id')->nullable();
            $table->boolean('confirmed')->default(true);
            $table->timestamps();

            $table->foreign('raid_id')->references('id')->on('raids');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('guild_id')->references('id')->on('guilds');
        });

        Schema::create('raid_channels', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('raid_id');
            $table->integer('guild_id');
            $table->string('channel_discord_id');
            $table->timestamps();

            $table->foreign('raid_id')->references('id')->on('raids');
            $table->foreign('guild_id')->references('id')->on('guilds');
        });

        Schema::create('raid_messages', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('raid_id');
            $table->integer('guild_id');
            $table->string('message_discord_id');
            $table->string('channel_discord_id');
            $table->timestamps();

            $table->foreign('raid_id')->references('id')->on('raids');
            $table->foreign('guild_id')->references('id')->on('guilds');
        });

        Schema::create('raids', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('egg_level');
            $table->dateTime('start_time');
            $table->integer('pokemon_id')->nullable();
            $table->integer('city_id')->nullable();
            $table->integer('gym_id')->nullable();
            $table->boolean('ex')->default(false);
            $table->string('status')->default('future');
            $table->timestamps();

            $table->foreign('pokemon_id')->references('id')->on('pokemons');
            $table->foreign('city_id')->references('id')->on('cities');
            $table->foreign('gym_id')->references('id')->on('stops');
        });

        Schema::create('connectors', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('filter_pokemon_type')->default('none'); //none, level, pokemon
            $table->json('filter_pokemon_level')->nullable();
            $table->json('filter_pokemon_pokemon')->nullable();
            $table->string('filter_gym_type')->default('none'); //none, zone, gym
            $table->json('filter_gym_zone')->nullable();
            $table->json('filter_gym_gym')->nullable();
            $table->string('format')->default('auto'); //auto, custom
            $table->json('filter_source_type')->nullable();
            $table->string('custom_message_before')->nullable();
            $table->string('custom_message_after')->nullable();
            $table->boolean('delete_after_end')->default(false);
            $table->string('guild_id');
            $table->string('channel_discord_id');
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
            $table->string('discord_id');
            $table->integer('guild_id');
            $table->integer('type_id');
            $table->timestamps();

            $table->foreign('guild_id')->references('id')->on('guilds');
            $table->foreign('type_id')->references('id')->on('channel_types');
        });

        Schema::create('guild_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('guild_id');
            $table->string('key');
            $table->string('value')->nullable();
            $table->timestamps();

            $table->foreign('guild_id')->references('id')->on('guilds');
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('discord_id');
            $table->integer('guild_id');
            $table->integer('category_id')->nullable();
            $table->string('name');
            $table->string('color_type')->default('category');
            $table->string('color')->default('#000000');
            $table->string('type')->nullable();
            $table->integer('gym_id')->nullable();
            $table->integer('zone_id')->nullable();
            $table->integer('pokemon_id')->nullable();
            $table->string('channel_discord_id')->nullable();
            $table->string('message_discord_id')->nullable();
            $table->timestamps();

            $table->foreign('guild_id')->references('id')->on('guilds');
            $table->foreign('gym_id')->references('id')->on('gyms');
            $table->foreign('zone_id')->references('id')->on('zones');
            $table->foreign('pokemon_id')->references('id')->on('pokemons');
            $table->foreign('category_id')->references('id')->on('role_categories');
        });

        Schema::create('role_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('guild_id');
            $table->string('name');
            $table->string('color')->default('#000000');
            $table->boolean('notifications')->default(false);
            $table->string('channel_discord_id')->nullable();
            $table->boolean('restricted')->default(false);
            $table->timestamps();

            $table->foreign('guild_id')->references('id')->on('guilds');
        });

        Schema::create('role_permissions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('role_category_id');
            $table->text('channels');
            $table->string('type')->default('auth');
            $table->text('roles');
            $table->timestamps();

            $table->foreign('role_category_id')->references('id')->on('role_categories');
        });

        Schema::create('quest_rewards', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('type');
            $table->timestamps();
        });

        Schema::create('quests', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('reward_type')->default('pokemon');
            $table->integer('reward_id')->nullable();
            $table->integer('pokemon_id')->nullable();
            $table->timestamps();

            $table->foreign('reward_id')->references('id')->on('quest_rewards');
            $table->foreign('pokemon_id')->references('id')->on('pokemons');
        });

        Schema::create('quest_connectors', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('filter_reward_type')->default('none'); //none, level, pokemon
            $table->json('filter_reward_reward')->nullable();
            $table->json('filter_reward_pokemon')->nullable();
            $table->string('filter_stop_type')->default('none'); //none, zone, gym
            $table->json('filter_stop_zone')->nullable();
            $table->json('filter_stop_stop')->nullable();
            $table->string('format')->default('auto'); //auto, custom
            $table->string('custom_message')->nullable();
            $table->boolean('delete_after_end')->default(false);
            $table->string('guild_id');
            $table->string('channel_discord_id');
            $table->timestamps();

            $table->foreign('guild_id')->references('id')->on('guilds');
        });

        Schema::create('quest_instances', function (Blueprint $table) {
            $table->increments('id');
            $table->dateTime('date');
            $table->integer('quest_id')->nullable();
            $table->integer('city_id')->nullable();
            $table->integer('gym_id')->nullable();
            $table->timestamps();

            $table->foreign('quest_id')->references('id')->on('quests');
            $table->foreign('city_id')->references('id')->on('cities');
            $table->foreign('gym_id')->references('id')->on('gyms');
        });

        Schema::create('quest_messages', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('quest_instance_id');
            $table->integer('guild_id');
            $table->string('message_discord_id');
            $table->string('channel_discord_id');
            $table->timestamps();

            $table->foreign('quest_instance_id')->references('id')->on('quest_instances');
            $table->foreign('guild_id')->references('id')->on('guilds');
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
