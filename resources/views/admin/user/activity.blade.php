<!DOCTYPE html>
<html>

<head>
    <title>User Report</title>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
            color: #000;
        }

        .container {
            width: 1000px;
            margin: auto;
            padding: 20px;
        }

        h2 {
            margin-bottom: 5px;
        }

        .section {
            margin-top: 25px;
        }

        .section-title {
            background: #222;
            color: #fff;
            padding: 6px 10px;
            font-weight: bold;
            font-size: 13px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 6px;
            text-align: left;
        }

        th {
            background: #f2f2f2;
            font-weight: bold;
        }

        .text-danger {
            color: red;
            font-weight: bold;
        }

        .text-success {
            color: green;
            font-weight: bold;
        }

        .no-border td {
            border: none;
        }

        .page-break {
            page-break-after: always;
        }

        @media print {

            html,
            body {
                width: 100%;
                margin: 0;
                padding: 0;
            }

            .container {
                width: 100% !important;
                padding: 0 10px !important;
                /* prevent edge cut */
                box-sizing: border-box;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                table-layout: fixed;
                /* important */
            }

            th,
            td {
                border: 1px solid #000;
            }
        }
    </style>
</head>

<body>

    <div class="container">

        <h2>User Detail Report</h2>
        <p><strong>Generated On:</strong> {{ date('m/d/Y h:i A') }}</p>

        <!-- ================= USER DETAILS ================= -->
        <div class="section">
            <div class="section-title">User Details</div>
            <table>
                <tr>
                    <th>User ID</th>
                    <th>Email</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Company</th>
                    <th>Phone</th>
                </tr>
                <tr>
                    <td>{{ $user->UserID }}</td>
                    <td>{{ $user->Email }}</td>
                    <td>{{ $user->FirstName }}</td>
                    <td>{{ $user->LastName }}</td>
                    <td>{{ $user->CompanyName ?? '-' }}</td>
                    <td>{{ $user->PhoneNumber }}</td>
                </tr>
            </table>

            <br>

            <table>
                <tr>
                    <th>Membership Plan</th>
                    <th>Membership Status</th>
                    <th>Trial Start</th>
                    <th>Trial End</th>
                    <th>First Billing Cycle Start Date</th>
                    <th>IP Address</th>
                </tr>
                <tr>
                    <td>{{ ($currentSubscription?->PackageID == -1) ? 'Free Trial' : $currentSubscription?->package?->Name }}</td>

                    @if($currentSubscription?->Status == 'Canceled')
                        <td class="text-danger">Canceled</td>
                    @elseif($currentSubscription?->Status == 'Pending')
                        <td class="text-alert">Pending</td>
                    @elseif($currentSubscription?->Status == 'Inactive')
                        <td class="text-danger">Inactive</td>
                    @elseif($currentSubscription?->Status == 'Active')
                        <td class="text-success">Active</td>
                    @else
                        <td>-</td>
                    @endif

                    @if($currentSubscription == null)
                        <td>-</td>
                    @else
                        <td>{{ date('m/d/Y', strtotime($currentSubscription?->PaymentStartDate)) }}</td>
                    @endif
                    
                    @if($currentSubscription == null || ($currentSubscription?->PackageID == -1))
                        <td>-</td>
                        <td>-</td>
                    @else
                        <td>{{ date('m/d/Y', strtotime($currentSubscription?->NextRenewalDate)) }}</td>
                        <td>{{ $firstBillingDate }}</td>
                    @endif
                    
                    <td>{{ $currentSubscription->ip_address ?? '-' }}</td>
                </tr>
            </table>
        </div>


        <!-- ================= MEMBERSHIP HISTORY ================= -->
        <div class="section">
            <div class="section-title">Membership History</div>
            <table>
                <tr>
                    <th>Membership Plan</th>
                    <th>IP Address</th>
                    <th>System Generated</th>
                    <th>Created At</th>
                </tr>
                @forelse($subscriptions as $subscription)
                    <tr>
                        <td>{{ ($subscription->PackageID == -1) ? 'Free Trial' : $subscription->package?->Name }}</td>
                        <td>{{ $subscription->ip_address ?? '-' }}</td>
                        <td>{{ ($subscription->is_sys_generated == 1) ? 'Yes' : 'No' ?? '-' }}</td>
                        <td>{{ $subscription?->created_at ? \Carbon\Carbon::parse($subscription->created_at)->format('m/d/Y h:i A') : '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">No past subscriptions found.</td>
                    </tr>
                @endforelse
            </table>
        </div>


        <!-- ================= PAYMENT INFO ================= -->
        <div class="section">
            <div class="section-title">Payment Information</div>
            <table>
                <tr>
                    <th>Card Holder</th>
                    <th>Card Number</th>
                    <th>Exp Month</th>
                    <th>Exp Year</th>
                    <th>Street Address 1</th>
                    <th>Street Address 2</th>
                    <th>City</th>
                    <th>State</th>
                    <th>Zip Code</th>
                </tr>
                @forelse($cardList as $card)
                
                    <tr>
                        <td>{{ $card['card_holder'] ?? '-' }}</td>
                        <td>{{ 'XXXXXX'.$card['last4'] ?? '-' }}</td>
                        <td>{{ $card['exp_month'] ?? '-' }}</td>
                        <td>{{ $card['exp_year'] ?? '-' }}</td>
                        <td>{{ $card['address_line1'] ?? '-' }}</td>
                        <td>{{ $card['address_line2'] ?? '-' }}</td>
                        <td>{{ $card['city'] ?? '-' }}</td>
                        <td>{{ $card['state'] ?? '-' }}</td>
                        <td>{{ $card['postal_code'] ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9">No payment information found.</td>
                    </tr>
                @endforelse
            </table>
        </div>

        <!-- ================= BILLING HISTORY ================= -->
        <div class="section">
            <div class="section-title">Billing History</div>
            <table>
                <tr>
                    <th>Billing ID</th>
                    <th>Membership Plan</th>
                    <th>Details</th>
                    <th>Price</th>
                    <th>Charged</th>
                    <th>Billing Cycle Start Date</th>
                    <th>Billing Cycle End Date</th>
                    <th>Created At</th>
                </tr>
                @forelse($payment_histories as $paymentHistory)
                    <tr>
                        <td>{{ $paymentHistory['billing_id'] ?? '-' }}</td>
                        <td>{{ $paymentHistory['plan'] ?? '-' }}</td>
                        <td>{{ $paymentHistory['details'] ?? '-' }}</td>
                        <td>{{ '$'.$paymentHistory['price'] ?? '-' }}</td>
                        <td>{{ $paymentHistory['charged'] ?? '-' }}</td>
                        <td>{{ $paymentHistory['billing_start_dt'] ?? '-' }}</td>
                        <td>{{ $paymentHistory['billing_end_dt'] ?? '-' }}</td>
                        <td>{{ ($paymentHistory['created_at']) ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">No billing history found.</td>
                    </tr>
                @endforelse
            </table>
        </div>

        <!-- ================= PAYMENT HISTORY ================= -->
        <div class="section">
            <div class="section-title">Payment History</div>
            <table>
                <tr>
                    <th>Payee Name</th>
                    <th>Check Date</th>
                    <th>Check Number</th>
                    <th>Payor Name</th>
                    <th>Amount</th>
                    <th>Memo</th>
                    <th>Create Date Time</th>
                    <th>Ip Address</th>
                </tr>
                @forelse($checks as $check)
                    <tr>
                        <td>{{ $check?->payee?->Name ?? '-' }}</td>
                        <td>{{ ($check?->IssueDate) ? \Carbon\Carbon::parse($check->IssueDate)->format('m/d/Y') : '-'  ?? '-' }}</td>
                        <td>{{ $check?->CheckNumber ?? '-' }}</td>
                        <td>{{ $check?->payor?->Name ?? '-' }}</td>
                        <td>{{ '$'.$check?->Total ?? '-' }}</td>
                        <td>{{ $check?->Memo ?? '-' }}</td>
                         <td>{{ ($check?->created_at) ? \Carbon\Carbon::parse($check->created_at)->format('m/d/Y h:i A') : '-'  ?? '-' }}</td>
                        <td>{{ $check->ip_address ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">No billing history found.</td>
                    </tr>
                @endforelse
            </table>
        </div>
    </div>

</body>

</html>