<?php

namespace App\Console\Commands;

use App\Helpers\Helpers;
use App\Models\Checks;
use App\Models\GridItem;
use App\Models\Payors;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use ZipArchive;

class ImportUgoFleetHistory extends Command
{
    protected $signature = 'ugofleet:import-history
        {xlsx : Path to eCheckDirect_PaymentHistory_32073.xlsx}
        {--email=JOSEPH@UGOFLEET.COM : Portal user email}
        {--dry-run : Show the plan without writing}
        {--force : Skip confirmation}';

    protected $description = 'Re-import UGOFLEET payment history from the client spreadsheet into Payments Received / Payments Sent';

    public function handle(): int
    {
        $path = $this->argument('xlsx');
        if (!is_file($path)) {
            $this->error('Spreadsheet not found: ' . $path);
            return self::FAILURE;
        }

        $email = $this->option('email');
        $user = User::where('Email', $email)->first();
        if (!$user) {
            $this->error('User not found: ' . $email);
            return self::FAILURE;
        }

        $sheets = $this->readWorkbook($path);
        if (!isset($sheets['Received']) || !isset($sheets['Payed Out'])) {
            $this->error('Workbook must contain "Received" and "Payed Out" tabs.');
            return self::FAILURE;
        }

        $received = $this->rows($sheets['Received']);
        $paidOut = $this->rows($sheets['Payed Out']);

        $this->info('User #' . $user->UserID . ' ' . $user->Email);
        $this->info('Received (Payments Received / Process Payment): ' . count($received));
        $this->info('Payed Out (Payments Sent / Make Payment): ' . count($paidOut));

        $this->table(
            ['Tab', 'Payee', 'Payor', 'Check #', 'Amount', 'Date'],
            array_merge(
                array_map(fn ($r) => ['Received', $r['PayeeName'], $r['PayorName'], $r['CheckNmbr'], $r['Amount'], $r['CheckDate']], $received),
                array_map(fn ($r) => ['Payed Out', $r['PayeeName'], $r['PayorName'], $r['CheckNmbr'], $r['Amount'], $r['CheckDate']], $paidOut)
            )
        );

        if ($this->option('dry-run')) {
            $this->warn('Dry run — no database changes.');
            return self::SUCCESS;
        }

        if (!$this->option('force') && !$this->confirm('Replace all checks for this user with spreadsheet rows?')) {
            return self::SUCCESS;
        }

        DB::transaction(function () use ($user, $received, $paidOut) {
            $payorRp = [];
            foreach ($received as $row) {
                $payorRp[$this->canon($row['PayorName'])] = $this->upsertPayor($user->UserID, $row, 'RP');
            }

            $payorSp = [];
            foreach ($paidOut as $row) {
                $payorSp[$this->canon($row['PayorName'])] = $this->upsertPayor($user->UserID, $row, 'SP');
            }

            $rpPayeeNames = array_unique(array_map(fn ($r) => $this->canon($r['PayeeName']), $received));
            $spPayeeNames = array_unique(array_map(fn ($r) => $this->canon($r['PayeeName']), $paidOut));

            $payeeRp = [];
            foreach ($received as $row) {
                $payeeRp[$this->canon($row['PayeeName'])] = $this->upsertPayee($user->UserID, $row['PayeeName'], 'RP', $spPayeeNames);
            }

            $payeeSp = [];
            foreach ($paidOut as $row) {
                $payeeSp[$this->canon($row['PayeeName'])] = $this->upsertPayee($user->UserID, $row['PayeeName'], 'SP', $rpPayeeNames);
            }

            $checkIds = Checks::where('UserID', $user->UserID)->pluck('CheckID');
            if ($checkIds->isNotEmpty()) {
                GridItem::whereIn('CheckID', $checkIds)->delete();
                Checks::where('UserID', $user->UserID)->delete();
            }

            foreach ($received as $row) {
                $this->insertCheck(
                    $user,
                    $row,
                    'Process Payment',
                    $payeeRp[$this->canon($row['PayeeName'])],
                    $payorRp[$this->canon($row['PayorName'])]
                );
            }

            foreach ($paidOut as $row) {
                $this->insertCheck(
                    $user,
                    $row,
                    'Make Payment',
                    $payeeSp[$this->canon($row['PayeeName'])],
                    $payorSp[$this->canon($row['PayorName'])]
                );
            }
        });

        $sent = Checks::where('UserID', $user->UserID)->where('CheckType', 'Make Payment')->count();
        $recv = Checks::where('UserID', $user->UserID)->where('CheckType', 'Process Payment')->count();
        $this->info("Imported {$recv} Payments Received and {$sent} Payments Sent.");

        return self::SUCCESS;
    }

    private function insertCheck(User $user, array $row, string $type, Payors $payee, Payors $payor): void
    {
        $date = Carbon::parse($row['CheckDate'])->format('Y-m-d');
        $amount = number_format((float) $row['Amount'], 2, '.', '');

        Checks::create([
            'UserID' => $user->UserID,
            'PayeeID' => $payee->EntityID,
            'CheckType' => $type,
            'Amount' => $amount,
            'ServiceFees' => 0,
            'Total' => $amount,
            'PayorID' => $payor->EntityID,
            'CheckNumber' => $row['CheckNmbr'],
            'IssueDate' => $date . ' 07:00:00',
            'ExpiryDate' => $date,
            'Status' => 'generated',
            'DigitalSignatureRequired' => !empty($row['BlankSignatureLine']) ? 1 : 0,
            'Memo' => $row['Memo'],
            'is_email_send' => 0,
            'is_seen' => 1,
            'ip_address' => $row['IpAddress'],
            'created_at' => now(),
        ]);
    }

    private function upsertPayor(int $userId, array $row, string $category): Payors
    {
        $name = $row['PayorName'];
        $canon = $this->canon($name);

        $payor = Payors::withTrashed()
            ->where('UserID', $userId)
            ->where('Type', 'Payor')
            ->where('Category', $category)
            ->get()
            ->first(fn ($entity) => $this->canon($entity->Name) === $canon);

        if (!$payor) {
            $payor = Payors::withTrashed()
                ->where('UserID', $userId)
                ->where('Type', 'Payor')
                ->get()
                ->first(fn ($entity) => $this->canon($entity->Name) === $canon);
        }

        if (!$payor) {
            $payor = new Payors();
            $payor->UserID = $userId;
            $payor->Type = 'Payor';
            $payor->CreatedAt = now();
        }

        $email = $this->looksLikeEmail($row['PayorAddress1']) ? $row['PayorAddress1'] : null;

        $payor->Name = $name;
        $payor->Address1 = $row['PayorAddress1'];
        $payor->Address2 = $row['PayorAddress2'];
        $payor->City = $row['PayorCity'];
        $payor->State = $this->expandState($row['PayorState']);
        $payor->Zip = $row['PayorZip'];
        $payor->BankName = $row['PayorBankName'];
        $payor->RoutingNumber = Helpers::normalizeRoutingNumber($row['RoutingNmbr']);
        $payor->AccountNumber = $row['AccountNmbr'];
        $payor->Email = $email;
        $payor->Status = 'Active';
        $payor->Category = $category;
        $payor->deleted_at = null;
        $payor->UpdatedAt = now();
        $payor->save();

        return $payor;
    }

    private function upsertPayee(int $userId, string $name, string $category, array $otherCategoryNames): Payors
    {
        $canon = $this->canon($name);

        $payee = Payors::withTrashed()
            ->where('UserID', $userId)
            ->where('Type', 'Payee')
            ->where('Category', $category)
            ->get()
            ->first(fn ($entity) => $this->canon($entity->Name) === $canon);

        if (!$payee && !in_array($canon, $otherCategoryNames, true)) {
            $payee = Payors::withTrashed()
                ->where('UserID', $userId)
                ->where('Type', 'Payee')
                ->get()
                ->first(fn ($entity) => $this->canon($entity->Name) === $canon);
        }

        if (!$payee) {
            $payee = new Payors();
            $payee->UserID = $userId;
            $payee->Type = 'Payee';
            $payee->CreatedAt = now();
        }

        $payee->Name = $name;
        $payee->Status = 'Active';
        $payee->Category = $category;
        $payee->deleted_at = null;
        $payee->UpdatedAt = now();
        $payee->save();

        return $payee;
    }

    private function canon(?string $name): string
    {
        $value = strtoupper(preg_replace('/[^A-Z0-9]/', '', $name ?? '') ?? '');
        $value = str_replace('FUEWATER', 'FUNWATER', $value);
        return str_replace('INC', '', $value);
    }

    private function expandState(?string $state): ?string
    {
        if ($state === null || $state === '') {
            return null;
        }

        $map = [
            'AL' => 'Alabama', 'AK' => 'Alaska', 'AZ' => 'Arizona', 'AR' => 'Arkansas',
            'CA' => 'California', 'CO' => 'Colorado', 'CT' => 'Connecticut', 'DE' => 'Delaware',
            'FL' => 'Florida', 'GA' => 'Georgia', 'HI' => 'Hawaii', 'ID' => 'Idaho',
            'IL' => 'Illinois', 'IN' => 'Indiana', 'IA' => 'Iowa', 'KS' => 'Kansas',
            'KY' => 'Kentucky', 'LA' => 'Louisiana', 'ME' => 'Maine', 'MD' => 'Maryland',
            'MA' => 'Massachusetts', 'MI' => 'Michigan', 'MN' => 'Minnesota', 'MS' => 'Mississippi',
            'MO' => 'Missouri', 'MT' => 'Montana', 'NE' => 'Nebraska', 'NV' => 'Nevada',
            'NH' => 'New Hampshire', 'NJ' => 'New Jersey', 'NM' => 'New Mexico', 'NY' => 'New York',
            'NC' => 'North Carolina', 'ND' => 'North Dakota', 'OH' => 'Ohio', 'OK' => 'Oklahoma',
            'OR' => 'Oregon', 'PA' => 'Pennsylvania', 'RI' => 'Rhode Island', 'SC' => 'South Carolina',
            'SD' => 'South Dakota', 'TN' => 'Tennessee', 'TX' => 'Texas', 'UT' => 'Utah',
            'VT' => 'Vermont', 'VA' => 'Virginia', 'WA' => 'Washington', 'WV' => 'West Virginia',
            'WI' => 'Wisconsin', 'WY' => 'Wyoming', 'DC' => 'District of Columbia',
        ];

        $key = strtoupper(trim($state));
        return $map[$key] ?? $state;
    }

    private function looksLikeEmail(?string $value): bool
    {
        return is_string($value) && filter_var($value, FILTER_VALIDATE_EMAIL);
    }

    private function rows(array $sheet): array
    {
        if (count($sheet) < 2) {
            return [];
        }

        $headers = $sheet[0];
        $out = [];
        for ($i = 1; $i < count($sheet); $i++) {
            $assoc = [];
            foreach ($headers as $col => $key) {
                if ($key === null || $key === '') {
                    continue;
                }
                $assoc[$key] = $sheet[$i][$col] ?? null;
            }
            $assoc = $this->normalizeRow($assoc);
            if ($assoc['PayeeName'] && $assoc['PayorName'] && $assoc['CheckNmbr'] !== null) {
                $out[] = $assoc;
            }
        }

        return $out;
    }

    private function normalizeRow(array $row): array
    {
        foreach ($row as $key => $value) {
            if (is_string($value)) {
                $value = trim($value);
                if ($value === '' || strtoupper($value) === 'NULL') {
                    $value = null;
                }
            }
            $row[$key] = $value;
        }

        $row['CheckNmbr'] = $this->asDigits($row['CheckNmbr'] ?? null);
        $row['AccountNmbr'] = $this->asDigits($row['AccountNmbr'] ?? null);
        $row['RoutingNmbr'] = $this->asDigits($row['RoutingNmbr'] ?? null);
        $row['Amount'] = $this->asAmount($row['Amount'] ?? null);
        $row['CheckDate'] = $this->asExcelDate($row['CheckDate'] ?? null);
        $row['BlankSignatureLine'] = (int) ($row['BlankSignatureLine'] ?? 0);

        return $row;
    }

    private function asDigits($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_string($value) && preg_match('/^\d+$/', $value)) {
            return $value;
        }
        if (is_numeric($value)) {
            return sprintf('%.0f', (float) $value);
        }
        return preg_replace('/\D/', '', (string) $value) ?: null;
    }

    private function asAmount($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        return number_format((float) $value, 2, '.', '');
    }

    private function asExcelDate($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_numeric($value)) {
            $unix = ((int) $value - 25569) * 86400;
            return gmdate('Y-m-d', $unix);
        }
        return Carbon::parse($value)->format('Y-m-d');
    }

    private function readWorkbook(string $path): array
    {
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            throw new \RuntimeException('Unable to open xlsx: ' . $path);
        }

        $shared = [];
        $ss = $zip->getFromName('xl/sharedStrings.xml');
        if ($ss !== false) {
            $xml = $this->loadXml($ss);
            foreach ($xml->si as $si) {
                $texts = [];
                if (isset($si->t)) {
                    $texts[] = (string) $si->t;
                }
                if (isset($si->r)) {
                    foreach ($si->r as $run) {
                        $texts[] = (string) $run->t;
                    }
                }
                $shared[] = implode('', $texts);
            }
        }

        $workbook = $this->loadXml($zip->getFromName('xl/workbook.xml'));
        $rels = $this->loadXml($zip->getFromName('xl/_rels/workbook.xml.rels'));
        $ridToTarget = [];
        foreach ($rels->Relationship as $rel) {
            $ridToTarget[(string) $rel['Id']] = (string) $rel['Target'];
        }

        $sheets = [];
        foreach ($workbook->sheets->sheet as $sheet) {
            $name = (string) $sheet['name'];
            $rid = (string) $sheet['id'];
            $target = $ridToTarget[$rid];
            if (!str_starts_with($target, 'xl/')) {
                $target = 'xl/' . ltrim($target, '/');
            }
            $sheets[$name] = $this->readSheet($zip->getFromName($target), $shared);
        }

        $zip->close();
        return $sheets;
    }

    private function readSheet(string $xmlString, array $shared): array
    {
        $xml = $this->loadXml($xmlString);
        $rows = [];

        foreach ($xml->xpath('//c') ?: [] as $cell) {
            $ref = (string) $cell['r'];
            if ($ref === '') {
                continue;
            }
            [$col, $row] = $this->cellRef($ref);
            $type = (string) $cell['t'];
            $value = null;
            if ($type === 's') {
                $value = $shared[(int) $cell->v] ?? null;
            } elseif ($type === 'inlineStr') {
                $value = (string) $cell->is->t;
            } elseif (isset($cell->v)) {
                $value = (string) $cell->v;
            }
            $rows[$row][$col] = $value;
        }

        if (!$rows) {
            return [];
        }

        ksort($rows);
        $maxCol = 0;
        foreach ($rows as $cols) {
            $maxCol = max($maxCol, max(array_keys($cols)));
        }

        $out = [];
        foreach ($rows as $row) {
            $line = [];
            for ($c = 1; $c <= $maxCol; $c++) {
                $line[] = $row[$c] ?? null;
            }
            $out[] = $line;
        }

        return $out;
    }

    private function loadXml(string $xmlString): \SimpleXMLElement
    {
        $xmlString = preg_replace('/xmlns(:[^=]+)?="[^"]*"/', '', $xmlString);
        $xmlString = str_replace('r:id=', 'id=', $xmlString);
        return simplexml_load_string($xmlString);
    }

    private function cellRef(string $ref): array
    {
        preg_match('/^([A-Z]+)(\d+)$/', $ref, $m);
        $col = 0;
        foreach (str_split($m[1]) as $ch) {
            $col = $col * 26 + (ord($ch) - 64);
        }
        return [$col, (int) $m[2]];
    }
}
