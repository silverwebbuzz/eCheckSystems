<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\AdminUser;

class AdminAuthController extends Controller
{
    public function adminLogin()
    {
        if(Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }
        if(Auth::guard('web')->check()) {
            return redirect()->route('user.login');
        }
        $pageConfigs = ['myLayout' => 'blank'];
        return view('content.authentications.auth-login-basic', ['pageConfigs' => $pageConfigs]);
    }

    public function login(Request $request)
    {

        if(Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Check credentials manually without using guards
        $admin = AdminUser::where('email', $request->email)->first();

        if ($admin && Hash::check($request->password, $admin->PasswordHash)) {
            Auth::guard('admin')->login($admin);
            return redirect()->route('admin.dashboard');
        }
        // Authentication failed, redirect back with an error message
        return redirect()->back()->withErrors(['email' => 'Invalid login credentials'])->withInput();
    }

    public function logout()
    {
        Auth::guard('admin')->logout();
        return redirect()->route('admin.login'); // Redirect to the admin login page
    }
}
