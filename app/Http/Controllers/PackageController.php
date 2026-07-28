<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Package;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Helpers\SubscriptionHelper;

class PackageController extends Controller
{
    protected $SubscriptionHelper;
    public function __construct(SubscriptionHelper $subscriptionHelper)
    {
        $this->subscriptionHelper = $subscriptionHelper;
    }

    public function index(Request $request)
    {
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('admin.login');
        }

        if ($request->ajax()) {
            $packages = Package::where('IsVisToAdmin', 1)
                        ->orderBy('PackageID', 'desc')->get();

            return datatables()->of($packages)
                ->addIndexColumn() // This adds an index column for row numbers
                ->addColumn('status', function ($package) {
                    return $package->Status == 'Active'
                        ? '<span class="badge bg-label-primary">' . $package->Status . '</span>'
                        : '<span class="badge bg-label-warning">' . $package->Status . '</span>';
                })
                ->addColumn('web_forms', function ($package) {
                    return $package->web_forms == 1
                        ? '<span class="badge bg-label-primary">Enable</span>'
                        : '<span class="badge bg-label-warning">Disable</span>';
                })
                ->addColumn('Price', function ($package) {
                    return '$' . number_format($package->Price, 2);
                })
                ->addColumn('created_at', function ($package) {
                    return Carbon::parse($package->CreatedAt)->format('m/d/Y');
                })
                ->addColumn('updated_at', function ($package) {
                    return Carbon::parse($package->UpdatedAt)->format('m/d/Y');
                })
                ->addColumn('actions', function ($row) {
                    // Dynamically build URLs for the edit and delete actions
                    $editUrl = route('admin.package.edit', ['id' => $row->PackageID]);
                    $deleteUrl = route('admin.package.delete', ['id' => $row->PackageID]);

                    return '
                        <div class="d-flex">
                            <a href="' . $editUrl . '" class="dropdown-item">
                                    <i class="ti ti-pencil me-1"></i> Edit
                                </a>
                                <a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" 
                                data-bs-target="#delete' . $row->PackageID . '">
                                    <i class="ti ti-trash me-1"></i> Delete
                                </a>
                        </div>
                        <!-- Modal for Deleting -->
                        <div class="modal fade" id="delete' . $row->PackageID . '" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Delete Package</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p>Are you sure you want to delete this Package?</p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <form action="' . $deleteUrl . '" method="POST" style="display:inline;">
                                            ' . csrf_field() . '
                                            ' . method_field('DELETE') . '
                                            <button type="submit" class="btn btn-danger">Delete</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>';
                })
                ->rawColumns(['status', 'web_forms', 'created_at', 'updated_at', 'actions']) // Specify which columns have HTML content
                ->make(true); // Send response to DataTables
        }

        return view('admin.package.index');
    }



    public function create()
    {
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('admin.login');
        }
        return view('admin.package.new');
    }

    public function store(Request $request)
    {
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('admin.login');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'description' => 'required',
            'price' => 'required|numeric',
            'duration' => 'required|numeric',
            'check_limit' => 'required|numeric',
            'frequency' => 'required',
            'status' => 'required',
            'web_form' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $package = new Package();
        $package->Name = $request->name;
        $package->Description = $request->description;
        $package->Price = $request->price;
        $package->Duration = $request->duration;
        $package->CheckLimitPerMonth = $request->check_limit;
        $package->RecurringPaymentFrequency = $request->frequency;
        $package->web_forms = $request->web_form;
        $package->Status = $request->status;

        $plan_data = [
            'name' => $request->name,
            'interval' => 'month',
            'price' => $request->price
        ];

        $res = $this->subscriptionHelper->addPlan($plan_data);

        if (!empty($res['id'])) {
            $price_obj = $this->subscriptionHelper->addPrice($res['product'], $request->price);
            $package->PlanID = $res['id'];
            $package->ProductID = $res['product'];
            $package->PriceID = $price_obj['id'];
        }

        $package->save();

        return redirect()->route('admin.package')->with('success', 'Package added successfully');

        return view('admin.package.new');
    }

    public function edit($id)
    {
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('admin.login');
        }
        $package = Package::find($id);
        return view('admin.package.edit', compact('package'));
    }

    public function update(Request $request, $id)
    {
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('admin.login');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'description' => 'required',
            'price' => 'required|numeric',
            'duration' => 'required|numeric',
            'check_limit' => 'required|numeric',
            'frequency' => 'required',
            'status' => 'required',
            'web_form' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {

            DB::beginTransaction();

            $is_stripe_updated = false;

            $old_package = Package::find($id);

            if ($request->price != $old_package->Price) {

                $plan_data = [
                    'name' => $request->name,
                    'interval' => 'month',
                    'price' => $request->price
                ];

                $res = $this->subscriptionHelper->addPlan($plan_data);

                if (!empty($res['id'])) {
                    $price_obj = $this->subscriptionHelper->addPrice($res['product'], $request->price);
                    $new_package = new Package();

                    $new_package->Name = $request->name;
                    $new_package->Description = $request->description;
                    $new_package->PlanID = $res['id'];
                    $new_package->ProductID = $res['product'];
                    $new_package->PriceID = $price_obj['id'];

                    $new_package->Price = $request->price;
                    $new_package->Duration = $request->duration;
                    $new_package->CheckLimitPerMonth = $request->check_limit;
                    $new_package->web_forms = $request->web_form;
                    $new_package->RecurringPaymentFrequency = $request->frequency;
                    $new_package->Status = $request->status;
                    $new_package->save();

                    $is_stripe_updated = true;
                }
            }

            if ($is_stripe_updated) {
                $old_package->Status = 'Inactive';
                $old_package->IsVisToAdmin = '0';
            } else {
                $old_package->Name = $request->name;
                $old_package->Description = $request->description;
                $old_package->Price = $request->price;
                $old_package->Duration = $request->duration;
                $old_package->CheckLimitPerMonth = $request->check_limit;
                $old_package->web_forms = $request->web_form;
                $old_package->RecurringPaymentFrequency = $request->frequency;
                $old_package->Status = $request->status;
            }

            $old_package->save();
            DB::commit();

            return redirect()->route('admin.package')->with('success', 'Package updated successfully');
        } catch (Exception $e) {

            DB::rollBack();
            return redirect()->route('admin.package')->with('error', 'Something went wrong');
        }

    }

    public function delete($id)
    {
        $package = Package::find($id);
        $this->subscriptionHelper->deleteProduct($package->ProductID);
        $this->subscriptionHelper->deletePlan($package->PlanID);
        $package->delete();

        return redirect()->route('admin.package')->with('success', 'Package deleted successfully');
    }
}
