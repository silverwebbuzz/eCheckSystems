<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('qbo_companies', function (Blueprint $table) {
            if (!Schema::hasColumn('qbo_companies', 'company_id')) {
                $table->unsignedInteger('company_id')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('qbo_companies', 'default_expense_account_id')) {
                $table->string('default_expense_account_id', 50)->nullable()->after('status');
            }
            if (!Schema::hasColumn('qbo_companies', 'default_expense_account_name')) {
                $table->string('default_expense_account_name', 255)->nullable()->after('default_expense_account_id');
            }
            if (!Schema::hasColumn('qbo_companies', 'default_bank_account_id')) {
                $table->string('default_bank_account_id', 50)->nullable()->after('default_expense_account_name');
            }
            if (!Schema::hasColumn('qbo_companies', 'default_bank_account_name')) {
                $table->string('default_bank_account_name', 255)->nullable()->after('default_bank_account_id');
            }
            if (!Schema::hasColumn('qbo_companies', 'last_sync_at')) {
                $table->timestamp('last_sync_at')->nullable()->after('default_bank_account_name');
            }
            if (!Schema::hasColumn('qbo_companies', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }
            if (!Schema::hasColumn('qbo_companies', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('qbo_companies', function (Blueprint $table) {
            $columns = [
                'company_id',
                'default_expense_account_id',
                'default_expense_account_name',
                'default_bank_account_id',
                'default_bank_account_name',
                'last_sync_at',
                'created_at',
                'updated_at',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('qbo_companies', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
