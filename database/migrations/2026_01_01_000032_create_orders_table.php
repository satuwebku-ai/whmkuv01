<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('invoice_id')->default(0);
            $table->unsignedInteger('coupon_id')->default(0);
            $table->unsignedInteger('user_id')->default(0);
            $table->decimal('amount', 28, 18)->default(0);
            $table->decimal('discount', 28, 8)->default(0);
            $table->decimal('after_discount', 28, 8)->default(0);
            $table->text('ip_address');
            $table->text('admin_notes')->nullable();
            $table->boolean('status')->default(1);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('orders');
    }
};
