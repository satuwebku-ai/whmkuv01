<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('domains', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->boolean('invoice')->default(1);
            $table->unsignedInteger('user_id')->default(0);
            $table->unsignedInteger('order_id')->default(0);
            $table->unsignedInteger('deposit_id')->default(0);
            $table->unsignedInteger('coupon_id')->default(0);
            $table->unsignedInteger('hosting_id')->default(0);
            $table->unsignedInteger('domain_setup_id')->default(0);
            $table->unsignedInteger('domain_register_id')->default(0);
            $table->integer('contact_id')->default(0);
            $table->integer('customer_id')->default(0);
            $table->integer('resell_order_id')->default(0);
            $table->string('domain', 255);
            $table->text('admin_notes')->nullable();
            $table->string('ns1', 255)->nullable();
            $table->string('ns2', 255)->nullable();
            $table->string('ns3', 255)->nullable();
            $table->string('ns4', 255)->nullable();
            $table->integer('whois_guard')->default(0);
            $table->boolean('id_protection')->default(0);
            $table->decimal('first_payment_amount', 28, 16)->default(0);
            $table->decimal('recurring_amount', 28, 16)->default(0);
            $table->decimal('discount', 28, 16)->default(0);
            $table->boolean('status')->default(2);
            $table->integer('reg_period')->default(1);
            $table->string('expiry_date', 40)->nullable();
            $table->string('next_due_date', 40)->nullable();
            $table->string('next_invoice_date', 40)->nullable();
            $table->string('reg_date', 40)->nullable();
            $table->string('last_cron', 40)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('domains');
    }
};
