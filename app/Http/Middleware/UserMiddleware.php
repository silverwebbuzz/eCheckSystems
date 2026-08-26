<?php

namespace App\Http\Middleware;

use App\Models\PaymentHistory;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\PaymentSubscription;
use Carbon\Carbon;
use Illuminate\Support\Facades\Route;

class UserMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && User::where('Email', Auth::user()->Email)->exists()) {
            $user = Auth::user()->fresh();

            if ($user->Status === User::STATUS_INACTIVE) {
                Auth::logout();
                return redirect()->route('user.login')->with('error', 'Your account is not active.');
            }

            if ($user->isPendingApproval()) {
                Auth::logout();
                return redirect()->route('user.login')->with(
                    'error',
                    'Your account is currently under review. You will receive an email once your account has been approved.'
                );
            }

            return $next($request);
        }

        return redirect()->route('user.login')->with('error', 'You do not have access.');
    }
}
