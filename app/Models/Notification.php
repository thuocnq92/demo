<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    const IS_SENT = 1;
    const IS_NOT_SENT = 0;

    protected $table = 'notifications';
    public $timestamps = false;
    protected $fillable = ['content', 'time'];
    protected $dates = ['time'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function game()
    {
        return $this->belongsTo(Game::class, 'game_id');
    }

    /**
     * @param string $game_id
     */
    public static function checkGameNotificationSented($game_id = '')
    {
        // If all notification is sent change is_notified is true
        if (!empty($game_id)) {
            $notifications = self::where('game_id', $game_id)
                ->where('is_sent', Notification::IS_NOT_SENT)
                ->get();

            if (count($notifications) == 0) {
                $game = Game::where('id', $game_id)
                    ->first();
                if ($game) {
                    $game->is_notified = Game::IS_NOTIFIED;
                    $game->save();
                }
            }
        }
    }

}
