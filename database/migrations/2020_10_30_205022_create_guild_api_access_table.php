<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGuildApiAccessTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('ext')->default(0)->after('password');
        });

        Schema::create('guild_api_access', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->nullable();
            $table->integer('guild_id');
            $table->string('key')->nullable();
            $table->json('authorizations')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('guild_api_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('api_access_id');
            $table->string('endpoint');
            $table->integer('status')->default(200);
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
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('ext');
        });
        Schema::dropIfExists('guild_api_access');
        Schema::dropIfExists('guild_api_logs');
    }
}