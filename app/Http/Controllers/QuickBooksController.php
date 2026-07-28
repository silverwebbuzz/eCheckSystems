<?php

namespace App\Http\Controllers;

use App\Models\QBOCompany;
use Exception;
use Illuminate\Http\Request;
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Facades\Vendor as QBOVendor;
use Illuminate\Support\Facades\Log;
use QuickBooksOnline\API\ReportService\ReportService;

class QuickBooksController extends Controller
{

    private static function getDefaultDataService()
    {

        return DataService::Configure([
            'auth_mode' => 'oauth2',
            'ClientID' => env('QBO_CLIENT_ID'),
            'ClientSecret' => env('QBO_CLIENT_SECRET'),
            'RedirectURI' => env('QBO_REDIRECT_URI'),
            'scope' => env('QBO_SCOPE', 'com.intuit.quickbooks.accounting'),
            'baseUrl' => env('QBO_ENVIRONMENT') === 'development' ? "development" : "production"
        ]);
    }

    private static function getTokens($qbo_company_id = null)
    {

        if ($qbo_company_id) {

            $qbo_company = QBOCompany::where('id', $qbo_company_id)->first();
            $accessToken = $qbo_company->access_token;
            $refreshToken = $qbo_company->refresh_token;
            $realmId = $qbo_company->realm_id;

            if (now()->gte($qbo_company->access_token_expires_at)) {

                $dataService = self::getDataService($accessToken, $refreshToken, $realmId);

                $oauth2LoginHelper = $dataService->getOAuth2LoginHelper();
                $newAccessTokenObj = $oauth2LoginHelper->refreshToken();

                $accessToken = $newAccessTokenObj->getAccessToken();
                $refreshToken = $newAccessTokenObj->getRefreshToken();

                // Update tokens in DB
                $qbo_company->update([
                    'access_token' => $accessToken,
                    'refresh_token' => $refreshToken,
                    'access_token_expires_at' => date('Y-m-d H:i:s', strtotime($newAccessTokenObj->getAccessTokenExpiresAt())),
                    'refresh_token_expires_at' => date('Y-m-d H:i:s', strtotime($newAccessTokenObj->getRefreshTokenExpiresAt()))
                ]);
            }
        } else {

            $accessToken = session('qbo_access_token');
            $refreshToken = session('qbo_refresh_token');
            $realmId = session('qbo_realm_id');
        }

        // If no tokens, start OAuth connect process
        if (!$accessToken || !$realmId) {
            throw new Exception("QuickBooks not connected or token missing.");
        }

        return [
            'accessToken' => $accessToken,
            'refreshToken' => $refreshToken,
            'realmId' => $realmId
        ];
    }
    private static function getDataService($accessToken = null, $refreshToken = null, $realmId = null)
    {

        return DataService::Configure([
            'auth_mode' => 'oauth2',
            'ClientID' => env('QBO_CLIENT_ID'),
            'ClientSecret' => env('QBO_CLIENT_SECRET'),
            'RedirectURI' => env('QBO_REDIRECT_URI'),
            'scope' => env('QBO_SCOPE', 'com.intuit.quickbooks.accounting'),
            'baseUrl' => env('QBO_ENVIRONMENT') === 'development' ? "development" : "production",
            'accessTokenKey' => $accessToken,
            'refreshTokenKey' => $refreshToken,
            'QBORealmID' => $realmId
        ]);
    }

    public static function connect()
    {
        $dataService = self::getDefaultDataService();

        $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();

        return redirect($OAuth2LoginHelper->getAuthorizationCodeURL());
    }

    public function callback(Request $request)
    {
        // 1. First, get token from Intuit
        $tempDataService = self::getDefaultDataService();

        $OAuth2LoginHelper = $tempDataService->getOAuth2LoginHelper();

       if(!$request->realmId || !$request->code){
           return redirect()->route('qbo.getCompanies');
       }
        $accessTokenObj = $OAuth2LoginHelper->exchangeAuthorizationCodeForToken(
            $request->code,
            $request->realmId
        );

        // 2. Now configure with Realm ID and tokens
        $dataService = self::getDataService(
            $accessTokenObj->getAccessToken(),
            $accessTokenObj->getRefreshToken(),
            $request->realmId
        );

        // 3. Get company info
        $companyInfo = $dataService->getCompanyInfo();

        $companyName = $companyInfo->CompanyName ?? 'Unknown Company';
        $addr = $companyInfo->CompanyAddr ?? null;

        $companyAddress = $addr
            ? trim(implode(' ', array_filter([
                $addr->Line1 ?? '',
                $addr->Line2 ?? '',
                $addr->Line3 ?? '',
                $addr->Line4 ?? '',
                $addr->Line5 ?? '',
                $addr->City ?? '',
                $addr->Country ?? '',
                $addr->PostalCode ?? ''
            ])))
            : 'No Address Found';

        self::disconnectAllQBOCompanies();
        // 4. Save to DB
        QBOCompany::updateOrcreate([
            'user_id' => 1,
            'realm_id' => $request->realmId,
        ], [
            'name' => $companyName,
            'address' => $companyAddress,
            'start_date' => $companyInfo->CompanyStartDate,
            'access_token' => $accessTokenObj->getAccessToken(),
            'refresh_token' => $accessTokenObj->getRefreshToken(),
            'access_token_expires_at' => date('Y-m-d H:i:s', strtotime($accessTokenObj->getAccessTokenExpiresAt())),
            'refresh_token_expires_at' => date('Y-m-d H:i:s', strtotime($accessTokenObj->getRefreshTokenExpiresAt())),
            'status' => 'connected'
        ]);

        return redirect()->route('qbo.getCompanies')->with('success', 'Company connected successfully!');
    }

    public static function disconnectAllQBOCompanies()
    {
        QBOCompany::where('user_id', 1)->update([
            'status' => 'not connected'
        ]);
    }

    public function getCompanies()
    {
        $companies = QBOCompany::where('user_id', 1)->get();
        return view('user.quickbooks.companies', compact('companies'));
    }

    public function connectCompany($id)
    {
        try {
            QBOCompany::whereNot('id', $id)->where('user_id', 1)
                ->update([
                    'status' => 'not connected'
                ]);

            $company = QBOCompany::where('id', $id)->where('user_id', 1)
                ->first();

            if (!$company) {

                return redirect()->route('qbo.getCompanies')->with('error', 'Company not found');
            }

            if ($company->status == 'not connected') {

                $company->update([
                    'status' => 'connected'
                ]);

                $tokens = self::getTokens($company->id);

                $message = 'Company connected successfully';
            } else if ($company->status == 'connected') {
                $company->update([
                    'status' => 'not connected'
                ]);

                $message = 'Company disconnected successfully';
            }

            return redirect()->back()->with('success', $message);
        } catch (Exception $e) {

            return redirect()->back()->with('error', 'Something went wrong');
        }

    }

    public function sync($qbo_company_id = null)
    {

        if ($qbo_company_id) {

            $tokens = self::getTokens($qbo_company_id);

            $dataService = self::getDataService($tokens['accessToken'], $tokens['refreshToken'], $tokens['realmId']);


            $query = "SELECT * FROM Purchase";
            $checks = $dataService->query($query);
           
            // $checks = collect($checks)->map(function ($purchase) use ($dataService) {
                
            //     $full = $dataService->FindById("Purchase", $purchase->Id);

            //     $vendorId = $full->EntityRef ?? null;
            //     $vendorName = null;

            //     if ($vendorId) {
            //         $vendor = $dataService->FindById("Vendor", $vendorId);
            //         $vendorName = $vendor ? $vendor->DisplayName : null;
            //     }

            //     return [
            //         'id' => $full->Id,
            //         'date' => $full->TxnDate,
            //         'amount' => $full->TotalAmt,
            //         'vendorId' => $vendorId,
            //         'vendorName' => $vendorName,
            //         'account' => $full->AccountRef ?? null,
            //     ];
            // });


            // $error = $dataService->getLastError();
            // if ($error) {
            //     dd($error->getResponseBody());
            // }
            // dd($checks);

            // Get last sync time (default: 2000-01-01)
            // $lastSyncTime = DB::table('sync_logs')
            //     ->where('entity', 'check')
            //     ->max('last_synced_at') ?? '2000-01-01T00:00:00Z';

            // $this->info("Fetching checks updated after: $lastSyncTime");

            // $start = 1;
            // $pageSize = 100;

            // do {
            //     $query = "SELECT * FROM Check 
            //           WHERE MetaData.LastUpdatedTime > '$lastSyncTime' 
            //           STARTPOSITION $start MAXRESULTS $pageSize";

            //     $checks = $dataService->Query($query);

            //     if (!$checks) {
            //         break;
            //     }

            //     foreach ($checks as $check) {
            //         Check::updateOrCreate(
            //             ['qbo_id' => $check->Id],
            //             [
            //                 'payee' => $check->PayeeRef->name ?? null,
            //                 'amount' => $check->TotalAmt,
            //                 'txn_date' => $check->TxnDate,
            //                 'memo' => $check->PrivateNote ?? null,
            //                 'status' => $check->ClearedStatus ?? 'Unknown',
            //             ]
            //         );
            //     }

            //     $this->info("Fetched " . count($checks) . " updated checks...");

            //     $start += $pageSize;

            // } while (count($checks) == $pageSize);

            // // Update sync log
            // DB::table('sync_logs')->updateOrInsert(
            //     ['entity' => 'check'],
            //     ['last_synced_at' => now()->toIso8601String()]
            // );

            // $this->info("✅ Sync finished!");
            return redirect()->route('qbo.getCompanies')->with('success', 'Synced successfully');
        }

    }

}
