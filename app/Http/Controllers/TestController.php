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

      if($final)
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
      }
      
      return view('games.final', compact('final'));
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
}
