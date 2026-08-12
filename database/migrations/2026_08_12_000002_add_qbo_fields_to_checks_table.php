<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('Checks', function (Blueprint $table) {
            if (!Schema::hasColumn('Checks', 'qbo_id')) {
                $table->string('qbo_id', 50)->nullable()->after('CheckID');
            }
            if (!Schema::hasColumn('Checks', 'qbo_sync_status')) {
                $table->string('qbo_sync_status', 50)->nullable()->after('qbo_id');
            }
            if (!Schema::hasColumn('Checks', 'qbo_print_later')) {
                $table->boolean('qbo_print_later')->default(false)->after('qbo_sync_status');
            }
            if (!Schema::hasColumn('Checks', 'qbo_company_id')) {
                $table->unsignedBigInteger('qbo_company_id')->nullable()->after('qbo_print_later');
            }
            if (!Schema::hasColumn('Checks', 'qbo_doc_number')) {
                $table->string('qbo_doc_number', 255)->nullable()->after('qbo_company_id');
            }
            if (!Schema::hasColumn('Checks', 'check_number_conflict')) {
                $table->boolean('check_number_conflict')->default(false)->after('qbo_doc_number');
            }
        });

        // Extend Status enum to include imported_from_qbo (draft-equivalent for QBO imports)
        try {
            DB::statement("ALTER TABLE `Checks` MODIFY `Status` ENUM('draft','generated','imported_from_qbo') NOT NULL DEFAULT 'draft'");
        } catch (\Throwable $e) {
            // Ignore if already altered or DB driver does not support this form
        }

        // Extend CheckType for QuickBooks-sourced checks list filtering
        try {
            DB::statement("ALTER TABLE `Checks` MODIFY `CheckType` ENUM('Process Payment','Make Payment','QuickBooks') NOT NULL");
        } catch (\Throwable $e) {
            // Ignore if already altered
        }
    }

    public function down(): void
    {
        Schema::table('Checks', function (Blueprint $table) {
            foreach (['qbo_id', 'qbo_sync_status', 'qbo_print_later', 'qbo_company_id', 'qbo_doc_number', 'check_number_conflict'] as $column) {
                if (Schema::hasColumn('Checks', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        try {
            DB::statement("ALTER TABLE `Checks` MODIFY `Status` ENUM('draft','generated') NOT NULL DEFAULT 'draft'");
            DB::statement("ALTER TABLE `Checks` MODIFY `CheckType` ENUM('Process Payment','Make Payment') NOT NULL");
        } catch (\Throwable $e) {
            // ignore
        }
    }
};
