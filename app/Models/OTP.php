<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OTP extends Model
{
    const TIME_OTP_EXPIRED = 10;
    const NOT_LOGGED = 0;
    const LOGGED = 1;

    protected $table = 'otps';

    protected $fillable = ['phone_id', 'code', 'is_logged', 'expired_at'];

    protected $dates = ['expired_at'];
}
