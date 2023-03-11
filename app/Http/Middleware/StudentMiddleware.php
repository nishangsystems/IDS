<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\User;

class StudentMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(Auth::user() == null){
            return redirect(route('login'));
          }elseif (!auth('student')->check()) //If user does //not have this permission
            {
                return redirect(route('login'));
            }

        return $next($request);
    }
}