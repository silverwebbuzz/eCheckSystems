<?php

namespace App\Http\Controllers;

use App\Models\Grid;
use App\Models\GridHistory;
use App\Models\GridItem;
use App\Models\HowItWork;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Company;
use App\Models\Payors;
use App\Models\Checks;
use Illuminate\Support\Facades\Validator;
use PDF;
use NumberToWords\NumberToWords;
use Illuminate\Support\Facades\File;
use App\Models\Package;
use App\Models\PaymentSubscription;
use Carbon\Carbon;
use App\Models\WebForm;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Artisan;
use setasign\Fpdi\TcpdfFpdi;
use App\Models\UserSignature;
use App\Mail\SendCheckMail;
use App\Mail\SendWebFormMail;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Models\CheckLineItem;
use App\Mail\SendWebFormMailForCilent;
use App\Helpers\Helpers;
use App\Models\QBOCompany;
use App\Services\QuickBooksService;
use App\Jobs\PushCheckToQuickBooksJob;
use App\Jobs\DeleteCheckFromQuickBooksJob;
use App\Jobs\MarkQuickBooksCheckPrintedJob;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Storage;


class CheckController extends Controller
{
    public function process_payment(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('user.login');
        }

        if ($request->ajax()) {
            $checks = Checks::where('UserID', Auth::id())->where('CheckType', 'Process Payment')
                ->where('is_seen', 0)->orderBy('CheckID', 'desc')->get();

            return datatables()->of($checks)
                ->addIndexColumn()
                ->addColumn('CompanyID', function ($row) {
                    $payee = Payors::withTrashed()->find($row->PayeeID);
                    return $payee->Name;
                })
                ->addColumn('EntityID', function ($row) {
                    $payor = Payors::withTrashed()->find($row->PayorID);
                    return $payor->Name;
                })
                ->addColumn('Amount', function ($row) {
                    return '$' . number_format($row->Amount, 2);
                })
                ->addColumn('ServiceFee', function ($row) {
                    return '$' . number_format($row->ServiceFees, 2);
                })
                ->addColumn('Total', function ($row) {
                    return '$' . number_format($row->Total, 2);
                })
                ->addColumn('IssueDate', function ($row) {
                    return User::user_timezone($row->IssueDate, 'm/d/Y');
                })
                ->addColumn('actions', function ($row) {
                    // $editUrl = route('user.payors.edit', ['type' => 'Payee', 'id' => $row->EntityID]);
                    // $deleteUrl = route('user.payors.delete', ['type' => 'Payee', 'id' => $row->EntityID]);
                    $editUrl = route('check.process_payment_check_edit', ['id' => $row->CheckID]);
                    $check_generate = route('check_generate', ['id' => $row->CheckID]);
                    $delete_check = route('check.delete', ['id' => $row->CheckID]);

                    if ($row->Status == 'draft') {
                        return '<div class="d-flex gap-3">
                                <a href="' . $editUrl . '" class="dropdown-item">
                                        <i class="ti ti-pencil me-1"></i> Edit
                                </a>
                                <a href="javascript:void(0);" class="dropdown-item" data-bs-toggle="modal" 
                                    data-bs-target="#check-generate' . $row->CheckID . '">
                                        <i class="ti ti-bookmark-plus me-1"></i> generate
                                </a>
                                <a href="javascript:void(0);" class="dropdown-item" data-bs-toggle="modal" 
                                    data-bs-target="#delete-check' . $row->CheckID . '">
                                        <i class="ti ti-trash me-1"></i> Delete
                                </a>
                            </div>
                            <div class="modal fade" id="delete-check' . $row->CheckID . '" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Delete Check</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Are you sure you want to delete check ?</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <a href="' . $delete_check . '" class="btn btn-primary">Delete</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal fade" id="check-generate' . $row->CheckID . '" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Check Generate</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Are you sure you want to generate check ?</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <a href="' . $check_generate . '" class="btn btn-primary">Generate</a>
                                        </div>
                                    </div>
                                </div>
                            </div>';
                    } else {
                        if (!empty($row->CheckPDF)) {
                            // return '<a href="'.asset('checks/' . $row->CheckPDF).'" target="_blank" class="btn">
                            //             <i class="menu-icon tf-icons ti ti-files"></i>
                            //     </a>';
                            return '<a data-url="' . route('view.pdf', $row->CheckID) . '" href="#" class="btn view_pdf">
                                        <i class="menu-icon tf-icons ti ti-files"></i>
                                </a>
                                <a href="' . route('download.pdf', $row->CheckID) . '" class="btn">
                                        <i class="menu-icon tf-icons ti ti-download"></i>
                                </a>
                                ';

                        } else {
                            return '-';
                        }
                    }
                })
                ->rawColumns(['logo', 'Status', 'actions'])
                ->make(true);
        }

        $how_it_works = HowItWork::select('section', 'link')->where('status', 'Active')->pluck('link', 'section');

        return view('user.check.process_payment_check', compact('how_it_works'));
    }

    public function delete($id)
    {
        try {

            DB::beginTransaction();

            $check = Checks::where('UserID', Auth::user()->UserID)->where('CheckID', $id)->first();
            
            if($check && in_array($check->Status, ['draft', 'imported_from_qbo'], true)){ 
                
                // Sync delete to QuickBooks when linked (queued)
                if ($check->qbo_id) {
                    DeleteCheckFromQuickBooksJob::dispatch(
                        (string) $check->qbo_id,
                        (int) Auth::id(),
                        $check->qbo_company_id ? (int) $check->qbo_company_id : null
                    )->afterCommit();
                }

                $subscription = PaymentSubscription::where('UserID', Auth::user()->UserID)->where('Status', 'Active')->first();

                if ($subscription != null) {

                    if ($check->CheckType == 'Process Payment') {

                        $subscription->ChecksReceived -= 1;

                    } else if ($check->CheckType == 'Make Payment') {

                        $subscription->ChecksSent -= 1;

                        GridItem::where('CheckID', $check->CheckID)->delete();
                    }

                    if (in_array($check->CheckType, ['Process Payment', 'Make Payment'], true)) {
                        $subscription->ChecksUsed -= 1;
                        $subscription->RemainingChecks += 1;
                        $subscription->save();
                    }

                    \App\Models\CheckLineItem::where('CheckID', $check->CheckID)->delete();
                    $check->delete();
                } else if ($check->CheckType === 'QuickBooks' || $check->Status === 'imported_from_qbo') {
                    \App\Models\CheckLineItem::where('CheckID', $check->CheckID)->delete();
                    $check->delete();
                } else {
                    return redirect()->back()->with('info', 'Active subscription required to delete this check.');
                }
            }else{
                return redirect()->back()->with('info', 'Check not found.');
            }

            DB::commit();
            return redirect()->back()->with('success', 'Check deleted successfully.');

        } catch (Exception $e) {

            DB::rollBack();
            return redirect()->back()->with('info', 'Something went wrong. Please try again.');
        }

    }

    public function process_payment_check()
    {
        if (!Auth::check()) {
            return redirect()->route('user.login');
        }

        $isSubscribed = Helpers::isSubscribed(Auth::user());

        if (!$isSubscribed) {
            return redirect()->route('check.process_payment')->with('info', 'Your check limit has been exceeded. Please upgrade your plan.');
        }

        // $lastCheck = Checks::where('UserID', Auth::id())->where('CheckType', 'Process Payment')->latest('CheckID')->first();

        $payees = Payors::where('UserID', Auth::id())->where('Type', 'Payee')->where('Category', 'RP')->orderBy('Name', 'asc')->get();
        $payors = Payors::where('UserID', Auth::id())->where('Type', 'Payor')->where('Category', 'RP')->orderBy('Name', 'asc')->get();
        return view('user.check.process_payment_generate_check', compact('payees', 'payors'));
    }
    public function process_payment_check_generate(Request $request)
    {

        if (!Auth::check()) {
            return redirect()->route('user.login');
        }

        $validator = Validator::make($request->all(), [
            'check_date' => 'required',
            'check_number' => 'required|numeric',
            'amount' => 'required|numeric|min:0.01',
            'payee' => 'required|exists:Entities,EntityID',
            'payor' => 'required|exists:Entities,EntityID',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        if (!empty($request->signed)) {
            $folderPath = public_path('sign/');

            $image_parts = explode(";base64,", $request->signed);
            $image_type_aux = explode("image/", $image_parts[0]);

            $fileName = '';
            if (!empty($image_type_aux[1])) {
                $image_type = $image_type_aux[1];
                $image_base64 = base64_decode($image_parts[1]);
                $fileName = uniqid() . '.' . $image_type;
                $file = $folderPath . $fileName;

                file_put_contents($file, $image_base64);
            }
        }

        $check_date = Carbon::parse(str_replace('-', '/', $request->check_date));

        if (!empty($request->id)) {
            // Update existing record
            $checks = Checks::find($request->id);
            if (empty($fileName)) {
                $fileName = $checks->DigitalSignature;
            }
            if ($checks) {
                $checks->update([
                    'PayeeID' => $request->payee,
                    'CheckType' => 'Process Payment',
                    'Amount' => $request->amount,
                    'ServiceFees' => 0,
                    'Total' => $request->amount,
                    'PayorID' => $request->payor,
                    'CheckNumber' => $request->check_number,
                    'IssueDate' => now(),
                    'ExpiryDate' => $check_date,
                    'Status' => 'draft',
                    'Memo' => $request->memo,
                    'CheckPDF' => null,
                    'DigitalSignatureRequired' => (!empty($request->is_sign) && $request->is_sign == 'on') ? 1 : 0,
                    'DigitalSignature' => (!empty($request->is_sign) && $request->is_sign == 'on') ? $fileName : '',
                    'ip_address' => request()->ip()
                ]);
            }
            $message = 'Check Updated successfully';
        } else {
            // Create new record
            $checks = Checks::create([
                'UserID' => Auth::id(),
                'PayeeID' => $request->payee,
                'CheckType' => 'Process Payment',
                'Amount' => $request->amount,
                'ServiceFees' => 0,
                'Total' => $request->amount,
                'PayorID' => $request->payor,
                'CheckNumber' => $request->check_number,
                'IssueDate' => now(),
                'ExpiryDate' => $check_date,
                'Status' => 'draft',
                'Memo' => $request->memo,
                'CheckPDF' => null,
                'DigitalSignatureRequired' => (!empty($request->is_sign) && $request->is_sign == 'on') ? 1 : 0,
                'DigitalSignature' => (!empty($request->is_sign) && $request->is_sign == 'on') ? $fileName : '',
                'ip_address' => request()->ip(),
                'created_at' => Carbon::now(),
            ]);

            // if (Auth::user()->CurrentPackageID != -1) {
            $paymentSubscription = PaymentSubscription::where('UserID', Auth::id())->where('PackageID', Auth::user()->CurrentPackageID)
                ->where('Status', 'Active')->orderBy('PaymentSubscriptionID', 'desc')->first();
            $paymentSubscription->ChecksReceived = $paymentSubscription->ChecksReceived + 1;
            $paymentSubscription->ChecksUsed = $paymentSubscription->ChecksUsed + 1;
            $paymentSubscription->RemainingChecks = $paymentSubscription->ChecksGiven - $paymentSubscription->ChecksUsed;
            $paymentSubscription->save();
            // }
            $message = 'Check Created successfully';
        }

        return redirect()->route('check.process_payment')->with('success', $message);
    }

    public function process_payment_check_edit(Request $request, $id)
    {
        $check = Checks::find($id);
        $check->ExpiryDate = Carbon::parse($check->ExpiryDate)->format('m-d-Y');
        $payees = Payors::where('UserID', Auth::id())->where('Type', 'Payee')->where('Category', 'RP')->orderBy('Name', 'asc')->get();
        $payors = Payors::where('UserID', Auth::id())->where('Type', 'Payor')->where('Category', 'RP')->orderBy('Name', 'asc')->get();
        $old_payee = Payors::find($check->PayeeID);
        $old_payor = Payors::find($check->PayorID);
        return view('user.check.process_payment_generate_check', compact('payees', 'payors', 'check', 'old_payee', 'old_payor'));
    }

    public function send_payment(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('user.login');
        }

        if ($request->ajax()) {
            $checks = Checks::where('UserID', Auth::id())->where('CheckType', 'Make Payment')
                ->where('is_seen', 0)->orderBy('CheckID', 'desc')->get();

            return datatables()->of($checks)
                ->addIndexColumn()
                ->addColumn('CompanyID', function ($row) {
                    $payee = Payors::withTrashed()->find($row->PayorID);
                    return $payee->Name;
                })
                ->addColumn('EntityID', function ($row) {
                    $payor = Payors::withTrashed()->find($row->PayeeID);
                    return $payor->Name;
                })
                ->addColumn('IssueDate', function ($row) {
                    return User::user_timezone($row->IssueDate, 'm/d/Y');
                })
                ->addColumn('Amount', function ($row) {
                    return '$' . number_format($row->Amount, 2);
                })
                ->addColumn('actions', function ($row) {

                    $editUrl = route('check.process_send_check_edit', ['id' => $row->CheckID]);
                    $check_generate = route('send_check_generate', ['id' => $row->CheckID]);
                    $send_email_url = route('send_check_email', ['id' => $row->CheckID]);
                    $send_email_lable = !empty($row->is_email_send) ? 'Resend' : 'Send';
                    $delete_check = route('check.delete', ['id' => $row->CheckID]);

                    if ($row->Status == 'draft') {
                        return '<div class="d-flex">
                                <a href="' . $editUrl . '" class="dropdown-item">
                                        <i class="ti ti-pencil me-1"></i> Edit
                                </a>
                                <a href="javascript:void(0);" class="dropdown-item" data-bs-toggle="modal" 
                                    data-bs-target="#check-generate' . $row->CheckID . '">
                                        <i class="ti ti-bookmark-plus me-1"></i> generate
                                </a>
                                <a href="javascript:void(0);" class="dropdown-item" data-bs-toggle="modal" 
                                    data-bs-target="#delete-check' . $row->CheckID . '">
                                        <i class="ti ti-trash me-1"></i> Delete
                                </a>
                            </div>
                            <div class="modal fade" id="delete-check' . $row->CheckID . '" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Delete Check</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Are you sure you want to delete check ?</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <a href="' . $delete_check . '" class="btn btn-primary">Delete</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal fade" id="check-generate' . $row->CheckID . '" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Check Generate</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Are you sure you want to generate check ?</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <a href="' . $check_generate . '" class="btn btn-primary">Generate</a>
                                        </div>
                                    </div>
                                </div>
                            </div>';
                    } else {
                        if (!empty($row->CheckPDF)) {
                            return '<div class="d-flex">
                                <a data-url="' . route('view.pdf', $row->CheckID) . '" href="#" class="btn view_pdf">
                                        <i class="menu-icon tf-icons ti ti-files"></i> Preview
                                </a>
                                 <a href="' . route('download.pdf', $row->CheckID) . '" class="btn">
                                        <i class="menu-icon tf-icons ti ti-download"></i> Download
                                </a>
                                <a href="' . $send_email_url . '" class="btn">
                                        <i class="menu-icon tf-icons ti ti-mail"></i> ' . $send_email_lable . '
                                </a>
                                </div>';

                        } else {
                            return '-';
                        }
                    }
                })
                ->rawColumns(['logo', 'Status', 'actions'])
                ->make(true);
        }

        $how_it_works = HowItWork::select('section', 'link')->where('status', 'Active')->pluck('link', 'section');
        return view('user.check.send_payment_check', compact('how_it_works'));
    }
    public function send_payment_check()
    {
        if (!Auth::check()) {
            return redirect()->route('user.login');
        }

        $isSubscribed = Helpers::isSubscribed(Auth::user());

        if (!$isSubscribed) {
            return redirect()->route('check.send_payment')->with('info', 'Your check limit has been exceeded. Please upgrade your plan.');
        }

        $payees = Payors::where('UserID', Auth::id())->where('Type', 'Payee')->where('Category', 'SP')->orderBy('Name', 'asc')->get();
        $payors = Payors::where('UserID', Auth::id())->where('Type', 'Payor')->where('Category', 'SP')->orderBy('Name', 'asc')->get();

        $lastCheck = Checks::where('UserID', Auth::id())->where('CheckType', 'Make Payment')->latest('CheckID')->first();
        $userSignatures = UserSignature::where('UserID', Auth::id())->get();

        $grid_histories = GridHistory::where('UserID', Auth::user()->UserID)
            ->where('Status', 1)
            ->get();

        $grid_history_ids = $grid_histories->pluck('id')->toArray();

        $qboAccounts = [];
        $qboCompany = app(QuickBooksService::class)->activeCompanyForUser(Auth::id());
        if ($qboCompany) {
            try {
                $qboAccounts = app(QuickBooksService::class)->fetchAccounts($qboCompany)['expense'] ?? [];
            } catch (\Throwable $e) {
                $qboAccounts = [];
            }
        }
        $lineItems = collect();

        return view('user.check.send_payment_generate_check', compact('grid_history_ids', 'grid_histories', 'lastCheck', 'payees', 'payors', 'userSignatures', 'qboAccounts', 'qboCompany', 'lineItems'));
    }
    public function send_payment_check_generate(Request $request)
    {

        DB::beginTransaction();
        if (!Auth::check()) {
            return redirect()->route('user.login');
        }

        // dd($request);
        $validator = Validator::make($request->all(), [
            'check_date' => 'required',
            'check_number' => 'required|numeric',
            'amount' => 'required|numeric|min:0.01',
            'payor' => 'required|exists:Entities,EntityID',
            'payee' => 'required|exists:Entities,EntityID',
            'signature_id' => 'required',
            'qbo_lines.*.qbo_account_id' => 'nullable|string',
            'qbo_lines.*.account_name' => 'nullable|string|max:255',
            'qbo_lines.*.description' => 'nullable|string|max:500',
            'qbo_lines.*.amount' => 'nullable|numeric|min:0',
        ], [
            'signature_id' => 'The signature field is required.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $payee = Payors::where('EntityID', $request->payee)
            ->where('UserID', Auth::id())
            ->where('Type', 'Payee')
            ->first();

        if (!$payee || empty(trim((string) $payee->Email))) {
            return redirect()->back()
                ->withErrors(['payee' => 'Selected payee must have an email address for send payment checks.'])
                ->withInput();
        }

        $qboCompany = app(QuickBooksService::class)->activeCompanyForUser(Auth::id());
        try {
            [$preparedQboLines, $qboLinesTotal] = app(QuickBooksService::class)
                ->prepareLocalCheckLineItems($request->input('qbo_lines', []), $qboCompany);
        } catch (\Throwable $e) {
            return redirect()->back()
                ->withErrors(['qbo_lines' => $e->getMessage()])
                ->withInput();
        }
        $requestedAmount = round((float) $request->amount, 2);

        if (!empty($preparedQboLines)) {
            if (round($qboLinesTotal, 2) !== $requestedAmount) {
                return redirect()->back()
                    ->withErrors(['amount' => 'Category details total must exactly match the check amount.'])
                    ->withInput();
            }
        }


        // if(!empty($request->signed)) {
        //     $folderPath = public_path('sign/');

        //     $image_parts = explode(";base64,", $request->signed);
        //     $image_type_aux = explode("image/", $image_parts[0]);

        //     $fileName = '';
        //     if(!empty($image_type_aux[1])) {
        //         $image_type = $image_type_aux[1];
        //         $image_base64 = base64_decode($image_parts[1]);
        //         $fileName = uniqid() . '.'.$image_type;
        //         $file = $folderPath . $fileName;

        //         file_put_contents($file, $image_base64);
        //     }
        // }

        $check_date = Carbon::parse(str_replace('-', '/', $request->check_date));

        if (!empty($request->id)) {
            $checks = Checks::find($request->id);
            if (empty($fileName)) {
                $fileName = $checks->DigitalSignature;
            }
            if ($checks) {
                $checks->update([
                    'CompanyID' => $request->payor,
                    'CheckType' => 'Make Payment',
                    'Amount' => $request->amount,
                    'ServiceFees' => 0,
                    'Total' => $request->amount,
                    'EntityID' => $request->payee,
                    'CheckNumber' => $request->check_number,
                    'IssueDate' => now(),
                    'ExpiryDate' => $check_date,
                    'Status' => 'draft',
                    'Memo' => $request->memo,
                    'CheckPDF' => null,
                    'SignID' => $request->signature_id,
                    'ip_address' => request()->ip(),
                    'qbo_company_id' => $qboCompany?->id,
                ]);
            }

            GridItem::where('CheckID', $checks->CheckID)->delete();

            if (isset($request->itemization) && $request->itemization == 1) {

                if (isset($request->grid_items) && !empty($request->grid_items)) {
                    // Get all required grid history fields
                    $requiredGridHistories = GridHistory::where('UserID', Auth::id())
                        ->where('Status', 1)
                        ->where('Required', 1)
                        ->pluck('Title', 'id')
                        ->toArray();

                    // Reorganize data by row instead of by column
                    $rowsData = [];
                    foreach ($request->grid_items as $gridHistoryID => $item) {
                        foreach ($item as $rowIndex => $value) {
                            if (!isset($rowsData[$rowIndex])) {
                                $rowsData[$rowIndex] = [];
                            }
                            $rowsData[$rowIndex][$gridHistoryID] = $value;
                        }
                    }

                    // Process each row
                    foreach ($rowsData as $rowIndex => $rowValues) {
                        // Check if all values in the row are null or blank
                        $allEmpty = true;
                        foreach ($rowValues as $value) {
                            if ($value != null && $value != '') {
                                $allEmpty = false;
                                break;
                            }
                        }

                        // If all values are empty, skip this row
                        if ($allEmpty) {
                            continue;
                        }

                        // Validate required fields for this row
                        foreach ($rowValues as $gridHistoryID => $value) {
                            if (isset($requiredGridHistories[$gridHistoryID])) {
                                if ($value == null || $value == '') {
                                    $grid_history = GridHistory::find($gridHistoryID);
                                    DB::rollBack();
                                    return redirect()->back()->with('grid_error', 'Value for ' . $grid_history->Title . ' is required.')->withInput();
                                }
                            }
                        }

                        // If any value exists, save the entire row (even if some values are empty)
                        $rowNumber = $rowIndex + 1;
                        foreach ($rowValues as $gridHistoryID => $value) {
                            GridItem::create([
                                'CheckID' => $checks->CheckID,
                                'Row' => $rowNumber,
                                'GridHistoryID' => $gridHistoryID,
                                'Value' => $value ?? ''
                            ]);
                        }
                    }
                }
            }

            // dd($request->all());
            $message = 'Check Updated successfully';
        } else {

            $checks = Checks::create([
                'UserID' => Auth::id(),
                'PayorID' => $request->payor,
                'CheckType' => 'Make Payment',
                'Amount' => $request->amount,
                'ServiceFees' => 0,
                'Total' => $request->amount,
                'PayeeID' => $request->payee,
                'CheckNumber' => $request->check_number,
                'IssueDate' => now(),
                'ExpiryDate' => $check_date,
                'Status' => 'draft',
                'Memo' => $request->memo,
                'CheckPDF' => null,
                'SignID' => $request->signature_id,
                'ip_address' => request()->ip(),
                'qbo_company_id' => $qboCompany?->id,
                'created_at' => Carbon::now(),
            ]);

            // if (Auth::user()->CurrentPackageID != -1) {
            $paymentSubscription = PaymentSubscription::where('UserID', Auth::id())->where('PackageID', Auth::user()->CurrentPackageID)
                ->where('Status', 'Active')->orderBy('PaymentSubscriptionID', 'desc')->first();
            $paymentSubscription->ChecksSent = $paymentSubscription->ChecksSent + 1;
            $paymentSubscription->ChecksUsed = $paymentSubscription->ChecksUsed + 1;
            $paymentSubscription->RemainingChecks = $paymentSubscription->ChecksGiven - $paymentSubscription->ChecksUsed;
            $paymentSubscription->save();
            // }

            if (isset($request->itemization) && $request->itemization == 1) {

                if (isset($request->grid_items) && !empty($request->grid_items)) {
                    // Get all required grid history fields
                    $requiredGridHistories = GridHistory::where('UserID', Auth::id())
                        ->where('Status', 1)
                        ->where('Required', 1)
                        ->pluck('Title', 'id')
                        ->toArray();

                    // Reorganize data by row instead of by column
                    $rowsData = [];
                    foreach ($request->grid_items as $gridHistoryID => $item) {
                        foreach ($item as $rowIndex => $value) {
                            if (!isset($rowsData[$rowIndex])) {
                                $rowsData[$rowIndex] = [];
                            }
                            $rowsData[$rowIndex][$gridHistoryID] = $value;
                        }
                    }

                    // Process each row
                    foreach ($rowsData as $rowIndex => $rowValues) {
                        // Check if all values in the row are null or blank
                        $allEmpty = true;
                        foreach ($rowValues as $value) {
                            if ($value != null && $value != '') {
                                $allEmpty = false;
                                break;
                            }
                        }

                        // If all values are empty, skip this row
                        if ($allEmpty) {
                            continue;
                        }

                        // Validate required fields for this row
                        foreach ($rowValues as $gridHistoryID => $value) {
                            if (isset($requiredGridHistories[$gridHistoryID])) {
                                if ($value == null || $value == '') {
                                    $grid_history = GridHistory::find($gridHistoryID);
                                    DB::rollBack();
                                    return redirect()->back()->with('grid_error', 'Value for ' . $grid_history->Title . ' is required.')->withInput();
                                }
                            }
                        }

                        // If any value exists, save the entire row (even if some values are empty)
                        $rowNumber = $rowIndex + 1;
                        foreach ($rowValues as $gridHistoryID => $value) {
                            GridItem::create([
                                'CheckID' => $checks->CheckID,
                                'Row' => $rowNumber,
                                'GridHistoryID' => $gridHistoryID,
                                'Value' => $value ?? ''
                            ]);
                        }
                    }
                }
            }

            $message = 'Check Created successfully';
        }

        $this->saveCheckLineItems($checks, $preparedQboLines);

        DB::commit();
        return redirect()->route('check.send_payment')->with('success', $message);
    }

    public function process_send_check_edit(Request $request, $id)
    {
        $check = Checks::find($id);
        $check->ExpiryDate = Carbon::parse($check->ExpiryDate)->format('m-d-Y');
        $payees = Payors::where('UserID', Auth::id())->where('Type', 'Payee')->where('Category', 'SP')->orderBy('Name', 'asc')->get();
        $payors = Payors::where('UserID', Auth::id())->where('Type', 'Payor')->where('Category', 'SP')->orderBy('Name', 'asc')->get();
        $old_payee = Payors::find($check->PayeeID);
        $old_payor = Payors::find($check->PayorID);
        $old_sign = UserSignature::find($check->SignID);
        $userSignatures = UserSignature::where('UserID', Auth::id())->get();

        $grid_items = GridItem::where('CheckID', $id)
            ->get();
        $grid_history_ids = $grid_items->pluck('GridHistoryID')->unique()->toArray();

        $grid_items = $grid_items->groupBy('Row');
        // $grid_items = $grid_items
        //     ->groupBy('Row')
        //     ->map(function ($items) {
        //         return $items->map(fn($item) => $item->toArray());
        //     })
        //     ->toArray();

        if ($grid_items->isEmpty()) {
            $grid_histories = GridHistory::where('UserID', Auth::user()->UserID)
                ->where('Status', 1)->get();

        } else {
            // $grid_history_ids = $grid_items->pluck('GridHistoryID')->toArray();
            $grid_histories = GridHistory::where('UserID', Auth::user()->UserID)
                ->whereIn('id', $grid_history_ids)->get();
        }
        if (empty($grid_history_ids)) {
            $grid_history_ids = $grid_histories->pluck('id')->toArray();
        }

        $qboAccounts = [];
        $qboCompany = app(QuickBooksService::class)->activeCompanyForUser(Auth::id());
        if ($qboCompany) {
            try {
                $qboAccounts = app(QuickBooksService::class)->fetchAccounts($qboCompany)['expense'] ?? [];
            } catch (\Throwable $e) {
                $qboAccounts = [];
            }
        }
        $lineItems = $check->lineItems;

        return view('user.check.send_payment_generate_check', compact('grid_history_ids', 'grid_histories', 'grid_items', 'payees', 'payors', 'check', 'old_payee', 'old_payor', 'old_sign', 'userSignatures', 'qboAccounts', 'qboCompany', 'lineItems'));
    }

    public function generateAndSavePDF($data, $send_check = 0)
    {

        $directoryPath = public_path('checks');
        // Create fonts directory if it doesn't exist
        $fontPath = storage_path('fonts');
        if (!File::exists($fontPath)) {
            File::makeDirectory($fontPath, 0755, true);
        }

        // Define font file paths
        $fontFiles = [
            'MICRCheckPrixa.ttf',
            'MICRCheckPrixa.woff',
            'MICRCheckPrixa.woff2',
            'MICRCheckPrixa.eot'
        ];

        // Copy font files from public to storage if they don't exist
        foreach ($fontFiles as $fontFile) {
            $sourcePath = public_path('storage/fonts/' . $fontFile);
            $destPath = $fontPath . '/' . $fontFile;

            if (!File::exists($destPath) && File::exists($sourcePath)) {
                File::copy($sourcePath, $destPath);
            }
        }

        // Configure DOMPDF font cache directory
        $configPath = config_path('dompdf.php');
        if (File::exists($configPath)) {
            config(['dompdf.options.font_cache' => $fontPath]);
        }

        // Check if the directory exists, if not, create it
        if (!File::exists($directoryPath)) {
            File::makeDirectory($directoryPath, 0755, true);
        }
        // Generate PDF from a view
        $pdf = PDF::loadView('user.check_formate.index', compact('data', 'send_check'))->setPaper('letter', 'portrait')
            // ->setPaper([0, 0, 1000, 1200])
            ->setOptions(['dpi' => 150])
            ->set_option('isHtml5ParserEnabled', true)
            ->set_option('isRemoteEnabled', true);

        // Define the file path where you want to save the PDF
        // $file_name = 'check-' . $data['check_number'] . '-' . time() . '.pdf';
        $file_name = 'check-' . $data['check_number'] . '-' . time() . '-' . $data['check_id'] . '.pdf';
        $filePath = $directoryPath . '/' . $file_name;

        // Save the PDF to the specified path
        $pdf->save($filePath);
        return $file_name;
    }

    function numberToWords($number)
    {
        $parts = explode('.', number_format($number, 2, '.', ''));

        $whole = (int) $parts[0];
        $decimal = $parts[1];

        $numberToWords = new NumberToWords();
        $transformer = $numberToWords->getNumberTransformer('en');

        $words = ucfirst($transformer->toWords($whole));
        return "{$words} and {$decimal}/100";
    }

    public function check()
    {
        return view('user.check_formate.index');
    }

    public function change_status(Request $request)
    {
        $check = Checks::find($request->id);
        $check->Status = $request->value;
        $check->save();

        if ($request->page == 1) {
            return response()->json([
                'redirectUrl' => route('check.process_payment'),
                'message' => 'Status changed successfully'
            ]);
        } else {
            return response()->json([
                'redirectUrl' => route('check.send_payment'),
                'message' => 'Status changed successfully'
            ]);
        }
    }

    public function history(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('user.login');
        }

        if ($request->ajax()) {
            $query = Checks::where('UserID', Auth::id());

            // Apply filter if "type" parameter exists (from JS)
            if ($request->has('type') && !empty($request->type)) {
                $query->where('CheckType', $request->type);
            }

            // if (isset($request->entity_id) && $request->entity_id != null) {
            //     $payor_ids = Payors::withTrashed()->where('Name','like',"%{$request->entity_id}%")->pluck('EntityID')->toArray();

            //     $query->where(function ($q) use ($payor_ids) {
            //         $q->whereIn('PayorID', $payor_ids)
            //             ->orWhereIn('PayeeID', $payor_ids);
            //     });
            // }


            $query->orderBy('CheckID', 'desc');

            return datatables()->of($query)
                ->addIndexColumn()
                ->setRowId(function ($row) {
                    return $row->id;
                })
                ->filterColumn('EntityID', function ($query, $keyword) {
                    $payor_ids = Payors::withTrashed()->where('Name', 'like', "%{$keyword}%")->pluck('EntityID')->toArray();

                    $query->where(function ($q) use ($payor_ids) {
                        $q->whereIn('PayorID', $payor_ids)
                            ->orWhereIn('PayeeID', $payor_ids);
                    });
                })
                ->addColumn('CompanyID', function ($row) {
                    $payee = Payors::withTrashed()->find($row->PayeeID);
                    return $payee ? $payee->Name : '-';
                })
                ->addColumn('EntityID', function ($row) {
                    $payor = Payors::withTrashed()->find($row->PayorID);
                    return $payor ? $payor->Name : '-';
                })
                ->editColumn('Amount', function ($row) {
                    return '$' . number_format($row->Amount, 2);
                })
                ->editColumn('ServiceFees', function ($row) {
                    return '$' . number_format($row->ServiceFees, 2);
                })
                ->editColumn('Total', function ($row) {
                    return '$' . number_format($row->Total, 2);
                })
                ->addColumn('IssueDate', function ($row) {
                    return $row->IssueDate ? User::user_timezone($row->IssueDate, 'm/d/Y') : '-';
                })
                ->addColumn('Status', function ($row) {
                    return $row->Status === 'draft' ? 'Draft' : 'Generated';
                })
                ->addColumn('actions', function ($row) {
                    if (!empty($row->CheckPDF)) {
                        $check_preview = asset('checks/' . $row->CheckPDF);
                        $send_email_url = route('send_check_email', ['id' => $row->CheckID]);
                        $send_email_lable = !empty($row->is_email_send) ? 'Resend' : 'Send';

                        if ($row->CheckType == 'Process Payment') {
                            return '<a href="' . $check_preview . '" target="_blank" class="btn">
                                        <i class="menu-icon tf-icons ti ti-files"></i>
                                    </a>
                                    <a href="' . route('download.pdf', $row->CheckID) . '" class="btn">
                                        <i class="menu-icon tf-icons ti ti-download"></i>
                                    </a>';
                        } else {
                            return '<div class="d-flex"><a href="' . $check_preview . '" target="_blank" class="btn">
                                            <i class="menu-icon tf-icons ti ti-files"></i> Preview
                                    </a>
                                     <a href="' . route('download.pdf', $row->CheckID) . '" class="btn">
                                        <i class="menu-icon tf-icons ti ti-download"></i> Download
                                    </a>
                                    <a href="' . $send_email_url . '" class="btn">
                                            <i class="menu-icon tf-icons ti ti-mail"></i> ' . $send_email_lable . '
                                    </a>
                                    </div>';
                        }
                    } else {
                        return '-';
                    }
                })
                ->rawColumns(['Status', 'actions'])
                ->make(true);
        }

        $currencyFormatter = new \NumberFormatter('en_US', \NumberFormatter::CURRENCY);

        // Metrics for main page (optional but already in your code)
        $total_receive_check = Checks::where('UserID', Auth::id())
            ->where('CheckType', 'Process Payment')
            ->count();

        $total_receive_check_amount = Checks::where('UserID', Auth::id())
            ->where('CheckType', 'Process Payment')
            ->sum('Amount');

        $total_receive_check_amount = $currencyFormatter->format($total_receive_check_amount);

        $total_send_check = Checks::where('UserID', Auth::id())
            ->where('CheckType', 'Make Payment')
            ->count();

        $total_send_check_amount = Checks::where('UserID', Auth::id())
            ->where('CheckType', 'Make Payment')
            ->sum('Amount');

        $total_send_check_amount = $currencyFormatter->format($total_send_check_amount);
        // $total_receive_check_amount = $this->formatToK($total_receive_check_amount);
        // $total_send_check_amount = $this->formatToK($total_send_check_amount);

        return view('user.check.history', compact(
            'total_receive_check',
            'total_send_check',
            'total_receive_check_amount',
            'total_send_check_amount'
        ));
    }

    function formatToK($number)
    {
        if ($number >= 10000000) { // 1 Crore
            return round($number / 10000000, 2) . ' Cr';
        } elseif ($number >= 100000) { // 1 Lakh
            return round($number / 100000, 2) . ' L';
        } elseif ($number >= 1000) { // 1 Thousand
            return round($number / 1000, 2) . ' K';
        }

        return $number;
    }


    public function amount_word(Request $request)
    {
        $word = '';
        if (!empty($request->amount)) {
            $word = $this->numberToWords($request->amount);
        }
        return response()->json(['success' => true, 'word' => $word]);
    }

    public function get_payee($id)
    {
        $type = request()->query('type');
        $payee = Payors::where('EntityID', $id)->where('Category', $type)->first();
        return response()->json(['success' => true, 'payee' => $payee]);
    }

    public function get_payor($id)
    {
        $type = request()->query('type');
        $payor = Payors::where('EntityID', $id)->where('Category', $type)->first();
        return response()->json(['success' => true, 'payor' => $payor]);
    }

    public function check_generate($id)
    {
        $check = Checks::find($id);

        $isSubscribed = Helpers::isSubscribed(Auth::user());

        if (!$isSubscribed) {
            return redirect()->back()->with('info', 'Your check limit has been exceeded. Please upgrade your plan.');
        }
        $check_date = Carbon::parse(str_replace('/', '-', $check->ExpiryDate))->format('m/d/Y');

        $data = [];
        $payee = Payors::withTrashed()->find($check->PayeeID);

        // QuickBooks imports use mapped Company bank details (Payor may be empty)
        if ($check->CheckType === 'QuickBooks' || $check->Status === 'imported_from_qbo' || $check->qbo_id) {
            $issuer = $this->resolveQboIssuerBank($check);
            if (!$issuer) {
                return redirect()->route('qbo.settings')
                    ->with('error', 'Map your QuickBooks company to one of our Companies (with bank details) before generating.');
            }
            $data['payor_name'] = $issuer['name'];
            $data['address1'] = $issuer['address1'];
            $data['address2'] = $issuer['address2'];
            $data['city'] = $issuer['city'];
            $data['state'] = $issuer['state'];
            $data['zip'] = $issuer['zip'];
            $data['routing_number'] = $issuer['routing_number'];
            $data['account_number'] = $issuer['account_number'];
            $data['bank_name'] = $issuer['bank_name'];
        } else {
            $payor = Payors::withTrashed()->find($check->PayorID);
            $data['payor_name'] = $payor->Name;
            $data['address1'] = $payor->Address1;
            $data['address2'] = $payor->Address2;
            $data['city'] = $payor->City;
            $data['state'] = $payor->State;
            $data['zip'] = $payor->Zip;
            $data['routing_number'] = $payor->RoutingNumber;
            $data['account_number'] = $payor->AccountNumber;
            $data['bank_name'] = $payor->BankName;
        }

        $data['check_number'] = $check->CheckNumber;
        $data['check_date'] = $check_date;
        $data['payee_name'] = $payee->Name ?? '';
        $data['amount'] = $check->Amount;
        $data['service_fee'] = $check->ServiceFees;
        $data['total'] = $check->Total;
        $data['amount_word'] = $this->numberToWords($check->Total);
        $data['memo'] = $check->Memo;
        $data['signature'] = (!empty($check->DigitalSignatureRequired)) ? $check->DigitalSignature : '';
        $data['email'] = !empty($payee->Email) ? $payee->Email : '';
        $data['package'] = Auth::user()->CurrentPackageID;
        $data['check_id'] = $id;

        $lineItems = $check->lineItems;
        if ($lineItems && $lineItems->isNotEmpty()) {
            $data['grid_headers'] = ['Category', 'Description', 'Amount'];
            $ItemsArr = [];
            foreach ($lineItems as $line) {
                $ItemsArr[] = [
                    $line->account_name,
                    $line->description,
                    number_format((float) $line->amount, 2),
                ];
            }
            $data['grid_items'] = $ItemsArr;
        }

        $check_file = $this->generateAndSavePDF($data);

        $check->Status = 'generated';
        $check->CheckPDF = $check_file;
        $check->ip_address = request()->ip();
        $check->save();

        $this->syncCheckToQuickBooksOnGenerate($check);

        return redirect()->back()->with('success', 'Check generated successfully.');
    }

    public function send_check_generate($id)
    {
        $check = Checks::find($id);

        $isSubscribed = Helpers::isSubscribed(Auth::user());

        if (!$isSubscribed) {
            return redirect()->back()->with('info', 'Your check limit has been exceeded. Please upgrade your plan.');
        }

        $check_date = Carbon::parse(str_replace('/', '-', $check->ExpiryDate))->format('m/d/Y');

        $data = [];
        $payor = Payors::withTrashed()->find($check->PayorID);
        $payee = Payors::withTrashed()->find($check->PayeeID);
        $userSignature = UserSignature::withTrashed()->find($check->SignID);
        $data['payor_name'] = $payor->Name;
        $data['address1'] = $payor->Address1;
        $data['address2'] = $payor->Address2;
        $data['city'] = $payor->City;
        $data['state'] = $payor->State;
        $data['zip'] = $payor->Zip;
        $data['check_number'] = $check->CheckNumber;
        $data['check_date'] = $check_date;
        $data['payee_name'] = $payee->Name;
        $data['amount'] = $check->Amount;
        $data['amount_word'] = $this->numberToWords($check->Amount);
        $data['memo'] = $check->Memo;
        $data['routing_number'] = $payor->RoutingNumber;
        $data['account_number'] = $payor->AccountNumber;
        $data['bank_name'] = $payor->BankName;
        $data['signature'] = (!empty($userSignature->Sign)) ? $userSignature->Sign : '';
        $data['email'] = !empty($payee->Email) ? $payee->Email : '';
        $data['package'] = Auth::user()->CurrentPackageID;
        $data['check_id'] = $id;
        $send_check = 1;

        $grid_items = GridItem::select('Row', 'Value')->where('CheckID', $id)
            ->groupBy('CheckID', 'Row', 'GridHistoryID', 'Value')
            ->get();

        $grid_history_ids = GridItem::where('CheckID', $id)->pluck('GridHistoryID')
            ->unique()->toArray();

        $grid_headers = GridHistory::whereIn('id', $grid_history_ids)->pluck('Title')->toArray();

        if ($grid_items->isNotEmpty()) {
            foreach ($grid_items as $item) {
                $ItemsArr[$item->Row][] = ($item->Value);
            }
            $data['grid_items'] = $ItemsArr;
            $data['grid_headers'] = $grid_headers;
        }

        // return view('user.check_formate.index', compact('data','send_check'));

        $check_file = $this->generateAndSavePDF($data, $send_check);

        $check->Status = 'generated';
        $check->CheckPDF = $check_file;
        $check->ip_address = request()->ip();
        $check->save();

        $this->syncCheckToQuickBooksOnGenerate($check);

        return redirect()->back()->with('success', 'Check generated successfully.');
    }

    public function web_form($slug)
    {
        $data = WebForm::where('page_url', $slug)->first();
        $company = Payors::withTrashed()->find($data->PayeeID);
        return view('user.web_form.web', compact('data', 'company'));
    }

    public function get_web_forms(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('user.login');
        }

        if ($request->ajax()) {
            $webforms = WebForm::where('UserID', Auth::id())->get();

            return datatables()->of($webforms)
                ->addIndexColumn()
                ->addColumn('logo', function ($row) {
                    if (!empty($row->Logo)) {
                        return '<img src="' . asset($row->Logo) . '" alt="Webform Logo" style="width: 50px;">';
                    } else {
                        return '<img src="' . asset('assets/img/empty.jpg') . '" alt="Webform Logo" style="width: 50px;">';
                    }
                })
                ->addColumn('company_name', function ($row) {
                    $company = Payors::withTrashed()->find($row->PayeeID);
                    if (!empty($company)) {
                        return $company->Name;
                    } else {
                        return '-';
                    }
                })
                ->editColumn('page_url', function ($row) {
                    $Preview = route('web_form', ['slug' => $row->page_url]);
                    return '<a href="' . $Preview . '" target="_blank">' . $Preview . '</a>';
                })
                ->editColumn('url', function ($row) {
                    $Preview = route('web_form', ['slug' => $row->page_url]);
                    return $Preview;
                })
                ->addColumn('actions', function ($row) {
                    $editUrl = route('web_form.edit', ['id' => $row->Id]);
                    $deleteUrl = route('web_form.delete', ['id' => $row->Id]);
                    return '<div class="d-flex">
                                <a href="' . $editUrl . '" class="dropdown-item">
                                        <i class="ti ti-pencil me-1"></i> Edit
                                </a>
                                <a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" 
                                data-bs-target="#delete' . $row->Id . '">
                                    <i class="ti ti-trash me-1"></i> Delete
                                </a>
                            </div>
                            <div class="modal fade" id="delete' . $row->Id . '" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Delete Web Form</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Are you sure you want to delete this  Web Form?</p>
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
                ->rawColumns(['page_url', 'logo', 'actions'])
                ->make(true);
        }

        $package = Package::find(Auth::user()->CurrentPackageID);
        $is_web_form = (Auth::user()->CurrentPackageID != -1) ? $package->web_forms : 0;

        $grids = Grid::where('UserID', Auth::id())->get();
        $how_it_works = HowItWork::select('section', 'link')->where('status', 'Active')->pluck('link', 'section');

        return view('user.web_form.index', compact('is_web_form', 'grids', 'how_it_works'));
    }

    public function new_web_form()
    {
        return view('user.web_form.new');
    }

    public function new_web_form_store(Request $request)
    {
        try {
            $rules = [
                'name' => 'required',
                'address' => 'required',
                'phone_number' => 'nullable|regex:/^\d{3}-\d{3}-\d{4}$/',
                'city' => 'required',
                'state' => 'required',
                'zip' => 'required',
                'page_desc' => 'required',
                'service_fees_type' => 'nullable|in:percentage,amount',
                'service_fees' => [
                    'nullable',
                    'required_if:service_fees_type,percentage,amount',
                    'numeric',
                    'gt:0'
                ],
            ];
            $messages = [
                'service_fees_type.in' => 'Service Fees Type is invalid.',
                'service_fees.numeric' => 'Service Fees must be a number.',
                'service_fees.gt' => 'Service Fees must be greater than 0.',
                'service_fees.required_if' => 'Service Fees is required.',
            ];
            if (empty($request->web_form_id)) {
                $rules['logo'] = 'required';
            }
            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                // dd($validator->errors());
                return redirect()->back()->withErrors($validator)->withInput();
            }

            if (!empty($request->web_form_id)) {
                $webform = WebForm::find($request->web_form_id);
                $payee = Payors::withTrashed()->find($webform->PayeeID);
                if ($payee->Name != $request->name) {
                    $slug = $this->generateUniqueSlug($request->name);
                }
                $slug = $webform->page_url;
            } else {
                $webform = new WebForm();
                $payee = new Payors();
                $slug = $this->generateUniqueSlug($request->name);
            }

            $payee = new Payors();

            $payee->UserID = Auth::id();
            $payee->Name = $request->name;
            $payee->Address1 = $request->address;
            $payee->City = $request->city;
            $payee->State = $request->state;
            $payee->Zip = $request->zip;
            $payee->Type = 'Payee';
            $payee->Category = 'RP';
            $payee->PhoneNumber = preg_replace('/\D/', '', $request->phone_number);
            $payee->ServiceFeeType = $request->service_fees_type;
            $payee->ServiceFee = ($request->service_fees_type != null) ? $request->service_fees : null;
            $payee->save();

            if ($request->hasFile('logo')) {
                $file = $request->file('logo');
                $uniqueName = Str::uuid() . '.' . $file->getClientOriginalExtension();
                $destinationPath = public_path('logos');

                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }

                $file->move($destinationPath, $uniqueName);
                $logoPath = 'logos/' . $uniqueName;
            } else {
                // Keep existing logo if no new file uploaded
                $logoPath = $webform->Logo ?? null;
            }

            $webform->UserID = Auth::id();
            $webform->PayeeID = $payee->EntityID;
            $webform->Logo = $logoPath;
            $webform->page_url = $slug;
            $webform->page_desc = $request->page_desc;
            $webform->service_fees_type = $request->service_fees_type;
            $webform->service_fees = ($request->service_fees_type != null) ? $request->service_fees : null;

            $webform->save();

            return redirect()->route('get_web_forms')->with('success', 'Web form generated successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('fail', 'Web form not generated.');
        }

    }

    public function store_web_form_data(Request $request)
    {
        // dd($request->all());
        // $token = $request->input('g-recaptcha-token');

        // if (!$token) {
        //     return response()->json(['error' => 'Missing CAPTCHA token'], 422);
        // }

        // $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
        //     'secret' => env('CAPTCHA_SECRET_KEY'), // from your .env file
        //     'response' => $token,
        //     'remoteip' => $request->ip(), // optional
        // ]);

        // $result = $response->json();

        // if (
        //     !$result['success'] ||
        //     $result['action'] !== 'form_submit' ||
        //     $result['score'] < 0.5
        // ) {
        //     return redirect()->back()->with('error', 'Captcha verification failed.')->withInput();
        // }
        try {

            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'check_number' => 'required|numeric',
                'check_date' => 'required',
                'amount' => 'required|numeric',
                'name' => 'required',
                'email' => 'required|email',
                'address' => 'required',
                'phone_number' => 'required|regex:/^\d{3}-\d{3}-\d{4}$/',
                'city' => 'required',
                'state' => 'required',
                'zip' => 'required',
                'bank_name' => 'required',
                'routing_number' => 'required',
                'account_number' => 'required',
                'account_number_verify' => 'required|same:account_number',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $payee = Payors::withTrashed()->find($request->company_id);

            $user = User::find($payee->UserID);
            // $isSubscribed = Helpers::isSubscribed(Auth::user());

            // if(!$isSubscribed){
            //      return redirect()->back()->withInput()->with('info', 'Your check limit has been exceeded. Please upgrade your plan.');
            // }

            $payor_data = [
                'Name' => $request->name,
                'Email' => $request->email,
                'Address1' => $request->address,
                'PhoneNumber' => preg_replace('/\D/', '', $request->phone_number),
                'City' => $request->city,
                'State' => $request->state,
                'Zip' => $request->zip,
                'UserID' => $payee->UserID,
                'BankName' => $request->bank_name,
                'RoutingNumber' => $request->routing_number,
                'AccountNumber' => $request->account_number_verify,
                'Type' => 'Payor',
                'Category' => 'RP',
            ];

            $payor = Payors::where('Email', $request->email)->first();

            if (!empty($payor)) {
                $payor->update($payor_data);
            } else {
                $payor = Payors::create($payor_data);
            }

            $check_date = Carbon::parse(str_replace('-', '/', $request->check_date));

            $result = Helpers::getWebFormServiceFeeAndTotal($payee->EntityID, $request->amount);

            // $webform =WebForm::where('PayeeID', $payee->EntityID)->first();

            // $service_fees = 0;
            // $total = $request->amount;

            // if($webform){
            //     if($webform->service_fees_type == 'amount'){
            //         $service_fees = $webform->service_fees;
            //         $total = $request->amount + $service_fees;
            //     }else if($webform->service_fees_type == 'percentage'){
            //         $service_fees = $request->amount * $webform->service_fees / 100;
            //         $total = $request->amount + $service_fees;
            //     }
            // }

            $check_data = [
                'UserID' => $payee->UserID,
                'PayeeID' => $request->company_id,
                'CheckType' => 'Process Payment',
                'Amount' => $request->amount,
                'ServiceFees' => $result['service_fees'],
                'Total' => $result['total'],
                'PayorID' => $payor->EntityID,
                'CheckNumber' => $request->check_number,
                'IssueDate' => now(),
                'ExpiryDate' => $check_date,
                'Status' => 'draft',
                'Memo' => $request->Memo, 
                'CheckPDF' => null,
                'DigitalSignatureRequired' => 0,
                'ip_address' => $request->ip(),
                'created_at' => Carbon::now(),
            ];

            $check = Checks::create($check_data);

            $user_name = $user->FirstName . ' ' . $user->LastName;

            $payment_subscription = PaymentSubscription::where('UserID', $user->UserID)
                ->where('PackageID', $user->CurrentPackageID)
                ->where('Status', 'Active')
                ->orderBy('PaymentSubscriptionID', 'desc')->first();

            if ($payment_subscription != null && $user->CurrentPackageID != -1) {
                $payment_subscription->update([
                    'ChecksReceived' => $payment_subscription->ChecksReceived + 1,
                    'ChecksUsed' => $payment_subscription->ChecksUsed + 1,
                    'RemainingChecks' => $payment_subscription->RemainingChecks - 1
                ]);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Something went wrong.');
        }

        try{
            Mail::to($user->Email)->send(new SendWebFormMail(5, $user_name, $payor->Name, $check));
            Mail::to($request->email)->send(new SendWebFormMailForCilent(11, $payee->Name, $request->check_number, $check->Total, $payor->Name));
        }catch(Exception $e){
            return redirect()->back()->with('error', '<span style="font-weight:600; color:#b94a48;">Check form successfully submitted but email failed with following error: </span>
            <br>'.$e->getMessage());
        }
        return redirect()->back()->with('success', 'Check form successfully submitted.');
    }

    public function thankyou()
    {
        return view('user.web_form.thank_you');
    }

    function generateUniqueSlug($name)
    {
        $slug = Str::slug($name);
        $count = 0;

        while (WebForm::where('page_url', $slug)->exists()) {
            $count++;
            $slug = Str::slug($name) . '-' . $count;
        }

        return $slug;
    }

    public function web_form_delete($id)
    {
        $webForm = WebForm::find($id);
        $webForm?->delete();

        return redirect()->route('get_web_forms')->with('success', 'Web Form deleted successfully');

    }


    public function updateRecordsPerPage(Request $request)
    {
        $rpPerPage = $request->input('rp_page', 10);
        $spPerPage = $request->input('sp_page', 10);
        $payeePerPage = $request->input('payee_page', 10);
        $payorPerPage = $request->input('payor_page', 10);
        $historyPerPage = $request->input('history_page', 10);

        // Validate the value
        $validated = $request->validate([
            'rp_page' => 'required|integer|in:10,20,30,40,50,100',
            'sp_page' => 'required|integer|in:10,20,30,40,50,100',
            'payee_page' => 'required|integer|in:10,20,30,40,50,100',
            'payor_page' => 'required|integer|in:10,20,30,40,50,100',
            'history_page' => 'required|integer|in:10,20,30,40,50,100',
        ]);

        // Store in config file
        $this->updateConfigValue('rp_per_page', $rpPerPage);
        $this->updateConfigValue('sp_per_page', $spPerPage);
        $this->updateConfigValue('payee_per_page', $payeePerPage);
        $this->updateConfigValue('payor_per_page', $payorPerPage);
        $this->updateConfigValue('history_per_page', $historyPerPage);

        return redirect()->back()->with('success', 'Records per page updated successfully!');
    }

    private function updateConfigValue($key, $value)
    {
        $path = config_path('app.php');

        if (File::exists($path)) {
            $config = File::getRequire($path);

            // Update value
            data_set($config, $key, $value);

            // Save back to the file
            $content = '<?php return ' . var_export($config, true) . ';';
            File::put($path, $content);

            // Clear and cache config
            Artisan::call('config:clear');
            Artisan::call('config:cache');
        }
    }

    public function bulk_generate(Request $request)
    {
        $checks_ids = $request->check_ids;
        foreach ($checks_ids as $id) {
            $check = Checks::find($id);
            if ($check->Status != 'generated') {

                $check_date = Carbon::parse(str_replace('/', '-', $check->ExpiryDate))->format('m/d/Y');

                $data = [];
                $payor = Payors::withTrashed()->find($check->PayorID);
                $payee = Payors::withTrashed()->find($check->PayeeID);
                $userSignature = UserSignature::withTrashed()->find($check->SignID);
                $data['payor_name'] = $payor->Name;
                $data['address1'] = $payor->Address1;
                $data['address2'] = $payor->Address2;
                $data['city'] = $payor->City;
                $data['state'] = $payor->State;
                $data['zip'] = $payor->Zip;
                $data['check_number'] = $check->CheckNumber;
                $data['check_date'] = $check_date;
                $data['payee_name'] = $payee->Name;
                $data['amount'] = $check->Amount;
                $data['service_fee'] = $check->ServiceFees;
                $data['total'] = $check->Total;

                if ($check->CheckType == 'Process Payment') {
                    $data['amount_word'] = $this->numberToWords($check->Total);
                } else if ($check->CheckType == 'Make Payment') {
                    $data['amount_word'] = $this->numberToWords($check->Amount);
                }
                $data['memo'] = $check->Memo;
                $data['routing_number'] = $payor->RoutingNumber;
                $data['account_number'] = $payor->AccountNumber;
                $data['bank_name'] = $payor->BankName;
                $data['signature'] = (!empty($userSignature->Sign)) ? $userSignature->Sign : '';
                $data['email'] = !empty($payee->Email) ? $payee->Email : '';
                $data['package'] = Auth::user()->CurrentPackageID;
                $data['check_id'] = $id;
                
                // return view('user.check_formate.index', compact('data'));

                $check_file = $this->generateAndSavePDF($data);

                $check->Status = 'generated';
                $check->CheckPDF = $check_file;

                $check->save();
            }
        }

        return response()->json([
            'status' => true,
        ]);
    }

    public function web_form_edit(Request $request, $id)
    {
        $webform = WebForm::find($id);
        $payee = Payors::withTrashed()->find($webform->PayeeID);

        return view('user.web_form.edit', compact('webform', 'payee'));
    }

    public function bulk_download(Request $request)
    {
        $check_ids = $request->check_ids;

        if (empty($check_ids) || !is_array($check_ids)) {
            return back()->with('error', 'No checks selected for download.');
        }

        $pdf_dir = public_path('checks');
        $pdfFiles = [];
        $has_valid_pdf = false;

        foreach ($check_ids as $id) {
            $check = Checks::find($id);

            if ($check && $check->Status === 'generated' && !empty($check->CheckPDF)) {
                $pdf_path = $pdf_dir . '/' . $check->CheckPDF;

                if (File::exists($pdf_path)) {
                    $pdfFiles[] = $pdf_path;
                    $has_valid_pdf = true;
                } else {
                    return back()->with('error', "PDF file not found for check ID $id: $pdf_path");
                }
            } else {
                return back()->with('error', "Check ID $id is either not generated or missing PDF.");
            }
        }

        if (!$has_valid_pdf) {
            return back()->with('error', 'No valid PDF files found for the selected checks.');
        }

        try {
            // Init with dummy Letter size (will override per page)
            $pdf = new TcpdfFpdi('P', 'pt', 'letter');
            $pdf->SetPrintHeader(false);
            $pdf->SetPrintFooter(false);

            foreach ($pdfFiles as $file) {
                $pageCount = $pdf->setSourceFile($file);

                for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                    $templateId = $pdf->importPage($pageNo);
                    $size = $pdf->getTemplateSize($templateId);

                    // Add new page with original PDF size
                    $pdf->AddPage('P', [$size['width'], $size['height']]);

                    // Place template at (0,0) without scaling
                    $pdf->useTemplate($templateId, 0, 0, $size['width'], $size['height']);
                }
            }

            $fileName = 'batch-checks-' . time() . '.pdf';
            $filePath = $pdf_dir . '/' . $fileName;

            $pdf->Output($filePath, 'F');

            return response()->download($filePath)->deleteFileAfterSend(true);

        } catch (\Throwable $e) {
            return back()->with('error', 'Failed to merge PDFs: ' . $e->getMessage());
        }
    }


    public function get_signature($id)
    {
        $signature = UserSignature::find($id);
        return response()->json(['success' => true, 'signature' => $signature]);
    }

    public function send_check_email($id)
    {
        $check = Checks::find($id);
        $data = [];
        $payor = Payors::withTrashed()->find($check->PayorID);
        $payee = Payors::withTrashed()->find($check->PayeeID);
        $userSignature = UserSignature::withTrashed()->find($check->SignID);
        $data['sender_name'] = $payor->Name;
        $data['clinet_name'] = $payee->Name;
        $data['check_number'] = 'EC' . $check->CheckNumber;
        $data['memo'] = $check->Memo;
        $data['issued_date'] = Carbon::parse(str_replace('/', '-', $check->IssueDate))->format('m/d/Y');
        $data['amount'] = $check->Amount;
        $check_pdf = public_path('checks/' . $check->CheckPDF);

        try {
            Mail::to($payee->Email)->send(new SendCheckMail(4, $data, $check_pdf));

            $check->is_email_send = 1;
            $check->is_seen = 1;
            $check->save();

            return redirect()->back()->with('success', 'Email sent successfully.');
        } catch (\Exception $e) {
            // dd($e->getMessage());
            return redirect()->back()->with('fail', 'Email not sent.');
        }
    }

    public function view_pdf($id)
    {

        $check = Checks::find($id);

        if (!$check) {
            return ['status' => false, 'message' => 'Check not found.'];
            // abort(404, 'Check not found.');
        }

        $check->update([
            'is_seen' => 1
        ]);

        $path = public_path('checks/' . $check->CheckPDF);

        if (!File::exists($path)) {
            return ['status' => false, 'message' => 'PDF not found.'];
            // abort(404, 'PDF not found.');
        }

        return ['status' => true, 'url' => asset('checks/' . $check->CheckPDF)];
        // return response()->file($path, [
        //     'Content-Type' => 'application/pdf',
        //     'Content-Disposition' => 'inline; filename="' . basename($path) . '"'
        // ]);

    }

    public function download_pdf($id)
    {

        $check = Checks::find($id);

        if (!$check) {
            abort(404, 'Check not found.');
        }

        $check->update([
            'is_seen' => 1
        ]);

        // Clear Print later in QuickBooks after printing/downloading (queued)
        if ($check->qbo_id && $check->qbo_print_later) {
            MarkQuickBooksCheckPrintedJob::dispatch((int) $check->CheckID)->afterCommit();
        }

        $path = public_path('checks/' . $check->CheckPDF);

        if (!File::exists($path)) {
            abort(404, 'PDF not found.');
        }

        return response()->download($path);
        // return ['status'=>true, 'url'=> asset('checks/' . $check->CheckPDF)];
    }

    /**
     * Bank/issuer details for QBO-imported checks from mapped Company.
     */
    protected function resolveQboIssuerBank(Checks $check): ?array
    {
        $qboCompany = null;
        if ($check->qbo_company_id) {
            $qboCompany = QBOCompany::where('id', $check->qbo_company_id)
                ->where('user_id', Auth::id())
                ->first();
        }
        if (!$qboCompany) {
            $qboCompany = app(QuickBooksService::class)->activeCompanyForUser(Auth::id());
        }
        if (!$qboCompany || !$qboCompany->company_id) {
            return null;
        }

        $company = Company::where('CompanyID', $qboCompany->company_id)
            ->where('UserID', Auth::id())
            ->first();

        if (!$company) {
            return null;
        }

        return [
            'name' => $company->Name,
            'address1' => $company->Address1,
            'address2' => $company->Address2,
            'city' => $company->City,
            'state' => $company->State,
            'zip' => $company->Zip,
            'routing_number' => $company->RoutingNumber,
            'account_number' => $company->AccountNumber,
            'bank_name' => $company->BankName,
        ];
    }

    /**
     * Queue push/update of check to QuickBooks when PDF is generated (outbound sync).
     */
    protected function syncCheckToQuickBooksOnGenerate(Checks $check): void
    {
        $active = app(QuickBooksService::class)->activeCompanyForUser((int) $check->UserID);
        if (!$active) {
            return;
        }

        PushCheckToQuickBooksJob::dispatch((int) $check->CheckID)->afterCommit();
    }

    protected function saveCheckLineItems(Checks $check, array $preparedQboLines): void
    {
        CheckLineItem::where('CheckID', $check->CheckID)->where('source', 'local')->delete();

        foreach ($preparedQboLines as $row) {
            CheckLineItem::create([
                'CheckID' => $check->CheckID,
                'line_no' => $row['line_no'],
                'qbo_account_id' => $row['qbo_account_id'],
                'account_name' => $row['account_name'],
                'description' => $row['description'],
                'amount' => $row['amount'],
                'source' => 'local',
            ]);
        }
    }

    public function isExists(Request $request)
    {
        $checkNumber = $request->check_number;

        $isExists = Checks::where('UserID', Auth::user()->UserID)->where('CheckNumber', $checkNumber)->exists();

        return response()->json(['exists' => $isExists]);
    }

    public function saveGrid(Request $request)
    {

        $input = $request->all();

        $gridItem['UserID'] = Auth::user()->UserID;
        $row = [];

        GridHistory::where('UserID', Auth::user()->UserID)->update([
            'Status' => 0
        ]);

        if (!isset($input['grid'])) {

            foreach ($input['name'] as $key => $val) {

                $row[$key]['name'] = $input['name'][$key];
                $row[$key]['type'] = $input['type'][$key];
                $row[$key]['status'] = ($input['status'][$key] == '1') ? 1 : 0;
                $row[$key]['required'] = isset($input['required'][$key]) ? (($input['required'][$key] == '1') ? 1 : 0) : 0;

                if ($row[$key]['status'] == 1 && $row[$key]['name'] == '') {
                    return redirect()->back()->with('grid_error', 'Title is required.')->withInput();
                }
                if ($row[$key]['status'] == 1 && $row[$key]['type'] == '') {
                    return redirect()->back()->with('grid_error', 'Type is required.')->withInput();
                }

                $grid = Grid::create([
                    'UserID' => Auth::user()->UserID,
                    'Title' => $row[$key]['name'],
                    'Type' => $row[$key]['type'],
                    'Status' => $row[$key]['status'],
                    'Required' => $row[$key]['required']
                ]);

                if ($row[$key]['status'] == 1) {
                    GridHistory::create([
                        'UserID' => Auth::user()->UserID,
                        'GridID' => $grid->id,
                        'Title' => $row[$key]['name'],
                        'Type' => $row[$key]['type'],
                        'Status' => $row[$key]['status'],
                        'Required' => $row[$key]['required']
                    ]);
                }
            }

            $message = 'Check Stub Custom Itemization Fields saved successfully.';
        } else {

            foreach ($input['grid'] as $key => $val) {

                if ($val['status'] == 1 && $val['name'] == '') {
                    return redirect()->back()->with('grid_error', 'Title is required.')->withInput();
                }
                if ($val['status'] == 1 && $val['type'] == '') {
                    return redirect()->back()->with('grid_error', 'Type is required.')->withInput();
                }

                $required = isset($val['required']) ? (($val['required'] == '1') ? 1 : 0) : 0;

                Grid::find($key)->update([
                    'Title' => $val['name'],
                    'Type' => $val['type'],
                    'Status' => $val['status'],
                    'Required' => $required
                ]);

                if ($val['status'] == 1) {
                    GridHistory::create([
                        'UserID' => Auth::user()->UserID,
                        'Title' => $val['name'],
                        'Type' => $val['type'],
                        'Status' => $val['status'],
                        'Required' => $required
                    ]);
                }
            }

            $message = 'Check Stub Custom Itemization Fields updated successfully.';
        }



        return redirect()->back()->with('grid_success', $message);
    }

    public function getGrids(Request $request)
    {

        $user_input = $request->all();
        $row = $user_input['grid_row_count'] + 1;

        $grid_history_ids = explode(',', $user_input['grid_history_ids']);

        $grid_histories = GridHistory::where('UserID', Auth::user()->UserID)
            ->whereIn('id', $grid_history_ids)->get();

        $result = [];
        $html = '<tr>';
        if ($grid_histories != null) {
            foreach ($grid_histories as $key => $grid_history) {
                // if ($grid_history->Status == 1) {
                $inputTd = '<td>';

                if ($grid_history->Type == 'text') {
                    $inputContent = 'name="grid_items[' . $grid_history->id . '][]" type="text" class="form-control"';
                } else if ($grid_history->Type == 'number') {
                    $inputContent = 'name="grid_items[' . $grid_history->id . '][]" type="text" class="form-control" onkeypress="return /^[0-9.]+$/.test(event.key)"';
                } else if ($grid_history->Type == 'date') {
                    $inputContent = 'name="grid_items[' . $grid_history->id . '][]" type="text" class="form-control mydatepicker"';
                    $date = true;
                }

                $input = $inputTd . '<input ' . $inputContent . ' autocomplete="off"></td>';
                $html = $html . $input;
                // }
            }
        }
        $html = $html . '
            <td class="text-center"> 
            <button type="button" class="btn btn-sm btn-primary" onclick="addRow(`' . $user_input['grid_history_ids'] . '`)"><i class="ti ti-plus"></i></button>
                        <button type="button" class="btn btn-sm btn-danger removeRow"><i class="ti ti-trash"></i></button>
                    </td>
                </tr>';
        return response()->json(['status' => true, 'html' => $html]);
    }

    // public function getDefaultGrids(Request $request)
    // {

    //     $user_input = $request->all();
    //     $row = $user_input['grid_row_count'] + 1;

    //     $grid_history_ids = explode(',', $user_input['grid_history_ids']);

    //     $grid_histories = GridHistory::where('UserID', Auth::user()->UserID)
    //         ->where('Status',1)->get();

    //     $result = [];
    //     $html = '<tr>';
    //     if ($grid_histories != null) {
    //         foreach ($grid_histories as $key => $grid_history) {
    //                 $inputTd = '<td>';

    //                 if ($grid_history->Type == 'text') {
    //                     $inputContent = 'name="grid_items[' . $grid_history->id . '][]" type="text" class="form-control"';
    //                 } else if ($grid_history->Type == 'number') {
    //                     $inputContent = 'name="grid_items[' . $grid_history->id . '][]" type="text" class="form-control" onkeypress="return /^[0-9.]+$/.test(event.key)"';
    //                 } else if ($grid_history->Type == 'date') {
    //                     $inputContent = 'name="grid_items[' . $grid_history->id . '][]" type="text" class="form-control mydatepicker"';
    //                     $date = true;
    //                 }

    //                 $input = $inputTd . '<input ' . $inputContent . ' autocomplete="off"></td>';
    //                 $html = $html . $input;
    //         }
    //     }
    //     $html = $html . '
    //         <td class="text-center"> 
    //         <button type="button" class="btn btn-sm btn-primary" onclick="addRow(`' . $user_input['grid_history_ids'] . '`)"><i class="ti ti-plus"></i></button>
    //                     <button type="button" class="btn btn-sm btn-danger removeRow"><i class="ti ti-trash"></i></button>
    //                 </td>
    //             </tr>';
    //     return response()->json(['status' => true, 'html' => $html]);
    // }

}
