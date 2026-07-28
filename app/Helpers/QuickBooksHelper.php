<?php

namespace App\Helpers;

use Illuminate\Http\Request;
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Facades\Vendor as QBOVendor;

class QuickBooksHelper
{
 private static function getDataService()
{
    $accessToken = session('qbo_access_token');
    $refreshToken = session('qbo_refresh_token');
    $realmId = session('qbo_realm_id');

    if (!$accessToken || !$realmId) {
        throw new \Exception("QuickBooks not connected or token missing.");
    }

    return DataService::Configure([
        'auth_mode'       => 'oauth2',
        'ClientID'        => env('QBO_CLIENT_ID'),
        'ClientSecret'    => env('QBO_CLIENT_SECRET'),
        'RedirectURI'     => env('QBO_REDIRECT_URI'),
        'scope'           => env('QBO_SCOPE', 'com.intuit.quickbooks.accounting'),
        'baseUrl'         => env('QBO_ENVIRONMENT') === 'development' ? "development" : "production",
        'accessTokenKey'  => $accessToken,
        'refreshTokenKey' => $refreshToken,
        'QBORealmID'      => $realmId
    ]);
}

    public static function createVendor($data)
{
    try {
        $dataService = self::getDataService();

        // Create vendor object
        $vendorData = QBOVendor::create($data);

        // Send to QuickBooks
        $result = $dataService->Add($vendorData);

        if ($error = $dataService->getLastError()) {
            $xml = simplexml_load_string($error->getResponseBody());
            $fault = $xml->Fault->Error;
            return[
                'status' => false, 
                'error' => (string) $fault->Message . ' - ' . (string) $fault?->Message . ' - ' . (string) $fault->Detail
            ];
        }

        return [
            'status' => true,
            'data' => $result
        ];

    } catch (\Exception $e) {
        return[
            'status' => false,
            'error' => $e->getMessage()
        ];
    }
}
}
