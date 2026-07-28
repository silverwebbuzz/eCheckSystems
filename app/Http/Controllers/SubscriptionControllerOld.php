<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\SubscriptionHelper;
use App\Models\User;
use App\Models\Package;
use Illuminate\Support\Facades\Http;
use App\Models\PaymentSubscription;
use App\Models\PaymentHistory;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\EmailTemplate;
use App\Mail\SendEmail;
use App\Mail\ResetPasswordMail;
use App\Mail\AdminMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Mail\SendNewSubMail;
use App\Mail\RegistrationVerificationMail;
use Illuminate\Support\Facades\Log;

class SubscriptionControllerOld extends Controller
{
    protected $SubscriptionHelper;
    public function __construct(SubscriptionHelper $subscriptionHelper)
    {
        $this->subscriptionHelper = $subscriptionHelper;
    }

    public function checkout($id, $plan)
    {
        $user = User::find($id);

        // if($user != null && $user->CurrentPackageID == -1){
        //     return redirect()->back();    
        // }

        if ($user->EmailVerified == false) {
            return redirect()->back()->with('verify_error', 'Your email is not verified.');
        }

        $package = Package::find($plan);

        if ($package != null && strtolower(trim($package->Name)) == 'trial') {
            return redirect()->back();
        }

        $data = [
            'cusID' => $user->CusID,
            'price_id' => $package->PriceID,
            'user_id' => $id,
            'plan' => $plan,
        ];

        $res = $this->subscriptionHelper->addSubscription($data);

        if (!empty($res)) {

            return redirect($res['url']);
        }

        return redirect()->route('user.login')->with('success', 'Something went wrong');
    }

    public function success(Request $request)
    {
        $sessionId = $request->get('session_id');
        $userHash = $request->get('user');
        $plan = $request->get('plan');

        $user = User::whereRaw('MD5(UserID) = ?', [$userHash])->firstOrFail();

        // 1. Get checkout session details
        $session = Http::withToken(config('services.stripe.secret'))
            ->get("https://api.stripe.com/v1/checkout/sessions/{$sessionId}");

        if (!$session->successful()) {
            abort(500, 'Payment session could not be verified.');
        }

        $session_data = $session->json();
        $subscriptionId = $session_data['subscription'] ?? null;

        if (!$subscriptionId) {
            abort(500, 'Subscription ID missing in session.');
        }

        // 2. Fetch latest invoice for subscription
        $invoice = Http::withToken(config('services.stripe.secret'))
            ->get("https://api.stripe.com/v1/invoices", [
                'subscription' => $subscriptionId,
                'limit' => 1,
            ]);

        $invoiceData = null;
        if ($invoice->successful() && !empty($invoice->json('data.0'))) {
            $invoiceData = $invoice->json('data.0');
        }

        $invoiceId = $invoiceData['id'] ?? null;

        if (!empty($invoiceId)) {
            // 3. Update user and create subscription record
            $user->Status = 'Active';
            $user->SubID = $subscriptionId;
            $user->CurrentPackageID = $plan;
            $user->save();

            // PaymentSubscription::where('UserID', $user->UserID)
            //     ->whereIn('Status', ['Canceled', 'Pending'])
            //     ->delete();

            $packages = Package::findOrFail($user->CurrentPackageID);

            $paymentStartDate = Carbon::now();
            $paymentEndDate = $paymentStartDate->copy()->addHours(24);
            $nextRenewalDate = $paymentStartDate->copy()->addDays((int) $packages->Duration + 1);

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
                'TransactionID' => $invoiceId,
                'InvoiceID' => $invoiceId,
                'Status' => 'Active',
            ]);

            PaymentHistory::create([
                'PaymentSubscriptionID' => $paymentSubscription->PaymentSubscriptionID,
                'PaymentAmount' => $packages->Price,
                'PaymentDate' => $paymentStartDate,
                'PaymentStatus' => 'Success',
                'PaymentAttempts' => 0,
                'TransactionID' => $invoiceId,
                'InvoiceID' => $invoiceId,
            ]);

            $user_name = $user->FirstName . ' ' . $user->LastName;
            $link = route('user.verify_email', [$user->UserID, sha1($user->Email)]);

            $data = [
                'plan_name' => $packages->Name,
                'start_date' => $paymentStartDate->format('m/d/Y'),
                'next_billing_date' => $nextRenewalDate->format('m/d/Y'),
                'amount' => $packages->Price,
                'verify_url' => $link,
                'verify_btn' => '<a href="' . $link . '" target="_blank">Verify Email</a>'
            ];

            // Mail::to($user->Email)->send(new RegistrationVerificationMail(12, $user->FirstName.' '.$user->LastName,$link_button,$link));   
            Mail::to($user->Email)->send(new SendNewSubMail(6, $user_name, $data));
            Mail::to(env('ADMIN_EMAIL'))->send(new AdminMail(10, $packages->Name, $user_name, $user->Email));
            // Optional: redirect or show a view
             if($user->EmailVerified == 1){
                return redirect()->route('user.login');
             }else{
                 return redirect()->route('user.login')->with('success', 'Verification link sent to your email');
             }
        }
        return redirect()->route('user.login')->with('error', 'Something want to wrong');
    }

    public function cancel()
    {
        return redirect()->route('user.login')->with('error', 'Something went wrong');
    }

    // public function add_card(Request $request) 
    // {
    
    //     $stripeSecretKey = env('STRIPE_SECRET');
    //     $stripeToken = $request->stripeToken; 
    //     $customerId =Auth::user()->CusID;

    //     try {
    //         $response = Http::withBasicAuth($stripeSecretKey, '')
    //         ->asForm()
    //         ->post("https://api.stripe.com/v1/customers/{$customerId}/sources", [
    //             'source' => $stripeToken
    //         ]);

    //         $data = $response->json();

    //         if ($response->failed() || isset($data['error'])) {
    //             $message = $data['error']['message'] ?? 'Something went wrong';
    //             return redirect()->back()->with('error_card', $message);
    //         }

    //         return redirect()->back()->with('success_card', 'Card added successfully');
    //     } catch (\Exception $e) {
    //         return redirect()->back()->with('error_card', 'Something went wrong');
    //     }
    // }
    public function add_card(Request $request)
    {
        $stripeSecretKey = env('STRIPE_SECRET');
        $paymentMethodId = $request->payment_method;
        $customerId = Auth::user()->CusID;

        try {
            // Step 1: List existing payment methods
            $existing = Http::withBasicAuth($stripeSecretKey, '')
                ->get("https://api.stripe.com/v1/payment_methods", [
                    'customer' => $customerId,
                    'type' => 'card',
                ]);

            $existingMethods = $existing->json()['data'] ?? [];

            // Step 2: Check if this payment method is already attached
            foreach ($existingMethods as $method) {
                if ($method['id'] === $paymentMethodId) {
                    return redirect()->back()->with('info_card', 'This card is already added.');
                }
            }

            // Get the fingerprint of the new payment method
            $newPm = Http::withBasicAuth($stripeSecretKey, '')
                ->get("https://api.stripe.com/v1/payment_methods/{$paymentMethodId}")
                ->json();

            $newFingerprint = $newPm['card']['fingerprint'] ?? null;

            foreach ($existingMethods as $method) {
                if (
                    isset($method['card']['fingerprint']) &&
                    $method['card']['fingerprint'] === $newFingerprint
                ) {
                    return redirect()->back()->with('error_card', 'This card already exists.');
                }
            }
            // Step 3: Attach if not already present
            $response = Http::withBasicAuth($stripeSecretKey, '')
                ->asForm()
                ->post("https://api.stripe.com/v1/payment_methods/{$paymentMethodId}/attach", [
                    'customer' => $customerId,
                ]);

            $data = $response->json();

            if ($response->failed() || isset($data['error'])) {
                $message = $data['error']['message'] ?? 'Something went wrong';
                return redirect()->back()->with('error_card', $message);
            }

            return redirect()->back()->with('success_card', 'Card added successfully');

        } catch (\Exception $e) {
            return redirect()->back()->with('error_card', 'Something went wrong');
        }
    }

    // public function delete_card($id)
    // {
    //     $stripeSecretKey = env('STRIPE_SECRET');
    //     $customerId =Auth::user()->CusID;

    //     try {
    //         $response = Http::withBasicAuth($stripeSecretKey, '')
    //             ->delete("https://api.stripe.com/v1/customers/{$customerId}/sources/{$id}");

    //         $data = $response->json();

    //         if ($response->failed() || isset($data['error'])) {
    //             $message = $data['error']['message'] ?? 'Failed to delete card';
    //             return redirect()->back()->with('error_card', $message);
    //         }

    //         return back()->with('success_card', 'Card deleted successfully!');
    //     } catch (\Exception $e) {
    //         return redirect()->back()->with('error_card', 'Failed to delete card');
    //     }
    // }
    public function delete_card($id)
    {
        $stripeSecretKey = env('STRIPE_SECRET');
        $customerId = Auth::user()->CusID;

        try {
            $paymentMethods = Http::withToken($stripeSecretKey)
                ->get("https://api.stripe.com/v1/payment_methods", [
                    'customer' => $customerId,
                    'type' => 'card',
                ])
                ->json()['data'] ?? [];

            if (!empty($paymentMethods)) {
                // If only one card, do not delete
                if (count($paymentMethods) <= 1) {
                    return redirect()->back()->with('error_card', 'At least one card is required.');
                }

                // Delete (detach) this payment method
                $response = Http::withToken($stripeSecretKey)
                    ->asForm()
                    ->post("https://api.stripe.com/v1/payment_methods/{$id}/detach");

            }

            if ($response->failed() || isset($data['error'])) {
                $message = $data['error']['message'] ?? 'Failed to delete card';
                return redirect()->back()->with('error_card', $message);
            }

            return back()->with('success_card', 'Card deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error_card', 'Failed to delete card');
        }
    }


    public function set_default($id)
    {
        $stripeSecretKey = env('STRIPE_SECRET');
        $customerId = Auth::user()->CusID;

        $response = Http::withBasicAuth($stripeSecretKey, '')
            ->asForm()
            ->post("https://api.stripe.com/v1/customers/{$customerId}", [
                'invoice_settings[default_payment_method]' => $id,
            ]);

        $data = $response->json();

        if ($response->failed() || isset($data['error'])) {
            return back()->with('error_card', $data['error']['message'] ?? 'Failed to set default card.');
        }

        return back()->with('success_card', 'Default card set successfully!');
    }

}