<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\BlockedIP;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Services\FraudService;

class CheckBlockedIP
{
    protected $fraudService;

    public function __construct(FraudService $fraudService)
    {
        $this->fraudService = $fraudService;
    }
    public function handle(Request $request, Closure $next)
    {
        // Skip admin routes
        if ($request->is('admin*')) {
            return $next($request);
        }

        $ip = $request->ip();

        $blocked = BlockedIP::where('ip_address', $ip)->first();

        if ($blocked) {
            return response()->view('errors.blocked', [], 403);
        }

        if (Auth::check()) {
            $user = Auth::user();
            $this->fraudService->addIpForFraudUser($user, $ip);

            if ($user->Status === 'Inactive') {

                Auth::logout(); // logout user

                request()->session()->invalidate(); // destroy session

                request()->session()->regenerateToken(); // regenerate csrf token
            }
        }

        return $next($request);
    }

}
