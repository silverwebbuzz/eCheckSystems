<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Plain-English reason the last push of this check to QuickBooks failed
 * (qbo_sync_status = 'push_failed'). Cleared when a push succeeds.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('Checks', function (Blueprint $table) {
            if (!Schema::hasColumn('Checks', 'qbo_sync_error')) {
                $table->string('qbo_sync_error', 500)->nullable()->after('qbo_sync_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('Checks', function (Blueprint $table) {
            if (Schema::hasColumn('Checks', 'qbo_sync_error')) {
                $table->dropColumn('qbo_sync_error');
            }
        });
    }
};
