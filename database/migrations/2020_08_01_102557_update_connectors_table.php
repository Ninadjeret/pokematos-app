<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateConnectorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('connectors', function (Blueprint $table) {
            $table->string('filter_ex_type')->defautl('none')->after('filter_gym_gym');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('connectors', function (Blueprint $table) {
            $table->dropColumn('filter_ex_type');
        });
    }
}