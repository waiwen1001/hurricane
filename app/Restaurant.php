<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Restaurant extends Model
{
    protected $table = 'restaurant';
    protected $fillable = [
      'name',
      'address',
      'postal',
      'email',
      'rebate',
      'rebate_wallet'
    ];
}
