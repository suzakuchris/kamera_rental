<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use Auth;

class PrivilegeLevels
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = Auth::user();
        $privilege_array = $roles;
        if(in_array($user->user_role, $privilege_array)){
            return $next($request);
        }else{
            return redirect()->route('dashboard');
        }
    }
}
