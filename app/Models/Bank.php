<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    protected $table = 'banks';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function branches() {
        return $this->hasMany(BankBranch::class, 'bank_id');
    }
}
