<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    const TYPE_RECEIVE_MONEY = 'receive_money';
    const TYPE_WITHDRAW_BANK = 'withdraw_bank';

    protected $table = 'transactions';

    protected $dates = ['date'];

    protected $appends = ['type_name'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return mixed|string
     */
    public function getTypeNameAttribute()
    {
        $type_name = '';
        $types = $this->getListTypeTransaction();
        if (!empty($this->type)) {
            if (array_key_exists($this->type, $types)) {
                $type_name = $types[$this->type];
            }
        }

        return $type_name;
    }

    /**
     * @return array
     */
    public function getListTypeTransaction()
    {
        return [
            self::TYPE_RECEIVE_MONEY => '賞金獲得',
            self::TYPE_WITHDRAW_BANK => '出金申請完了'
        ];
    }
}
