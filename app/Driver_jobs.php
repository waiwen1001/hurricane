<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Driver_jobs extends Model
{
    protected $table = 'driver_jobs';
    protected $fillable = [
      'name',
      'email',
      'contact_number',
      'address',
      'est_delivery_from',
      'est_delivery_to',
      'price',
      'pick_up_id',
      'pick_up',
      'driver',
      'driver_id',
      'driver_accepted_at',
      'status',
      'remarks',
      'inactive',
      'job_date',
      'created_by',
      'created_by_id',
      'completed'
    ];
}
