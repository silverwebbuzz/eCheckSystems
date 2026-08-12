<?php

namespace App\Services;

use App\Models\CheckLineItem;
use App\Models\Checks;
use App\Models\Payors;
use App\Models\QBOCompany;
use App\Models\QboSyncLog;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Facades\Purchase;

class QuickBooksService
{
    public function configureDataService(?string $accessToken = null, ?string $refreshToken = null, ?string $realmId = null): DataService
    {
        $config = [
            'auth_mode' => 'oauth2',
            'ClientID' => config('quickbooks.client_id'),
            'ClientSecret' => config('quickbooks.client_secret'),
            'RedirectURI' => config('quickbooks.redirect_uri'),
            'scope' => config('quickbooks.scope'),
            'baseUrl' => config('quickbooks.environment') === 'development' ? 'development' : 'production',
        ];

        if ($accessToken && $realmId) {
            $config['accessTokenKey'] = $accessToken;
            $config['refreshTokenKey'] = $refreshToken;
            $config['QBORealmID'] = $realmId;
        }

        return DataService::Configure($config);
    }

    public function getAuthorizationUrl(): string
    {
        $dataService = $this->configureDataService();
        return $dataService->getOAuth2LoginHelper()->getAuthorizationCodeURL();
    }

    public function refreshTokensIfNeeded(QBOCompany $qboCompany): QBOCompany
    {
        if (!$qboCompany->access_token_expires_at || now()->lt($qboCompany->access_token_expires_at->subMinutes(2))) {
            return $qboCompany;
        }

        $dataService = $this->configureDataService(
            $qboCompany->access_token,
            $qboCompany->refresh_token,
            $qboCompany->realm_id
        );

        $oauth2LoginHelper = $dataService->getOAuth2LoginHelper();
        $newAccessTokenObj = $oauth2LoginHelper->refreshToken();

        $qboCompany->update([
            'access_token' => $newAccessTokenObj->getAccessToken(),
            'refresh_token' => $newAccessTokenObj->getRefreshToken(),
            'access_token_expires_at' => date('Y-m-d H:i:s', strtotime($newAccessTokenObj->getAccessTokenExpiresAt())),
            'refresh_token_expires_at' => date('Y-m-d H:i:s', strtotime($newAccessTokenObj->getRefreshTokenExpiresAt())),
        ]);

        return $qboCompany->fresh();
    }

    public function dataServiceForCompany(QBOCompany $qboCompany): DataService
    {
        $qboCompany = $this->refreshTokensIfNeeded($qboCompany);

        return $this->configureDataService(
            $qboCompany->access_token,
            $qboCompany->refresh_token,
            $qboCompany->realm_id
        );
    }

    public function activeCompanyForUser(int $userId): ?QBOCompany
    {
        return QBOCompany::forUser($userId)->active()->first();
    }

    /**
     * Fetch Chart of Accounts for mapping UI (Bank + Expense).
     */
    public function fetchAccounts(QBOCompany $qboCompany): array
    {
        $dataService = $this->dataServiceForCompany($qboCompany);
        $accounts = $dataService->Query("SELECT * FROM Account MAXRESULTS 1000");

        if ($error = $dataService->getLastError()) {
            throw new Exception($error->getResponseBody() ?: $error->getOAuthHelperError());
        }

        $bank = [];
        $expense = [];

        foreach ($accounts ?: [] as $account) {
            $item = [
                'id' => (string) ($account->Id ?? ''),
                'name' => (string) ($account->FullyQualifiedName ?? $account->Name ?? ''),
                'type' => (string) ($account->AccountType ?? ''),
                'sub_type' => (string) ($account->AccountSubType ?? ''),
            ];

            $type = strtolower($item['type']);
            if (in_array($type, ['bank', 'credit card'], true)) {
                $bank[] = $item;
            }
            if (in_array($type, ['expense', 'cost of goods sold', 'other expense'], true) || str_contains(strtolower($item['sub_type']), 'expense')) {
                $expense[] = $item;
            }
        }

        return compact('bank', 'expense');
    }

    /**
     * Sync all QBO Checks (Purchase PaymentType=Check) into local Checks.
     */
    public function syncChecksFromQbo(QBOCompany $qboCompany, int $userId): array
    {
        $dataService = $this->dataServiceForCompany($qboCompany);
        $imported = 0;
        $updated = 0;
        $warnings = [];

        $start = 1;
        $pageSize = 100;

        do {
            $query = "SELECT * FROM Purchase WHERE PaymentType = 'Check' STARTPOSITION {$start} MAXRESULTS {$pageSize}";
            $purchases = $dataService->Query($query);

            if ($error = $dataService->getLastError()) {
                throw new Exception($error->getResponseBody() ?: 'Failed to query QBO checks');
            }

            if (!$purchases) {
                break;
            }

            foreach ($purchases as $purchase) {
                $result = $this->upsertLocalCheckFromQboPurchase($purchase, $qboCompany, $userId, $dataService);
                if ($result['created']) {
                    $imported++;
                } else {
                    $updated++;
                }
                if (!empty($result['warning'])) {
                    $warnings[] = $result['warning'];
                }
            }

            $count = is_countable($purchases) ? count($purchases) : 0;
            $start += $pageSize;
        } while ($count === $pageSize);

        $qboCompany->update(['last_sync_at' => now()]);

        QboSyncLog::create([
            'user_id' => $userId,
            'qbo_company_id' => $qboCompany->id,
            'direction' => 'inbound',
            'action' => 'sync_checks',
            'status' => 'success',
            'records' => $imported + $updated,
            'message' => "Imported {$imported}, updated {$updated}",
        ]);

        return [
            'imported' => $imported,
            'updated' => $updated,
            'warnings' => $warnings,
        ];
    }

    protected function upsertLocalCheckFromQboPurchase($purchase, QBOCompany $qboCompany, int $userId, DataService $dataService): array
    {
        $qboId = (string) ($purchase->Id ?? '');
        $docNumber = (string) ($purchase->DocNumber ?? '');
        $amount = (float) ($purchase->TotalAmt ?? 0);
        $txnDate = $purchase->TxnDate ?? now()->toDateString();
        $memo = (string) ($purchase->PrivateNote ?? '');
        $printStatus = (string) ($purchase->PrintStatus ?? '');
        $printLater = in_array($printStatus, ['NeedToPrint', 'Pending'], true);

        $payeeId = $this->resolveOrCreatePayee($purchase, $userId, $dataService);

        $existing = Checks::where('UserID', $userId)->where('qbo_id', $qboId)->first();

        $conflict = false;
        if ($docNumber !== '') {
            $conflictQuery = Checks::where('UserID', $userId)
                ->where('CheckNumber', $docNumber);
            if ($existing) {
                $conflictQuery->where('CheckID', '!=', $existing->CheckID);
            }
            $conflict = $conflictQuery->exists();
        }

        $payload = [
            'UserID' => $userId,
            'PayeeID' => $payeeId,
            'CheckType' => 'QuickBooks',
            'Amount' => $amount,
            'ServiceFees' => 0,
            'Total' => $amount,
            'CheckNumber' => $docNumber !== '' ? $docNumber : ('QBO-' . $qboId),
            'IssueDate' => Carbon::parse($txnDate)->format('Y-m-d H:i:s'),
            'ExpiryDate' => Carbon::parse($txnDate)->addYear()->format('Y-m-d'),
            'Memo' => $memo,
            'qbo_id' => $qboId,
            'qbo_sync_status' => 'imported',
            'qbo_print_later' => $printLater,
            'qbo_company_id' => $qboCompany->id,
            'qbo_doc_number' => $docNumber,
            'check_number_conflict' => $conflict,
            'is_seen' => 0,
        ];

        // Keep generated status if user already generated locally; otherwise imported_from_qbo (draft-like)
        if (!$existing || $existing->Status !== 'generated') {
            $payload['Status'] = 'imported_from_qbo';
        }

        if ($existing) {
            $existing->update($payload);
            $check = $existing;
            $created = false;
        } else {
            $payload['created_at'] = now();
            $check = Checks::create($payload);
            $created = true;
        }

        $this->syncLineItemsFromPurchase($check, $purchase);

        return [
            'created' => $created,
            'warning' => $conflict ? "Check #{$docNumber} already exists locally (QBO Id {$qboId})" : null,
        ];
    }

    protected function resolveOrCreatePayee($purchase, int $userId, DataService $dataService): ?int
    {
        $entityRef = $purchase->EntityRef ?? null;
        if (!$entityRef) {
            return null;
        }

        $vendorId = is_object($entityRef) ? ($entityRef->value ?? null) : null;
        $vendorName = is_object($entityRef) ? ($entityRef->name ?? null) : null;
        $email = null;
        $address1 = null;
        $city = null;
        $state = null;
        $zip = null;

        if ($vendorId) {
            try {
                $vendor = $dataService->FindById('Vendor', $vendorId);
                if ($vendor) {
                    $vendorName = $vendor->DisplayName ?? $vendorName;
                    $email = $vendor->PrimaryEmailAddr->Address ?? null;
                    $addr = $vendor->BillAddr ?? null;
                    if ($addr) {
                        $address1 = $addr->Line1 ?? null;
                        $city = $addr->City ?? null;
                        $state = $addr->CountrySubDivisionCode ?? null;
                        $zip = $addr->PostalCode ?? null;
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('QBO vendor fetch failed: ' . $e->getMessage());
            }
        }

        if (!$vendorName) {
            return null;
        }

        $query = Payors::where('UserID', $userId)->where('Type', 'Payee');
        $payee = (clone $query)->where('Name', $vendorName)->first();

        if (!$payee && $email) {
            $payee = (clone $query)->where('Email', $email)->first();
        }

        if ($payee) {
            $payee->update(array_filter([
                'Email' => $email ?: $payee->Email,
                'Address1' => $address1 ?: $payee->Address1,
                'City' => $city ?: $payee->City,
                'State' => $state ?: $payee->State,
                'Zip' => $zip ?: $payee->Zip,
            ], fn ($v) => $v !== null && $v !== ''));

            return $payee->EntityID;
        }

        $payee = Payors::create([
            'UserID' => $userId,
            'Name' => $vendorName,
            'Type' => 'Payee',
            'Email' => $email,
            'Address1' => $address1,
            'City' => $city,
            'State' => $state,
            'Zip' => $zip,
            'Status' => 'Active',
            'CreatedAt' => now(),
            'UpdatedAt' => now(),
        ]);

        return $payee->EntityID;
    }

    protected function syncLineItemsFromPurchase(Checks $check, $purchase): void
    {
        CheckLineItem::where('CheckID', $check->CheckID)->where('source', 'qbo')->delete();

        $lines = $purchase->Line ?? [];
        if (!is_array($lines) && !($lines instanceof \Traversable)) {
            $lines = $lines ? [$lines] : [];
        }

        $lineNo = 1;
        foreach ($lines as $line) {
            $detail = $line->AccountBasedExpenseLineDetail ?? null;
            if (!$detail && empty($line->Amount)) {
                continue;
            }

            $accountRef = $detail->AccountRef ?? null;

            CheckLineItem::create([
                'CheckID' => $check->CheckID,
                'line_no' => $lineNo++,
                'qbo_line_id' => isset($line->Id) ? (string) $line->Id : null,
                'qbo_account_id' => is_object($accountRef) ? (string) ($accountRef->value ?? '') : null,
                'account_name' => is_object($accountRef) ? (string) ($accountRef->name ?? '') : null,
                'description' => (string) ($line->Description ?? ''),
                'amount' => (float) ($line->Amount ?? 0),
                'billable' => isset($detail->BillableStatus) && $detail->BillableStatus === 'Billable',
                'tax' => false,
                'customer_name' => isset($detail->CustomerRef) ? (string) ($detail->CustomerRef->name ?? '') : null,
                'customer_ref' => isset($detail->CustomerRef) ? (string) ($detail->CustomerRef->value ?? '') : null,
                'source' => 'qbo',
            ]);
        }
    }

    /**
     * Push a local check to QBO as a Check (Purchase PaymentType=Check).
     * Called on generate.
     */
    public function pushCheckToQbo(Checks $check, QBOCompany $qboCompany): Checks
    {
        if (!$qboCompany->default_bank_account_id) {
            throw new Exception('Please set a default bank account in Settings → QuickBooks before pushing checks.');
        }

        $dataService = $this->dataServiceForCompany($qboCompany);
        $lines = $this->buildQboLines($check, $qboCompany);

        $payload = [
            'PaymentType' => 'Check',
            'AccountRef' => [
                'value' => $qboCompany->default_bank_account_id,
                'name' => $qboCompany->default_bank_account_name,
            ],
            'TotalAmt' => (float) $check->Total,
            'TxnDate' => Carbon::parse($check->IssueDate)->toDateString(),
            'DocNumber' => (string) $check->CheckNumber,
            'PrivateNote' => (string) ($check->Memo ?? ''),
            'PrintStatus' => $check->qbo_print_later ? 'NeedToPrint' : 'NotSet',
            'Line' => $lines,
        ];

        if ($check->PayeeID) {
            $payee = Payors::find($check->PayeeID);
            if ($payee) {
                $vendorId = $this->findOrCreateVendor($dataService, $payee);
                if ($vendorId) {
                    $payload['EntityRef'] = [
                        'value' => $vendorId,
                        'type' => 'Vendor',
                    ];
                }
            }
        }

        if ($check->qbo_id) {
            $existing = $dataService->FindById('Purchase', $check->qbo_id);
            if ($existing) {
                $payload['Id'] = $check->qbo_id;
                $payload['SyncToken'] = $existing->SyncToken;
                $resource = Purchase::update($existing, $payload);
                $result = $dataService->Update($resource);
            } else {
                $resource = Purchase::create($payload);
                $result = $dataService->Add($resource);
            }
        } else {
            $resource = Purchase::create($payload);
            $result = $dataService->Add($resource);
        }

        if ($error = $dataService->getLastError()) {
            throw new Exception($error->getResponseBody() ?: 'Failed to push check to QuickBooks');
        }

        $check->update([
            'qbo_id' => (string) ($result->Id ?? $check->qbo_id),
            'qbo_sync_status' => 'pushed',
            'qbo_company_id' => $qboCompany->id,
            'qbo_doc_number' => (string) ($result->DocNumber ?? $check->CheckNumber),
        ]);

        QboSyncLog::create([
            'user_id' => $check->UserID,
            'qbo_company_id' => $qboCompany->id,
            'direction' => 'outbound',
            'action' => $check->qbo_id ? 'update_check' : 'push_check',
            'status' => 'success',
            'records' => 1,
            'message' => 'Check ' . $check->CheckID . ' synced to QBO Id ' . ($result->Id ?? ''),
        ]);

        return $check->fresh();
    }

    protected function buildQboLines(Checks $check, QBOCompany $qboCompany): array
    {
        $items = $check->lineItems()->get();
        $lines = [];
        $n = 1;

        if ($items->isNotEmpty()) {
            foreach ($items as $item) {
                $accountId = $item->qbo_account_id ?: $qboCompany->default_expense_account_id;
                if (!$accountId) {
                    throw new Exception('Missing expense account for line item. Set a default expense account in Settings → QuickBooks.');
                }

                $lines[] = [
                    'Id' => (string) $n,
                    'Amount' => (float) $item->amount,
                    'DetailType' => 'AccountBasedExpenseLineDetail',
                    'Description' => (string) ($item->description ?: $item->account_name),
                    'AccountBasedExpenseLineDetail' => [
                        'AccountRef' => [
                            'value' => $accountId,
                            'name' => $item->account_name ?: $qboCompany->default_expense_account_name,
                        ],
                    ],
                ];
                $n++;
            }

            return $lines;
        }

        if (!$qboCompany->default_expense_account_id) {
            throw new Exception('Please set a default expense account in Settings → QuickBooks.');
        }

        return [[
            'Id' => '1',
            'Amount' => (float) $check->Total,
            'DetailType' => 'AccountBasedExpenseLineDetail',
            'Description' => (string) ($check->Memo ?: 'Check from Echeck Systems'),
            'AccountBasedExpenseLineDetail' => [
                'AccountRef' => [
                    'value' => $qboCompany->default_expense_account_id,
                    'name' => $qboCompany->default_expense_account_name,
                ],
            ],
        ]];
    }

    protected function findOrCreateVendor(DataService $dataService, Payors $payee): ?string
    {
        $name = addslashes($payee->Name);
        $existing = $dataService->Query("SELECT * FROM Vendor WHERE DisplayName = '{$name}' MAXRESULTS 1");
        if ($existing && isset($existing[0]->Id)) {
            return (string) $existing[0]->Id;
        }

        $vendorData = [
            'DisplayName' => $payee->Name,
            'PrimaryEmailAddr' => ['Address' => $payee->Email],
            'BillAddr' => array_filter([
                'Line1' => $payee->Address1,
                'City' => $payee->City,
                'CountrySubDivisionCode' => $payee->State,
                'PostalCode' => $payee->Zip,
            ]),
        ];

        $vendor = \QuickBooksOnline\API\Facades\Vendor::create($vendorData);
        $result = $dataService->Add($vendor);

        if ($dataService->getLastError()) {
            Log::warning('QBO vendor create failed', ['body' => $dataService->getLastError()->getResponseBody()]);
            return null;
        }

        return isset($result->Id) ? (string) $result->Id : null;
    }

    public function deleteCheckInQbo(Checks $check, QBOCompany $qboCompany): void
    {
        if (!$check->qbo_id) {
            return;
        }

        $dataService = $this->dataServiceForCompany($qboCompany);
        $existing = $dataService->FindById('Purchase', $check->qbo_id);
        if (!$existing) {
            return;
        }

        $dataService->Delete($existing);

        if ($error = $dataService->getLastError()) {
            throw new Exception($error->getResponseBody() ?: 'Failed to delete QBO check');
        }

        QboSyncLog::create([
            'user_id' => $check->UserID,
            'qbo_company_id' => $qboCompany->id,
            'direction' => 'outbound',
            'action' => 'delete_check',
            'status' => 'success',
            'records' => 1,
            'message' => 'Deleted QBO Id ' . $check->qbo_id,
        ]);
    }

    public function markPrintedInQbo(Checks $check, QBOCompany $qboCompany): void
    {
        if (!$check->qbo_id) {
            return;
        }

        $dataService = $this->dataServiceForCompany($qboCompany);
        $existing = $dataService->FindById('Purchase', $check->qbo_id);
        if (!$existing) {
            return;
        }

        $resource = Purchase::update($existing, [
            'Id' => $check->qbo_id,
            'SyncToken' => $existing->SyncToken,
            'PrintStatus' => 'PrintComplete',
        ]);
        $dataService->Update($resource);

        if ($error = $dataService->getLastError()) {
            throw new Exception($error->getResponseBody() ?: 'Failed to mark QBO check printed');
        }

        $check->update(['qbo_print_later' => false]);

        QboSyncLog::create([
            'user_id' => $check->UserID,
            'qbo_company_id' => $qboCompany->id,
            'direction' => 'outbound',
            'action' => 'mark_printed',
            'status' => 'success',
            'records' => 1,
            'message' => 'Marked PrintComplete for QBO Id ' . $check->qbo_id,
        ]);
    }
}
