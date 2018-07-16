<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    const CORRECT_ANSWER = 1;
    const WRONG_ANSWER = 2;
    const POINT_HEART = 3;

    protected $table = 'answers';
    public $timestamps = false;
    public $dates = ['date'];

    public $fillable = ['game_id', 'question_id', 'user_id', 'answer', 'date', 'result'];

    public $casts = [
        'game_id' => 'Int',
        'question_id' => 'Int',
        'answer' => 'Int',
        'result' => 'Int',
        'user_id' => 'Int'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function question(){
        return $this->belongsTo(Question::class, 'question_id');
    }
}
