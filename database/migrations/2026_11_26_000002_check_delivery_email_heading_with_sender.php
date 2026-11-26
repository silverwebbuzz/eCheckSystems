<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The "You have a check" email heading should name the sender, like Deluxe:
 * "You have a check from {{ sender_name }}". Only updates the original text, so a heading
 * changed by an admin in Email Templates is left alone.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('email_templates')
            ->where('id', 18)
            ->where('head', 'You have a check')
            ->update(['head' => 'You have a check from {{ sender_name }}']);
    }

    public function down(): void
    {
        DB::table('email_templates')
            ->where('id', 18)
            ->where('head', 'You have a check from {{ sender_name }}')
            ->update(['head' => 'You have a check']);
    }
};
