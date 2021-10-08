<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // executed after the middleware is loaded so the session have variable
        view()->composer('*', function ($view) 
        {
          $user = Auth::user();

          //...with this variable
          $view->with(['user' => $user]); 
             
        }); 
    }
}
