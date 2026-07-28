<?php

namespace App\Services;

use App\Models\Checks;
use App\Models\PaymentSubscription;
use App\Models\User;
use App\Models\BlockedIP;

class FraudService
{
    public function handleFraudUser(User $user)
    {
        $ips = $this->collectUserIps($user);

        if ($ips->isEmpty()) {
            return collect();
        }

        $insertData = $ips->map(function ($ip) use ($user) {
            return [
                'ip_address' => $ip,
                'user_id' => $user->UserID,
                'created_at' => now(),
            ];
        })->toArray();

        BlockedIP::insertOrIgnore($insertData);

        return $ips;
    }

    public function removeFraudBlock(User $user)
    {
        $userId = $user->UserID;

        // Only remove blocks created for this user
        BlockedIP::where('user_id', $userId)->delete();
    }

    /**
     * Collect all user-related IPs from all sources
     */
    private function collectUserIps(User $user)
    {
        $userId = $user->UserID;

        return collect()
            ->merge(
                PaymentSubscription::where('UserID', $userId)
                    ->whereNotNull('ip_address')
                    ->pluck('ip_address')
            )
            ->merge(
                Checks::where('UserID', $userId)
                    ->whereNotNull('ip_address')
                    ->pluck('ip_address')
            )
            ->merge(
                $user->ip_address ? [$user->ip_address] : []
            )
            ->map(fn($ip) => trim($ip))
            ->filter()
            ->unique()
            ->values();
    }

    public function addIpForFraudUser(User $user, string $ip)
    {
        if (strtolower($user->reason) !== 'fraud') {
            return false; // only for fraud users
        }

        return BlockedIP::firstOrCreate(
            ['ip_address' => trim($ip)],
            [
                'user_id' => $user->UserID,
                'created_at' => now(),
            ]
        );
    }

}
