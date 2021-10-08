<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'order';
    protected $fillable = [
      'restaurant_id',
      'restaurant_name',
      'order_no',
      'address',
      'postal',
      'name',
      'phone',
      'date_time',
      'remarks',
      'detail',
      'price'
    ];
}
