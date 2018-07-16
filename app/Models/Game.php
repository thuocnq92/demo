<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Game extends Model
{
    use SoftDeletes;

    const NOT_NOTIFY = 0;
    const IS_NOTIFIED = 1;
    const STATUS_DEFAULT = 0;
    const STATUS_OPENED = 1;
    const STATUS_SHOW = 2;
    const STATUS_ENDED = 3;

    protected $table = 'games';
    public $timestamps = false;
    public $fillable = ['date', 'date_notification', 'name', 'price', 'live_code', 'joiners'];
    protected $appends = ['stream_link', 'is_join'];
    public $dates = ['date', 'date_notification', 'deleted_at'];
    public $casts = [
        'price' => 'Double',
        'joiners' => 'array'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function answers()
    {
        return $this->hasMany(Answer::class, 'game_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function questions()
    {
        return $this->hasMany(Question::class, 'game_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'answers', 'game_id', 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class, 'game_id');
    }

    /**
     * @return string
     */
    public function setJoinersAttribute($value)
    {
        return $this->attributes['joiners'] = is_array($value) ? json_encode($value) : $value;
    }

    public function getJoinersAttribute()
    {
        return (is_string($this->attributes['joiners']) && $this->attributes['joiners'] != '') ? json_decode($this->attributes['joiners']) : [];
    }

    /**
     * @return bool
     */
    public function getIsJoinAttribute()
    {
        // Check user login
        $user = Auth::user();
        if (empty($user)) {
            return false;
        }

        // If game have status is Init to user can join
        if ($this->status == self::STATUS_DEFAULT) {
            return true;
        }

        // If game status is open and not have question is open to user can join
        if ($this->status == self::STATUS_OPENED) {
            $question_opened = $this->questions()
                ->where('status', Question::STATUS_ANSWERED)
                ->first();
            if (empty($question_opened)) {
                return true;
            }
        }

        // If game have user belong to joiners to user can join
        if (is_array($this->joiners) && in_array($user->id, $this->joiners)) {
            return true;
        }

        return false;
    }

    /**
     * @return string
     */
    public function getStreamLinkAttribute()
    {
        $link = '';
        if (!empty($this->live_code)) {
            $link = env('HOST_STREAM', 'rtmp://13.231.12.160/live1/') . $this->live_code;
        }

        return $link;
    }

    /**
     * @param array $input
     * @return $this|Model
     */
    public static function createGame(array $input)
    {
        $input['live_code'] = self::generateLiveCode();
        $game = self::create($input);

        return $game;
    }

    /**
     * @param array $input
     * @return Model
     */
    public function createNotifications(array $input)
    {
        $content = '';
        if (isset($input['content'])) {
            $content = $input['content'];
        }
        $time = Carbon::createFromFormat('Y-m-d H:i:s', $input['time']);
        $notification = $this->notifications()->create([
            'time' => $time,
            'content' => $content
        ]);

        return $notification;
    }

    /**
     * Generate live code and check unique
     * @return string
     */
    protected static function generateLiveCode()
    {
        $str = str_random(6);
        $check = true;
        while ($check) {
            $model = Game::query()->where('live_code', $str)->first();
            if (!$model) {
                $check = false;
            } else {
                $str = str_random(6);
            }
        }

        return $str;
    }

    /**
     * @param User $user
     * @param int $bonus
     * @param null $game_id
     */
    public static function createTransaction(User $user, $bonus = 0, $game_id = null)
    {
        $user->total_amount += doubleval($bonus);
        $user->current_amount += doubleval($bonus);

        $user->save();

        $transaction = new Transaction();
        $transaction->user_id = $user->id;
        $transaction->date = Carbon::now();
        $transaction->type = Transaction::TYPE_RECEIVE_MONEY;
        $transaction->txn_amount = (double)$bonus;
        $transaction->current_amount = $user->current_amount;
        $transaction->note = 'Received money from game ' . $game_id;
        $transaction->reference_game_id = $game_id;

        $transaction->save();
    }
}
