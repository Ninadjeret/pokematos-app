<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class QuestRewardsAdd extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $rewards = [
            [
                'name' => 'Écaille Draco',
                'type' => 'evolve_object',
            ],
            [
                'name' => 'Peau Métal',
                'type' => 'evolve_object',
            ],
            [
                'name' => 'Améliorator',
                'type' => 'evolve_object',
            ],
            [
                'name' => 'Roche Royale',
                'type' => 'evolve_object',
            ],
            [
                'name' => 'Pierre Soleil',
                'type' => 'evolve_object',
            ],
            [
                'name' => 'Pierre Sinnoh',
                'type' => 'evolve_object',
            ],
            [
                'name' => 'Leurre magnétique',
                'type' => 'evolve_object',
            ],
            [
                'name' => 'Leurre galcial',
                'type' => 'evolve_object',
            ],
            [
                'name' => 'Leurre moussu',
                'type' => 'evolve_object',
            ],
            [
                'name' => 'Pierre d\'Unys',
                'type' => 'evolve_object',
            ],
        ];
        foreach( $rewards as $reward ) {
            DB::table('quest_rewards')->insert($reward);            
        }
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
