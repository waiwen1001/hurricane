<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    protected $table = 'attachment';
    protected $fillable = [
      'user_id',
      'user_name',
      'status',
      'driver_status_id',
      'job_id',
      'attachment_type',
      'file_name',
      'file_path',
      'file_size'
    ];
}
