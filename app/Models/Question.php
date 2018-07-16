<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    const STATUS_INIT = 0;
    const STATUS_OPENED = 1;
    const STATUS_ANSWERED = 2;

    protected $table = 'questions';
    public $timestamps = false;
    protected $fillable = ['game_id', 'no', 'question', 'answer1', 'answer2', 'answer3', 'answer4', 'correct_answer'];
    protected $casts = [
        'game_id' => 'Int',
        'no' => 'Int',
        'correct_answer' => 'Int'
    ];

    protected $hidden = ['correct_answer'];
    protected $dates = ['expired_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function answers()
    {
        return $this->hasMany(Answer::class, 'question_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function game() {
        return $this->belongsTo(Game::class, 'game_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function setting() {
        return $this->hasOne(GameSetting::class, 'question_id');
    }
}
