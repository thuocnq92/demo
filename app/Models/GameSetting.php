<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameSetting extends Model
{
    const ANSWER_TIME = 10;
    const CORRECT_ANSWER_TIME = 8;
    const WRONG_ANSWER_TIME = 2;
    const POINT_HEART_TIME = 8;
    const NO_POINT_HEART_TIME = 5;
    const SHOW_ANSWER_AGAIN_TIME = 2;
    const VIEW_MODE_TIME = 10;
    const SHOW_RESULT_TIME = 40;

    protected $fillable = [
        'game_id',
        'question_id',
        'answer_time',
        'correct_answer_time',
        'wrong_answer_time',
        'point_heart_time',
        'no_point_heart_time',
        'show_answer_again_time',
        'view_mode_time',
        'show_result_time'
    ];
    protected $table = 'game_settings';
    public $timestamps = false;
    protected $hidden = ['view_mode_time'];

    public static function defaultSetting()
    {
        return [
            'answer_time' => 10,
            'correct_answer_time' => 8,
            'wrong_answer_time' => 2,
            'point_heart_time' => 8,
            'no_point_heart_time' => 5,
            'show_answer_again_time' => 2,
            //'view_mode_time' => 10,
            'show_result_time' => 40,
        ];
    }
}
