<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\FraudService;
use App\Models\AdminUser;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\Package;
use App\Models\PaymentSubscription;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\Checks;
use App\Models\Company;
use App\Models\Payors;
use App\Models\PaymentHistory;
use App\Models\UserHistory;
use App\Helpers\SubscriptionHelper;
use App\Mail\SendUpgradeSubMail;
use App\Mail\SendDowngradeSubMail;
use App\Mail\SendCancelSubMail;
use Illuminate\Support\Facades\Mail;

class AdminDashboardController extends Controller
{
    protected $SubscriptionHelper;
    public function __construct(SubscriptionHelper $subscriptionHelper)
    {
        $this->subscriptionHelper = $subscriptionHelper;
    }

    public function index()
    {
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('admin.login');
        }
        $total_users = User::count();
        $total_checks = PaymentSubscription::sum('ChecksGiven');
        $total_revanue = PaymentHistory::where('PaymentStatus', 'Success')->sum('PaymentAmount');
        $month_revanue = PaymentHistory::where('PaymentStatus', 'Success')
            ->whereMonth('PaymentDate', Carbon::now()->month)
            ->whereYear('PaymentDate', Carbon::now()->year)
            ->sum('PaymentAmount');

        $month_revanue = number_format($month_revanue, 2);
        $total_revanue = number_format($total_revanue, 2);

        $total_used_checks = PaymentSubscription::sum('ChecksUsed');
        $total_unused_checks = abs($total_checks - $total_used_checks);

        $package_selected_user = User::whereNotNull('CurrentPackageID')->count();
        $package_data = [];
        $packages = Package::where('Status', 'Active')->get();
        $total_package_count = 0;
        foreach ($packages as $key => $package) {
            $package_data[$key]['name'] = $package->Name;
            if (strtolower($package->Name) == 'trial') {
                $package_data[$key]['total_count'] = PaymentSubscription::select('PaymentSubscriptionID')->where('Status', 'Active')->where('PackageID', '-1')->distinct('UserID')->count();
            } else {
                $package_data[$key]['total_count'] = PaymentSubscription::select('PaymentSubscriptionID')->where('Status', 'Active')->where('PackageID', $package->PackageID)->distinct('UserID')->count();
            }
            $total_package_count += $package_data[$key]['total_count'];
        }
        return view('content.dashboard.dashboards-analytics', compact('total_package_count', 'total_users', 'total_checks', 'total_revanue', 'total_used_checks', 'total_unused_checks', 'package_data', 'package_selected_user', 'month_revanue'));
    }

    public function profile()
    {
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('admin.login');
        }

        $admin = AdminUser::where('AdminID', Auth::guard('admin')->user()->AdminID)->first();
        return view('admin.profile.profile', compact('admin'));
    }

    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'email' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $admin = AdminUser::where('AdminID', $request->admin_id)->first();
        $admin->Username = $request->username;
        $admin->Email = $request->email;
        $admin->UpdatedAt = now();
        $admin->save();

        return redirect()->route('admin.profile')->with('profile_success', 'Profile updated successfully');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
            'new_password_confirmation' => 'required|min:6',
        ]);

        if (!Hash::check($request->old_password, Auth::guard('admin')->user()->PasswordHash)) {
            return back()->withErrors(['old_password' => 'The old password is incorrect.']);
        }

        $admin = AdminUser::where('AdminID', $request->admin_id)->first();
        $admin->PasswordHash = Hash::make($request->new_password);
        $admin->save();

        // Redirect back with success message
        return redirect()->route('admin.profile')->with('pass_success', 'Password changed successfully');
    }

    // public function users(Request $request)
    // {
    //     if ($request->ajax()) {

    //         $query = User::query();

    //         if (isset($request->status)) {
    //             $query->where('status', $request->status);
    //         }

    //         $users = $query->get();

    //         foreach ($users as $user) {
    //             $package = Package::find($user->CurrentPackageID);

    //             $user->package = $package ? $package->Name : 'N/A';
    //             if ($user->CurrentPackageID == '-1') {
    //                 $user->package = 'TRIAL';
    //             }
    //             $user->package_price = $package ? '$' . number_format($package->Price, 2) : '$0';

    //         }

    //         return datatables()->of($users)
    //             ->addIndexColumn()

    //             ->editColumn('PhoneNumber', function ($user) {
    //                 return $this->formatPhoneNumber($user->PhoneNumber);
    //             })
    //             ->addColumn('status', function ($user) {
    //                 return $user->Status == 'Active'
    //                     ? '<span class="badge bg-label-primary">' . $user->Status . '</span>'
    //                     : '<span class="badge bg-label-warning">' . $user->Status . '</span>';
    //             })
    //             ->addColumn('created_at', function ($user) {
    //                 return Carbon::parse($user->CreatedAt)->format('m-d-Y'); // Convert to MM/DD/YYYY
    //             })
    //             ->addColumn('actions', function ($user) {
    //                 // Dynamically build URLs for the edit and delete actions
    //                 $editUrl = route('admin.user.edit', ['id' => $user->UserID]);
    //                 $deleteUrl = route('admin.user.delete', ['id' => $user->UserID]);
    //                 $viewUrl = route('admin.user.view', ['id' => $user->UserID]);

    //                 return '
    //                 <div class="d-flex">
    //                     <a href="' . $editUrl . '" class="dropdown-item">
    //                     <i class="ti ti-pencil me-1"></i> Edit
    //                     </a>
    //                     <a href="' . $viewUrl . '" class="dropdown-item">
    //                         <i class="ti ti-eye me-1"></i> View
    //                     </a>
    //                 </div>';
    //             })
    //             ->rawColumns(['status', 'created_at', 'actions'])
    //             ->make(true);
    //     }

    //     return view('admin.user.index');
    // }

    public function users(Request $request)
    {
        if ($request->ajax()) {

            $query = User::query();

            if ($request->status) {
                $query->where('Status', $request->status);
            }

            if ($request->has('order') && $request->order[0]['column'] == 0) {
                $query->orderBy('UserID', 'desc');
            }

            return datatables()->of($query)

                ->addIndexColumn()

                // 🔍 Search hidden email
                ->filter(function ($query) use ($request) {
                    $search = $request->input('search.value');

                    if ($search) {
                        $query->where(function ($q) use ($search) {
                            $q->where('FirstName', 'like', "%{$search}%")
                                ->orWhere('LastName', 'like', "%{$search}%")
                                ->orWhere('PhoneNumber', 'like', "%{$search}%")
                                ->orWhere('Email', 'like', "%{$search}%");
                        });
                    }
                })

                ->editColumn('PhoneNumber', function ($user) {
                    return $this->formatPhoneNumber($user->PhoneNumber);
                })

                ->addColumn('package', function ($user) {

                    if ($user->CurrentPackageID == '-1') {
                        return 'TRIAL';
                    }

                    $package = Package::find($user->CurrentPackageID);

                    return $package ? $package->Name : 'N/A';
                })

                ->addColumn('package_price', function ($user) {

                    if ($user->CurrentPackageID == '-1') {
                        return '$0';
                    }

                    $package = Package::find($user->CurrentPackageID);

                    return $package
                        ? '$' . number_format($package->Price, 2)
                        : '$0';
                })

                ->editColumn('status', function ($user) {
                    return $user->Status == 'Active'
                        ? '<span class="badge bg-label-primary">' . $user->Status . '</span>'
                        : '<span class="badge bg-danger">' . $user->Status . '</span>';
                })
                ->editColumn('reason', function ($user) {
                    return $user->reason ? $user->reason : '-';
                })

                ->editColumn('created_at', function ($user) {
                    return Carbon::parse($user->CreatedAt)->format('m-d-Y');
                })
                ->setRowClass(function ($user) {

                    if (strtolower($user->reason) === 'fraud') {
                        return 'table-danger'; // or use custom class
                    }

                    return '';
                })
                ->addColumn('actions', function ($user) {

                    $editUrl = route('admin.user.edit', ['id' => $user->UserID]);
                    $viewUrl = route('admin.user.view', ['id' => $user->UserID]);

                    return '
                <div class="d-flex">
                    <a href="' . $editUrl . '" class="dropdown-item">
                        <i class="ti ti-pencil me-1"></i> Edit
                    </a>
                    <a href="' . $viewUrl . '" class="dropdown-item">
                        <i class="ti ti-eye me-1"></i> View
                    </a>
                </div>';
                })

                ->rawColumns(['status', 'reason', 'actions'])
                ->make(true);
        }

        return view('admin.user.index');
    }


    public function user_edit(Request $request, $id)
    {
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('admin.login');
        }


        $user = User::where('userID', $id)->first();
        $user_history = UserHistory::where('UserID', $id)->first();
        if (!empty($user_history)) {
            $user_history->last_login = !empty($user_history->last_login) ? Carbon::parse($user_history->last_login)->format('M, d Y h:i A') : '';
        }
        $paymentSubscription = PaymentSubscription::where('UserID', $id)
            ->where('Status', 'Active')->where('PackageID', $user->CurrentPackageID)
            ->orderBy('PaymentSubscriptionID', 'desc')->first();
        $package = Package::find($user->CurrentPackageID);
        $total_days = !empty($package->Duration) ? $package->Duration : '';
        $package_name = !empty($package->Name) ? $package->Name : '';
        $expiry = !empty($paymentSubscription->NextRenewalDate) ? Carbon::createFromFormat('Y-m-d', $paymentSubscription->NextRenewalDate) : '';
        $expiryDate = !empty($expiry) ? $expiry->format('M d, Y') : '';
        $remainingDays = !empty($expiry) ? $expiry->diffInDays(Carbon::now(), false) : '';
        $packages = Package::where('Status', 'Active')->get();
        $check_used = '-';
        $remaining_checks = '-';
        if (!empty($paymentSubscription)) {
            $check_used = ($paymentSubscription->ChecksGiven == 0) ? '-' : $paymentSubscription->ChecksUsed;
            $remaining_checks = ($paymentSubscription->ChecksGiven == 0) ? '-' : $paymentSubscription->RemainingChecks;
        }

        $package_data = [
            'total_days' => $total_days,
            'package_name' => $package_name,
            'expiryDate' => $expiryDate,
            'remainingDays' => !empty($remainingDays) ? abs(round($remainingDays)) : '',
        ];
        $type = 'default';
        if (!empty($request->type)) {
            $type = $request->type;
        }
        $maxPricePackage = Package::orderBy('price', 'desc')->first();
        $stander_Plan_price = $maxPricePackage->Price;
        $currentPackage = $user->CurrentPackageID;

        $lastPaymentSubscription = PaymentSubscription::where('UserID', $id)
            ->where('PackageID', $user->CurrentPackageID)
            ->orderBy('PaymentSubscriptionID', 'desc')->first();

        return view('admin.user.user_detail_page', compact('lastPaymentSubscription', 'user', 'package_data', 'packages', 'check_used', 'remaining_checks', 'package', 'type', 'stander_Plan_price', 'currentPackage', 'user_history', 'paymentSubscription'));
    }

    public function updateUserProfile(Request $request)
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
        $admin->Address = $request->address;
        $admin->Email = $request->email;
        $admin->FirstName = $request->firstname;
        $admin->LastName = $request->lastname;
        $admin->PhoneNumber = preg_replace('/\D/', '', $request->phone_number);
        $admin->CompanyName = $request->company_name;
        $admin->UpdatedAt = now();
        $admin->save();

        return redirect()->route('admin.user.edit', ['id' => $request->user_id])->with('profile_success', 'Profile updated successfully');
    }

    public function changeUserPassword(Request $request)
    {
        $request->validate([
            'new_password' => 'required|min:6|confirmed',
            'new_password_confirmation' => 'required|min:6',
        ]);


        $admin = User::where('UserID', $request->user_id)->first();
        $admin->PasswordHash = Hash::make($request->new_password);
        $admin->save();

        // Redirect back with success message
        return redirect()->route('admin.user.edit', ['id' => $request->user_id, 'type' => 'security'])->with('pass_success', 'Password changed successfully');
    }

    public function user_delete($id)
    {
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('admin.login');
        }

        $user = User::find($id);
        $paymentSubscription = PaymentSubscription::where('UserID', $id);
        $paymentSubscriptionIds = $paymentSubscription->pluck('PaymentSubscriptionID')->toArray();
        PaymentHistory::whereIn('PaymentSubscriptionID', $paymentSubscriptionIds)->delete();
        $paymentSubscription->delete();
        $user->delete();

        return redirect()->route('admin.users')->with('success', 'User deleted successfully');
    }

    // public function change_plan(Request $request)
    // {
    //     $user = User::find($request->user_id);
    //     $plan = $request->plan;

    //     if($plan != $user->CurrentPackageID)  {
    //         $user->CurrentPackageID = $request->plan;
    //         $user->Status = 'Active';
    //         $user->save();


    //         $packages = Package::find($plan);

    //         $paymentStartDate = Carbon::now();

    //         $paymentEndDate = $paymentStartDate->copy()->addHours(24);

    //         $nextRenewalDate = $paymentStartDate->copy()->addDays($packages->Duration);
    //         $paymentSubscription = PaymentSubscription::where('UserID', $request->user_id)->where('PackageID', $request->old_plan)->first();

    //         if(!empty($paymentSubscription)) {
    //             $paymentSubscription->update([
    //                 'UserID' => $request->user_id,
    //                 'PackageID' => $plan,
    //                 'PaymentMethodID' => 1,
    //                 'PaymentAmount' => $packages->Price,
    //                 'PaymentStartDate' => $paymentStartDate,
    //                 'PaymentEndDate' => $paymentEndDate,
    //                 'NextRenewalDate' => $nextRenewalDate,
    //                 'ChecksGiven' => $packages->CheckLimitPerMonth,
    //                 'RemainingChecks' => $packages->CheckLimitPerMonth,
    //                 'PaymentDate' => $paymentStartDate,
    //                 'PaymentAttempts' => 0 ,
    //                 'TransactionID' => Str::random(10),
    //                 'Status' => 'Active', 
    //             ]);

    //         }
    //     }
    //     return redirect()->route('admin.user.edit', ['id' => $request->user_id])->with('success', 'User plan changed successfully');
    // }

    public function user_profile_edit($id)
    {
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('admin.login');
        }

        $user = User::where('userID', $id)->first();
        $packages = Package::all();
        return view('admin.user.edit', compact('user', 'packages'));
    }

    public function upgragde_plan($id)
    {
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('admin.login');
        }

        $user = User::where('userID', $id)->first();
        $paymentSubscription = PaymentSubscription::where('UserID', $id)->where('Status', 'Active')->where('PackageID', $user->CurrentPackageID)
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

        return view('admin.user.plan_change', compact('user', 'package_data', 'packages'));
    }

    public function change_plan($id, $plan)
    {
        $user = User::find($id);
        $package = Package::find($plan);
        $user_current_package = Package::find($user->CurrentPackageID);
        $data_current_package = PaymentSubscription::where('UserId', $id)
            ->where('Status', 'Active')->where('PackageID', $user->CurrentPackageID)
            ->orderBy('PaymentSubscriptionID', 'desc')->first();

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
                    $cancel_or_pending_query = PaymentSubscription::where('UserId', $id)
                        ->whereIn('Status', ['Pending', 'Canceled']);

                    $subscriptionIds = $cancel_or_pending_query->pluck('PaymentSubscriptionID')->toArray();

                    if (!empty($subscriptionIds)) {
                        PaymentHistory::whereIn('PaymentSubscriptionID', $subscriptionIds)->delete();
                    }

                    $cancel_or_pending_query->delete();

                    // Update current subscription
                    $paymentSubscription = PaymentSubscription::find($data_current_package->PaymentSubscriptionID);
                    $paymentSubscription->update([
                        'UserID' => $id,
                        'PackageID' => $plan,
                        'PaymentMethodID' => 1,
                        'PaymentAmount' => $price_difference,
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
                        'Remarks' => 'Upgrade to ' . $package->Name,
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

                    $new_plan = Package::find($plan);
                    $user_name = $user->FirstName . ' ' . $user->LastName;
                    $data = [
                        'old_plan_name' => $package->Name,
                        'new_plan_name' => $new_plan->Name,
                        'end_date' => Carbon::parse($paymentSubscription->NextRenewalDate)->format('m/d/Y'),
                    ];
                    Mail::to($user->Email)->send(new SendDowngradeSubMail(8, $user_name, $data));
                } else {
                    return redirect()->route('admin.user.edit', ['id' => $user->UserID, 'type' => 'billing'])->with('error', 'Something went wrong');
                }
            }
        }

        return redirect()->route('admin.user.edit', ['id' => $user->UserID, 'type' => 'billing'])->with('success', 'Your plan has been updated successfully');
    }


    public function company(Request $request, $id)
    {
        if ($request->ajax()) {
            $companies = Company::where('UserID', $id)->get();

            return datatables()->of($companies)
                ->addIndexColumn()
                ->addColumn('logo', function ($row) {
                    if (!empty($row->Logo)) {
                        return '<img src="' . asset('storage/' . $row->Logo) . '" alt="Company Logo" style="width: 50px;">';
                    } else {
                        return '<img src="' . asset('assets/img/empty.jpg') . '" alt="Company Logo" style="width: 50px;">';
                    }
                })
                ->addColumn('CreatedAt', function ($row) {
                    return Carbon::parse($row->CreatedAt)->format('m/d/Y');
                })
                ->addColumn('status', function ($row) {
                    return '<span class="badge ' .
                        ($row->Status == 'Active' ? 'bg-label-primary' : 'bg-label-warning') .
                        '">' . $row->Status . '</span>';
                })
                ->rawColumns(['logo', 'status'])
                ->make(true);
        }
    }

    public function invoice(Request $request, $id)
    {
        if ($request->ajax()) {
            $paymentSubscriptionIds = PaymentSubscription::where('UserID', $id)->pluck('PaymentSubscriptionID')->toArray();
            $invoice = PaymentHistory::whereIn('PaymentSubscriptionID', $paymentSubscriptionIds)->where('PaymentStatus', 'Success');

            return datatables()->of($invoice)
                ->addIndexColumn()
                ->addColumn('PaymentDate', function ($row) {
                    return Carbon::parse($row->PaymentDate)->format('m/d/Y');
                })
                ->addColumn('PaymentStatus', function ($row) {
                    return '<span class="badge ' .
                        ($row->PaymentStatus == 'Success' ? 'bg-label-primary' : 'bg-label-warning') .
                        '">' . ($row->PaymentStatus == 'Success' ? 'paid' : 'unpaid') . '</span>';
                })
                ->rawColumns(['PaymentStatus'])
                ->make(true);
        }
    }

    public function client(Request $request, $id)
    {

        if ($request->ajax()) {
            $payors = Payors::where('UserID', $id)
                ->whereIn('Type', ['Client', 'Both'])
                ->get();

            return datatables()->of($payors)
                ->addIndexColumn()
                ->addColumn('CreatedAt', function ($row) {
                    return Carbon::parse($row->CreatedAt)->format('m/d/Y');
                })
                ->addColumn('Status', function ($row) {
                    return '<span class="badge ' .
                        ($row->Status == 'Active' ? 'bg-label-primary' : 'bg-label-warning') .
                        '">' . $row->Status . '</span>';
                })
                ->rawColumns(['Status'])
                ->make(true);
        }
    }
    public function vendor(Request $request, $id)
    {

        if ($request->ajax()) {
            $payors = Payors::where('UserID', $id)
                ->whereIn('Type', ['Vendor', 'Both'])
                ->get();

            return datatables()->of($payors)
                ->addIndexColumn()
                ->addColumn('CreatedAt', function ($row) {
                    return Carbon::parse($row->CreatedAt)->format('m/d/Y');
                })
                ->addColumn('Status', function ($row) {
                    return '<span class="badge ' .
                        ($row->Status == 'Active' ? 'bg-label-primary' : 'bg-label-warning') .
                        '">' . $row->Status . '</span>';
                })
                ->rawColumns(['Status'])
                ->make(true);
        }
    }

    public function formatPhoneNumber($number)
    {
        // Remove all non-digit characters
        $number = preg_replace('/\D/', '', $number);

        // Get the last 10 digits
        $number = substr($number, -10);

        // Format as 3-3-4
        return preg_replace('/(\d{3})(\d{3})(\d{4})/', '$1-$2-$3', $number);
    }

    // public function changeStatus(Request $request)
    // {
    //     $user = User::findOrFail($request->id);
    //     $user->Status = $request->status == 'active' ? 'Active' : 'Inactive';
    //     $user->save();
    //     return response()->json(['message' => 'Status updated successfully.']);
    // }
    public function changeStatus(Request $request, FraudService $fraudService)
    {
        $user = User::findOrFail($request->id);

        // If status is being updated
        if ($request->has('status')) {

            $status = $request->status === 'active' ? 'Active' : 'Inactive';
            $user->Status = $status;

            // If activating user → clear reason
            if ($status === 'Active') {
                $user->reason = null;
                $fraudService->removeFraudBlock($user);
            } else {
                DB::table('sessions')->where('user_id', $user->UserID)->delete();
            }
        }

        // If reason is being updated
        if ($request->has('reason')) {

            // Only allow reason if user is inactive
            if ($user->Status === 'Inactive') {

                $user->reason = $request->reason;

                if ($request->reason === 'Fraud') {
                    $fraudService->handleFraudUser($user);
                } else {
                    $fraudService->removeFraudBlock($user);
                }
            }
        }

        $user->save();

        return response()->json([
            'message' => 'Status updated successfully.'
        ]);
    }


    public function user_view($id)
    {
        $user = User::findOrFail($id);

        $currentSubscription = PaymentSubscription::where('UserID', $user->UserID)->orderBy('PaymentSubscriptionID', 'desc')->first();

        $firstSubscription = PaymentSubscription::where('UserID', $user->UserID)->where('PackageID', '!=', -1)->first();

        $firstBillingDate = Carbon::parse($firstSubscription?->PaymentStartDate)?->format('m/d/Y');

        $IPAddress = UserHistory::where('UserID', $user->UserID)->orderBy('id', 'desc')->first()?->ip;

        $subscriptions = PaymentSubscription::with([
            'package' => function ($query) {
                return $query->select('PackageID', 'Name');
            }
        ])->select('PaymentSubscriptionID', 'PackageID', 'PaymentStartDate', 'ip_address', 'created_at', 'is_sys_generated')->where('UserID', $user->UserID)->get();

        $stripeSecret = config('services.stripe.secret');

        $paymentMethods = Http::withToken(config('services.stripe.secret'))
            ->get('https://api.stripe.com/v1/payment_methods', [
                'customer' => $user->CusID,
                'type' => 'card',
                'limit' => 3,
            ]);

        $cards = $paymentMethods->json('data');

        $cardList = [];

        if ($cards != null) {
            foreach ($cards as $card) {
                $cardList[] = [
                    'payment_method_id' => $card['id'],
                    'card_holder' => $card['billing_details']['name'] ?? null,
                    'brand' => $card['card']['brand'],
                    'last4' => $card['card']['last4'],
                    'exp_month' => $card['card']['exp_month'],
                    'exp_year' => $card['card']['exp_year'],
                    'address_line1' => $card['billing_details']['address']['line1'] ?? null,
                    'address_line2' => $card['billing_details']['address']['line2'] ?? null,
                    'city' => $card['billing_details']['address']['city'] ?? null,
                    'state' => $card['billing_details']['address']['state'] ?? null,
                    'postal_code' => $card['billing_details']['address']['postal_code'] ?? null,
                    'country' => $card['billing_details']['address']['country'] ?? null,
                ];
            }
        }

        $subscriptionIds = $subscriptions->pluck('PaymentSubscriptionID')->toArray();

        $PaymentHistories = PaymentHistory::with([
            'subscription' => function ($q) {
                return $q->with([
                    'package' => function ($q1) {
                        return $q1->select('PackageID', 'Name', );
                    }
                ])->select('PaymentSubscriptionID', 'PackageID', 'PaymentStartDate', 'NextRenewalDate');
            }
        ])->select('PaymentHistoryID', 'PaymentSubscriptionID', 'PaymentDate', 'PaymentStatus', 'PaymentAmount', 'Remarks', 'created_at')->whereIn('PaymentSubscriptionID', $subscriptionIds)->get();

        $payment_histories = [];
        foreach ($PaymentHistories as $PaymentHistory) {
            $payment_histories[] = [
                'billing_id' => $PaymentHistory->PaymentHistoryID,
                'plan' => $PaymentHistory?->subscription?->package?->Name,
                'details' => $PaymentHistory->Remarks,
                'price' => $PaymentHistory->PaymentAmount,
                'charged' => $PaymentHistory->PaymentStatus == 'Success' ? 'True' : 'False',
                'error_message' => '',
                'billing_start_dt' => $PaymentHistory?->subscription?->PaymentStartDate ? Carbon::parse($PaymentHistory?->subscription?->PaymentStartDate)->format('m/d/Y') : '-',
                'billing_end_dt' => $PaymentHistory?->subscription?->NextRenewalDate ? Carbon::parse($PaymentHistory?->subscription?->NextRenewalDate)->format('m/d/Y') : '-',
                'created_at' => $PaymentHistory->created_at ? Carbon::parse($PaymentHistory->created_at)->format('m/d/Y h:i A') : '-',
            ];
        }

        $checks = Checks::where('UserID', $id)->get();

        return view('admin.user.activity', compact(
            'user',
            'currentSubscription',
            'firstBillingDate',
            'IPAddress',
            'subscriptions',
            'cardList',
            'payment_histories',
            'checks'
        ));
    }
}
