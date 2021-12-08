<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mail\sendMail;
use App\Pick_up;
use App\Driver_status;
use App\Attachment;
use App\Driver_jobs;
use App\User;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class DriverController extends Controller
{
    public function getDriver()
    {
      return redirect(route('getDriverJobs'));
    }

    public function getDriverPickUp()
    {
      $pick_up_list = Pick_up::get();
      return view('driver.pick_up', compact('pick_up_list'));
    }

    public function getDriverSelectJobs()
    {
      $user = Auth::user();
      $driver_status = Driver_status::where('user_id', $user->id)->where('completed', null)->orderBy('id', 'desc')->first();

      if(!$driver_status)
      {
        return redirect(route('getDriverPickUp'));
      }
      else
      {
        $pick_up = Pick_up::where('id', $driver_status->pick_up_id)->first();
        $driver_jobs = Driver_jobs::where('pick_up_id', $driver_status->pick_up_id)->where('inactive', null)->where('job_date', '<=', date('Y-m-d'))->where('driver_id', null)->get();

        return view('driver.select_jobs', compact('driver_jobs', 'pick_up'));
      }
    }

    public function getDriverJobs()
    {
      $driver_jobs_info = $this->driverJobsList();

      $driver_status_list = $driver_jobs_info->driver_status_list;
      $driver_jobs = $driver_jobs_info->driver_jobs;
      $pick_up_list = $driver_jobs_info->pick_up_list;
      $have_job = $driver_jobs_info->have_job;
      $total_urgent_hours_two = $driver_jobs_info->total_urgent_hours_two;
      $total_urgent_hours_four = $driver_jobs_info->total_urgent_hours_four;

      return view('driver.jobs', compact('driver_jobs', 'pick_up_list', 'have_job', 'driver_status_list', 'total_urgent_hours_two', 'total_urgent_hours_four'));
    }

    public function submitPickUp(Request $request)
    {
      $user = Auth::user();
      $pick_up = Pick_up::where('id', $request->pick_up)->first();

      $driver_pick_up = Driver_status::create([
        'user_id' => $user->id,
        'user_name' => $user->name,
        'status' => 'collected',
        'date_time' => date('Y-m-d H:i:s', strtotime(now())),
        'pick_up_id' => $pick_up->id,
        'pick_up' => $pick_up->name,
        'completed' => 1,
        'completed_date_time' => date('Y-m-d H:i:s', strtotime(now())),
      ]);

      $files = ['file_unit_number', 'file_items', 'file_signatory'];

      foreach($files as $attachment_type)
      {
        if($request->hasfile($attachment_type))
        {
          foreach($request->file($attachment_type) as $file)
          {
            $filename = $file->getClientOriginalName();
            $filesize = $file->getSize();

            $path = $file->store('driver/collected');

            Attachment::create([
              'user_id' => $user->id,
              'user_name' => $user->name,
              'status' => $driver_pick_up->status,
              'driver_status_id' => $driver_pick_up->id,
              'attachment_type' => $attachment_type,
              'file_name' => $filename,
              'file_path' => $path,
              'file_size' => $filesize
            ]);
          }
        }
      }
      
      Driver_status::create([
        'user_id' => $user->id,
        'user_name' => $user->name,
        'status' => 'select_jobs',
        'date_time' => date('Y-m-d H:i:s', strtotime(now())),
        'pick_up_id' => $pick_up->id,
        'pick_up' => $pick_up->name,
      ]);

      Driver_jobs::where('driver_id', $user->id)->whereNull('status')->where('pick_up_id', $pick_up->id)->whereNull('completed')->update([
        'status' => "accepted",
        'status_updated_at' => date('Y-m-d H:i:s')
      ]);

      // return redirect(route('getDriverSelectJobs'));
      return redirect(route('getDriverJobs'));
    }

    public function driverAcceptJobs(Request $request)
    {
      $user = Auth::user();

      $now = date('Y-m-d H:i:s');
      if($request->accept_job)
      {
        foreach($request->accept_job as $job_id)
        {
          Driver_jobs::where('id', $job_id)->update([
            'driver' => $user->name,
            'driver_id' => $user->id,
            'driver_accepted_at' => $now,
            'status' => "accepted",
            'status_updated_at' => $now,
          ]);
        }
      }
      
      return redirect(route('getDriverJobs'));
    }

    public function driverStartJobs(Request $request)
    {
      $user = Auth::user();
      if($request->job_id)
      {
        $driver_job = Driver_jobs::where('id', $request->job_id)->first();

        Driver_jobs::where('id', $request->job_id)->update([
          'status' => 'starting',
          'status_updated_at' => date('Y-m-d H:i:s'),
        ]);

        if($driver_job->email)
        {
          $data = [
            'subject' => "Your delivery are on the way.",
            'type' => 'delivery',
            'message' => "Hi ".$driver_job->name.", Your item will be delivered within 1 hour."
          ];

          Mail::to($driver_job->email)->send(new sendMail($data));
        }

        $driver_jobs = Driver_jobs::where('completed', null)->where('driver_id', $user->id)->get();

        $response = new \stdClass();
        $response->error = 0;
        $response->message = "Job has been selected.";
        $response->driver_jobs_info = $this->driverJobsList();

        return response()->json($response);
      }
    }

    public function submitCompleteJob(Request $request)
    {
      $user = Auth::user();
      $now = date("Y-m-d H:i:s");

      if($request->file_pod && $request->job_id && $request->signature)
      {
        $driver_job = Driver_jobs::where('id', $request->job_id)->first();
        foreach($request->file_pod as $file)
        {
          $filename = $file->getClientOriginalName();
          $filesize = $file->getSize();

          $path = $file->store('driver/completed/'.$driver_job->id);

          Attachment::create([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'status' => "completed",
            'job_id' => $driver_job->id,
            'attachment_type' => "pod_file",
            'file_name' => $filename,
            'file_path' => $path,
            'file_size' => $filesize
          ]);
        }

        $signature_img = $request->signature;
        $signature_img = str_replace('data:image/png;base64,', '', $signature_img);
        $signature_img = str_replace(' ', '+', $signature_img);
        $signature_file = base64_decode($signature_img);

        $signature_filename = $this->quickRandom(16).'.'.'png';
        $signature_path = "driver/completed/".$driver_job->id."/".$signature_filename;
        Storage::put($signature_path, $signature_file);

        $signature_filesize = Storage::size($signature_path);

        Attachment::create([
          'user_id' => $user->id,
          'user_name' => $user->name,
          'status' => "completed",
          'job_id' => $driver_job->id,
          'attachment_type' => "signature",
          'file_name' => $signature_filename,
          'file_path' => $signature_path,
          'file_size' => $signature_filesize
        ]);

        Driver_jobs::where('id', $request->job_id)->update([
          'status' => "completed",
          'status_updated_at' => $now,
          'completed' => 1
        ]);

        $wallet_value = $user->wallet;
        if(!$wallet_value)
        {
          $wallet_value = 0;
        }

        if($driver_job->price)
        {
          $wallet_value += $driver_job->price;
          User::where('id', $user->id)->update([
            'wallet' => $wallet_value
          ]);
        }

        return redirect(route('getDriverJobs'));
      }
      else
      {
        dd($request->file_pod, $request->job_id, $request->signature);
      }
    }

    public function cancelJob(Request $request)
    {
      Driver_jobs::where('id', $request->job_id)->update([
        'driver' => null,
        'driver_id' => null,
        'driver_accepted_at' => null,
        'status' => null,
        'status_updated_at' => null,
      ]);

      $response = new \stdClass();
      $response->error = 0;
      $response->message = "Job has been cancelled.";
      $response->driver_jobs_info = $this->driverJobsList();

      return response()->json($response);
    }

    public function driverJobsList($driver = 1, $admin = 0, $date_from = null, $date_to = null)
    {
      $user = Auth::user();
      $now = date("Y-m-d H:i:s");

      if($driver == 1)
      {
        $driver_jobs = Driver_jobs::where('completed', null)->where('driver_id', $user->id)->get();
      }
      elseif($admin == 1)
      {
        $driver_jobs = Driver_jobs::whereBetween('created_at', [($date_from." 00:00:00"), ($date_to." 23:59:59")])->get();
      }

      $pick_up_location = array();
      foreach($driver_jobs as $job)
      {
        if(!in_array($job->pick_up_id, $pick_up_location))
        {
          array_push($pick_up_location, $job->pick_up_id);
        }
      }

      $pick_up_list = pick_up::whereIn('id', $pick_up_location)->get();
      foreach($pick_up_list as $pick_up)
      {
        $pick_up->job_list = array();
      }
      
      $driver_status_list = $this->driverStatus();

      $have_job = null;
      $total_urgent_hours_two = 0;
      $total_urgent_hours_four = 0;

      foreach($driver_jobs as $job)
      {
        $job->color = "#fff";
        if(!$job->status)
        {
          $job->status = "new job";
        }
        $job->est_delivery_from_text = "";
        $job->est_delivery_to_text = "";
        $job->price_text = number_format($job->price, 2);
        if($job->est_delivery_from)
        {
          $job->est_delivery_from_text = date('d M Y h:i A', strtotime($job->est_delivery_from));
        }

        if($job->est_delivery_to)
        {
          $job->est_delivery_to_text = date('d M Y h:i A', strtotime($job->est_delivery_to));
        }

        foreach($driver_status_list as $s_key => $status)
        {
          if($job->status == $status['status'])
          {
            $job->color = $status['color'];
            if(!$have_job)
            {
              $have_job = $status['have_job'];
            }
            
            $driver_status_list[$s_key]['count']++;
            break;
          }
        }

        if($job->est_delivery_to)
        {
          $hour_4 = date('Y-m-d H:i:s', strtotime(now()." +4 hours"));
          $hour_2 = date('Y-m-d H:i:s', strtotime(now()." +2 hours"));

          if($job->est_delivery_to <= $hour_2)
          {
            $total_urgent_hours_two++;
          }
          elseif($job->est_delivery_to <= $hour_4)
          {
            $total_urgent_hours_four++;
          }
        }

        foreach($pick_up_list as $key => $pick_up)
        {
          if($job->pick_up_id == $pick_up->id)
          {
            $pick_up_job_list = $pick_up->job_list;
            array_push($pick_up_job_list, $job);

            $pick_up->job_list = $pick_up_job_list;
            break;
          }
        }
      }

      foreach($pick_up_list as $pick_up)
      {
        $pick_up->disabled = 0;
        foreach($pick_up->job_list as $job)
        {
          if($job->status == "new job")
          {
            $pick_up->disabled = 1;
            break;
          }
        }
      }

      if($driver == 1)
      {
        $driver_jobs_info = new \stdClass();
        $driver_jobs_info->driver_jobs = $driver_jobs;
        $driver_jobs_info->pick_up_list = $pick_up_list;
        $driver_jobs_info->have_job = $have_job;
        $driver_jobs_info->total_urgent_hours_two = $total_urgent_hours_two;
        $driver_jobs_info->total_urgent_hours_four = $total_urgent_hours_four;
        $driver_jobs_info->driver_status_list = $driver_status_list;

        return $driver_jobs_info;
      }
      elseif($admin == 1)
      {
        return $driver_jobs;
      }
    }

    public function driverStatus()
    {
      $status = [
        [
          'status' => "new job",
          'color' => "#fff",
          'have_job' => null,
          'count' => 0
        ],
        [
          'status' => "accepted",
          'color' => "#ccc",
          'have_job' => null,
          'count' => 0
        ],
        [
          'status' => "starting",
          'color' => "#00bcd4",
          'have_job' => 1,
          'count' => 0
        ],
        [
          'status' => "completed",
          'color' => "#8bc34a",
          'have_job' => null,
          'count' => 0
        ]
      ];

      return $status;
    }

    public static function quickRandom($length = 16)
    {
        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        return substr(str_shuffle(str_repeat($pool, 5)), 0, $length);
    }
}
