<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGameSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('game_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('game_id')->unsigned();
            $table->tinyInteger('answer_time')->unsigned()->default(\App\Models\GameSetting::ANSWER_TIME);
            $table->tinyInteger('correct_answer_time')->unsigned()->default(\App\Models\GameSetting::CORRECT_ANSWER_TIME);
            $table->tinyInteger('wrong_answer_time')->unsigned()->default(\App\Models\GameSetting::WRONG_ANSWER_TIME);
            $table->tinyInteger('point_heart_time')->unsigned()->default(\App\Models\GameSetting::POINT_HEART_TIME);
            $table->tinyInteger('no_point_heart_time')->unsigned()->default(\App\Models\GameSetting::NO_POINT_HEART_TIME);
            $table->tinyInteger('show_answer_again_time')->unsigned()->default(\App\Models\GameSetting::SHOW_ANSWER_AGAIN_TIME);
            $table->tinyInteger('view_mode_time')->unsigned()->default(\App\Models\GameSetting::VIEW_MODE_TIME);
            $table->tinyInteger('show_result_time')->unsigned()->default(\App\Models\GameSetting::SHOW_RESULT_TIME);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('game_settings');
    }
}
