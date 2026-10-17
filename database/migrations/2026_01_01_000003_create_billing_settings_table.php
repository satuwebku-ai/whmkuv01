<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('billing_settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('create_default_invoice_days')->default(0);
            $table->text('create_invoice');
            $table->integer('create_domain_invoice_days')->default(0);
            $table->integer('invoice_send_reminder_days')->default(0);
            $table->integer('invoice_first_over_due_reminder')->default(0);
            $table->integer('invoice_second_over_due_reminder')->default(0);
            $table->integer('invoice_third_over_due_reminder')->default(0);
            $table->integer('late_fee_days')->default(0);
            $table->decimal('invoice_late_fee_amount', 28, 16)->default(0);
            $table->tinyInteger('invoice_late_fee_type')->default(1);
            $table->boolean('invoice_send_reminder')->default(0);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('billing_settings');
    }
};
