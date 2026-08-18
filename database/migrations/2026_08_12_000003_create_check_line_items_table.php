<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('check_line_items')) {
            return;
        }

        Schema::create('check_line_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('CheckID');
            $table->unsignedInteger('line_no')->default(1);
            $table->string('qbo_line_id', 50)->nullable();
            $table->string('qbo_account_id', 50)->nullable();
            $table->string('account_name', 255)->nullable();
            $table->string('description', 500)->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->boolean('billable')->default(false);
            $table->boolean('tax')->default(false);
            $table->string('customer_name', 255)->nullable();
            $table->string('customer_ref', 50)->nullable();
            $table->enum('source', ['qbo', 'local'])->default('qbo');
            $table->timestamps();

            $table->index('CheckID');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('check_line_items');
    }
};
