<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Driver_status extends Model
{
    protected $table = 'driver_status';
    protected $fillable = [
      'user_id',
      'user_name',
      'status',
      // pick_up, select_jobs, 
      'date_time',
      'pick_up_id',
      'pick_up',
      'completed',
      'completed_date_time',
    ];
}
