<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateQuestRewardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('quest_rewards', function (Blueprint $table) {
            $table->string('sstype')->nullable()->after('type');
            $table->integer('qty')->default(1)->after('sstype');
        });

        Schema::create('quest_reward_types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slug');
            $table->string('niantic_id');
            $table->string('name');
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
        Schema::table('quest_rewards', function (Blueprint $table) {
            $table->dropColumn('qty');
            $table->dropColumn('sstype');
        });
        Schema::dropIfExists('quest_reward_types');
    }
}
