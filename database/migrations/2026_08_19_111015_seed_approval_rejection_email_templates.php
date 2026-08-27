<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!DB::table('email_templates')->where('id', 15)->exists()) {
            DB::table('email_templates')->insert([
                'id' => 15,
                'name' => 'Account Approved',
                'subject' => 'Your Account Has Been Approved - eCheckSystems',
                'head' => 'Account Approved',
                'content' => 'Dear {{ name }},',
                'body1' => '<p>Great news! Your eCheckSystems account has been reviewed and approved.</p>
                <p>You can now log in and start using all the features and services available with your account.</p>',
                'body2' => '<p>If you have any questions, please contact our support team.</p><p>Thank you for choosing eCheckSystems!</p>',
            ]);
        }

        if (!DB::table('email_templates')->where('id', 16)->exists()) {
            DB::table('email_templates')->insert([
                'id' => 16,
                'name' => 'Account Rejected',
                'subject' => 'Account Registration Update - eCheckSystems',
                'head' => 'Account Update',
                'content' => 'Dear {{ name }},',
                'body1' => '<p>If you believe this is an error, please contact our support team for further assistance.</p>',
                'body2' => '<p>Thank you,<br>eCheckSystems Team</p>',
            ]);
        }
    }

    public function down(): void
    {
        DB::table('email_templates')->whereIn('id', [15, 16])->delete();
    }
};
