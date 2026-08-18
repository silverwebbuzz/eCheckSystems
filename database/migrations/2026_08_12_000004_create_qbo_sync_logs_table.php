<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('qbo_sync_logs')) {
            return;
        }

        Schema::create('qbo_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('qbo_company_id')->nullable();
            $table->string('direction', 20); // inbound | outbound
            $table->string('action', 50); // sync_checks | push_check | update_check | delete_check | mark_printed
            $table->string('status', 20); // success | error
            $table->unsignedInteger('records')->default(0);
            $table->text('message')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'qbo_company_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qbo_sync_logs');
    }
};
