<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pick_up extends Model
{
    protected $table = 'pick_up';
    protected $fillable = [
      'address',
      'name'
    ];
}
