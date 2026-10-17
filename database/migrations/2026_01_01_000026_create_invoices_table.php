<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('invoice_number')->nullable();
            $table->unsignedInteger('user_id')->default(0);
            $table->unsignedInteger('hosting_id')->default(0);
            $table->unsignedInteger('domain_id')->default(0);
            $table->text('reminder')->nullable();
            $table->decimal('amount', 28, 18)->default(0);
            $table->decimal('refund_amount', 28, 18)->default(0);
            $table->text('admin_notes')->nullable();
            $table->boolean('status')->default(0);
            $table->string('due_date', 40)->nullable();
            $table->string('paid_date', 40)->nullable();
            $table->string('next_due_date', 40)->nullable();
            $table->string('created', 40)->nullable();
            $table->string('last_cron', 40)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('invoices');
    }
};
