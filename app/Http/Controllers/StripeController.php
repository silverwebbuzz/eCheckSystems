<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class StripeController extends Controller
{
    public function list()
    {
        return view('stripe.subscription.list');
    }
    public function getSubscriptions()
    {
        $stripeSecret = config('services.stripe.secret');

        $response = Http::withToken($stripeSecret)
            ->get('https://api.stripe.com/v1/subscriptions', [
                'limit' => 100,
                'expand' => ['data.customer', 'data.plan.product']
            ]);

        if ($response->successful()) {
            $subscriptions = $response->json()['data'];

            foreach ($subscriptions as $key => $val) {
                $subscriptions[$key]['product'] = $val['plan']['product'];
                $subscriptions[$key]['customer'] = $val['customer'];
                $subscriptions[$key]['created_at'] = Carbon::createFromTimestamp($val['created'])
                    ->format('M d, h:i A');
            }

            return datatables()->of($subscriptions)
                ->addColumn('actions', function ($subscription) {
                    return '<a href="' . route('stripe.subscription.view', [$subscription['id']]) . '" class="btn btn-primary btn-sm">View</a>';
                })
                ->editColumn('status', function ($subscription) {
                    if ($subscription['cancel_at']) {
                        $status = '<span class="badge bg-danger">Cancels ' . Carbon::createFromTimestamp($subscription['cancel_at'])->format('M d') . '</span>';
                    } else {
                        $status = '<span class="badge bg-success">Active</span>';
                    }
                    return $status;
                })
                ->rawColumns(['status', 'actions'])
                ->make(true);
        }

        return response()->json([
            'error' => 'Failed to fetch subscriptions',
            'message' => $response->body(),
        ], $response->status());
    }

    public function viewSubscription($subscriptionId)
    {
        $stripeSecret = config('services.stripe.secret'); // or env('STRIPE_SECRET')

        $response = Http::withToken($stripeSecret)
            ->get("https://api.stripe.com/v1/subscriptions/{$subscriptionId}");

        if ($response->successful()) {
            $subscription = $response->json();

            $customer = Http::withToken($stripeSecret)
                ->get("https://api.stripe.com/v1/customers/{$subscription['customer']}");
            $subscription['customer'] = $customer->json();

            $product = Http::withToken($stripeSecret)
                ->get("https://api.stripe.com/v1/products/{$subscription['plan']['product']}");

            $subscription['product'] = $product->json();

            $invoice = Http::withToken($stripeSecret)
                ->asForm()
                ->post('https://api.stripe.com/v1/invoices/create_preview', [
                    'subscription' => $subscription['id'],
                    'subscription_details[proration_behavior]' => 'create_prorations',
                    'subscription_details[proration_date]' => time(),
                ]);

            $subscription['started_at'] = Carbon::createFromTimestamp($subscription['created'])
                ->format('M d');

            if ($invoice->successful()) {

                $subscription['invoice'] = $invoice->json();

                $subscription['next_invoice_at'] = Carbon::createFromTimestamp($subscription['invoice']['period_end'])
                    ->format('M d');

                $subscription['upcoming_start_date'] = Carbon::createFromTimestamp($subscription['invoice']['lines']['data'][0]['period']['start'])
                    ->format('M d');

                $subscription['upcoming_end_date'] = Carbon::createFromTimestamp($subscription['invoice']['lines']['data'][0]['period']['end'])
                    ->format('M d, Y');

            }

            $invoices = Http::withToken($stripeSecret)
                ->get("https://api.stripe.com/v1/invoices", [
                    'subscription' => $subscriptionId,
                ]);

            $subscription['invoices'] = $invoices->json()['data'];

            return view('stripe.subscription.view', compact('subscription'));
        }

        return response()->json([
            'error' => 'Failed to fetch subscription',
            'message' => $response->body(),
        ], $response->status());
    }




    // public function viewSubscription($subscriptionId)
// {
//     $stripeSecret = config('services.stripe.secret');
//     $baseUrl = 'https://api.stripe.com/v1';
//     $client = Http::withToken($stripeSecret);

    //     // Fetch Subscription
//     $subscriptionResponse = $client->get("{$baseUrl}/subscriptions/{$subscriptionId}");

    //     if (!$subscriptionResponse->successful()) {
//         return $this->handleStripeError($subscriptionResponse, 'Failed to fetch subscription');
//     }

    //     $subscription = $subscriptionResponse->json();

    //     // Fetch Customer
//     $customerResponse = $client->get("{$baseUrl}/customers/{$subscription['customer']}");
//     $customer = $customerResponse->successful() ? $customerResponse->json() : null;

    //     // Fetch Product
//     $productId = data_get($subscription, 'plan.product');
//     $productResponse = $productId
//         ? $client->get("{$baseUrl}/products/{$productId}")
//         : null;
//     $product = $productResponse && $productResponse->successful()
//         ? $productResponse->json()
//         : null;

    //     // Create Invoice Preview with proration info
//     $invoiceResponse = $client->asForm()->post("{$baseUrl}/invoices/create_preview", [
//         'subscription' => $subscription['id'],
//         'subscription_details[proration_behavior]' => 'create_prorations',
//         'subscription_details[proration_date]' => time(),
//     ]);

    //     $invoice = $invoiceResponse->successful() ? $invoiceResponse->json() : null;

    //     // Dates
//     $subscription['started_at'] = $invoice && isset($invoice['period_start'])
//         ? Carbon::createFromTimestamp($invoice['period_start'])->format('M d')
//         : null;

    //     $subscription['next_invoice_at'] = $invoice && isset($invoice['period_end'])
//         ? Carbon::createFromTimestamp($invoice['period_end'])->format('M d')
//         : null;

    //     $firstLine = data_get($invoice, 'lines.data.0.period', []);
//     $subscription['upcoming_start_date'] = isset($firstLine['start'])
//         ? Carbon::createFromTimestamp($firstLine['start'])->format('M d')
//         : null;

    //     $subscription['upcoming_end_date'] = isset($firstLine['end'])
//         ? Carbon::createFromTimestamp($firstLine['end'])->format('M d, Y')
//         : null;

    //     // Get Only Proration Lines
//     $prorationLines = collect(data_get($invoice, 'lines.data', []))->filter(function ($line) {
//         return data_get($line, 'subscription_item_details.proration') === true;
//     });

    //     // Fetch Past Invoices
//     $invoicesResponse = $client->get("{$baseUrl}/invoices", [
//         'subscription' => $subscriptionId,
//     ]);

    //     $invoices = $invoicesResponse->successful()
//         ? $invoicesResponse->json()['data']
//         : [];

    //     // Fetch Subscription Schedule (if available)
//     $schedule = null;
//     if (!empty($subscription['schedule'])) {
//         $scheduleResponse = $client->get("{$baseUrl}/subscription_schedules/{$subscription['schedule']}");
//         if ($scheduleResponse->successful()) {
//             $schedule = $scheduleResponse->json();
//         }
//     }

    //     return view('stripe.subscription.view', [
//         'subscription'    => $subscription,
//         'customer'        => $customer,
//         'plan'            => $product,
//         'invoicePreview'  => $invoice,
//         'prorationLines'  => $prorationLines,
//         'schedule'        => $schedule,
//         'invoices'        => $invoices,
//     ]);
// }

    private function handleStripeError($response, $defaultMessage = 'An error occurred')
    {


        return response()->json([
            'error' => $defaultMessage,
            'message' => $response->body(),
        ], $response->status());
    }

    public function webhookList()
    {
        return view('stripe.webhook.list', );
    }

    public function getWebhooks()
    {
        $stripeSecret = config('services.stripe.secret');

        $response = Http::withToken($stripeSecret)
            ->get("https://api.stripe.com/v1/webhook_endpoints");

        if ($response->successful()) {
            $endpoints = $response->json()['data'];

            // Flatten webhook events with reference to their endpoint URL or ID
            $webhooks = [];
            foreach ($endpoints as $endpoint) {
                foreach ($endpoint['enabled_events'] as $event) {
                    $webhooks[] = [
                        'endpoint_id' => $endpoint['id'],
                        'url' => $endpoint['url'],
                        'event' => $event,
                    ];
                }
            }

            return datatables()->of($webhooks)
                ->addColumn('name', function ($webhook) {
                    return $webhook['event'];
                })
                ->addColumn('url', function ($webhook) {
                    return $webhook['url'];
                })
                ->addColumn('endpoint_id', function ($webhook) {
                    return $webhook['endpoint_id'];
                })
                ->make(true);
        }

        return response()->json([
            'error' => 'Failed to fetch subscriptions',
            'message' => $response->body(),
        ], $response->status());
    }

    public function addWebhook($webhookEndpointId)
    {
        $stripeSecret = config('services.stripe.secret');

        $response = Http::withToken($stripeSecret)->asForm()->post(
            "https://api.stripe.com/v1/webhook_endpoints/{$webhookEndpointId}",
            [
                'enabled_events' => [
                    'customer.subscription.deleted',
                    'invoice.payment_succeeded',
                    'invoice.payment_failed'
                ]
            ]
        );

        if ($response->successful()) {
            return 'Webhook updated successfully.';
        }
    }
}
