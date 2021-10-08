<?php

namespace App\Http\Controllers;

use App\User;
use App\Mail\sendMail;
use App\Restaurant;
use App\Order;
use App\Tier;
use App\Settings;
use App\Driver_jobs;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class DeliveryController extends Controller
{
    public function getAdminRegister()
    {
      if(!$this->checkAuth())
      {
        return view('delivery.register');
      }
      else
      {
        return redirect(route('getAdminHome'));
      }
    }

    public function checkAuth()
    {
      if (Auth::check())
      {
        return Auth::user()->user_type;
      }

      return false;
    }

    public function getAdminReset()
    {
      return view('delivery.reset');
    }

    public function sendReset(Request $request)
    {
      $user = User::where('email', $request->email)->first();
      if($user)
      {
        $otp = rand(0, 999999);
        if(strlen($otp) != 6)
        {
          $less = 6 - strlen($otp);
          for($a = 0; $a < $less; $a++)
          {
            $otp = "0".$otp;
          }
        }

        User::where('id', $user->id)->update([
          'otp' => $otp
        ]);

        $message = "<h5>OTP : ".$otp."</h5>";

        $data = [
          'subject' => "Reset password OTP",
          'type' => 'account',
          'message' => $message
        ];

        Mail::to($user->email)->send(new sendMail($data));

        return redirect(route('getResetPassword', ['user_id' => $user->id]));
      }
      else
      {
        return back()->withErrors(['email' => 'Email not found']);
      }
    }

    public function getResetPassword(Request $request)
    {
      $user = User::where('id', $request->user_id)->first();
      if(!$user)
      {
        return back()->withErrors(['email' => 'User not found']);
      }

      return view('delivery.reset_password');
    }

    public function resetPassword(Request $request)
    {
      $this->validator($request->all())->validate();
      $user = User::where('email', $request->reset_user_email)->first();
      if(!$user)
      {
        return back()->withErrors(['otp' => 'User not found']);
      }

      $existing_user = User::where('username', $request->username)->where('id', '<>', $user->id)->first();
      if($existing_user)
      {
        return back()->withErrors(['username' => 'Username already been used.']);
      }

      if($user->otp != $request->otp)
      {
        return back()->withErrors(['otp' => 'Incorrect OTP']);
      }

      User::where('id', $user->id)->update([
        'username' => $request->username,
        'password' => Hash::make($request->password),
      ]);

      return redirect(route('getResetSuccess'));
    }

    public function getResetSuccess()
    {
      return view('delivery.reset_success');
    }

    protected function validator(array $data)
    {
      return Validator::make($data, [
        'otp' => ['required', 'string', 'min:6'],
        'username' => ['required', 'string', 'min:6'],
        'password' => ['required', 'string', 'min:8', 'confirmed'],
      ]);
    }

    public function getAdminHome()
    {
      return view('delivery.home');
    }

    public function getAdminRestaurant()
    {
      $restaurant_list = Restaurant::orderBy('name')->get();
      $tier_list = tier_list();

      foreach($restaurant_list as $restaurant)
      {
        $restaurant->tier_list = Tier::where('restaurant_id', $restaurant->id)->get();
      }

      $settings = Settings::first();
      $active = 0;
      if($settings)
      {
        $active = $settings->active;
      }

      $date_start = date('Y-m-01', strtotime(now()));
      $date_end = date('Y-m-d');

      return view('delivery.index', compact('restaurant_list', 'tier_list', 'active', 'date_start', 'date_end'));
    }

    public function getOrderDetail($id)
    {
      $restaurant_detail = Restaurant::where('id', $id)->first();
      $order_list = Order::where('restaurant_id', $id)->get();

      return view('delivery.order_detail', compact('restaurant_detail', 'order_list'));
    }

    public function getRestaurant()
    {
      $user = Auth::user();
      if(!$user)
      {
        return redirect(route('login'));
      }

      $restaurant_detail = Restaurant::where('id', $user->restaurant_id)->first();
      if(!$restaurant_detail)
      {
        dd("Restaurant not found");
      }

      $settings = Settings::first();
      if($settings)
      {
        if($settings->active == 0)
        {
          return view('restaurant.maintenance');
        }
      }

      $date_start = date('Y-m-01');
      $date_end = date('Y-m-d');

      return view('restaurant.index', compact('restaurant_detail', 'date_start', 'date_end'));
    }

    public function createRestaurant(Request $request)
    {
      $existing_restaurant = Restaurant::where('email', $request->restaurant_email)->first();
      if($existing_restaurant)
      {
        $response = new \stdClass();
        $response->error = 1;
        $response->message = "Email already been used.";
      }
      else
      {
        $existing_user = User::where('username', $request->restaurant_username)->first();
        if($existing_user)
        {
          $response = new \stdClass();
          $response->error = 1;
          $response->message = "Username already been used.";
        }
        else
        {
          $tier_list = tier_list();
          $restaurant_detail = Restaurant::create([
            'name' => $request->restaurant_name,
            'address' => $request->restaurant_address,
            'postal' => $request->restaurant_postal,
            'email' => $request->restaurant_email
          ]);

          foreach($tier_list as $tier)
          {
            Tier::create([
              'restaurant_id' => $restaurant_detail->id,
              'restaurant_name' => $restaurant_detail->name,
              'tier' => $tier['value'],
              'active' => 0
            ]);
          }

          User::create([
            'user_type' => 'restaurant',
            'restaurant_id' => $restaurant_detail->id,
            'name' => $request->restaurant_name,
            'username' => $request->restaurant_username,
            'email' => $request->restaurant_email,
            'password' => Hash::make($request->restaurant_password),
          ]);

          $response = new \stdClass();
          $response->error = 0;
          $response->message = "Restaurant is created.";
        }
      }

      return response()->json($response);
    }

    public function saveRestaurantTier(Request $request)
    { 
      $restaurant_detail = Restaurant::where('id', $request->tier_restaurant_id)->first();

      $tier_list = tier_list();
      Tier::where('restaurant_id', $restaurant_detail->id)->update([
        'active' => 0
      ]);

      foreach($tier_list as $tier)
      {
        $tier_name = $tier['input'];
        $selected_tier = $request->$tier_name;
        $tier_price_name = "tier_price_".$tier['value'];
        $tier_price = $request->$tier_price_name;

        $check_tier = Tier::where('restaurant_id', $restaurant_detail->id)->where('tier', $tier['value'])->first();
        if(!$check_tier)
        {
          Tier::create([
            'restaurant_id' => $restaurant_detail->id,
            'restaurant_name' => $restaurant_detail->name,
            'tier' => $tier['value'],
            'tier_price' => null,
            'active' => 0
          ]);
        }

        if($selected_tier)
        {
          Tier::where('restaurant_id', $restaurant_detail->id)->where('tier', $selected_tier)->update([
            'tier_price' => $tier_price,
            'active' => 1
          ]);
        }
      }

      $response = new \stdClass();
      $response->error = 0;
      $response->message = "Restaurant tier is updated.";

      return response()->json($response);
    }

    public function saveRestaurantRebate(Request $request)
    {
      Restaurant::where('id', $request->restaurant_id)->update([
        'rebate' => $request->rebate
      ]);

      $response = new \stdClass();
      $response->error = 0;
      $response->message = "Restaurant rebate is updated.";

      return response()->json($response);
    }

    public function submitOrder(Request $request)
    {
      $settings = Settings::first();
      if($settings)
      {
        if($settings->active == 0)
        {
          $response = new \stdClass();
          $response->error = 1;
          $response->message = "System currently unable to accept order, please try again later.";

          return response()->json($response);
        }
      }

      $restaurant_detail = Restaurant::where('id', $request->restaurant_id)->first();

      $full_date = $request->date." ".$request->time;
      $full_date = date('Y-m-d H:i:s', strtotime($full_date));

      $restaurant_tier = Tier::where('restaurant_id', $restaurant_detail->id)->where('active', 1)->get();

      $order_count = Order::where('restaurant_id', $restaurant_detail->id)->count();
      $order_count++;
      $order_count_text = $order_count;
      for($a = strlen($order_count); $a < 6; $a++)
      {
        $order_count_text = "0".$order_count_text;
      }
      $order_no = "R".date('Ymd')."A".$order_count_text;

      $price = null;
      foreach($restaurant_tier as $tier)
      {
        if($tier->tier_price)
        {
          $tier_start = null;
          $tier_end = null;
          $max_tier = null;

          if(strpos($tier->tier, "-") !== false)
          {
            $tier_array = explode("-", $tier->tier);
            if(count($tier_array) == 2)
            {
              $tier_start = $tier_array[0];
              $tier_end = $tier_array[1];
            }
          }
          elseif(strpos($tier->tier, ">") !== false)
          {
            $max_tier = str_replace(">", "", $tier->tier);
          }

          if($tier_start && $tier_end)
          {
            if($order_count >= $tier_start)
            {
              $price = $tier->tier_price;
            }
          }
          elseif($max_tier)
          {
            if($order_count >= $max_tier)
            {
              $price = $tier->tier_price;
            }
          }
        }
      }

      $rebate = 0;
      if($price && $restaurant_detail->rebate && $restaurant_detail->rebate > 0)
      {
        $rebate = $price * $restaurant_detail->rebate / 100;
        if(!$restaurant_detail->rebate_wallet)
        {
          $restaurant_detail->rebate_wallet = 0;
        }
        $total_rebate = $restaurant_detail->rebate_wallet + $rebate;

        Restaurant::where('id', $restaurant_detail->id)->update([
          'rebate_wallet' => $total_rebate
        ]);
      }

      $order_detail = Order::create([
        'restaurant_id' => $restaurant_detail->id,
        'restaurant_name' => $restaurant_detail->name,
        'order_no' => $order_no,
        'address' => $request->order_address,
        'postal' => $request->postal,
        'name' => $request->name,
        'phone' => $request->phone_number,
        'date_time' => $full_date,
        'remarks' => $request->remarks,
        'price' => $price
      ]);

      $full_date_text = date('Y M d h:i A', strtotime($full_date));

      $message = "<p>Hi ".$restaurant_detail->name."</p><br>";
      $message .= "<p>Your order of ".$order_no." will be delivered on <b>".$full_date_text."</b></p><br>";
      $message .= "<p>Order details :</p><br>";
      $message .= "<table style='width: 100%;'>";
      $message .= "<tr>";
      $message .= "<td>Address</td>";
      $message .= "<td>".$request->order_address."</td>";
      $message .= "</tr>";
      $message .= "<tr>";
      $message .= "<td>Postal</td>";
      $message .= "<td>".$request->postal."</td>";
      $message .= "</tr>";
      $message .= "<tr>";
      $message .= "<td>Name</td>";
      $message .= "<td>".$request->name."</td>";
      $message .= "</tr>";
      $message .= "<tr>";
      $message .= "<td>Date Time</td>";
      $message .= "<td>".$full_date_text."</td>";
      $message .= "</tr>";
      $message .= "<tr>";
      $message .= "<td>Remarks</td>";
      $message .= "<td>".$request->remarks."</td>";
      $message .= "</tr>";
      $message .= "<tr>";
      $message .= "<td>Price</td>";
      $message .= "<td>S$ ".$price."</td>";
      $message .= "</tr>";
      $message .= "</table>";

      $data = [
        'subject' => "Order detail",
        'type' => 'delivery',
        'message' => $message,
      ];

      $email_cc = [$restaurant_detail->email];

      Mail::to("wongwaiwen1001@gmail.com")->cc($email_cc)->send(new sendMail($data));

      $response = new \stdClass();
      $response->error = 0;
      $response->message = "Order submitted.";

      return response()->json($response);
    }

    public function updateSystemStatus(Request $request)
    {
      $settings = Settings::first();
      Settings::where('id', $settings->id)->update([
        'active' => $request->active
      ]);

      $response = new \stdClass();
      $response->error = 0;
      $response->message = "Settings updated.";

      return response()->json($response);
    }

    public function exportOrder(Request $request)
    {
      $order_list = Order::whereBetween('created_at', [$request->export_start, $request->export_end])->get();
      Storage::makeDirectory('public/admin', 0775, true);
      $download_path = substr(Storage::url('admin/order list.xlsx'), 1);

      $this->exportOrderExcel($order_list, $download_path);
      return response()->download($download_path);
    }

    public function exportRestaurantOrder(Request $request)
    {
      $order_list = Order::whereBetween('created_at', [$request->export_start, $request->export_end])->where('restaurant_id', $request->restaurant_id)->get();

      Storage::makeDirectory('public/restaurant', 0775, true);
      $download_path = substr(Storage::url('restaurant/order list.xlsx'), 1);

      $this->exportOrderExcel($order_list, $download_path);
      return response()->download($download_path);
    }

    public function exportOrderExcel($order_list, $download_path)
    {
      $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
      $sheet = $spreadsheet->getActiveSheet();
      $sheet->setTitle('Order list');

      $sheet->setCellValue('A1', "RESTAURANT");
      $sheet->setCellValue('B1', "ORDER NO");
      $sheet->setCellValue('C1', "ADDRESS");
      $sheet->setCellValue('D1', "POSTAL");
      $sheet->setCellValue('E1', "NAME");
      $sheet->setCellValue('F1', "PHONE");
      $sheet->setCellValue('G1', "DATE TIME");
      $sheet->setCellValue('H1', "REMARKS");
      $sheet->setCellValue('I1', "PRICE");
      $sheet->setCellValue('J1', "ORDER DATE");

      $started_row = 2;
      foreach($order_list as $order)
      {
        $sheet->setCellValue('A'.$started_row, $order->restaurant_name);
        $sheet->setCellValue('B'.$started_row, $order->order_no);
        $sheet->setCellValue('C'.$started_row, $order->address);
        $sheet->setCellValue('D'.$started_row, $order->postal);
        $sheet->setCellValue('E'.$started_row, $order->name);
        $sheet->setCellValue('F'.$started_row, $order->phone);
        $sheet->setCellValue('G'.$started_row, $order->date_time);
        $sheet->setCellValue('H'.$started_row, $order->remarks);
        $sheet->setCellValue('I'.$started_row, $order->price);
        $sheet->setCellValue('J'.$started_row, $order->created_at);
        $started_row++;
      }

      $sheet->getStyle("A1:J1")->getFont()->setBold( true );
      $sheet->getStyle('A1:J'. ($started_row - 1) )->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

      $sheet->getColumnDimension('A')->setWidth(20);
      $sheet->getColumnDimension('B')->setWidth(15);
      $sheet->getColumnDimension('C')->setWidth(40);
      $sheet->getColumnDimension('D')->setWidth(15);
      $sheet->getColumnDimension('E')->setWidth(15);
      $sheet->getColumnDimension('F')->setWidth(15);
      $sheet->getColumnDimension('G')->setWidth(20);
      $sheet->getColumnDimension('H')->setWidth(20);
      $sheet->getColumnDimension('I')->setWidth(15);
      $sheet->getColumnDimension('J')->setWidth(20);

      $writer = new Xlsx($spreadsheet);
      $writer->save($download_path);
    }

    public function downloadImportFormat(Request $request)
    {
      Storage::makeDirectory('public/format', 0775, true);
      $download_path = substr(Storage::url('format/order list format.xlsx'), 1);

      $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

      $hidden_sheet = $spreadsheet->getActiveSheet();
      $hidden_sheet->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN);
      $hidden_sheet->setTitle('Hidden');
      $hidden_sheet->setCellValue('A1', "RESTAURANT ID");
      $hidden_sheet->setCellValue('A2', $request->id);

      $spreadsheet->createSheet();
      $sheet = $spreadsheet->setActiveSheetIndex(1);
      $sheet->setTitle('Order list');

      $sheet->setCellValue('A1', "ADDRESS");
      $sheet->setCellValue('B1', "POSTAL");
      $sheet->setCellValue('C1', "NAME");
      $sheet->setCellValue('D1', "PHONE");
      $sheet->setCellValue('E1', "DATE (2021-12-31)");
      $sheet->setCellValue('F1', "TIME (23:59)");
      $sheet->setCellValue('G1', "REMARKS");

      $sheet->getStyle("A1:G1")->getFont()->setBold( true );
      $sheet->getStyle('A1:G1')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

      $sheet->getColumnDimension('A')->setWidth(40);
      $sheet->getColumnDimension('B')->setWidth(15);
      $sheet->getColumnDimension('C')->setWidth(15);
      $sheet->getColumnDimension('D')->setWidth(15);
      $sheet->getColumnDimension('E')->setWidth(20);
      $sheet->getColumnDimension('F')->setWidth(20);
      $sheet->getColumnDimension('G')->setWidth(30);

      $writer = new Xlsx($spreadsheet);
      $writer->save($download_path);

      return response()->download($download_path);
    }

    public function importRestaurantOrder(Request $request)
    {
      $file = $request->file;
      $filename = $file->getClientOriginalName();
      $ext = $file->getClientOriginalExtension();

      $file_name_array = explode(".", $filename);
      $new_file_name = $file_name_array[0]." ".date('Y-m-d His').".".$ext;

      Storage::makeDirectory('public/restaurant/import', 0775, true);
      $path = $file->storeAs('public/restaurant/import', $new_file_name);

      $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
      $path = substr(Storage::url('restaurant/import/'.$new_file_name), 1);
      $spreadsheet = $reader->load($path);

      $hidden_sheet = $spreadsheet->getSheetByName("Hidden");
      $restaurant_id = $hidden_sheet->getCell("A2")->getValue();

      if(!$restaurant_id)
      {
        dd("Restaurant ID not found");
      }

      $restaurant_detail = Restaurant::where('id', $restaurant_id)->first();

      $sheet = $spreadsheet->getSheet(1);
      $sheet_rows = $sheet->getHighestDataRow();

      $order_count = Order::where('restaurant_id', $restaurant_detail->id)->count();
      

      // to update
      $price = 10;
      for($a = 2; $a <= $sheet_rows; $a++)
      {
        $address = $sheet->getCell("A".$a)->getValue();
        $postal = $sheet->getCell("B".$a)->getValue();
        $name = $sheet->getCell("C".$a)->getValue();
        $phone = $sheet->getCell("D".$a)->getValue();
        $date = $sheet->getCell("E".$a)->getValue();
        $time = $sheet->getCell("F".$a)->getValue();
        $remarks = $sheet->getCell("G".$a)->getValue();

        $order_count++;
        $order_count_text = $order_count;
        for($b = strlen($order_count); $b < 6; $b++)
        {
          $order_count_text = "0".$order_count_text;
        }
        $order_no = "R".date('Ymd')."A".$order_count_text;

        if(is_numeric($date))
        {
          $date = gmdate("Y-m-d", (($date - 25569) * 86400));
        }

        if(is_numeric($time))
        {
          $time = gmdate("H:i:s", (($time - 25569) * 86400));
        }

        $time = date('H:i:s', strtotime($time));

        $date_time = $date." ".$time;

        Order::create([
          'restaurant_id' => $restaurant_detail->id,
          'restaurant_name' => $restaurant_detail->name,
          'order_no' => $order_no,
          'address' => $address,
          'postal' => $postal,
          'name' => $name,
          'phone' => $phone,
          'date_time' => $date_time,
          'remarks' => $remarks,
          'price' => $price
        ]);

        // send email
      }
    }

    public function getAdminDriver()
    {
      $now = date('Y-m-d H:i:s');
      $today = date('Y-m-d');
      $driver_jobs = Driver_jobs::whereDate('created_at', $today)->orWhere('status', '<>', 'completed')->get();

      $total_pending = 0;
      $total_accepted = 0;
      $total_completed = 0;
      $total_overdue = 0;

      $two_hour = date('Y-m-d H:i:s', strtotime(now()." +2 hours"));
      $four_hour = date('Y-m-d H:i:s', strtotime(now()." +4 hours"));

      $urgent_two = 0;
      $urgent_four = 0;

      $driver_list = User::where('user_type', 'driver')->get();

      foreach($driver_list as $d_key => $driver)
      {
        $driver->total_accepted = 0;
        $driver->total_completed = 0;
        $driver->total_overdue = 0;
        $driver->online = 0;

        if($driver->last_login_at && date('Y-m-d H:i:s', strtotime($driver->last_login_at." +180 seconds")) >= $now)
        {
          $driver->online = 1;
        }
      }

      foreach($driver_jobs as $job)
      {
        $job->color = "#ccc";
        $job->est_delivery_from_text = "";
        $job->est_delivery_to_text = "";
        $job->urgent = 0;

        $driver_key = null;
        if($job->driver_id)
        {
          foreach($driver_list as $d_key => $driver)
          {
            if($driver->id == $job->driver_id)
            {
              $driver_key = $d_key;
              break;
            }
          }
        }

        if($job->status == "completed")
        {
          $job->color = "#8bc34a";
          $total_completed++;

          if($driver_key !== null)
          {
            $driver_list[$driver_key]->total_completed;
          }
        }
        elseif($job->status == "accepted" || $job->status == "starting")
        {
          $job->color = "#00bcd4";
          $total_accepted++;

          if($driver_key !== null)
          {
            $driver_list[$driver_key]->total_accepted++;
          }
        }
        elseif(date('Y-m-d', strtotime($job->created_at) == $today))
        {
          $total_pending++;
        }
        else
        {
          $job->color = "#ff0000";
          $total_overdue++;

          if($driver_key !== null)
          {
            $driver_list[$driver_key]->total_overdue++;
          }
        }

        if($job->status != "completed" && $job->est_delivery_to)
        {
          if(strtotime($job->est_delivery_to) <= strtotime($two_hour))
          {
            $urgent_two++;
            $job->urgent = 1;
          }
          elseif(strtotime($job->est_delivery_to) <= strtotime($four_hour))
          {
            $urgent_four++;
            $job->urgent = 1;
          }
        }

        if($job->est_delivery_from)
        {
          $job->est_delivery_from_text = date('d M Y h:i A', strtotime($job->est_delivery_from));
        }

        if($job->est_delivery_to)
        {
          $job->est_delivery_to_text = date('d M Y h:i A', strtotime($job->est_delivery_to));
        }
      }

      return view('delivery.delivery_index', compact('total_completed', 'total_accepted', 'total_pending', 'driver_jobs', 'total_overdue', 'urgent_two', 'urgent_four', 'now', 'driver_list'));
    }

    public function getAdminJobsList()
    {
      $today = date('Y-m-d');

      $date_from = $today;
      $date_to = $today;

      if(isset($_GET['date_from']))
      {
        $date_from = $_GET['date_from'];
      }

      if(isset($_GET['date_to']))
      {
        $date_to = $_GET['date_to'];
      }

      $driver_jobs = app('App\Http\Controllers\DriverController')->driverJobsList(0, 1, $date_from, $date_to);

      return view('delivery.jobs_list', compact('date_from', 'date_to', 'driver_jobs'));
    }

    public function checkOnline(Request $request)
    {
      $user = Auth::user();
      if($user)
      {
        User::where('id', $user->id)->update([
          'last_login_at' => date('Y-m-d H:i:s')
        ]);
      }
    }

    public function updateLocation(Request $request)
    {
      $user = Auth::user();
      if($user)
      {
        User::where('id', $user->id)->update([
          'lat' => $request->lat,
          'lng' => $request->lng
        ]);
      }
    }
}