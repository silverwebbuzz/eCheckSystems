<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * When a background (AJAX) request is sent to the customer or admin login page because the
 * login expired, answer 401 JSON instead of the login page HTML. The page script
 * (layouts/sections/session-expired) then shows "Your session has expired" and opens the login page,
 * instead of DataTables' confusing "Invalid JSON response" warning.
 *
 * Runs around the route middleware (UserMiddleware / AdminMiddleware), so those stay unchanged.
 */
class AjaxSessionExpired
{
    private const LOGIN_ROUTES = ['user.login', 'admin.login'];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (!($request->ajax() || $request->expectsJson()) || !$response instanceof RedirectResponse) {
            return $response;
        }

        $target = rtrim(strtok($response->getTargetUrl(), '?'), '/');

        foreach (self::LOGIN_ROUTES as $name) {
            if (Route::has($name) && $target === rtrim(route($name), '/')) {
                return response()->json([
                    'message' => 'Your session has expired. Please log in again.',
                    'redirect' => route($name),
                ], 401);
            }
        }

        return $response;
    }
}
