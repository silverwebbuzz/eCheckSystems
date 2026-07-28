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

class EnsurePaymentIsComplete
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $PaymentSubscription = PaymentSubscription::where('UserID', Auth::user()->UserID)->orderBy('PaymentSubscriptionID', 'desc')->first();

        if ($PaymentSubscription) {
            // $CancelAt = Carbon::parse($PaymentSubscription->CancelAt);
            if ($PaymentSubscription->Status == 'Canceled' || $PaymentSubscription->Status == 'Inactive') {
                return redirect()->route('expired_sub');
            }
            if ($PaymentSubscription->Status == 'Pending') {
                return redirect()->route('pending_sub');
            }
        }
        return $next($request);
    }
}
