<?php

namespace Komu4e\Http\Middleware;

use Closure;
use Auth;
use Gate;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */


    /*public function __construct()
    {
        dump('ddd');
    }*/

    public function handle($request, Closure $next)
    {
        if (Auth::user()->id==1) {
            return $next($request);
        }
        else
        {
            return redirect('/');
        }
    }
}
