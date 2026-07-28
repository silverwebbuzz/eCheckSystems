<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <title>Subscriptions</title>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <link rel='stylesheet' type='text/css' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css'>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.2/css/dataTables.dataTables.min.css">
</head>
<body>

<div class="container">
    <h2>Subscription: {{ $subscription['id'] }}</h2>
    <hr>

    <h4>🔹 Customer</h4>
    <p><strong>Name:</strong> {{ $customer['name'] ?? 'N/A' }}</p>
    <p><strong>Email:</strong> {{ $customer['email'] ?? 'N/A' }}</p>

    <h4>🔹 Current Plan</h4>
    <p><strong>Product:</strong> {{ $plan['name'] ?? 'N/A' }}</p>
    <p><strong>Price:</strong> {{ number_format($subscription['plan']['amount'] / 100, 2) }} {{ strtoupper($subscription['plan']['currency']) }}</p>
    <p><strong>Interval:</strong> {{ $subscription['plan']['interval'] }}</p>

    <h4>🔹 Subscription Status</h4>
    <p><strong>Status:</strong> {{ $subscription['status'] }}</p>
    <p><strong>Start Date:</strong> {{ $subscription['started_at'] }}</p>
    <p><strong>Next Billing:</strong> {{ $subscription['next_invoice_at'] }}</p>

    @if($schedule)
        <h4>🔹 Upcoming Changes (Schedule)</h4>
        <pre>{{ json_encode($schedule, JSON_PRETTY_PRINT) }}</pre>
    @endif

    <h4>🔹 Upcoming Invoice</h4>
    <ul>
        @foreach ($invoicePreview['lines']['data'] ?? [] as $line)
            <li>
                <strong>{{ $line['description'] }}</strong> —
                {{ number_format($line['amount'] / 100, 2) }} {{ strtoupper($line['currency']) }}
                @if(data_get($line, 'subscription_item_details.proration') === true)
                    <span class="text-warning">(Proration)</span>
                @endif
            </li>
        @endforeach
    </ul>

    <h4>🔹 Past Invoices</h4>
    <ul>
        @foreach ($invoices as $invoice)
            <li>
                {{ \Carbon\Carbon::createFromTimestamp($invoice['created'])->toFormattedDateString() }} —
                {{ number_format($invoice['amount_paid'] / 100, 2) }} {{ strtoupper($invoice['currency']) }}
                ({{ ucfirst($invoice['status']) }})
            </li>
        @endforeach
    </ul>
</div>

</body>
</html>