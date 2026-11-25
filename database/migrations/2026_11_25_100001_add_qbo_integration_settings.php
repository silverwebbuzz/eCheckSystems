<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * QuickBooks integration settings agreed with the client (Jul 27 spec):
 *   2a  auto_import        Create eCheck (draft) when QuickBooks creates a check   (default ON  = current behaviour)
 *       notify_on_import   Email the account owner to review newly imported checks (default OFF)
 *   2b  sync_back          Sync eCheck checks back to QuickBooks on Generate       (default ON  = current behaviour)
 * Per check:
 *       Checks.qbo_sync    "Sync this check to QuickBooks?" chosen on the Send Check form (default = 2b)
 *
 * Imported checks are never auto-generated or emailed to the payee: the user reviews them first.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('qbo_companies', function (Blueprint $table) {
            if (!Schema::hasColumn('qbo_companies', 'auto_import')) {
                $table->boolean('auto_import')->default(true)->after('status');
            }
            if (!Schema::hasColumn('qbo_companies', 'notify_on_import')) {
                $table->boolean('notify_on_import')->default(false)->after('auto_import');
            }
            if (!Schema::hasColumn('qbo_companies', 'sync_back')) {
                $table->boolean('sync_back')->default(true)->after('notify_on_import');
            }
        });

        Schema::table('Checks', function (Blueprint $table) {
            if (!Schema::hasColumn('Checks', 'qbo_sync')) {
                $table->boolean('qbo_sync')->default(true)->after('qbo_company_id');
            }
        });

        // Email template for the "new QuickBooks check to review" notification (editable in admin).
        if (!DB::table('email_templates')->where('id', 17)->exists()) {
            DB::table('email_templates')->insert([
                'id' => 17,
                'name' => 'QuickBooks Check Imported',
                'subject' => 'New check from QuickBooks is ready for review',
                'head' => 'New QuickBooks Check',
                'content' => 'Dear {{ name }},',
                'body1' => '<p>A new check was created in QuickBooks and has been added to your eCheckSystems account as a <strong>draft</strong>.</p>'
                    . '<p><strong>Check #:</strong> {{ check_number }}<br><strong>Pay to:</strong> {{ payee }}<br><strong>Amount:</strong> ${{ amount }}</p>'
                    . '<p>Please review it, then generate and send it when ready: {{ review_link }}</p>',
                'body2' => '<p>Nothing has been sent to the payee yet.</p><p>Thank you,<br>eCheckSystems Team</p>',
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('qbo_companies', function (Blueprint $table) {
            foreach (['auto_import', 'notify_on_import', 'sync_back'] as $column) {
                if (Schema::hasColumn('qbo_companies', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('Checks', function (Blueprint $table) {
            if (Schema::hasColumn('Checks', 'qbo_sync')) {
                $table->dropColumn('qbo_sync');
            }
        });

        DB::table('email_templates')->where('id', 17)->delete();
    }
};
