<?php

namespace App\Http\Controllers;

use App\User;
use App\Mail\sendMail;
use App\Restaurant;
use App\Order;
use App\Tier;
use App\Settings;
use App\Driver_jobs;
use App\Pick_up;

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
      $reset_user = User::where('id', $request->user_id)->first();
      if(!$reset_user)
      {
        return back()->withErrors(['email' => 'User not found']);
      }

      return view('delivery.reset_password', compact('reset_user'));
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
      $download_path = 'admin/order list.xlsx';

      $this->exportOrderExcel($order_list, $download_path);
      return response()->download($download_path);
    }

    public function exportRestaurantOrder(Request $request)
    {
      $order_list = Order::whereBetween('created_at', [$request->export_start, $request->export_end])->where('restaurant_id', $request->restaurant_id)->get();

      Storage::makeDirectory('public/restaurant', 0775, true);
      $download_path = 'format/import job format result.xlsx';

      $this->exportOrderExcel($order_list, $download_path);
      return Storage::download($download_path);
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
      $path = "format/import job format result.xlsx";
      $this->storeExcel($writer, $path);
    }

    public function downloadImportFormat(Request $request)
    {
      Storage::makeDirectory('public/format', 0775, true);

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

      $path = "format/order list format.xlsx";
      $this->storeExcel($writer, $path);
      return Storage::download($path);
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
      $path = 'storage/restaurant/import/'.$new_file_name;
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
      $driver_jobs = Driver_jobs::whereDate('created_at', $today)->orWhere('status', '<>', 'completed')->orWhere('status', null)->get();

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
        $driver->current_job = null;

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
            if($job->status == "starting")
            {
              $driver_list[$driver_key]->current_job = $job;
            }
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
      $status_filter = null;
      $driver_id = null;

      if(isset($_GET['date_from']))
      {
        $date_from = $_GET['date_from'];
      }

      if(isset($_GET['date_to']))
      {
        $date_to = $_GET['date_to'];
      }

      if(isset($_GET['driver']))
      {
        $driver_id = $_GET['driver'];
      }

      if(isset($_GET['status']))
      {
        $status_filter = $_GET['status'];
      }

      $driver_jobs = app('App\Http\Controllers\DriverController')->driverJobsList(0, 1, $date_from, $date_to, $driver_id, $status_filter);
      $driver_status = app('App\Http\Controllers\DriverController')->driverStatus();
      $driver_list = User::where('user_type', 'driver')->get();

      return view('delivery.jobs_list', compact('date_from', 'date_to', 'driver_jobs', 'driver_status', 'driver_list', 'status_filter', 'driver_id'));
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

    public function getDriverLocation(Request $request)
    {
      $driver_list = User::where('user_type', 'driver')->whereDate('last_login_at', date('Y-m-d'))->get();

      foreach($driver_list as $driver)
      {
        if($driver->lat && $driver->lng)
        {
          $driver->lat = floatval($driver->lat);
          $driver->lng = floatval($driver->lng);
        }
      }

      $response = new \stdClass();
      $response->message = "Success";
      $response->error = 0;
      $response->driver_list = $driver_list;

      return response()->json($response);
    }

    public function downloadImportJobFormat()
    {
      $pick_up_list = Pick_up::get();

      $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

      $hidden_sheet = $spreadsheet->setActiveSheetIndex(0);
      $hidden_sheet->setTitle('Hidden');
      $hidden_sheet->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN);
      $hidden_sheet->setCellValue('A1', "Import Jobs Format");
      $hidden_sheet->mergeCells("A1:C1");
      $hidden_sheet->setCellValue('D1', "Document generated at ".date('Y M d h:i A')." by ".Auth::user()->name);
      $hidden_sheet->mergeCells("D1:F1");
      $hidden_sheet->setCellValue('A2', "Pick Up List");

      $started_row = 3;
      foreach($pick_up_list as $pick_up)
      {
        $hidden_sheet->setCellValue('A'.$started_row, $pick_up->name);
        $started_row++;
      }

      $spreadsheet->createSheet();
      $sheet = $spreadsheet->setActiveSheetIndex(1);
      $sheet->setTitle('Jobs list');
      $sheet->setCellValue('A1', "Import Jobs Format");
      $sheet->mergeCells("A1:C1");
      $sheet->setCellValue('D1', "Document generated at ".date('Y M d h:i A')." by ".Auth::user()->name);
      $sheet->mergeCells("D1:F1");

      $sheet->setCellValue('A2', "Pick Up Location");
      $sheet->setCellValue('B2', "Customer name *");
      $sheet->setCellValue('C2', "Email");
      $sheet->setCellValue('D2', "Contact Number *");
      $sheet->setCellValue('E2', 'Address*');
      $sheet->setCellValue('F2', 'Postal Code*');
      $sheet->setCellValue('G2', "Estimate Delivery From Date\n(YYYY-MM-DD)");
      $sheet->setCellValue('H2', "Estimate Delivery From Time\n(HH:MM)");
      $sheet->setCellValue('I2', "Estimate Delivery To Date\n(YYYY-MM-DD)");
      $sheet->setCellValue('J2', "Estimate Delivery To Time\n(HH:MM)");
      $sheet->setCellValue('K2', "Wallet Value *");
      $sheet->setCellValue('L2', "Remarks");

      $sheet->getColumnDimension('A')->setWidth(20);
      $sheet->getColumnDimension('B')->setWidth(20);
      $sheet->getColumnDimension('C')->setWidth(20);
      $sheet->getColumnDimension('D')->setWidth(20);
      $sheet->getColumnDimension('E')->setWidth(20);
      $sheet->getColumnDimension('F')->setWidth(20);
      $sheet->getColumnDimension('G')->setWidth(27);
      $sheet->getColumnDimension('H')->setWidth(27);
      $sheet->getColumnDimension('I')->setWidth(27);
      $sheet->getColumnDimension('J')->setWidth(27);
      $sheet->getColumnDimension('K')->setWidth(20);
      $sheet->getColumnDimension('L')->setWidth(20);

      $sheet->getRowDimension('2')->setRowHeight(35);
      $sheet->getStyle("A2:L2")->getAlignment()->setWrapText(true);

      for($a = 3; $a <= 100; $a++)
      {
        $validation = $sheet->getCell("A".$a)->getDataValidation();
        $validation->setType( \PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST );
        $validation->setErrorStyle( \PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION );
        $validation->setAllowBlank(true);
        $validation->setShowDropDown(true);
        $validation->setFormula1('Hidden!$A$3:$A$'.(count($pick_up_list) + 2));
      }

      $sheet->getStyle('A2:L2')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
      
      $writer = new Xlsx($spreadsheet);

      $path = "format/import job format.xlsx";
      $this->storeExcel($writer, $path);
      return Storage::download($path);
    }

    public function importNewJobs(Request $request)
    {
      $file = $request->file('file');
      if($file)
      {
        $filename = $file->getClientOriginalName();
        $path = $file->store('temp');
        $inputFileName = 'storage/'.$path;
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $spreadsheet = $reader->load($inputFileName);

        $sheet = $spreadsheet->getSheetByName("Jobs list");
        $sheet->setCellValue('M2', "Result");
        $user = Auth::user();
        $today = date('Y-m-d');
        if($sheet)
        {
          $pick_up_list = Pick_up::get();

          $sheet_rows = $sheet->getHighestDataRow();
          $sheet->getStyle('A3'.':M'.$sheet_rows)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFFF');

          for($a = 3; $a <= $sheet_rows; $a++)
          {
            $pick_up = null;

            $pick_up_location = $sheet->getCell("A".$a)->getValue();
            $name = $sheet->getCell("B".$a)->getValue();
            $email = $sheet->getCell("C".$a)->getValue();
            $contact_number = $sheet->getCell("D".$a)->getValue();
            $address = $sheet->getCell("E".$a)->getValue();
            $postal_code = $sheet->getCell("F".$a)->getValue();
            $est_delivery_from_date = $sheet->getCell("G".$a)->getValue();
            $est_delivery_from_time = $sheet->getCell("H".$a)->getValue();
            $est_delivery_to_date = $sheet->getCell("I".$a)->getValue();
            $est_delivery_to_time = $sheet->getCell("J".$a)->getValue();
            $wallet = $sheet->getCell("K".$a)->getValue();
            $remarks = $sheet->getCell("L".$a)->getValue();

            if($pick_up_location)
            {
              foreach($pick_up_list as $pick_up_detail)
              {
                if($pick_up_detail->name == $pick_up_location)
                {
                  $pick_up = $pick_up_detail;
                  break;
                }
              }
            }

            if(!$pick_up)
            {
              $sheet->setCellValue('M'.$a, "Pick up location not found.");
              $sheet->getStyle('A'.$a.':M'.$a)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('F2DCDB');
            }
            elseif(!$name)
            {
              $sheet->setCellValue('M'.$a, "Customer name cannot be empty.");
              $sheet->getStyle('A'.$a.':M'.$a)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('F2DCDB');
            }
            elseif(!$contact_number)
            {
              $sheet->setCellValue('M'.$a, "Customer contact number cannot be empty.");
              $sheet->getStyle('A'.$a.':M'.$a)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('F2DCDB');
            }
            elseif(!$address)
            {
              $sheet->setCellValue('M'.$a, "Customer address cannot be empty.");
              $sheet->getStyle('A'.$a.':M'.$a)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('F2DCDB');
            }
            elseif(!$postal_code)
            {
              $sheet->setCellValue('M'.$a, "Address postal code cannot be empty.");
              $sheet->getStyle('A'.$a.':M'.$a)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('F2DCDB');
            }
            elseif(!$wallet)
            {
              $sheet->setCellValue('M'.$a, "Wallet value cannot be empty.");
              $sheet->getStyle('A'.$a.':M'.$a)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('F2DCDB');
            }
            else
            {
              $est_from = null;
              $est_to = null;

              if($est_delivery_from_date && $est_delivery_from_time)
              {
                $est_from_date = ($est_delivery_from_date - 25569) * 86400;
                $est_from_date = date('Y-m-d', $est_from_date);
                $est_from_time = $this->getExcelTime($est_delivery_from_time);

                $est_from = $est_from_date." ".$est_from_time;
              }

              if($est_delivery_to_date && $est_delivery_to_time)
              {
                $est_to_date = ($est_delivery_to_date - 25569) * 86400;
                $est_to_date = date('Y-m-d', $est_to_date);
                $est_to_time = $this->getExcelTime($est_delivery_to_time);

                $est_to = $est_to_date." ".$est_to_time;
              }

              if(strlen($postal_code) != 6)
              {
                for($a = strlen($postal_code); $a < 6; $a++)
                {
                  $postal_code = "0".$postal_code;
                }
              }

              Driver_jobs::create([
                'name' => $name,
                'email' => $email,
                'contact_number' => $contact_number,
                'address' => $address,
                'postal_code' => $postal_code,
                'est_delivery_from' => $est_from,
                'est_delivery_to' => $est_to,
                'price' => $wallet,
                'pick_up_id' => $pick_up->id,
                'pick_up' => $pick_up->name,
                'remarks' => $remarks,
                'job_date' => $today,
                'created_by' => $user->name,
                'created_by_id' => $user->id
              ]);

              $sheet->setCellValue('M'.$a, "Created.");
              $sheet->getStyle('A'.$a.':M'.$a)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFFF');
            }
          }

          $sheet->getStyle('A2:M'.$sheet_rows)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

          $writer = new Xlsx($spreadsheet);
          $path = "format/import job format result.xlsx";
          $this->storeExcel($writer, $path);
          return Storage::download($path);
        }
      }
    }

    public function getExcelTime($time)
    {
      $total = $time * 24; //multiply by the 24 hours
      $hours = floor($total); //Gets the natural number part
      $minute_fraction = $total - $hours; //Now has only the decimal part
      $minutes = $minute_fraction * 60; //Get the number of minutes
      $display = $hours . ":" . ($minutes < 10 ? substr("0".$minutes, 0, 2) : substr($minutes, 0, 2)). ":00";

      return $display;
    }

    public function getAdminReport()
    {
      return view('delivery.report');
    }

    public function getDriverEarningReport()
    {
      $date_from = date("Y-m-01");
      $date_to = date('Y-m-d');

      if(isset($_GET['date_from']))
      {
        $date_from = date('Y-m-d', strtotime($_GET['date_from']));
      }

      if(isset($_GET['date_to']))
      {
        $date_to = date('Y-m-d', strtotime($_GET['date_to']));
      }

      $driver_list = User::where('user_type', 'driver')->get();
      $driver_jobs = Driver_jobs::whereBetween('job_date', [$date_from, $date_to])->orderBy('driver_id')->get();

      $total = 0;
      foreach($driver_jobs as $job)
      {
        if($job->completed == 1)
          $total += $job->price;
      }

      foreach($driver_list as $driver)
      {
        $driver->total = 0;
        $driver->total_completed = 0;
        $driver_jobs_list = array();
        foreach($driver_jobs as $job)
        {
          if($driver->id == $job->driver_id)
          {
            if($job->completed == 1)
            {
              $driver->total += $job->price;
              $driver->total_completed++;
            }
            array_push($driver_jobs_list, $job);
          }
        }

        $driver->jobs = $driver_jobs_list;
      }

      return view('delivery.earning_report', compact('date_from', 'date_to', 'driver_list', 'total'));
    }

    public function getDriverEarningDetail()
    {
      $date_from = $_GET['date_from'];
      $date_to = $_GET['date_to'];
      $driver_id = $_GET['driver_id'];

      $driver = User::where('id', $driver_id)->first();
      $driver_jobs = Driver_jobs::whereBetween('job_date', [$date_from, $date_to])->where('driver_id', $driver_id)->get();

      return view('delivery.earning_detail', compact('date_from', 'date_to', 'driver_jobs', 'driver'));
    }

    public function storeExcel($writer, $path)
    {
      ob_start();
      $writer->save('php://output');
      $content = ob_get_contents();
      ob_end_clean();
      Storage::disk('local')->put($path, $content); 
    }

    public function getAdminAutoRoute()
    {
      $autoroute = null;
      if(isset($_GET['autoroute']))
      {
        $autoroute = $_GET['autoroute'];
      }

      $job_list = Driver_jobs::where('completed', null)->orderBy('postal_code')->get();
      $driver_list = User::where('user_type', 'driver')->get();

      foreach($driver_list as $driver)
      {
        $driver->job_list = array();
        $driver->lat_lng = array();
      }

      $no_driver_job_list = array();
      if($autoroute == 1)
      {
        $average = 0;
        if(count($driver_list) > 0)
        {
          $average = intval(round(count($job_list) / count($driver_list)));
        }

        if($average > 0)
        {
          $job_count = 0;
          foreach($driver_list as $driver)
          {
            $driver_jobs = array();
            $driver_id = array();
            for($a = 0; $a < $average; $a++)
            {
              if($job_count >= count($job_list))
              {
                break;
              }

              array_push($driver_jobs, $job_list[$job_count]);
              $job_count++;
            }

            $driver->job_list = $driver_jobs;
            $driver->lat_lng = array();
          }

          if($job_count < count($job_list))
          {
            foreach($driver_list as $driver)
            {
              $driver_jobs = $driver->job_list;
              array_push($driver_jobs, $job_list[$job_count]);

              $driver->job_list = $driver_jobs;
              $job_count++;

              if($job_count >= count($job_list))
              {
                break;
              }
            }
          }
        }
        else
        {
          $j_key = array();
          foreach($driver_list as $driver)
          {
            $driver_jobs = array();
            foreach($job_list as $key => $job)
            {
              if(!in_array($key, $j_key))
              {
                array_push($driver_jobs, $job);
                array_push($j_key, $key);

                $driver->job_list = $driver_jobs;
                break;
              }
            }
          }
        }
      }
      else
      {
        foreach($job_list as $job)
        {
          if($job->driver_id == null)
          {
            array_push($no_driver_job_list, $job);
          }
        }

        foreach($driver_list as $driver)
        {
          $driver_jobs = array();
          foreach($job_list as $job)
          {
            if($job->driver_id == $driver->id)
            {
              array_push($driver_jobs, $job);
            }
          }

          $driver->job_list = $driver_jobs;
        }
      }
      
      return view('delivery.autoroute', compact('job_list', 'driver_list', 'no_driver_job_list', 'autoroute'));
    }

    public function assignDriver(Request $request)
    {
      if($request->job_id)
      {
        Driver_jobs::where('completed', null)->update([
          'driver' => null,
          'driver_id' => null
        ]);
        
        $driver_list = User::where('user_type', 'driver')->get();
        $now = date('Y-m-d H:i:s');
        foreach($request->job_id as $job_id)
        {
          $job_id_name = "job_id_".$job_id;
          $driver_id = $request->$job_id_name;

          $driver_detail = null;
          foreach($driver_list as $driver)
          {
            if($driver->id == $driver_id)
            {
              $driver_detail = $driver;
              break;
            }
          }

          Driver_jobs::where('id', $job_id)->update([
            'driver' => $driver_detail->name,
            'driver_id' => $driver_detail->id,
            'assigned_at' => $now
          ]);
        }
      }

      $response = new \stdClass();
      $response->error = 0;
      $response->message = "Jobs assigned.";

      return response()->json($response);
    }

    public function deleteJob(Request $request)
    {
      driver_jobs::where('id', $request->job_id)->delete();
      $response = new \stdClass();
      $response->error = 0;
      $response->message = "Jobs deleted.";

      return response()->json($response);
    }
}
