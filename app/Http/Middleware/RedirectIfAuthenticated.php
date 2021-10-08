<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->check())
        {
          $user = \Auth::user();
          if($user)
          {
            if($user->user_type == "delivery")
            {
              return redirect(RouteServiceProvider::ADMIN);
            }
            elseif($user->user_type == "restaurant")
            {
              return redirect(RouteServiceProvider::RESTAURANT);
            }
            elseif($user->user_type == "driver")
            {
              return redirect(RouteServiceProvider::DRIVER);
            }
          }

          return redirect(RouteServiceProvider::RESTAURANT);
        }

        return $next($request);
    }
}
