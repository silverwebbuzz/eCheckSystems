<?php

namespace App\Http\Controllers;

use App\Models\Checks;
use App\Models\Company;
use App\Models\Payors;
use App\Models\QBOCompany;
use App\Jobs\SyncQuickBooksChecksJob;
use App\Services\QuickBooksService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class QuickBooksController extends Controller
{
    public function __construct(private QuickBooksService $qbo)
    {
    }

    public function settings()
    {
        $userId = Auth::id();
        $companies = QBOCompany::forUser($userId)->orderByDesc('id')->get();
        $localCompanies = Company::where('UserID', $userId)->where('Status', 'Active')->get();
        $active = $companies->firstWhere('status', 'connected');

        $accounts = ['bank' => [], 'expense' => []];
        $accountError = null;

        if ($active) {
            try {
                $accounts = $this->qbo->fetchAccounts($active);
            } catch (Exception $e) {
                $accountError = $e->getMessage();
                Log::warning('QBO account fetch failed', ['error' => $accountError]);
            }
        }

        return view('user.quickbooks.settings', compact(
            'companies',
            'localCompanies',
            'active',
            'accounts',
            'accountError'
        ));
    }

    public function connect()
    {
        if (!Auth::check()) {
            return redirect()->route('user.login');
        }

        if (!config('quickbooks.client_id') || !config('quickbooks.client_secret')) {
            return redirect()->route('qbo.settings')
                ->with('error', 'QuickBooks sandbox credentials are not configured. Set QBO_CLIENT_ID and QBO_CLIENT_SECRET in .env');
        }

        session(['qbo_connecting_user_id' => Auth::id()]);

        try {
            return redirect($this->qbo->getAuthorizationUrl());
        } catch (Exception $e) {
            return redirect()->route('qbo.settings')->with('error', $e->getMessage());
        }
    }

    public function callback(Request $request)
    {
        $userId = session('qbo_connecting_user_id') ?: Auth::id();

        if (!$userId) {
            return redirect()->route('user.login')->with('error', 'Please login before connecting QuickBooks.');
        }

        if (!$request->realmId || !$request->code) {
            return redirect()->route('qbo.settings')->with('error', 'QuickBooks authorization was cancelled or incomplete.');
        }

        try {
            $tempDataService = $this->qbo->configureDataService();
            $OAuth2LoginHelper = $tempDataService->getOAuth2LoginHelper();
            $accessTokenObj = $OAuth2LoginHelper->exchangeAuthorizationCodeForToken(
                $request->code,
                $request->realmId
            );

            $dataService = $this->qbo->configureDataService(
                $accessTokenObj->getAccessToken(),
                $accessTokenObj->getRefreshToken(),
                $request->realmId
            );

            $companyInfo = $dataService->getCompanyInfo();
            $companyName = $companyInfo->CompanyName ?? 'Unknown Company';
            $addr = $companyInfo->CompanyAddr ?? null;
            $companyAddress = $addr
                ? trim(implode(' ', array_filter([
                    $addr->Line1 ?? '',
                    $addr->Line2 ?? '',
                    $addr->City ?? '',
                    $addr->CountrySubDivisionCode ?? '',
                    $addr->PostalCode ?? '',
                ])))
                : 'No Address Found';

            // Only one active connection per user
            QBOCompany::forUser($userId)->update(['status' => 'not connected']);

            $defaultLocalCompanyId = Company::where('UserID', $userId)
                ->where('Status', 'Active')
                ->value('CompanyID');

            QBOCompany::updateOrCreate(
                [
                    'user_id' => $userId,
                    'realm_id' => $request->realmId,
                ],
                [
                    'name' => $companyName,
                    'address' => $companyAddress,
                    'start_date' => $companyInfo->CompanyStartDate ?? now()->toDateString(),
                    'access_token' => $accessTokenObj->getAccessToken(),
                    'refresh_token' => $accessTokenObj->getRefreshToken(),
                    'access_token_expires_at' => date('Y-m-d H:i:s', strtotime($accessTokenObj->getAccessTokenExpiresAt())),
                    'refresh_token_expires_at' => date('Y-m-d H:i:s', strtotime($accessTokenObj->getRefreshTokenExpiresAt())),
                    'status' => 'connected',
                    'company_id' => $defaultLocalCompanyId,
                ]
            );

            session()->forget('qbo_connecting_user_id');

            return redirect()->route('qbo.settings')->with('success', 'QuickBooks company connected. Map your bank company and default accounts, then Sync.');
        } catch (Exception $e) {
            Log::error('QBO callback failed', ['error' => $e->getMessage()]);
            return redirect()->route('qbo.settings')->with('error', 'Connection failed: ' . $e->getMessage());
        }
    }

    public function setActive($id)
    {
        $userId = Auth::id();
        $company = QBOCompany::forUser($userId)->where('id', $id)->firstOrFail();

        QBOCompany::forUser($userId)->where('id', '!=', $id)->update(['status' => 'not connected']);
        $company->update(['status' => 'connected']);

        return redirect()->route('qbo.settings')->with('success', 'Active QuickBooks company updated.');
    }

    public function disconnect($id)
    {
        $userId = Auth::id();
        $company = QBOCompany::forUser($userId)->where('id', $id)->firstOrFail();
        $company->update(['status' => 'not connected']);

        return redirect()->route('qbo.settings')->with('success', 'QuickBooks company disconnected.');
    }

    public function updateMapping(Request $request, $id)
    {
        $userId = Auth::id();
        $company = QBOCompany::forUser($userId)->where('id', $id)->firstOrFail();

        $request->validate([
            'company_id' => 'nullable|integer',
            'default_bank_account_id' => 'nullable|string|max:50',
            'default_bank_account_name' => 'nullable|string|max:255',
            'default_expense_account_id' => 'nullable|string|max:50',
            'default_expense_account_name' => 'nullable|string|max:255',
        ]);

        if ($request->company_id) {
            $owns = Company::where('UserID', $userId)->where('CompanyID', $request->company_id)->exists();
            if (!$owns) {
                return back()->with('error', 'Invalid local company selected.');
            }
        }

        $company->update([
            'company_id' => $request->company_id,
            'default_bank_account_id' => $request->default_bank_account_id,
            'default_bank_account_name' => $request->default_bank_account_name,
            'default_expense_account_id' => $request->default_expense_account_id,
            'default_expense_account_name' => $request->default_expense_account_name,
        ]);

        return redirect()->route('qbo.settings')->with('success', 'QuickBooks mapping saved.');
    }

    public function sync($qbo_company_id = null)
    {
        $userId = Auth::id();
        $company = $qbo_company_id
            ? QBOCompany::forUser($userId)->where('id', $qbo_company_id)->first()
            : $this->qbo->activeCompanyForUser($userId);

        if (!$company || $company->status !== 'connected') {
            return redirect()->route('qbo.settings')->with('error', 'Connect and activate a QuickBooks company first.');
        }

        SyncQuickBooksChecksJob::dispatch((int) $company->id, (int) $userId);

        return redirect()->route('qbo.checks')
            ->with('success', 'QuickBooks sync has been queued. Checks will appear shortly — refresh this page in a moment.');
    }

    /** @deprecated keep old route name working */
    public function getCompanies()
    {
        return redirect()->route('qbo.settings');
    }

    public function connectCompany($id)
    {
        return $this->setActive($id);
    }

    public function checks(Request $request)
    {
        if ($request->ajax()) {
            $checks = Checks::with(['payee', 'payor', 'lineItems'])
                ->where('UserID', Auth::id())
                ->quickBooks()
                ->orderByDesc('CheckID')
                ->get();

            return datatables()->of($checks)
                ->addIndexColumn()
                ->addColumn('txn_type', function ($row) {
                    if ($row->CheckType === 'Process Payment') {
                        return '<span class="badge bg-label-success">Receive Payment</span>';
                    }

                    return '<span class="badge bg-label-primary">Send Payment</span>';
                })
                ->addColumn('party_name', function ($row) {
                    if ($row->CheckType === 'Process Payment') {
                        return $row->payor->Name ?? $row->payee->Name ?? '—';
                    }

                    return $row->payee->Name ?? $row->payor->Name ?? '—';
                })
                ->addColumn('amount_fmt', fn ($row) => '$' . number_format((float) $row->Total, 2))
                ->addColumn('issue_date', fn ($row) => $row->IssueDate ? date('m/d/Y', strtotime($row->IssueDate)) : '—')
                ->addColumn('status_badge', function ($row) {
                    $label = $row->Status;
                    $class = $row->Status === 'generated' ? 'success' : ($row->Status === 'imported_from_qbo' ? 'info' : 'secondary');
                    $html = '<span class="badge bg-label-' . $class . '">' . e($label) . '</span>';
                    if ($row->check_number_conflict) {
                        $html .= ' <span class="badge bg-label-warning" title="Check number already exists">Conflict</span>';
                    }
                    if ($row->qbo_print_later) {
                        $html .= ' <span class="badge bg-label-primary">Print later</span>';
                    }
                    return $html;
                })
                ->addColumn('lines_count', fn ($row) => $row->lineItems->count())
                ->addColumn('actions', function ($row) {
                    $view = route('qbo.checks.show', ['id' => $row->CheckID]);
                    $html = '<a href="' . $view . '" class="btn btn-sm btn-outline-secondary me-1">View</a>';
                    if ($row->Status !== 'generated') {
                        $generate = $row->CheckType === 'Make Payment'
                            ? route('send_check_generate', ['id' => $row->CheckID])
                            : route('check_generate', ['id' => $row->CheckID]);
                        $html .= '<a href="' . $generate . '" class="btn btn-sm btn-primary">Generate / Print</a>';
                    }
                    return $html;
                })
                ->rawColumns(['txn_type', 'status_badge', 'actions'])
                ->make(true);
        }

        return view('user.quickbooks.checks');
    }

    public function showCheck($id)
    {
        $check = Checks::with(['payee', 'payor', 'lineItems', 'qboCompany'])
            ->where('UserID', Auth::id())
            ->where('CheckID', $id)
            ->firstOrFail();

        $category = $check->CheckType === 'Process Payment' ? 'RP' : 'SP';

        $payors = Payors::where('UserID', Auth::id())
            ->where('Type', 'Payor')
            ->where('Category', $category)
            ->orderBy('Name')
            ->get();

        $payees = Payors::where('UserID', Auth::id())
            ->where('Type', 'Payee')
            ->where('Category', $category)
            ->orderBy('Name')
            ->get();

        $states = [
            'Alabama', 'Alaska', 'Arizona', 'Arkansas', 'California', 'Colorado', 'Connecticut',
            'Delaware', 'Florida', 'Georgia', 'Hawaii', 'Idaho', 'Illinois', 'Indiana', 'Iowa',
            'Kansas', 'Kentucky', 'Louisiana', 'Maine', 'Maryland', 'Massachusetts', 'Michigan',
            'Minnesota', 'Mississippi', 'Missouri', 'Montana', 'Nebraska', 'Nevada', 'New Hampshire',
            'New Jersey', 'New Mexico', 'New York', 'North Carolina', 'North Dakota', 'Ohio',
            'Oklahoma', 'Oregon', 'Pennsylvania', 'Rhode Island', 'South Carolina', 'South Dakota',
            'Tennessee', 'Texas', 'Utah', 'Vermont', 'Virginia', 'Washington', 'West Virginia',
            'Wisconsin', 'Wyoming',
        ];

        return view('user.quickbooks.check_show', compact('check', 'payors', 'payees', 'category', 'states'));
    }

    /**
     * Link a Payor or Payee to a QuickBooks-imported check.
     */
    public function assignParty(Request $request, $id)
    {
        $check = Checks::where('UserID', Auth::id())
            ->where('CheckID', $id)
            ->firstOrFail();

        $validator = Validator::make($request->all(), [
            'party' => 'required|in:payor,payee',
            'entity_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $category = $check->CheckType === 'Process Payment' ? 'RP' : 'SP';
        $expectedType = $request->party === 'payor' ? 'Payor' : 'Payee';

        $entity = Payors::where('UserID', Auth::id())
            ->where('EntityID', $request->entity_id)
            ->where('Type', $expectedType)
            ->where('Category', $category)
            ->first();

        if (!$entity) {
            return response()->json(['success' => false, 'message' => 'Selected ' . $expectedType . ' not found.'], 404);
        }

        if ($request->party === 'payor') {
            $check->PayorID = $entity->EntityID;
        } else {
            $check->PayeeID = $entity->EntityID;
        }
        $check->save();

        return response()->json([
            'success' => true,
            'party' => $request->party,
            'entity' => $entity,
            'name' => $entity->Name,
        ]);
    }
}
