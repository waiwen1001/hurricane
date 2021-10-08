<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tier extends Model
{
    protected $table = 'tier';
    protected $fillable = [
      'restaurant_id',
      'restaurant_name',
      'tier',
      'tier_price',
      'active'
    ];

    
}
