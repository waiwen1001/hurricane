<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
  if (Auth::check())
  {
    if(Auth::user()->user_type == "delivery")
    {
      return redirect(route('getAdminHome'));
    }
    elseif(Auth::user()->user_type == "restaurant")
    {
      return redirect(route('getRestaurant'));
    }
    elseif(Auth::user()->user_type == "driver")
    {
      return redirect(route('getDriver'));
    }
  }
  return redirect(route('login'));
});

Auth::routes();

Route::prefix('admin')->group(function () {
  Route::get('/register', 'DeliveryController@getAdminRegister')->name('getAdminRegister');
  Route::get('/reset', 'DeliveryController@getAdminReset')->name('getAdminReset');
  Route::post('/send_reset', 'DeliveryController@sendReset')->name('sendReset');
  Route::get('/reset_password', 'DeliveryController@getResetPassword')->name('getResetPassword');
  Route::post('/reset_password', 'DeliveryController@resetPassword')->name('resetPassword');
  Route::get('/reset_success', 'DeliveryController@getResetSuccess')->name('getResetSuccess');
  Route::post('/getDriverLocation', 'DeliveryController@getDriverLocation')->name('getDriverLocation');
  Route::post('/importNewJobs', 'DeliveryController@importNewJobs')->name('importNewJobs');
});

Route::group(['middleware' => ['auth:web'] ], function(){
  Route::prefix('admin')->group(function () {
    Route::get('/', 'DeliveryController@getAdminHome')->name('getAdminHome');
    Route::get('/restaurant', 'DeliveryController@getAdminRestaurant')->name('getAdminRestaurant');
    Route::post('/createRestaurant', 'DeliveryController@createRestaurant')->name('createRestaurant');
    Route::post('/saveRestaurantTier', 'DeliveryController@saveRestaurantTier')->name('saveRestaurantTier');
    Route::post('/saveRestaurantRebate', 'DeliveryController@saveRestaurantRebate')->name('saveRestaurantRebate');
    Route::get('/getOrderDetail/{id}', 'DeliveryController@getOrderDetail')->name('getOrderDetail');
    Route::post('/updateSystemStatus', 'DeliveryController@updateSystemStatus')->name('updateSystemStatus');
    Route::post('/exportOrder', 'DeliveryController@exportOrder')->name('exportOrder');

    Route::get('/driver', 'DeliveryController@getAdminDriver')->name('getAdminDriver');
    Route::get('/jobs_list', 'DeliveryController@getAdminJobsList')->name('getAdminJobsList');
    Route::get('/downloadImportJobFormat', 'DeliveryController@downloadImportJobFormat')->name('downloadImportJobFormat');
    Route::post('/importNewJobs', 'DeliveryController@importNewJobs')->name('importNewJobs');
    Route::get('/report', 'DeliveryController@getAdminReport')->name('getAdminReport');
    Route::get('/driver_earning_report', 'DeliveryController@getDriverEarningReport')->name('getDriverEarningReport');
    Route::get('/driver_earning_detail', 'DeliveryController@getDriverEarningDetail')->name('getDriverEarningDetail');
  });

  Route::prefix('order')->group(function () {
    Route::get('/', 'DeliveryController@getRestaurant')->name('getRestaurant');
    Route::post('/submitOrder', 'DeliveryController@submitOrder')->name('submitOrder');
    Route::post('/exportRestaurantOrder', 'DeliveryController@exportRestaurantOrder')->name('exportRestaurantOrder');
    Route::post('/importRestaurantOrder', 'DeliveryController@importRestaurantOrder')->name('importRestaurantOrder');
    Route::get('/downloadImportFormat', 'DeliveryController@downloadImportFormat')->name('downloadImportFormat');
  });

  Route::prefix('driver')->group(function () {
    Route::get('/', 'DriverController@getDriver')->name('getDriver');
    Route::post('/submitPickUp', 'DriverController@submitPickUp')->name('submitPickUp');
    Route::get('/pick_up', 'DriverController@getDriverPickUp')->name('getDriverPickUp');
    Route::get('/select_jobs', 'DriverController@getDriverSelectJobs')->name('getDriverSelectJobs');
    Route::post('/driverAcceptJobs', 'DriverController@driverAcceptJobs')->name('driverAcceptJobs');
    Route::get('/jobs', 'DriverController@getDriverJobs')->name('getDriverJobs');
    Route::post('/driverStartJobs', 'DriverController@driverStartJobs')->name('driverStartJobs');
    Route::post('/submitCompleteJob', 'DriverController@submitCompleteJob')->name('submitCompleteJob');
    Route::post('/cancelJob', 'DriverController@cancelJob')->name('cancelJob');
  });

  Route::post('/checkOnline', 'DeliveryController@checkOnline')->name('checkOnline');
  Route::post('/updateLocation', 'DeliveryController@updateLocation')->name('updateLocation');
});

Route::get('/manual_logout', 'HomeController@logout')->name('manual_logout');


