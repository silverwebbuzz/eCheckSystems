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
    /** @var array<string, string> */
    protected array $accountNameCache = [];

    /** @var array<string, string> */
    protected array $accountTypeCache = [];

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
            if (!$this->isActiveQboAccount($account)) {
                continue;
            }

            $item = [
                'id' => (string) ($account->Id ?? ''),
                'name' => (string) ($account->FullyQualifiedName ?? $account->Name ?? ''),
                'type' => (string) ($account->AccountType ?? ''),
                'sub_type' => (string) ($account->AccountSubType ?? ''),
            ];

            if ($item['id'] === '') {
                continue;
            }

            $type = strtolower($item['type']);
            if (in_array($type, ['bank', 'credit card'], true)) {
                $bank[] = $item;
            }
            if ($this->isExpenseAccountType($type)) {
                $expense[] = $item;
            }
        }

        return compact('bank', 'expense');
    }

    /**
     * Sync QBO Checks only (Purchase PaymentType=Check). Cash / credit-card purchases are skipped.
     * QBO query often ignores PaymentType filters, so we always verify in PHP.
     */
    public function syncChecksFromQbo(QBOCompany $qboCompany, int $userId): array
    {
        $dataService = $this->dataServiceForCompany($qboCompany);
        $imported = 0;
        $updated = 0;
        $skipped = 0;
        $warnings = [];

        $start = 1;
        $pageSize = 100;

        do {
            $query = "SELECT * FROM Purchase STARTPOSITION {$start} MAXRESULTS {$pageSize}";
            $purchases = $dataService->Query($query);

            if ($error = $dataService->getLastError()) {
                throw new Exception($error->getResponseBody() ?: 'Failed to query QBO purchases');
            }

            if (!$purchases) {
                break;
            }

            foreach ($purchases as $purchase) {
                if (!$this->isCheckPurchase($purchase, $dataService)) {
                    $skipped++;
                    continue;
                }

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
            'message' => "Imported {$imported}, updated {$updated}, skipped {$skipped} non-check purchases",
        ]);

        return [
            'imported' => $imported,
            'updated' => $updated,
            'skipped' => $skipped,
            'warnings' => $warnings,
        ];
    }

    /**
     * Only printable bank checks: PaymentType=Check, paid from a Bank account.
     * Skips Credit Card Expense, Cash, and bank Debit/ACH recorded as Check.
     */
    protected function isCheckPurchase($purchase, ?DataService $dataService = null): bool
    {
        $type = $purchase->PaymentType ?? '';
        if (is_object($type)) {
            $type = $type->value ?? $type->name ?? (string) $type;
        }
        if (strcasecmp(trim((string) $type), 'Check') !== 0) {
            return false;
        }

        $doc = trim((string) ($purchase->DocNumber ?? ''));
        $printStatus = (string) ($purchase->PrintStatus ?? '');
        $printLater = in_array($printStatus, ['NeedToPrint', 'Pending'], true);
        if ($doc === '') {
            if (!$printLater) {
                return false;
            }
        } elseif (!preg_match('/^\d+$/', $doc)) {
            return false;
        }

        if ($dataService) {
            $accountRef = $this->parseQboRef($purchase->AccountRef ?? null);
            if (!empty($accountRef['id']) && !$this->isBankAccount($dataService, $accountRef['id'])) {
                return false;
            }
        }

        return true;
    }

    protected function isBankAccount(DataService $dataService, string $accountId): bool
    {
        if (isset($this->accountTypeCache[$accountId])) {
            return $this->accountTypeCache[$accountId] === 'bank';
        }

        try {
            $account = $dataService->FindById('Account', $accountId);
            $type = strtolower((string) ($account->AccountType ?? ''));
            $this->accountTypeCache[$accountId] = $type;

            return $type === 'bank';
        } catch (\Throwable $e) {
            Log::warning('QBO account type resolve failed: ' . $e->getMessage(), ['account_id' => $accountId]);
            $this->accountTypeCache[$accountId] = '';

            return false;
        }
    }

    /**
     * Drop a previously imported non-check (e.g. credit card expense) if it is still a draft import.
     */
    protected function removeNonCheckImport($purchase, int $userId): void
    {
        $qboId = (string) ($purchase->Id ?? '');
        if ($qboId === '') {
            return;
        }

        $check = Checks::where('UserID', $userId)
            ->where('qbo_id', $qboId)
            ->where('Status', 'imported_from_qbo')
            ->first();

        if (!$check) {
            return;
        }

        CheckLineItem::where('CheckID', $check->CheckID)->delete();
        $check->delete();
    }

    protected function upsertLocalCheckFromQboPurchase($purchase, QBOCompany $qboCompany, int $userId, DataService $dataService): array
    {
        if (!$this->isCheckPurchase($purchase, $dataService)) {
            
            return ['created' => false, 'warning' => null, 'skipped' => true];
        }

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

        $this->syncLineItemsFromPurchase($check, $purchase, $dataService);

        return [
            'created' => $created,
            'warning' => $conflict ? "Check #{$docNumber} already exists locally (QBO Id {$qboId})" : null,
        ];
    }

    /**
     * QBO SDK often returns refs as plain id strings; sometimes as IPPReferenceType objects.
     *
     * @param mixed $ref
     * @return array{id: ?string, name: ?string, type: ?string}
     */
    protected function parseQboRef($ref): array
    {
        if ($ref === null || $ref === '') {
            return ['id' => null, 'name' => null, 'type' => null];
        }

        if (is_string($ref) || is_int($ref) || is_float($ref)) {
            return ['id' => (string) $ref, 'name' => null, 'type' => null];
        }

        if (is_array($ref)) {
            $id = $ref['value'] ?? $ref['Value'] ?? $ref['id'] ?? null;
            $name = $ref['name'] ?? $ref['Name'] ?? null;
            $type = $ref['type'] ?? $ref['Type'] ?? null;

            return [
                'id' => $id !== null && $id !== '' ? (string) $id : null,
                'name' => $name !== null && $name !== '' ? (string) $name : null,
                'type' => $type !== null && $type !== '' ? (string) $type : null,
            ];
        }

        if (is_object($ref)) {
            $id = $ref->value ?? $ref->Value ?? null;
            $name = $ref->name ?? $ref->Name ?? null;
            $type = $ref->type ?? $ref->Type ?? null;

            if (($id === null || $id === '') && method_exists($ref, '__toString')) {
                $asString = trim((string) $ref);
                if ($asString !== '' && $asString !== 'Object') {
                    $id = $asString;
                }
            }

            return [
                'id' => $id !== null && $id !== '' ? (string) $id : null,
                'name' => $name !== null && $name !== '' ? (string) $name : null,
                'type' => $type !== null && $type !== '' ? (string) $type : null,
            ];
        }

        return ['id' => null, 'name' => null, 'type' => null];
    }

    protected function resolveOrCreatePayee($purchase, int $userId, DataService $dataService): ?int
    {
        $parsed = $this->parseQboRef($purchase->EntityRef ?? null);
        $entityId = $parsed['id'];
        $entityName = $parsed['name'];
        $entityType = strtolower((string) ($parsed['type'] ?: 'vendor'));
        $email = null;
        $address1 = null;
        $city = null;
        $state = null;
        $zip = null;

        if (!$entityId && !$entityName) {
            return null;
        }

        if ($entityId) {
            $lookupTypes = array_values(array_unique(array_filter([
                $entityType ?: null,
                'vendor',
                'customer',
                'employee',
            ])));

            foreach ($lookupTypes as $type) {
                $apiEntity = match ($type) {
                    'customer' => 'Customer',
                    'employee' => 'Employee',
                    default => 'Vendor',
                };

                try {
                    $remote = $dataService->FindById($apiEntity, $entityId);
                    if ($remote) {
                        $resolved = $remote->DisplayName
                            ?? $remote->FullyQualifiedName
                            ?? trim((string) (($remote->GivenName ?? '') . ' ' . ($remote->FamilyName ?? '')));
                        if (is_string($resolved) && trim($resolved) !== '') {
                            $entityName = trim($resolved);
                        }

                        $emailAddr = $remote->PrimaryEmailAddr ?? null;
                        if (is_object($emailAddr) && !empty($emailAddr->Address)) {
                            $email = (string) $emailAddr->Address;
                        }

                        $addr = $remote->BillAddr ?? $remote->PrimaryAddr ?? null;
                        if (is_object($addr)) {
                            $address1 = $addr->Line1 ?? $address1;
                            $city = $addr->City ?? $city;
                            $state = $addr->CountrySubDivisionCode ?? $state;
                            $zip = $addr->PostalCode ?? $zip;
                        }
                        break;
                    }
                } catch (\Throwable $e) {
                    Log::warning("QBO {$apiEntity} fetch failed: " . $e->getMessage());
                }
            }
        }

        if (!$entityName && $entityId) {
            $entityName = 'QBO Payee #' . $entityId;
        }

        if (!$entityName) {
            return null;
        }

        $query = Payors::where('UserID', $userId)->where('Type', 'Payee');
        $payee = (clone $query)->where('Name', $entityName)->first();

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
                'Category' => $payee->Category ?: 'SP',
                'Status' => $payee->Status ?: 'Active',
            ], fn ($v) => $v !== null && $v !== ''));

            return $payee->EntityID;
        }

        $payee = Payors::create([
            'UserID' => $userId,
            'Name' => $entityName,
            'Type' => 'Payee',
            'Category' => 'SP',
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

    protected function syncLineItemsFromPurchase(Checks $check, $purchase, DataService $dataService): void
    {
        CheckLineItem::where('CheckID', $check->CheckID)->delete();

        $lines = $purchase->Line ?? [];
        if (!is_array($lines) && !($lines instanceof \Traversable)) {
            $lines = $lines ? [$lines] : [];
        }

        $lineNo = 1;
        foreach ($lines as $line) {
            $detailType = (string) ($line->DetailType ?? '');
            if ($detailType === 'SubTotalLineDetail') {
                continue;
            }

            $accountDetail = $line->AccountBasedExpenseLineDetail ?? null;
            $itemDetail = $line->ItemBasedExpenseLineDetail ?? null;

            if (!$accountDetail && !$itemDetail) {
                continue;
            }

            $accountRef = $this->parseQboRef($accountDetail?->AccountRef ?? null);
            $itemRef = $this->parseQboRef($itemDetail?->ItemRef ?? null);

            $accountId = $accountRef['id'] ?? null;
            $accountName = $accountRef['name'] ?? '';

            if ($accountId && $accountName === '') {
                $accountName = $this->resolveAccountName($dataService, $accountId);
            }

            if ($accountName === '' && !empty($itemRef['name'])) {
                $accountName = $itemRef['name'];
            }
            $detail = $accountDetail ?: $itemDetail;
            $customerRef = $this->parseQboRef($detail->CustomerRef ?? null);

            CheckLineItem::create([
                'CheckID' => $check->CheckID,
                'line_no' => $lineNo++,
                'qbo_line_id' => isset($line->Id) ? (string) $line->Id : null,
                'qbo_account_id' => $accountId ?: null,
                'account_name' => $accountName !== '' ? $accountName : null,
                'description' => (string) ($line->Description ?? ''),
                'amount' => (float) ($line->Amount ?? 0),
                'billable' => isset($detail->BillableStatus) && $detail->BillableStatus === 'Billable',
                'tax' => false,
                'customer_name' => $customerRef['name'] ?? null,
                'customer_ref' => $customerRef['id'] ?? null,
                'source' => 'qbo',
            ]);
        }
    }

    protected function resolveAccountName(DataService $dataService, string $accountId): string
    {
        if (isset($this->accountNameCache[$accountId])) {
            return $this->accountNameCache[$accountId];
        }

        try {
            $account = $dataService->FindById('Account', $accountId);
            $name = '';
            if ($account) {
                $name = (string) ($account->FullyQualifiedName ?? $account->Name ?? '');
            }
            $this->accountNameCache[$accountId] = $name;

            return $name;
        } catch (\Throwable $e) {
            Log::warning('QBO account name resolve failed: ' . $e->getMessage(), ['account_id' => $accountId]);
            $this->accountNameCache[$accountId] = '';

            return '';
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
        $bankAccount = $this->verifyBankAccountId($dataService, (string) $qboCompany->default_bank_account_id);
        if (!$bankAccount) {
            throw new Exception('Default bank account is no longer valid in QuickBooks. Please reselect it in Settings → QuickBooks.');
        }

        $isUpdate = (bool) $check->qbo_id;
        $lines = $this->buildQboLines($check, $qboCompany, $dataService, $isUpdate);

        $payload = [
            'PaymentType' => 'Check',
            'AccountRef' => [
                'value' => $bankAccount['id'],
                'name' => $bankAccount['name'],
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

    /**
     * Normalize and validate Send Payment category rows before saving locally.
     *
     * @return array{0: array<int, array<string, mixed>>, 1: float}
     */
    public function prepareLocalCheckLineItems(array $rawLines, ?QBOCompany $qboCompany): array
    {
        $rows = [];
        $lineNo = 1;
        $total = 0.0;

        foreach ($rawLines as $line) {
            $accountId = trim((string) ($line['qbo_account_id'] ?? ''));
            $accountName = trim((string) ($line['account_name'] ?? ''));
            $description = trim((string) ($line['description'] ?? ''));
            $amount = $this->normalizeMoneyValue($line['amount'] ?? null);

            $isBlank = $accountId === '' && $accountName === '' && $description === '' && $amount <= 0;
            if ($isBlank || $amount <= 0) {
                continue;
            }

            if ($accountId === '') {
                throw new Exception('Each Category detail row with an amount must have a Category selected. Incomplete rows are not saved as the default expense account.');
            }

            if ($qboCompany) {
                $dataService = $this->dataServiceForCompany($qboCompany);
                $resolved = $this->resolveExpenseAccountRef(
                    $dataService,
                    $qboCompany,
                    $accountId,
                    $accountName !== '' ? $accountName : null,
                    false
                );
                $accountId = $resolved['id'];
                $accountName = $resolved['name'];
            }

            $rows[] = [
                'line_no' => $lineNo++,
                'qbo_account_id' => $accountId,
                'account_name' => $accountName !== '' ? $accountName : null,
                'description' => $description !== '' ? $description : null,
                'amount' => $amount,
            ];

            $total = round($total + $amount, 2);
        }

        return [$rows, $total];
    }

    protected function buildQboLines(Checks $check, QBOCompany $qboCompany, DataService $dataService, bool $isUpdate = false): array
    {
        $items = $check->lineItems()->get();
        $lines = [];

        if ($items->isNotEmpty()) {
            foreach ($items as $item) {
                $resolved = $this->resolveExpenseAccountRef(
                    $dataService,
                    $qboCompany,
                    $item->qbo_account_id ? (string) $item->qbo_account_id : null,
                    $item->account_name ? (string) $item->account_name : null,
                    false
                );

                $linePayload = [
                    'Amount' => (float) $item->amount,
                    'DetailType' => 'AccountBasedExpenseLineDetail',
                    'Description' => (string) ($item->description ?: $resolved['name']),
                    'AccountBasedExpenseLineDetail' => [
                        'AccountRef' => [
                            'value' => $resolved['id'],
                        ],
                    ],
                ];

                if ($isUpdate && !empty($item->qbo_line_id)) {
                    $linePayload['Id'] = (string) $item->qbo_line_id;
                }

                $lines[] = $linePayload;
            }

            return $lines;
        }

        $resolved = $this->resolveExpenseAccountRef($dataService, $qboCompany, null, null, true);
        $fallbackLine = [
            'Amount' => (float) $check->Total,
            'DetailType' => 'AccountBasedExpenseLineDetail',
            'Description' => (string) ($check->Memo ?: 'Check from Echeck Systems'),
            'AccountBasedExpenseLineDetail' => [
                'AccountRef' => [
                    'value' => $resolved['id'],
                ],
            ],
        ];

        if ($isUpdate) {
            $existingLineId = $items->first()?->qbo_line_id;
            if ($existingLineId) {
                $fallbackLine['Id'] = (string) $existingLineId;
            }
        }

        return [$fallbackLine];
    }

    protected function resolveExpenseAccountRef(
        DataService $dataService,
        QBOCompany $qboCompany,
        ?string $accountId,
        ?string $accountName,
        bool $allowDefault = false
    ): array {
        if ($accountId) {
            $verified = $this->verifyExpenseAccountId($dataService, $accountId);
            if ($verified) {
                return $verified;
            }
        }

        if ($accountName) {
            $match = $this->findExpenseAccountByName($qboCompany, $accountName);
            if ($match) {
                $verified = $this->verifyExpenseAccountId($dataService, $match['id']);
                if ($verified) {
                    return $verified;
                }
            }
        }

        if ($allowDefault) {
            $defaultId = $qboCompany->default_expense_account_id ? (string) $qboCompany->default_expense_account_id : null;
            if ($defaultId) {
                $verified = $this->verifyExpenseAccountId($dataService, $defaultId);
                if ($verified) {
                    return $verified;
                }
            }

            throw new Exception('Please set a valid default expense account in Settings → QuickBooks.');
        }

        $label = $accountName ?: ($accountId ?: 'unknown');
        throw new Exception("QuickBooks expense account \"{$label}\" is invalid or unavailable. Reselect Category in Send Payment or update Settings → QuickBooks.");
    }

    protected function findExpenseAccountByName(QBOCompany $qboCompany, string $accountName): ?array
    {
        $normalized = trim($accountName);
        if ($normalized === '') {
            return null;
        }

        $expenseAccounts = collect($this->fetchAccounts($qboCompany)['expense'] ?? []);

        return $expenseAccounts->first(function (array $acct) use ($normalized) {
            $candidate = (string) ($acct['name'] ?? '');
            if ($candidate === '') {
                return false;
            }

            if (strcasecmp($candidate, $normalized) === 0) {
                return true;
            }

            $shortName = str_contains($candidate, ':')
                ? trim(substr($candidate, strrpos($candidate, ':') + 1))
                : $candidate;

            return strcasecmp($shortName, $normalized) === 0;
        });
    }

    protected function verifyExpenseAccountId(DataService $dataService, string $accountId): ?array
    {
        try {
            $account = $dataService->FindById('Account', $accountId);
            if (!$account || !$this->isActiveQboAccount($account)) {
                return null;
            }

            $type = strtolower((string) ($account->AccountType ?? ''));
            if (!$this->isExpenseAccountType($type)) {
                return null;
            }

            return [
                'id' => (string) ($account->Id ?? $accountId),
                'name' => (string) ($account->FullyQualifiedName ?? $account->Name ?? ''),
            ];
        } catch (\Throwable $e) {
            Log::warning('QBO expense account verify failed: ' . $e->getMessage(), ['account_id' => $accountId]);

            return null;
        }
    }

    protected function verifyBankAccountId(DataService $dataService, string $accountId): ?array
    {
        try {
            $account = $dataService->FindById('Account', $accountId);
            if (!$account || !$this->isActiveQboAccount($account)) {
                return null;
            }

            $type = strtolower((string) ($account->AccountType ?? ''));
            if (!in_array($type, ['bank', 'credit card'], true)) {
                return null;
            }

            return [
                'id' => (string) ($account->Id ?? $accountId),
                'name' => (string) ($account->FullyQualifiedName ?? $account->Name ?? ''),
            ];
        } catch (\Throwable $e) {
            Log::warning('QBO bank account verify failed: ' . $e->getMessage(), ['account_id' => $accountId]);

            return null;
        }
    }

    protected function isActiveQboAccount($account): bool
    {
        if (!isset($account->Active)) {
            return true;
        }

        return filter_var($account->Active, FILTER_VALIDATE_BOOLEAN);
    }

    protected function isExpenseAccountType(string $type): bool
    {
        return in_array(strtolower($type), ['expense', 'cost of goods sold', 'other expense'], true);
    }

    protected function normalizeMoneyValue($value): float
    {
        if ($value === null) {
            return 0.0;
        }

        $normalized = preg_replace('/[^0-9.\-]/', '', (string) $value);
        if ($normalized === '' || $normalized === null) {
            return 0.0;
        }

        return round((float) $normalized, 2);
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

    /**
     * Verify Intuit webhook signature (HMAC-SHA256 → base64).
     * @see https://developer.intuit.com/app/developer/qbo/docs/develop/webhooks/configure-webhooks
     */
    public function verifyWebhookSignature(string $rawPayload, ?string $signature): bool
    {
        $token = config('quickbooks.webhook_verifier_token');
        if (!$token || !$signature) {
            return false;
        }

        $computed = base64_encode(hash_hmac('sha256', $rawPayload, $token, true));

        return hash_equals($computed, $signature);
    }

    /**
     * Parse Intuit webhook JSON into Purchase events for inbound queue jobs.
     * Webhooks only notify — each event is fetched later by ImportQuickBooksCheckJob.
     *
     * @return array<int, array{realmId: string, id: string, operation: string}>
     */
    public function extractWebhookPurchaseEvents(array $payload): array
    {
        $events = [];
        $allowed = array_map('strtolower', config('quickbooks.webhook_entities', ['Purchase']));

        foreach ($payload['eventNotifications'] ?? [] as $notification) {
            $realmId = (string) ($notification['realmId'] ?? '');
            if ($realmId === '') {
                continue;
            }

            foreach ($notification['dataChangeEvent']['entities'] ?? [] as $entity) {
                $name = (string) ($entity['name'] ?? '');
                $id = (string) ($entity['id'] ?? '');
                $operation = strtolower((string) ($entity['operation'] ?? ''));

                if ($id === '' || !in_array(strtolower($name), $allowed, true)) {
                    continue;
                }

                if (!in_array($operation, ['create', 'update', 'merge', 'delete', 'void'], true)) {
                    continue;
                }

                $events[] = [
                    'realmId' => $realmId,
                    'id' => $id,
                    'operation' => $operation,
                ];
            }
        }

        return $events;
    }

    /**
     * Import or delete a single QBO Purchase on the inbound queue.
     */
    public function processWebhookEntity(string $realmId, string $purchaseId, string $operation): array
    {
        $qboCompany = QBOCompany::where('realm_id', $realmId)
            ->where('status', 'connected')
            ->first();

        if (!$qboCompany) {
            Log::info('QBO webhook ignored — no connected company for realm', ['realmId' => $realmId]);
            return ['imported' => false, 'reason' => 'no_company'];
        }

        $operation = strtolower($operation);

        if (in_array($operation, ['delete', 'void'], true)) {
            $this->deleteLocalCheckByQboId($purchaseId, (int) $qboCompany->user_id);
            return ['imported' => true, 'action' => 'deleted'];
        }

        if (in_array($operation, ['create', 'update', 'merge'], true)) {
            return $this->importPurchaseById($qboCompany, $purchaseId);
        }

        return ['imported' => false, 'reason' => 'unknown_operation'];
    }

    /**
     * Fetch one Purchase by Id and import only if it is a Check.
     */
    public function importPurchaseById(QBOCompany $qboCompany, string $purchaseId): array
    {
        $dataService = $this->dataServiceForCompany($qboCompany);
        $purchase = $dataService->FindById('Purchase', $purchaseId);

        if ($error = $dataService->getLastError()) {
            throw new Exception($error->getResponseBody() ?: "Failed to fetch Purchase {$purchaseId}");
        }

        if (!$purchase) {
            return ['imported' => false, 'reason' => 'not_found'];
        }

        if (!$this->isCheckPurchase($purchase, $dataService)) {
            return ['imported' => false, 'reason' => 'not_a_check'];
        }

        $result = $this->upsertLocalCheckFromQboPurchase(
            $purchase,
            $qboCompany,
            (int) $qboCompany->user_id,
            $dataService
        );

        QboSyncLog::create([
            'user_id' => $qboCompany->user_id,
            'qbo_company_id' => $qboCompany->id,
            'direction' => 'inbound',
            'action' => 'webhook_purchase',
            'status' => 'success',
            'records' => 1,
            'message' => ($result['created'] ? 'Created' : 'Updated') . " check from QBO Purchase {$purchaseId}",
        ]);

        $qboCompany->update(['last_sync_at' => now()]);

        return ['imported' => true, 'created' => $result['created'], 'warning' => $result['warning'] ?? null];
    }

    public function deleteLocalCheckByQboId(string $qboId, int $userId): void
    {
        $check = Checks::where('UserID', $userId)->where('qbo_id', $qboId)->first();
        if (!$check) {
            return;
        }

        $qboCompanyId = $check->qbo_company_id;

        // Do not wipe locally if user already generated/printed
        if ($check->Status === 'generated') {
            $check->update([
                'qbo_sync_status' => 'deleted_in_qbo',
                'qbo_print_later' => false,
            ]);
            return;
        }

        CheckLineItem::where('CheckID', $check->CheckID)->delete();
        $check->delete();

        QboSyncLog::create([
            'user_id' => $userId,
            'qbo_company_id' => $qboCompanyId,
            'direction' => 'inbound',
            'action' => 'webhook_delete',
            'status' => 'success',
            'records' => 1,
            'message' => "Deleted local check for QBO Id {$qboId}",
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
