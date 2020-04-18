<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEventQuizsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('event_quizs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('event_id');
            $table->integer('nb_questions')->default(10);
            $table->integer('delay')->default(5);
            $table->json('themes');
            $table->json('difficulties');
            $table->boolean('only_pogo')->defaul(0);
            $table->string('message_discord_id')->nullable();
            $table->timestamps();
        });

        Schema::create('event_quiz_questions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('quiz_id');
            $table->integer('question_id');
            $table->dateTime('start_time')->nullable();
            $table->dateTime('end_time')->nullable();
            $table->integer('order')->nullable();
            $table->timestamps();
        });

        Schema::create('event_quiz_answers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('question_id');
            $table->integer('user_id');
            $table->integer('guild_id');
            $table->string('message_discord_id')->nullable();
            $table->dateTime('answer_time')->nullable();
            $table->boolean('correct')->default(0);
            $table->timestamps();
        });

        Schema::create('quiz_questions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('question');
            $table->string('answer');
            $table->text('explanation')->nullable();
            $table->text('tip')->nullable();
            $table->json('alt_answers')->nullable();
            $table->boolean('about_pogo')->default(0);
            $table->integer('difficulty')->default(1);
            $table->string('theme_id')->nullable();
            $table->timestamps();
        });
        Schema::create('quiz_themes', function (Blueprint $table) {
            $table->increments('id');
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
        Schema::dropIfExists('event_quizs');
        Schema::dropIfExists('event_quiz_questions');
        Schema::dropIfExists('event_quiz_answers');
        Schema::dropIfExists('quiz_questions');
        Schema::dropIfExists('quiz_themes');
    }
}
