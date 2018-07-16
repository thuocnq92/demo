<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserDeviceToken extends Model
{
    const TOPIC_LIVE_GAME = 'arn:aws:sns:ap-southeast-1:639461452342:send_notification_game';
    const TOPIC_LIVE_GAME_IOS = 'arn:aws:sns:ap-southeast-1:639461452342:send_notification_game_ios';
}
