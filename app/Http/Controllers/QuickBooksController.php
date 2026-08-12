<?php

namespace App\Http\Controllers;

use App\Models\Checks;
use App\Models\Company;
use App\Models\QBOCompany;
use App\Services\QuickBooksService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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

        try {
            $result = $this->qbo->syncChecksFromQbo($company, $userId);
            $msg = "Sync complete. Imported {$result['imported']}, updated {$result['updated']}.";
            if (!empty($result['warnings'])) {
                $msg .= ' Warnings: ' . count($result['warnings']) . ' check number conflict(s).';
            }
            return redirect()->route('qbo.checks')->with('success', $msg);
        } catch (Exception $e) {
            Log::error('QBO sync failed', ['error' => $e->getMessage()]);
            return redirect()->route('qbo.settings')->with('error', 'Sync failed: ' . $e->getMessage());
        }
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
            $checks = Checks::with(['payee', 'lineItems'])
                ->where('UserID', Auth::id())
                ->quickBooks()
                ->orderByDesc('CheckID')
                ->get();

            return datatables()->of($checks)
                ->addIndexColumn()
                ->addColumn('payee_name', fn ($row) => $row->payee->Name ?? '—')
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
                    $generate = route('check_generate', ['id' => $row->CheckID]);
                    $view = route('qbo.checks.show', ['id' => $row->CheckID]);
                    return '<a href="' . $view . '" class="btn btn-sm btn-outline-secondary me-1">View</a>'
                        . '<a href="' . $generate . '" class="btn btn-sm btn-primary">Generate / Print</a>';
                })
                ->rawColumns(['status_badge', 'actions'])
                ->make(true);
        }

        return view('user.quickbooks.checks');
    }

    public function showCheck($id)
    {
        $check = Checks::with(['payee', 'lineItems', 'qboCompany'])
            ->where('UserID', Auth::id())
            ->where('CheckID', $id)
            ->firstOrFail();

        return view('user.quickbooks.check_show', compact('check'));
    }
}
