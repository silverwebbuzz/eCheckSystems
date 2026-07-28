<?php

namespace App\Http\Controllers;

use App\Models\QBOCompany;
use App\Services\FraudService;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\Package;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\PaymentSubscription;
use App\Models\PaymentHistory;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\EmailTemplate;
use App\Models\UserHistory;
use App\Mail\SendEmail;
use App\Mail\ResetPasswordMail;
use Illuminate\Support\Facades\Mail;
use App\Helpers\SubscriptionHelper;
use App\Mail\AdminMail;
use App\Mail\RegistrationVerificationMail;
use Illuminate\Support\Facades\Crypt;
use App\Mail\SendNewSubMail;

class UserAuthController extends Controller
{
    protected $SubscriptionHelper;
    public function __construct(SubscriptionHelper $subscriptionHelper)
    {
        $this->subscriptionHelper = $subscriptionHelper;
    }

    public function register()
    {
        if (Auth::check()) {
            return redirect()->route('user.dashboard');
        }
        return view('frontend.auth.register');
    }

    public function login()
    {
        if (Auth::check()) {
            return redirect()->route('user.dashboard');
        }
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.login');
        }
        return view('frontend.auth.login');
    }

    public function package(Request $request)
    {
        $userId = request()->query('user_id');
        $user=User::find($userId);
        $query=Package::where('Status', 'Active');

        $PaymentSubscription = PaymentSubscription::where('UserID', $user->UserID)->exists();
        if($PaymentSubscription){
            $query->whereRaw('LOWER(Name) != ?', ['trial']);
        }
        
        $packages = $query->get();

        return view('frontend.auth.package', compact('packages', 'userId'));
    }

    public function login_action(Request $request,FraudService $fraudService)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $user = User::where('Email', $request->email)->first();

        if (!empty($user) && Hash::check($request->password, $user->PasswordHash)) {

            if (!empty($user) && $user->Status == 'Inactive') {
                
                $fraudService->addIpForFraudUser($user,$request->ip());
                
                return redirect()->back()->withErrors(['login' => 'User status is not Active'])->withInput();
            }
            
            $packag_c = PaymentSubscription::where('UserID', $user->UserID)->where('PackageID', $user->CurrentPackageID)
                ->orderBy('PaymentSubscriptionID', 'desc')->first()?->RemainingChecks ?? 0;

            if($user->CurrentPackageID == null ){
                return redirect()->route('user.package', ['user_id' => $user->UserID]);
            }

            $package = Package::find($user->CurrentPackageID);

            if ($packag_c == 0 && $user->CurrentPackageID != -1 && $package->CheckLimitPerMonth != 0) {
                return redirect()->route('user.package', ['user_id' => $user->UserID]);
            }

            // if ($user->EmailVerified == false) {
            //     $enc_user_id = Crypt::encrypt($user->UserID);
            //     $link = route('user.resend_verify_email', $enc_user_id);
            //     return redirect()->back()->withErrors(['login' => 'Please verify your email first <a href="' . $link . '">Resend</a>'])->withInput();
            // }

            Auth::login($user);
            $name = $user->FirstName . ' ' . $user->LastName;

            $user_history = UserHistory::where('UserID', $user->UserID)->first();
            if (!empty($user_history)) {
                $user_history->last_login = now();
                $user_history->ip = $request->ip();
                $user_history->save();
            } else {
                UserHistory::create([
                    'UserID' => $user->UserID,
                    'last_login' => now(),
                    'ip' => $request->ip(),
                ]);
            }
            
            QBOCompany::where('user_id', $user->UserID)->update([
                'status' => 'not connected'
            ]);

            return redirect()->route('user.dashboard');
        }

        // Authentication failed
        return redirect()->back()->withErrors(['email' => 'Invalid login credentials'])->withInput();
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            // 'username' => 'required|string|unique:User,Username|max:255',
            'address' => 'required',
            'email' => 'required|string|email|unique:User,Email|max:255',
            'phone_number' => 'required|regex:/^\d{3}-\d{3}-\d{4}$/',
            // 'company_name' => 'required'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = [
            'name' => $request->firstname . ' ' . $request->lastname,
            'email' => $request->email,
        ];

        $cus = $this->subscriptionHelper->addCustomer($data);

        $user = User::create([
            'FirstName' => $request->firstname,
            'LastName' => $request->lastname,
            'Email' => $request->email,
            'Address' => $request->address,
            'PhoneNumber' => preg_replace('/\D/', '', $request->phone_number),
            'PasswordHash' => Hash::make($request->password),
            'Status' => 'Active',
            'CreatedAt' => now(),
            'UpdatedAt' => now(),
            'CusID' => !empty($cus['id']) ? $cus['id'] : NULL,
            'CompanyName' => $request->company_name,
            'City' => $request->city,
            'State' => $request->state,
            'Zip' => $request->zip,
            'timezone' => $request->timezone,
            'ip_address' => $request->ip(),
        ]);

       $package = Package::whereRaw('LOWER(Name) = ?', ['trial'])->first();

        return redirect()->route('user-select-free-package', [$user->UserID,$package->PackageID]);
        // return redirect()->route('user.package', ['user_id' => $user->UserID]);
    }

    public function select_package(Request $request, $id, $plan)
    {
        $user = User::find($id);
        $user->CurrentPackageID = $plan;
        $user->Status = 'Active';
        $user->save();

        $PaymentSubscription_plan = PaymentSubscription::where('UserID', $id)->whereIn('Status', ['Canceled', 'Pending'])->delete();

        $packages = Package::find($plan);

        return redirect()->route('user.login')->with('success', 'Account created successful!');
    }


    public function logout()
    {
        Auth::logout();
        return redirect()->route('user.login'); // Redirect to the admin login page
    }

    public function expired_sub()
    {
        $PaymentSubscription = PaymentSubscription::where('UserID', Auth::user()->UserID)->orderBy('PaymentSubscriptionID', 'desc')->first();

        $PaymentHistory = PaymentHistory::where('PaymentSubscriptionID', $PaymentSubscription->PaymentSubscriptionID)
            ->orderBy('PaymentHistoryID', 'desc')->first();

        if ($PaymentSubscription->Status != 'Canceled' && $PaymentSubscription->Status != 'Inactive') {
            return redirect()->route('user.dashboard');
        }
        
        $user = Auth::user();
        $package_id = $PaymentSubscription->PackageID;

        if ($package_id == -1) {
            $package = Package::whereRaw('LOWER(Name) = ?', ['trial'])->first();
        } else {
            $package = Package::find($package_id);
        }

        return view('frontend.auth.expired', compact('user', 'PaymentHistory', 'package_id', 'package'));
    }

    public function pending_sub()
    {

        $PaymentSubscription = PaymentSubscription::where('UserID', Auth::user()->UserID)->orderBy('PaymentSubscriptionID', 'desc')->first();

        $PaymentHistory = PaymentHistory::where('PaymentSubscriptionID', $PaymentSubscription->PaymentSubscriptionID)
            ->orderBy('PaymentHistoryID', 'desc')->first();

        if ($PaymentHistory?->PaymentStatus != 'Failed' || $PaymentSubscription->Status != 'Pending') {
            return redirect()->route('user.dashboard');
        }

        $user = Auth::user();
        $package_id = $user->CurrentPackageID;

        if ($package_id == -1) {
            $package = Package::whereRaw('LOWER(Name) = ?', ['trial'])->first();
        } else {
            $package = Package::find($user->CurrentPackageID);
        }

        return view('frontend.auth.pending', compact('user', 'PaymentHistory', 'package_id', 'package'));
    }

    public function email()
    {
        $emailContent = EmailTemplate::find(1);
        return view('user.emails.mail', compact('emailContent'));
    }

    public function showForgotPasswordForm()
    {
        return view('frontend.auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:User,email',
        ]);

        $user = User::where('Email', $request->email)->first();

        if (!empty($user)) {
            $token = Str::random(60);
            $user->reset_token = $token;
            $user->reset_token_expiry = Carbon::now()->addMinutes(30);
            $user->save();

            $name = $user->FirstName . ' ' . $user->LastName;

            Mail::to($user->Email)->send(new SendEmail(3, $name, $token));
            return back()->with('success', 'We have emailed your password reset link!');
        }
        return back()->with('error', 'The email address you entered does not exist');
    }

    public function showResetForm($token)
    {
        $user = User::where('reset_token', $token)
            ->where('reset_token_expiry', '>', Carbon::now())
            ->first();

        if (!$user) {
            return redirect()->route('user.login')->with('error', 'Invalid or expired token!');
        }

        return view('frontend.auth.reset-password', compact('token'));
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'password' => 'required|min:6',
            'confirm-password' => 'required|min:6',
        ]);

        $user = User::where('reset_token', $request->token)
            ->where('reset_token_expiry', '>', Carbon::now())
            ->first();

        if (!$user) {
            return redirect()->route('user.login')->with('error', 'Invalid or expired token!');
        }

        // Update password and clear reset token
        $user->PasswordHash = Hash::make($request->password);
        $user->reset_token = null;
        $user->reset_token_expiry = null;
        $user->save();

        return redirect()->route('user.login')->with('success', 'Your password has been reset successfully!');
    }

    public function select_free_package(Request $request, $id, $plan)
    {
        $user = User::find($id);

        $alreadySubscribed = PaymentSubscription::where('UserID', $user->UserID)->exists();

        if($user != null && $alreadySubscribed){
            return redirect()->back();
        }
        
        $user->CurrentPackageID = -1;
        $user->Status = 'Active';

        $packages = Package::findOrFail($plan);

        $paymentStartDate = Carbon::now();
        $paymentEndDate = $paymentStartDate->copy()->addHours(24);
        $nextRenewalDate = $paymentStartDate->copy()->addDays((int) $packages->Duration);

        $paymentSubscription = PaymentSubscription::create([
            'UserID' => $user->UserID,
            'PackageID' => $user->CurrentPackageID,
            'PaymentMethodID' => 1,
            'PaymentAmount' => $packages->Price,
            'PaymentStartDate' => $paymentStartDate,
            'PaymentEndDate' => $paymentEndDate,
            'NextRenewalDate' => $nextRenewalDate,
            'ChecksGiven' => $packages->CheckLimitPerMonth,
            'ChecksReceived' => 0,
            'ChecksSent' => 0,
            'ChecksUsed' => 0,
            'RemainingChecks' => $packages->CheckLimitPerMonth,
            'PaymentDate' => $paymentStartDate,
            'PaymentAttempts' => 0,
            'TransactionID' => 'Trial',
            'InvoiceID' => 'Tiral',
            'Status' => 'Active',
            'created_at' => Carbon::now(),
            'ip_address' => $request->ip(),
            'is_sys_generated' => 1
        ]);

        $user->save();

        $name = $user->FirstName . ' ' . $user->LastName;

        $link = route('user.verify_email', [$user->UserID, sha1($user->Email)]);
        $link_button = '<a href="' . $link . '" target="_blank">Verify Email</a>';

        // Mail::to($user->Email)->send(new RegistrationVerificationMail(12, $user->FirstName.' '.$user->LastName,$link_button,$link));
        Mail::to($user->Email)->send(new SendEmail(1, $name, null, $link_button, $link));
        Mail::to(env('ADMIN_EMAIL'))->send(new AdminMail(10, 'Trial', $name, $user->Email));

        return redirect()->route('user.login')->with('success', 'Verification link sent to your email');
        // return redirect()->route('user.login')->with('success', 'Account created successful!');
    }

    public function verify_email($id, $hash)
    {
        $user = User::find($id);

        if ($hash != sha1($user->Email)) {
            return redirect()->route('user.login')->with('error', 'Invalid token!');
        }

        $user->EmailVerified = true;
        $user->save();

        return redirect()->route('user.login')->with('success', 'Email verified successfully!');
    }

    public function resend_verify_email($userId)
    {

        try {

            $userId = Crypt::decrypt($userId);

            $user = User::findOrFail($userId);

            if ($user->EmailVerified) {
                return redirect()->route('user.login')->with('error', 'Email already verified!');
            }
            
            $name = $user->FirstName . ' ' . $user->LastName;

            $link = route('user.verify_email', [$user->UserID, sha1($user->Email)]);
            $link_button = '<a href="' . $link . '" target="_blank">Verify Email</a>';

            if($user->CurrentPackageID == -1){

                Mail::to($user->Email)->send(new SendEmail(1, $name, null, $link_button, $link));

            }else{
             
                $packages = Package::findOrFail($user->CurrentPackageID);

                $paymentStartDate = Carbon::now();
                $paymentEndDate = $paymentStartDate->copy()->addHours(24);
                $nextRenewalDate = $paymentStartDate->copy()->addDays((int)$packages->Duration + 1);

                $data = [
                    'plan_name' => $packages->Name,
                    'start_date' => $paymentStartDate->format('m/d/Y'),
                    'next_billing_date' => $nextRenewalDate->format('m/d/Y'),
                    'amount' => $packages->Price,
                    'verify_url' => $link,
                    'verify_btn'=> $link_button
                ];

                Mail::to($user->Email)->send(new SendNewSubMail(6, $name, $data));
            }
            
            return redirect()->back()->with('success', 'Verification link sent to your email');
            
        } catch (\Exception $e) {
            return redirect()->route('user.login')->with('error', 'Something went wrong!');
        }

    }
}
