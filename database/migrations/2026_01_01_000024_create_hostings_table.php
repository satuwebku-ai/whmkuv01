<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('hostings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->boolean('invoice')->default(1);
            $table->string('plesk_customer_id', 255)->nullable();
            $table->unsignedInteger('user_id')->default(0);
            $table->unsignedInteger('order_id')->default(0);
            $table->unsignedInteger('product_id')->default(0);
            $table->unsignedInteger('server_id')->default(0);
            $table->unsignedInteger('deposit_id')->default(0);
            $table->unsignedInteger('domain_setup_id')->default(0);
            $table->string('domain', 255)->nullable();
            $table->string('package_name', 255)->nullable();
            $table->decimal('first_payment_amount', 28, 18)->default(0);
            $table->decimal('recurring_amount', 28, 18)->default(0);
            $table->decimal('setup_fee', 28, 8)->default(0);
            $table->decimal('discount', 28, 16)->default(0);
            $table->boolean('status')->default(2);
            $table->boolean('billing_cycle')->default(1);
            $table->boolean('stock_control')->nullable();
            $table->string('username', 255)->nullable();
            $table->string('password', 255)->nullable();
            $table->string('ns1', 255)->nullable();
            $table->string('ns2', 255)->nullable();
            $table->string('ns3', 255)->nullable();
            $table->string('ns4', 255)->nullable();
            $table->string('ns1_ip', 255)->nullable();
            $table->string('ns2_ip', 255)->nullable();
            $table->string('ns3_ip', 255)->nullable();
            $table->string('ns4_ip', 255)->nullable();
            $table->string('dedicated_ip', 255)->nullable();
            $table->string('ip', 255)->nullable();
            $table->text('assigned_ips')->nullable();
            $table->text('admin_notes')->nullable();
            $table->string('suspend_reason', 255)->nullable();
            $table->string('termination_date', 40)->nullable();
            $table->string('suspend_date', 40)->nullable();
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
        Schema::dropIfExists('hostings');
    }
};
