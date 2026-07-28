<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PaymentSubscription;
use App\Models\Package;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\PaymentHistory;
use Illuminate\Support\Str;
use App\Helpers\SubscriptionHelper;
use App\Mail\SendUpgradeSubMail;
use App\Mail\SendDowngradeSubMail;
use App\Mail\SendCancelSubMail;
use Illuminate\Support\Facades\Mail;

class BillingAndPlanController extends Controller
{
    protected $SubscriptionHelper;
    public function __construct(SubscriptionHelper $subscriptionHelper)
    {
        $this->subscriptionHelper = $subscriptionHelper;
    }
    public function index()
    {
        $user = User::where('userID', Auth::user()->UserID)->first();
        $package_id = $user->CurrentPackageID;
        $paymentSubscription = PaymentSubscription::where('UserID', Auth::user()->UserID)
            ->where('Status', 'Active')->where('PackageID', $user->CurrentPackageID)
            ->orderBy('PaymentSubscriptionID', 'desc')->first();

        if ($package_id == -1) {
            $package = Package::whereRaw('LOWER(Name) = ?', ['trial'])->first();
        } else {
            $package = Package::find($user->CurrentPackageID);
        }
        $total_days = $package->Duration;
        $package_name = $package->Name;
        $expiry = Carbon::createFromFormat('Y-m-d', $paymentSubscription->NextRenewalDate);
        $expiryDate = User::user_timezone($expiry, 'M d, Y');
        $remainingDays = $expiry->diffInDays(Carbon::now(), false);

        $package_data = [
            'is_unlimited' => ($package->CheckLimitPerMonth == 0) ? true : false,
            'total_days' => $total_days,
            'package_name' => $package_name,
            'expiryDate' => $expiryDate,
            'remainingDays' => abs((int) $remainingDays),
            'RemainingChecks' => $paymentSubscription->RemainingChecks
        ];
        $maxPricePackage = Package::orderBy('price', 'desc')->first();
        $stander_Plan_price = $maxPricePackage->Price;
        $cards = $this->subscriptionHelper->getCustomerPaymentMethods($user->CusID);
        $default_card = $this->subscriptionHelper->getDefaultCard($user->CusID);

        $query = Package::where('Status', 'Active')->whereRaw('LOWER(Name) != ?', ['trial']);

        $packages = $query->get();

        return view('user.billing_and_plan.index', compact('package_data', 'stander_Plan_price', 'user', 'packages', 'package_id', 'cards', 'default_card', 'paymentSubscription'));
    }

    public function upgragde_plan($id)
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->route('user.login');
        }

        $user = User::where('userID', $id)->first();
        $paymentSubscription = PaymentSubscription::where('UserID', $id)->where('PackageID', $user->CurrentPackageID)
            ->orderBy('PaymentSubscriptionID', 'desc')->first();
        $package = Package::find($user->CurrentPackageID);
        $total_days = $package->Duration;
        $package_name = $package->Name;
        $expiry = Carbon::createFromFormat('Y-m-d', $paymentSubscription->NextRenewalDate);
        $expiryDate = $expiry->format('M d, Y');
        $remainingDays = $expiry->diffInDays(Carbon::now(), false);
        $packages = Package::all();
        $package_data = [
            'total_days' => $total_days,
            'package_name' => $package_name,
            'expiryDate' => $expiryDate,
            'remainingDays' => abs(round($remainingDays)),
        ];

        return view('user.billing_and_plan.plan_change', compact('user', 'package_data', 'packages'));
    }

    public function change_plan($id, $plan)
    {
        $user = User::find($id);
        $package = Package::find($plan);
        $user_current_package = Package::find($user->CurrentPackageID);
        $data_current_package = PaymentSubscription::where('UserId', $id)
            ->where('PackageID', $user->CurrentPackageID)
            ->where('Status', 'Active')
            ->orderBy('PaymentSubscriptionID', 'desc')->first();

        if(trim(strtolower($package->Name)) == 'trial' ){
            return redirect()->back();
        }

        if (!empty($data_current_package)) {
            // If upgrading to a higher priced plan
            if ($package->Price > $user_current_package->Price) {
                // Calculate price difference
                $price_difference = $package->Price - $user_current_package->Price;

                // Update subscription in Stripe
                $data = [
                    'subscription_id' => $user->SubID,
                    'new_price_id' => $package->PriceID,
                    'upgrade_amount' => $price_difference * 100
                ];
                $res = $this->subscriptionHelper->updateSubscription($data);
                if (!empty($res['id'])) {
                    // Delete any pending or canceled subscriptions
                    // $cancel_or_pending_query = PaymentSubscription::where('UserId', $id)
                    //     ->whereIn('Status', ['Pending', 'Canceled']);

                    // $subscriptionIds = $cancel_or_pending_query->pluck('PaymentSubscriptionID')->toArray();

                    // if (!empty($subscriptionIds)) {
                    //     PaymentHistory::whereIn('PaymentSubscriptionID', $subscriptionIds)->delete();
                    // }

                    // $cancel_or_pending_query->delete();

                    // Update current subscription
                    $paymentSubscription = PaymentSubscription::find($data_current_package->PaymentSubscriptionID);
                    $paymentSubscription->update([
                        'UserID' => $id,
                        'PackageID' => $plan,
                        'PaymentMethodID' => 1,
                        'PaymentAmount' => $package->Price,
                        'PaymentStartDate' => $data_current_package->PaymentStartDate,
                        'PaymentEndDate' => $data_current_package->PaymentEndDate,
                        'NextRenewalDate' => $data_current_package->NextRenewalDate,
                        'ChecksGiven' => $package->CheckLimitPerMonth,
                        'RemainingChecks' => $package->CheckLimitPerMonth - $data_current_package->ChecksUsed,
                        'ChecksReceived' => $data_current_package->ChecksReceived,
                        'ChecksSent' => $data_current_package->ChecksSent,
                        'ChecksUsed' => $data_current_package->ChecksUsed,
                        'PaymentDate' => $data_current_package->PaymentDate,
                        'PaymentAttempts' => 0,
                        'TransactionID' => $res['id'],
                        'Status' => 'Active',
                    ]);

                    // Create payment history for the upgrade charge
                    PaymentHistory::create([
                        'PaymentSubscriptionID' => $paymentSubscription->PaymentSubscriptionID,
                        'PaymentAmount' => $price_difference,
                        'PaymentDate' => now(),
                        'PaymentStatus' => 'Success',
                        'PaymentAttempts' => 0,
                        'TransactionID' => $res['id'],
                    ]);

                    $old_plan = Package::find($user->CurrentPackageID);
                    $user->CurrentPackageID = $plan;
                    $user->save();

                    $paymentStartDate = Carbon::now();

                    $user_name = $user->FirstName . ' ' . $user->LastName;
                    $data = [
                        'old_plan_name' => $old_plan->Name,
                        'new_plan_name' => $package->Name,
                        'upgrade_date' => $paymentStartDate->format('m/d/Y'),
                    ];
                    Mail::to($user->Email)->send(new SendUpgradeSubMail(7, $user_name, $data));
                }
            } else {

                $res = $this->subscriptionHelper->schedulePlanDowngrade($user->SubID, $package->PriceID);
                if (!empty($res)) {
                    $paymentSubscription = PaymentSubscription::find($data_current_package->PaymentSubscriptionID);
                    $paymentSubscription->update([
                        'NextPackageID' => $plan,
                    ]);
                    $old_plan = Package::find($user->CurrentPackageID);
                    $new_plan = Package::find($plan);
                    $user_name = $user->FirstName . ' ' . $user->LastName;
                    $data = [
                        'old_plan_name' => $old_plan->Name,
                        'new_plan_name' => $new_plan->Name,
                        'end_date' => Carbon::parse($paymentSubscription->NextRenewalDate)->format('m/d/Y'),
                    ];

                    Mail::to($user->Email)->send(new SendDowngradeSubMail(8, $user_name, $data));
                } else {
                    return redirect()->route('billing_and_plan')->with('error', 'Something went wrong');
                }
            }
        }

        return redirect()->route('billing_and_plan')->with('success', 'Your plan has been updated successfully');
    }

    public function cancel_plan()
    {
        $user = Auth::user();
        $res = $this->subscriptionHelper->cancelAtPeriodEnd($user->SubID);
        $data_current_package = PaymentSubscription::where('UserId', $user->UserID)->where('Status', 'Active')
            ->orderBy('PaymentSubscriptionID', 'desc')->first();

        if (!empty($res) && !empty($data_current_package)) {
            $data_current_package->CancelAt = $data_current_package->NextRenewalDate;
            // $data_current_package->Status = 'Canceled';
            $data_current_package->save();
            // PaymentSubscription::where('UserId', $id)->where('Status', 'Pending')->delete();
            $user_name = $user->FirstName . ' ' . $user->LastName;
            $package = Package::find($data_current_package->PackageID);
            $data = [
                'plan_name' => $package->Name,
                'end_date' => Carbon::parse($data_current_package->NextRenewalDate)->format('m/d/Y'),
            ];

            Mail::to($user->Email)->send(new SendCancelSubMail(9, $user_name, $data));
            return redirect()->route('billing_and_plan')->with('success', 'Your plan has been canceled');
        } else {
            return redirect()->route('billing_and_plan')->with('error', 'Something went wrong');
        }

    }

    public function invoice(Request $request)
    {
        if ($request->ajax()) {
            $paymentSubscriptionIds = PaymentSubscription::where('UserID', Auth::id())->pluck('PaymentSubscriptionID')->toArray();
            $invoice = PaymentHistory::whereIn('PaymentSubscriptionID', $paymentSubscriptionIds);

            return datatables()->of($invoice)
                ->addIndexColumn()
                ->addColumn('PaymentDate', function ($row) {
                    return User::user_timezone($row->PaymentDate);
                })
                ->addColumn('PaymentAmount', function ($row) {
                    return '$' . number_format($row->PaymentAmount, 2);
                })
                ->addColumn('PaymentStatus', function ($row) {
                    if ($row->PaymentStatus == 'Success') {
                        return '<span class="badge bg-label-primary">Paid</span>';
                    } else if ($row->PaymentStatus == 'Failed') {
                        return '<span class="badge bg-label-danger">Failed</span>';
                    }
                    // return '<span class="badge ' .
                    //     ($row->PaymentStatus == 'Success' ? 'bg-label-primary' : 'bg-label-warning') .
                    //     '">'. ($row->PaymentStatus == 'Success' ? 'paid' : 'unpaid'). '</span>';
                })
                ->rawColumns(['PaymentStatus'])
                ->make(true);
        }
    }
}
