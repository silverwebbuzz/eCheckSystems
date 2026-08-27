<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Fraud is tracked via reason, not Status
        DB::table('User')
            ->where('Status', 'Fraud')
            ->update([
                'Status' => 'Inactive',
                'reason' => 'Fraud',
            ]);

        // Former pending/trial statuses become Active (login-capable)
        DB::table('User')
            ->whereIn('Status', ['Pending_Approval', 'Trial'])
            ->update(['Status' => 'Active']);

        // Any unexpected leftover values become Inactive
        DB::table('User')
            ->whereNotIn('Status', ['Active', 'Inactive'])
            ->update(['Status' => 'Inactive']);

        Schema::table('User', function (Blueprint $table) {
            $table->string('Status', 50)->default('Active')->change();
        });
    }

    public function down(): void
    {
        // Irreversible data normalization; default only
        Schema::table('User', function (Blueprint $table) {
            $table->string('Status', 50)->default('Active')->change();
        });
    }
};
