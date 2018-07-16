<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    use Notifiable;

    const SHOW_RANK = 1;
    const HIDE_RANK = 0;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'phone_id', 'avatar', 'affiliate_id', 'referred_by'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];

    protected $appends = ['phone_number', 'bank_name', 'bank_branch', 'bank_account'];

    protected $casts = [
        'current_amount' => 'Int',
        'total_amount' => 'Int',
        'seven_days_amount' => 'Int',
        'fukkatu' => 'Int',
        'show_ranking' => 'Boolean'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function phone()
    {
        return $this->belongsTo(Phone::class);
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeIsActived($query)
    {
        return $query->whereNotNull('name');
    }

    /**
     * @return string
     */
    public function getBankNameAttribute()
    {
        $bank_name = '';
        $bank = $this->bank()->first();
        if ($bank) {
            $bank_name = $bank->bank_name;
        }

        return $bank_name;
    }

    /**
     * @return string
     */
    public function getBankBranchAttribute()
    {
        $bank_branch = '';
        $bank = $this->bank()->first();
        if ($bank) {
            $bank_branch = $bank->bank_branch;
        }

        return $bank_branch;
    }

    /**
     * @return string
     */
    public function getBankAccountAttribute()
    {
        $bank_account = '';
        $bank = $this->bank()->first();
        if ($bank) {
            $bank_account = $bank->bank_id;
        }

        return $bank_account;
    }

    /**
     * @return bool
     */
    public function getIsNewUserAttribute()
    {
        $new_user = false;

        if (empty($this->name)) {
            $new_user = true;
        }

        return $new_user;
    }

    /**
     * @return mixed|null
     */
    public function getPhoneNumberAttribute()
    {
        $phone_number = null;
        if (!empty($this->phone_id)) {
            $phone = $this->phone()->first();
            if ($phone) {
                $phone_number = $phone->phone;
            }
        }

        return $phone_number;
    }

    /**
     * Generate random 8 number affiliate_id unique
     *
     * @return int
     */
    public static function generate_unique_affiliate_id()
    {
        $affiliate_id = rand(10000000, 99999999);
        $exist = self::where('affiliate_id', $affiliate_id)->first();

        if ($exist) {
            $results = self::generate_unique_affiliate_id();
        } else {
            $results = $affiliate_id;
        }

        return $results;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function answers()
    {
        return $this->hasMany(Answer::class, 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function bank()
    {
        return $this->hasOne(UserBank::class, 'user_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function referrer()
    {
        return $this->hasOne(User::class, 'id', 'referred_by');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'user_id', 'id');
    }
}
