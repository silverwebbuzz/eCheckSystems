<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SubscriptionHelper
{
    public function addPlan($data)
    {
        $product = $this->addProduct($data['name']);

        if (!empty($product['id'])) {
            $response = Http::withBasicAuth(config('services.stripe.secret'), '')
                ->asForm()
                ->post('https://api.stripe.com/v1/plans', [
                    'amount' => $data['price'] * 100,
                    'currency' => 'usd',
                    'interval' => 'month',
                    'product' => $product['id'],
                ]);

            if ($response->successful()) {
                return $response->json();
            } else {
                return false;
            }
        }

    }

    public function addProduct($name)
    {
        $response = Http::withBasicAuth(config('services.stripe.secret'), '')
            ->asForm()
            ->post('https://api.stripe.com/v1/products', [
                'name' => $name,
            ]);

        if ($response->successful()) {
            return $response->json();
        } else {
            return false;
        }
    }

    public function deleteProduct($productId)
    {
        $response = Http::withBasicAuth(config('services.stripe.secret'), '')
            ->delete("https://api.stripe.com/v1/products/{$productId}");

        if ($response->successful()) {
            return $response->json();
        } else {
            return false;
        }
    }


    public function deletePlan($planId)
    {
        $response = Http::withBasicAuth(config('services.stripe.secret'), '')
            ->delete("https://api.stripe.com/v1/plans/{$planId}");

        if ($response->successful()) {
            return $response->json();
        } else {
            return false;
        }
    }

    public function addCustomer($data)
    {
        $response = Http::withBasicAuth(config('services.stripe.secret'), '')
            ->asForm()
            ->post('https://api.stripe.com/v1/customers', [
                'name' => $data['name'],
                'email' => $data['email'],
            ]);

        if ($response->successful()) {
            return $response->json();
        } else {
            return false;
        }
    }

    public function addPrice($product, $amount)
    {
        $response = Http::withBasicAuth(config('services.stripe.secret'), '')
            ->asForm()
            ->post('https://api.stripe.com/v1/prices', [
                'currency' => 'usd',
                'unit_amount' => $amount * 100,
                'recurring[interval]' => 'month',
                'product' => $product
            ]);


        if ($response->successful()) {
            return $response->json();
        } else {
            return false;
        }
    }

    public function addSubscription($data)
    {
        $session = Http::withToken(config('services.stripe.secret'))->asForm()->post('https://api.stripe.com/v1/checkout/sessions', [
            'customer' => $data['cusID'],
            'payment_method_types[]' => 'card',
            'line_items[0][price]' => $data['price_id'],
            'line_items[0][quantity]' => 1,
            'mode' => 'subscription',
            'success_url' => route('stripe.success') . '?session_id={CHECKOUT_SESSION_ID}&user=' . md5($data['user_id']) . '&plan=' . $data['plan'],
            'cancel_url' => route('stripe.cancel'),
            'subscription_data[metadata][set_default_pm]' => 'true',
            'subscription_data[metadata][ip_address]' => request()->ip(),
        ]);

        if (!$session->successful()) {
            return false;
        }

        return $session->json();
    }

    public function updateSubscription($data)
    {
        $subscriptionId = $data['subscription_id'];
        $newPriceId = $data['new_price_id'];
        $upgradeAmount = $data['upgrade_amount']; // e.g. 2000 for $20

        $stripeSecret = config('services.stripe.secret');

        // Step 1: Retrieve subscription
        $subscriptionResponse = Http::withToken($stripeSecret)
            ->get("https://api.stripe.com/v1/subscriptions/{$subscriptionId}");

        if (!$subscriptionResponse->successful()) {
            return false;
        }

        $subscription = $subscriptionResponse->json();
        $scheduleId = $data['schedule'] ?? null;

        $ip = request()->ip();

        Http::withToken($stripeSecret)
            ->asForm()
            ->post("https://api.stripe.com/v1/subscriptions/{$subscriptionId}", [
                'metadata[ip_address]' => $ip,
            ]);

        if ($scheduleId) {
            $reponse = $this->releaseSchedule($scheduleId);

            if (!$reponse) {
                return false;
            }
        }

        $items = $subscription['items']['data'];

        if (empty($items)) {
            return false;
        }

        $itemId = $items[0]['id'];
        $customerId = $subscription['customer'];

        // Step 2: Update subscription item without proration
        $updateItemResponse = Http::withToken($stripeSecret)
            ->asForm()
            ->post("https://api.stripe.com/v1/subscription_items/{$itemId}", [
                'price' => $newPriceId,
                'proration_behavior' => 'none',
            ]);

        if (!$updateItemResponse->successful()) {
            return false;
        }

        // Step 3: Add manual invoice item for full upgrade amount
        $invoiceItemResponse = Http::withToken($stripeSecret)
            ->asForm()
            ->post('https://api.stripe.com/v1/invoiceitems', [
                'customer' => $customerId,
                'amount' => $upgradeAmount, // in cents
                'currency' => 'usd',
                'description' => 'Plan upgrade charge',
                'subscription' => $subscriptionId, // associates with subscription
            ]);

        if (!$invoiceItemResponse->successful()) {
            return false;
        }

        // Step 4: Create invoice
        $invoiceResponse = Http::withToken($stripeSecret)
            ->asForm()
            ->post("https://api.stripe.com/v1/invoices", [
                'subscription' => $subscriptionId,
                'customer' => $customerId,
                'auto_advance' => 'false',
            ]);

        if (!$invoiceResponse->successful()) {
            return false;
        }

        $invoiceId = $invoiceResponse->json('id');

        // Step 5: Finalize invoice
        $finalizeResponse = Http::withToken($stripeSecret)
            ->asForm()
            ->post("https://api.stripe.com/v1/invoices/{$invoiceId}/finalize");

        if (!$finalizeResponse->successful()) {
            return false;
        }

        // Step 6: Pay invoice
        $payResponse = Http::withToken($stripeSecret)
            ->asForm()
            ->post("https://api.stripe.com/v1/invoices/{$invoiceId}/pay");

        if (!$payResponse->successful()) {
            return false;
        }

        return $payResponse->json();
    }

    public function getCustomerPaymentMethods($customerId)
    {
        $response = Http::withToken(config('services.stripe.secret'))
            ->get("https://api.stripe.com/v1/payment_methods", [
                'customer' => $customerId,
                'type' => 'card',
            ]);

        if ($response->successful()) {
            return $response->json();
        }
        return false;
    }

    public function getDefaultCard($id)
    {
        $customerResponse = Http::withToken(config('services.stripe.secret'))
            ->get("https://api.stripe.com/v1/customers/" . $id);

        $customer = $customerResponse->json();
        $defaultPaymentMethodId = $customer['invoice_settings']['default_payment_method'] ?? null;

        if ($defaultPaymentMethodId) {
            // Fetch the payment method details (card brand, last4, etc.)
            $pmResponse = Http::withToken(config('services.stripe.secret'))
                ->get("https://api.stripe.com/v1/payment_methods/" . $defaultPaymentMethodId);

            $data = $pmResponse->json();
            return !empty($data['id']) ? $data['id'] : null;
        }

        return null;
    }

    public function cancelAtPeriodEnd($subscriptionId)
    {
        $response = Http::withToken(config('services.stripe.secret'))
            ->asForm()
            ->post("https://api.stripe.com/v1/subscriptions/{$subscriptionId}", [
                'cancel_at_period_end' => 'true',
            ]);

        if ($response->successful()) {
            return true;
        } else {

            $response = Http::withToken(config('services.stripe.secret'))
                ->get("https://api.stripe.com/v1/subscriptions/{$subscriptionId}");

            $data = $response->json();

            $scheduleId = $data['schedule'] ?? null;

            if ($scheduleId) {
                $reponse = $this->releaseSchedule($scheduleId);

                if ($reponse) {
                    return $this->cancelAtPeriodEnd($subscriptionId);
                }
            }
        }

        return false;
    }

    public function cancelImmediately($subscriptionId)
    {
        $response = Http::withToken(config('services.stripe.secret'))
            ->delete("https://api.stripe.com/v1/subscriptions/{$subscriptionId}");

        Log::info('cancelImmediately');
        Log::info($response->json());
        if ($response->successful()) {
            return true;
        }

        return false;
    }

    public function schedulePlanDowngrade($subscriptionId, $newPriceId)
    {
        $stripeSecret = config('services.stripe.secret');

        // Step 1: Retrieve the current subscription with expanded items.price (optional but recommended)
        $subscriptionResponse = Http::withToken($stripeSecret)
            ->get("https://api.stripe.com/v1/subscriptions/{$subscriptionId}?expand[]=items.data.price");
        if (!$subscriptionResponse->successful()) {
            return false;
        }

        $subscription = $subscriptionResponse->json();

        $ip = request()->ip();

        Http::withToken($stripeSecret)
            ->asForm()
            ->post("https://api.stripe.com/v1/subscriptions/{$subscriptionId}", [
                'metadata[ip_address]' => $ip,
            ]);
            
        // Correctly fetch current price ID and period end from subscription items data
        $currentPriceId = $subscription['items']['data'][0]['price']['id'] ?? null;
        $currentPeriodEnd = $subscription['items']['data'][0]['current_period_end'] ?? null;
        // $startDate = $subscription['start_date'] ?? null;
        $startDate = $subscription['items']['data'][0]['current_period_start'] ?? null;


        if (!$currentPriceId || !$currentPeriodEnd || !$startDate) {
            return false;
        }


        if (empty($subscription['schedule'])) {

            Log::info('Create schedule');
            Log::info(Carbon::createFromTimestamp($startDate)->toDateTimeString());
            Log::info(Carbon::createFromTimestamp($currentPeriodEnd)->toDateTimeString());

            // Step 3: Create Subscription Schedule from current subscription
            $scheduleResponse = Http::withToken($stripeSecret)
                ->asForm()
                ->post("https://api.stripe.com/v1/subscription_schedules", [
                    'from_subscription' => $subscriptionId,
                ]);

            if (!$scheduleResponse->successful()) {
                return false;
            }

            $scheduleId = $scheduleResponse->json()['id'];

            // Step 3: Update schedule with phases (current plan till period end, then downgrade)
            $updateResponse = Http::withToken($stripeSecret)
                ->asForm()
                ->post("https://api.stripe.com/v1/subscription_schedules/{$scheduleId}", [
                    'phases[0][start_date]' => $startDate,
                    'phases[0][end_date]' => $currentPeriodEnd,
                    'phases[0][items][0][price]' => $currentPriceId,
                    'phases[0][items][0][quantity]' => 1,

                    'phases[1][start_date]' => $currentPeriodEnd,
                    'phases[1][items][0][price]' => $newPriceId,
                    'phases[1][items][0][quantity]' => 1,
                    'phases[1][iterations]' => 1,
                    'end_behavior' => 'release',
                ]);
            Log::info($updateResponse->json());
            if (!$updateResponse->successful()) {
                return false;
            }

        } else {

            Log::info('Release schedule');

            $scheduleId = $subscription['schedule'];

            $response = $this->releaseSchedule($scheduleId);

            if (empty($response)) {
                return false;
            }

            $response = $this->schedulePlanDowngrade($subscriptionId, $newPriceId);

            if (empty($response)) {
                return false;
            }
        }

        return true;
    }

    public function releaseSchedule($scheduleId)
    {
        $stripeSecret = config('services.stripe.secret');
        $response = Http::withToken($stripeSecret)
            ->asForm()
            ->post("https://api.stripe.com/v1/subscription_schedules/{$scheduleId}/release");
        if (!$response->successful()) {
            return false;
        }
        return $response->json();
    }

    public function getSubscriptions($stripeCustomerId)
    {
        $stripeSecret = config('services.stripe.secret');

        $response = Http::withToken($stripeSecret)
            ->get('https://api.stripe.com/v1/subscriptions', [
                'customer' => $stripeCustomerId,
                'status' => 'active',
            ]);
        if (!$response->successful()) {
            return false;
        }
        return $response->json();
    }
}