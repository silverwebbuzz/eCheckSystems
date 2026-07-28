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
   
    <div class="container py-5">
        <h3>View Subscription</h3>
        <div class="row mt-5">
            <div class="col">
                <b>{{ $subscription['customer']['name'] }} </b>({{ $subscription['customer']['id'] }}) on {{ $subscription['product']['name'] }}
                @if($subscription['status'] == 'active')
                    <span class="badge text-bg-success">Active</span>
                @endif
                @if($subscription['cancel_at'] != null)
                    <span class="badge text-bg-danger">Cancels on {{ \Carbon\Carbon::createFromTimestamp($subscription['cancel_at'])->format('M d') }}</span>
                @endif
            </div>
        </div>
            <div class="row mt-3">
                <div class="col-6 ">
                    <table class="table table-bordered" style="font-size:14px;">
                        <thead>
                            <tr>
                                <th>Started</th>
                                <th>Next Invoice</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{{ $subscription['started_at'] }}</td>
                                <td>{{ ($subscription['cancel_at'] != null) ? 'No further invoice' : $subscription['next_invoice_at'] }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        <div class="row mt-5">
            <div class="col-6">
                <h4>Items</h4>
                <table class="table table-bordered"> 
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                            <tr>
                                <td>{{ $subscription['product']['name'] }} ({{ $subscription['product']['id']}}) </td>
                                <td>${{ $subscription['plan']['amount']  / 100}} ({{ $subscription['plan']['id'] }})</td>
                            </tr>
                    </tbody>
                </table>
            </div>
        </div>
        @if($subscription['cancel_at'] == null)
            <div class="row mt-5">
                <div class="col">
                    <h4>Upcoming Invoice ({{ $subscription['upcoming_start_date'].' - '.$subscription['upcoming_end_date'] }})</h4>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th>Quantity</th>
                                <th>Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $invoiceLine=$subscription['invoice']['lines']['data'][0];

                            @endphp
                            <tr>
                                <td>{{ $invoiceLine['description'] }} ({{ $invoiceLine['pricing']['price_details']['product'] }})</td>
                                <td>1</td>
                                <td>${{ number_format($invoiceLine['amount'] / 100, 2) }} ({{ $invoiceLine['pricing']['price_details']['price'] }})</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
        <div class="row mt-5">
            <div class="col">
                <h4>Invoices</h4>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Total</th>
                            <th>Invoice Number</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($subscription['invoices'] as $invoice)
                            <tr>
                                <td>{{ $invoice['id'] }}</td>
                                <td>${{ $invoice['amount_paid'] / 100 }}</td>
                                <td>{{ $invoice['number'] }}</td>
                                <td>{{ \Carbon\Carbon::parse($invoice['created'])->format('M d H:i') }}</td>
                                <td><a href="{{ $invoice['invoice_pdf'] }}">Download</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>