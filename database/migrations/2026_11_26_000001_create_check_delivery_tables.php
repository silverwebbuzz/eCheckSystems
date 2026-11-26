<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Deluxe-style check delivery (client Point 5).
 *
 * Instead of emailing the check PDF, the payee gets a "Get Check" link to a secure page where they
 * print as a guest or keep the check in a payee account.
 *
 *  check_deliveries  one row per emailed check link (only a SHA-256 hash of the link token is stored)
 *  payee_accounts    accounts for people who RECEIVE checks; separate from customer accounts
 *                    (no plan, no admin approval)
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('payee_accounts')) {
            Schema::create('payee_accounts', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->string('password');
                $table->rememberToken();
                $table->timestamp('last_login_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('check_deliveries')) {
            Schema::create('check_deliveries', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('CheckID');
                $table->unsignedBigInteger('UserID');           // sender (customer)
                $table->string('sender_name')->nullable();       // name shown to the payee ("check from ...")
                $table->string('payee_email');
                $table->string('token_hash', 64)->unique();      // sha256 of the emailed token
                $table->string('status', 20)->default('sent');   // sent | viewed | printed
                $table->timestamp('sent_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->timestamp('first_viewed_at')->nullable();
                $table->timestamp('last_viewed_at')->nullable();
                $table->unsignedInteger('view_count')->default(0);
                $table->unsignedInteger('print_count')->default(0);
                $table->timestamp('last_printed_at')->nullable();
                $table->string('last_printed_ip', 45)->nullable();
                $table->unsignedBigInteger('payee_account_id')->nullable();
                $table->timestamps();

                $table->index('CheckID');
                $table->index('payee_email');
            });
        }

        if (!DB::table('email_templates')->where('id', 18)->exists()) {
            DB::table('email_templates')->insert([
                'id' => 18,
                'name' => 'Check Delivery (Get Check link)',
                'subject' => 'You have a check from {{ sender_name }}',
                'head' => 'You have a check from {{ sender_name }}',
                'content' => 'Hello {{ payee_name }},',
                'body1' => '<p><strong>{{ sender_name }}</strong> sent you a check for <strong>${{ amount }}</strong> (check #{{ check_number }}).</p>'
                    . '<p>Memo: {{ memo }}</p>'
                    . '<p style="text-align:center;margin:28px 0;">{{ get_check_button }}</p>'
                    . '<p><strong>How it works:</strong><br>'
                    . '1. Click <strong>Get Check</strong>.<br>'
                    . '2. Print it as a guest, or create a free account to keep your checks in one place.<br>'
                    . '3. Print on plain white paper and deposit it like any other check.</p>'
                    . '<p style="font-size:13px;color:#666;">If the button does not work, copy this link into your browser:<br>{{ get_check_url }}</p>'
                    . '<p style="font-size:13px;color:#666;">This link works until {{ expires_date }}.</p>',
                'body2' => '<p style="font-size:13px;color:#666;">Questions about this payment? Please contact {{ sender_name }}. '
                    . 'For help with eCheckSystems, email {{ support_email }}.</p>',
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('check_deliveries');
        Schema::dropIfExists('payee_accounts');
        DB::table('email_templates')->where('id', 18)->delete();
    }
};
