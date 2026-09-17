<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Existing QuickBooks-typed rows → Make Payment (identity via qbo_id)
        try {
            DB::table('Checks')
                ->where('CheckType', 'QuickBooks')
                ->update(['CheckType' => 'Make Payment']);
        } catch (\Throwable $e) {
            // ignore if column/value already migrated
        }

        try {
            DB::statement("ALTER TABLE `Checks` MODIFY `CheckType` ENUM('Process Payment','Make Payment') NOT NULL");
        } catch (\Throwable $e) {
            // Ignore if already altered or DB driver does not support this form
        }
    }

    public function down(): void
    {
        try {
            DB::statement("ALTER TABLE `Checks` MODIFY `CheckType` ENUM('Process Payment','Make Payment','QuickBooks') NOT NULL");
        } catch (\Throwable $e) {
            // ignore
        }

        try {
            DB::table('Checks')
                ->whereNotNull('qbo_id')
                ->where('CheckType', 'Make Payment')
                ->update(['CheckType' => 'QuickBooks']);
        } catch (\Throwable $e) {
            // ignore
        }
    }
};
