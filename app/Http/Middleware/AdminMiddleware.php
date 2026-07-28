<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\AdminUser;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('admin')->check() && AdminUser::where('Email', Auth::guard('admin')->user()->Email)->exists()) {
            return $next($request);
        }

        // Redirect if the user is not an admin
        return redirect()->route('admin.login')->with('error', 'You do not have admin access.');
    }
}
