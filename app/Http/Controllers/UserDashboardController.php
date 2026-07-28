<?php

namespace App\Http\Controllers;

use App\Models\WebForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\PaymentSubscription;
use App\Models\Package;
use Carbon\Carbon;
use App\Models\Company;
use App\Models\Payors;
use App\Models\PaymentHistory;

class UserDashboardController extends Controller
{
    public function index()
    {
        if(!Auth::check()) {
            return redirct()->route('user.login');
        }

        $user = User::where('userID', Auth::user()->UserID)->first();
        $package = $user->CurrentPackageID;
        $current_package_id = $user->CurrentPackageID;
        if($package == -1) {
            $package = Package::whereRaw('LOWER(Name) = ?', ['trial'])->first();
        } else {
            $package = Package::find($user->CurrentPackageID);
        }
        
        $paymentSubscription = PaymentSubscription::where('UserID', Auth::user()->UserID)->where('PackageID', $user->CurrentPackageID)
                                ->where('Status', 'Active')->orderBy('PaymentSubscriptionID', 'desc')->first();
        
        $total_days = $package->Duration;
        $package_name = $package->Name;
        $expiry = Carbon::createFromFormat('Y-m-d', $paymentSubscription->NextRenewalDate);
        $expiryDate = User::user_timezone($expiry, 'M d, Y');
        $remainingDays = $expiry->diffInDays(Carbon::now(), false);
        
        $package_data = [
            'total_days' => $total_days,
            'package_name' => $package_name,
            'expiryDate' => $expiryDate,
            'remainingDays' => abs((int)$remainingDays),
        ];

        $given_checks = ($paymentSubscription->ChecksGiven == 0) ? 'Unlimited' : $paymentSubscription->ChecksGiven;
        $used_checks = ($paymentSubscription->ChecksGiven == 0) ? '-' :$paymentSubscription->ChecksUsed;
        $remaining_checks =($paymentSubscription->ChecksGiven == 0) ? '-'  : $paymentSubscription->RemainingChecks;
        $checks_received = ($paymentSubscription->ChecksGiven == 0) ? '-' :$paymentSubscription->ChecksReceived;
        $checks_sent = ($paymentSubscription->ChecksGiven == 0) ? '-' :$paymentSubscription->ChecksSent;

        $total_vendor = Payors::where('UserID', Auth::user()->UserID)
                        ->whereIn('Type', ['Payor'])
                        ->count();

        //
        $total_client = Payors::where('UserID', Auth::user()->UserID)
                        ->whereIn('Type', ['Payee'])
                        ->count();                
        //
        $total_companies = Company::where('UserID', Auth::user()->UserID)->count();

        $query = Package::where('Status', 'Active');

        // if ($user && $user->CurrentPackageID == -1) {
            $query->whereRaw('LOWER(Name) != ?', ['trial']);
        // }

        $packages = $query->get();
       
        $webforms = WebForm::where('UserID', Auth::user()->UserID)->get();


        return view('content.dashboard.user-dashboards-analytics', compact('current_package_id','webforms','user','packages','checks_received', 'checks_sent', 'package_data', 'total_vendor', 'total_client', 'total_companies', 'given_checks', 'used_checks', 'remaining_checks', 'package', 'paymentSubscription'));
    }

    public function profile()
    {
        if(!Auth::check()) {
            return redirect()->route('user.login');
        }

        $user = User::where('userID', Auth::user()->UserID)->first();
        $package = $user->CurrentPackageID;
        if($user->CurrentPackageID == -1) {
            $package_data = [
                'total_days' => 0,
                'package_name' => 0,
                'expiryDate' => 0,
                'remainingDays' => 0,
                'downgrade_payment' => 0,
                'cancel_plan' => 0,
            ];
    
        } else {
            $paymentSubscription = PaymentSubscription::where('UserID', Auth::user()->UserID)
                ->where('Status', 'Active')->where('PackageID', $user->CurrentPackageID)
                ->orderBy('PaymentSubscriptionID', 'desc')->first();
            $package = Package::find($user->CurrentPackageID);
            $total_days = $package->Duration;
            $package_name = $package->Name;
            $expiry = Carbon::createFromFormat('Y-m-d', $paymentSubscription->NextRenewalDate);
            $expiryDate = User::user_timezone($expiry, 'M d, Y');
            $remainingDays = $expiry->diffInDays(Carbon::now(), false);

            $package_data = [
                'total_days' => $total_days,
                'package_name' => $package_name,
                'expiryDate' => $expiryDate,
                'remainingDays' => abs((int)$remainingDays),
            ];
        }

        return view('user.profile.profile', compact('user', 'package_data', 'package'));
    }

    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstname' => 'required',
            'lastname' => 'required',
            'address' => 'required',
            'email' => 'required|email',
            'phone_number' => 'required|regex:/^\d{3}-\d{3}-\d{4}$/',
            'company_name' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $admin = User::where('UserID', $request->user_id)->first();
        // $admin->Username = $request->username;
        $admin->Email = $request->email;
        $admin->FirstName = $request->firstname;
        $admin->Address = $request->address;
        $admin->LastName = $request->lastname;
        $admin->PhoneNumber = preg_replace('/\D/', '', $request->phone_number);
        $admin->CompanyName = $request->company_name;
        $admin->City = $request->city;
        $admin->State = $request->state;
        $admin->Zip = $request->zip;
        // $admin->timezone = $request->timezone;
        $admin->UpdatedAt = now();
        $admin->save();

        return redirect()->route('user.profile')->with('profile_success', 'Profile updated successfully');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
            'new_password_confirmation' => 'required|min:6',
        ]);

        if (!Hash::check($request->old_password, Auth::user()->PasswordHash)) {
            return back()->withErrors(['old_password' => 'The old password is incorrect.']);
        }

        $admin = User::where('UserID', $request->user_id)->first();
        $admin->PasswordHash = Hash::make($request->new_password);
        $admin->save();

        // Redirect back with success message
        return redirect()->route('user.profile')->with('pass_success', 'Password changed successfully');
    }
}
