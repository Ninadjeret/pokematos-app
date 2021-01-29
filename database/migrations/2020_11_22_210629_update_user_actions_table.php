<?php

use App\Models\Raid;
use App\Models\Guild;
use App\Models\UserAction;
use App\Models\QuestInstance;
use App\Models\RocketInvasion;
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
            $table->unsignedInteger('city_id')->nullable()->after('guild_id');
        });

        $this->updateCityId();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_actions', function (Blueprint $table) {
            $table->dropColumn('city_id');
        });
    }

    public function updateCityId()
    {
        $actions = UserAction::whereIn('type', ['quest-create', 'quest-update', 'quest-duplicate'])->get();
        foreach ($actions as $action) {
            $quest = QuestInstance::find($action->relation_id);
            if (empty($quest)) continue;
            $action->update(['city_id' => $quest->city_id]);
        }

        $actions = UserAction::whereIn('type', ['rocket-invasion-create', 'rocket-invasion-update', 'rocket-invasion-duplicate'])->get();
        foreach ($actions as $action) {
            $invasion = RocketInvasion::find($action->relation_id);
            if (empty($invasion)) continue;
            $action->update(['city_id' => $invasion->city_id]);
        }

        $actions = UserAction::whereIn('type', ['raid-create', 'raid-update', 'raid-duplicate'])->get();
        foreach ($actions as $action) {
            $raid = Raid::find($action->relation_id);
            if (empty($raid)) continue;
            $action->update(['city_id' => $raid->city_id]);
        }

        $actions = UserAction::whereIn('type', ['conversation'])->get();
        foreach ($actions as $action) {
            $guild = Guild::find($action->guiuld_id);
            if (empty($guild)) continue;
            $action->update(['city_id' => $guild->city_id]);
        }
    }
}