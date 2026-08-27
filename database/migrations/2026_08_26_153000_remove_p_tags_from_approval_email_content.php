<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // content is rendered with {{ }} (escaped), so keep it as plain text only.
        // body1/body2 already use {!! !!} and can keep HTML.
        DB::table('email_templates')->where('id', 15)->update([
            'content' => 'Dear {{ name }}, Great news! Your eCheckSystems account has been reviewed and approved. You can now log in and start using all the features of your account.',
        ]);

        DB::table('email_templates')->where('id', 16)->update([
            'content' => 'Dear {{ name }}, Thank you for your interest in eCheckSystems. After reviewing your registration, we are unable to approve your account at this time.',
        ]);
    }

    public function down(): void
    {
        DB::table('email_templates')->where('id', 15)->update([
            'content' => '<p>Dear {{ name }},</p><p>Great news! Your eCheckSystems account has been reviewed and approved.</p><p>You can now log in and start using all the features of your account.</p>',
        ]);

        DB::table('email_templates')->where('id', 16)->update([
            'content' => '<p>Dear {{ name }},</p><p>Thank you for your interest in eCheckSystems.</p><p>After reviewing your registration, we are unable to approve your account at this time.</p>',
        ]);
    }
};
