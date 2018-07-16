<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserBank extends Model
{
    const TYPE_COMMON = 1;
    const TYPE_CURRENT = 2;

    protected $table = 'user_bank';
    public $fillable = ['bank_id', 'bank_name', 'bank_branch', 'bank_owner', 'type', 'user_id'];
    protected $casts = [
//        'bank_name_id' => 'Int',
//        'bank_branch_id' => 'Int',
        'type' => 'Int',
    ];

    protected $hidden = ['bank_name_id', 'bank_branch_id'];

//    protected $appends = ['bank_name', 'bank_branch_name'];

    public static $types = [
        self::TYPE_COMMON => '普通',
        self:: TYPE_CURRENT => '当座',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return mixed
     */
    public function getTypeNameAttribute()
    {
        return isset(self::$types[$this->attributes['type']]) ? self::$types[$this->attributes['type']] : '';
    }

    /**
     * @return mixed
     */
//    public function getBankNameAttribute()
//    {
//        $bank = Bank::query()->find($this->attributes['bank_name_id']);
//
//        return $bank != null ? $bank->name : $this->attributes['bank_name_id'];
//    }

    /**
     * @return mixed
     */
//    public function getBankBranchNameAttribute()
//    {
//        $branch = BankBranch::query()->find($this->attributes['bank_branch_id']);
//
//        return $branch != null ? $branch->name : $this->attributes['bank_branch_id'];
//    }
}
