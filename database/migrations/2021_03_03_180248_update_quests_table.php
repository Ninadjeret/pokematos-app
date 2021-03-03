<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateQuestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('quests', function (Blueprint $table) {
            $table->boolean('event')->default(0)->after('name');
        });
        Schema::table('quest_connectors', function (Blueprint $table) {
            $table->string('filter_event')->default('none')->after('filter_reward_pokemon');
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
            $table->dropColumn('event');
        });
        Schema::table('quest_connectors', function (Blueprint $table) {
            $table->dropColumn('filter_event');
        });
    }
}
