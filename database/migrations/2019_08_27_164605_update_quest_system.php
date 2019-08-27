<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateQuestSystem extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('quests', function (Blueprint $table) {
            $table->dropColumn('reward_type');
            $table->dropColumn('reward_id');
            $table->dropColumn('pokemon_id');
            $table->json('reward_ids')->after('name')->nullable();
            $table->json('pokemon_ids')->after('reward_ids')->nullable();
        });
        Schema::table('quest_instances', function (Blueprint $table) {
            $table->string('name')->after('quest_id')->nullable();
            $table->string('reward_type')->after('name')->nullable();
            $table->string('reward_id')->after('name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('quests', function (Blueprint $table) {
            //
        });
    }
}
