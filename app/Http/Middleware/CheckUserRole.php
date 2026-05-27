<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */

    //  public function handle(Request $request, Closure $next): Response

    public function handle(Request $request, Closure $next): Response
    {
      // Check if the user is authenticated and has 'admin' role
      if ($request->user() && $request->user()->role !== 'admin') {

        // i want to return view but i got error
        // return response()->json(['message' => 'Access denied. Admins only.'], 403);

        // return view('admin.error.errors_login');
        return response()->view('admin.error.errors_login');


    }

    return $next($request);
    }
}
