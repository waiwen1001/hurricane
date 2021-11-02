<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Testing extends Model
{
    protected $table = 'testing';
    protected $fillable = [
      'start_at',
      'end_in',
      'q1',
      'q1_tips',
      'q2',
      'q2_tips',
      'q3',
      'q3_tips',
      'q4',
      'q4_tips',
      'point',
      'hdl',
      'kr_food',
      'b_tea',
    ];
}
