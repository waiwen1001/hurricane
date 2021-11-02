<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Testing;

class TestController extends Controller
{
    public function showGames()
    {
      return view('games.youtube');
    }

    public function showFinal()
    {
      $final = Testing::first();
      $q1 = null;
      $q2 = null;
      $q3 = null;
      $q4 = null;
      $duration = null;
      $completed = null;
      $total_used = null;
      $total_used_format = null;

      if($final)
      {
        $q1 = $final->q1;
        $q2 = $final->q2;
        $q3 = $final->q3;

        if($q1 == 1 && $q2 == 1 && $q3 == 1)
        {
          $completed = 1;

          $total_time = strtotime($final->end_in) - strtotime($final->start_at);
          $total_time = gmdate("H:i:s", $total_time);

          $hour = date('H', strtotime($total_time));
          $minute = date('i', strtotime($total_time));
          $seconds = date('s', strtotime($total_time));

          $total_used = $hour." hour ".$minute." minute ".$seconds." seconds";
          $total_used_format = $hour.":".$minute.":".$seconds;
        }
        else
        {
          $duration = strtotime(now()) - strtotime($final->start_at);
          $duration = gmdate("H:i:s", $duration);

          if(date("i", strtotime($duration)) >= 10 || date("H", strtotime($duration)) >= 1)
          {
            $deduct_mul = 0;
            if(date("H", strtotime($duration)) >= 1)
            {
              $deduct_mul += date("H", strtotime($duration)) * 6;
            }

            if(date("i", strtotime($duration)) >= 10)
            {
              $deduct_mul += round(date("i", strtotime($duration)) / 10);
            }
            $deduct = $deduct_mul * 100;
            
            $point = 1000 - $deduct;

            Testing::where('id', $final->id)->update([
              'point' => $point
            ]);

            $final->point = $point;
          }
          else
          {
            Testing::where('id', $final->id)->update([
              'point' => 1000
            ]);

            $final->point = 1000;
          }
        }
      }
      
      return view('games.final', compact('final', 'q1', 'q2', 'q3', 'duration', 'completed', 'total_used', 'total_used_format'));
    }

    public function startFinal()
    {
      $final = Testing::first();
      if(!$final)
      {
        Testing::create([
          'start_at' => date("Y-m-d H:i:s"),
          'point' => 1000
        ]);
      }
    }

    public function completedQuestion(Request $request)
    {
      $type = $request->type;
      if($type == "q1")
      {
        Testing::where('id', 1)->update([
          'q1' => 1
        ]);
      }
      elseif($type == "q2")
      {
        Testing::where('id', 1)->update([
          'q2' => 1
        ]);
      }
      elseif($type == "q3")
      {
        Testing::where('id', 1)->update([
          'q3' => 1
        ]);
      }

      $final = Testing::where('id', 1)->first();

      $response = new \stdClass();
      $response->pending = 1;
      $response->point = null;
      $response->hour = null;
      $response->minute = null;
      $response->seconds = null;
      $response->final = $final;

      if($final->q1 == 1 && $final->q2 == 1 && $final->q3 == 1)
      {
        $now = date("Y-m-d H:i:s");
        Testing::where('id', 1)->update([
          'end_in' => $now
        ]);

        $duration = strtotime($now) - strtotime($final->start_at);
        $duration = gmdate("H:i:s", $duration);

        $hour = date('H', strtotime($duration));
        $minute = date('i', strtotime($duration));
        $seconds = date('s', strtotime($duration));

        $response->pending = 0;
        $response->point = $final->point;
        $response->hour = $hour;
        $response->minute = $minute;
        $response->seconds = $seconds;
      }

      return response()->json($response);
    }

    public function minusPoint(Request $request)
    {
      $final = Testing::first();
      $point = $final->point - $request->point;

      Testing::where('id', 1)->update([
        'point' => $point
      ]);
    }

    public function claimRewards(Request $request)
    {
      if($request->rewards)
      {
        $update_query = [
          'b_tea' => null,
          'kr_food' => null,
          'hdl' => null
        ];

        foreach($request->rewards as $rewards)
        {
          if($rewards == "b_tea")
          {
            $update_query['b_tea'] = 1;
          }
          elseif($rewards == "kr_food")
          {
            $update_query['kr_food'] = 1;
          }
          elseif($rewards == "hdl")
          {
            $update_query['hdl'] = 1;
          }
        }

        Testing::where('id', 1)->update($update_query);
      }
    }
}
