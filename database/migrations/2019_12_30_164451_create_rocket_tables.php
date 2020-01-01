<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRocketTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rocket_bosses', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('level'); //1 pour les sbires, 2 pour les boss, 3 pour Giovianni
            $table->text('pokemon_step1')->nullable();
            $table->text('pokemon_step2')->nullable();
            $table->text('pokemon_step3')->nullable();
            $table->timestamps();
        });

        Schema::create('rocket_invasions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('city_id')->nullable();
            $table->integer('stop_id')->nullable();
            $table->date('date');
            $table->integer('boss_id')->nullable();
            $table->integer('pokemon_step1')->nullable();
            $table->integer('pokemon_step2')->nullable();
            $table->integer('pokemon_step3')->nullable();
            $table->timestamps();
        });

        $bosses = [
            [
                'name' => 'Cliff',
                'level' => 2
            ],
            [
                'name' => 'Arlo',
                'level' => 2
            ],
            [
                'name' => 'Sierra',
                'level' => 2
            ],
            [
                'name' => 'Giovanni',
                'level' => 3
            ],
        ];
        foreach( $bosses as $boss ) {
            DB::table('rocket_bosses')->insert($boss);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rocket_bosses');
        Schema::dropIfExists('rocket_invasions');
    }
}
