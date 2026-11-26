<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The manual-approval signup flow treats any Active user with an empty approved_at
 * as "under review" and blocks login. Users who signed up before that flow existed
 * were never approved, so without this backfill every existing customer would be
 * locked out when the flow goes live.
 *
 * "Existing customer" = has at least one PaymentSubscription row. Under the old flow
 * every signup got a trial subscription immediately; under the new flow a user gets
 * none until an admin approves them. So pending sign-ups stay pending.
 *
 * approved_by stays NULL to mark these as system-approved (admin approvals set it).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('User', 'approved_at')) {
            return;
        }

        DB::table('User')
            ->whereNull('approved_at')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('PaymentSubscription')
                    ->whereColumn('PaymentSubscription.UserID', 'User.UserID');
            })
            ->update([
                'approved_at' => DB::raw('COALESCE(`CreatedAt`, NOW())'),
            ]);
    }

    public function down(): void
    {
        // Undo only system approvals made by up(); admin approvals have approved_by set.
        if (!Schema::hasColumn('User', 'approved_at')) {
            return;
        }

        DB::table('User')
            ->whereNull('approved_by')
            ->whereNotNull('approved_at')
            ->update(['approved_at' => null]);
    }
};
