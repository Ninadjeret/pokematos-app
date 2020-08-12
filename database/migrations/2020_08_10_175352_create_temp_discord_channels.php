<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTempDiscordChannels extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('channels');
        Schema::dropIfExists('channel_types');

        Schema::create('discord_channels', function (Blueprint $table) {
            $table->increments('id');
            $table->string('relation_type');
            $table->integer('relation_id');
            $table->integer('guild_id');
            $table->string('discord_id');
            $table->string('type')->nullable();
            $table->integer('connector_id')->nullable();
            $table->dateTime('to_delete_at')->nullable();
            $table->timestamps();
        });

        Schema::create('discord_messages', function (Blueprint $table) {
            $table->increments('id');
            $table->string('relation_type');
            $table->integer('relation_id');
            $table->integer('guild_id');
            $table->string('discord_id');
            $table->string('channel_discord_id');
            $table->string('type')->nullable();
            $table->integer('connector_id')->nullable();
            $table->dateTime('to_delete_at')->nullable();
            $table->timestamps();
        });

        Schema::create('raid_groups', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('guild_id');
            $table->integer('raid_id');
            $table->timestamps();
        });

        Schema::create('raid_participants', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('raid_group_id');
            $table->integer('user_id');
            $table->string('type')->default('present'); // or remote
            $table->integer('accounts')->default(1); // or remote
            $table->timestamps();
        });

        Schema::table('connectors', function (Blueprint $table) {
            $table->boolean('add_channel')->default(0)->after('delete_after_end');
            $table->string('channei_category_discord_id')->nullable()->after('add_channel');
            $table->string('channel_duration')->nullable()->after('channei_category_discord_id');
            $table->boolean('add_participants')->default(0)->after('channel_duration');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('discord_channels');
        Schema::dropIfExists('discord_messages');
        Schema::dropIfExists('raid_groups');
        Schema::dropIfExists('raid_participants');

        Schema::table('connectors', function (Blueprint $table) {
            $table->dropColumn('add_channel');
            $table->dropColumn('channei_category_discord_id');
            $table->dropColumn('channel_duration');
            //$table->dropColumn('add_participants');
        });
    }
}