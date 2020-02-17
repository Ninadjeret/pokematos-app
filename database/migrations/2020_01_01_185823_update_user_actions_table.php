<?php

use App\Models\UserAction;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateUserActionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_actions', function (Blueprint $table) {
            $table->string('relation_id')->after('type')->nullable();
        });
        foreach( UserAction::all() as $userAction ) {
            if( !empty($userAction->quest_instance_id) ) {
                $userAction->update(['relation_id' => $userAction->quest_instance_id]);
            }
            if( !empty($userAction->raid_id) ) {
                $userAction->update(['relation_id' => $userAction->raid_id]);
            }
        }
        Schema::table('user_actions', function (Blueprint $table) {
            $table->dropColumn('quest_instance_id');
            $table->dropColumn('raid_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
